<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CloudCartController extends Controller
{
    public function showUploadForm()
    {
        return view('cloudcart.upload');
    }

    public function handleUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->storeAs('csv_uploads', $file->getClientOriginalName());

        $response = $this->processCsvApi(Storage::path($path));

        // Başarı ve hata mesajlarını session'a kaydet
        session()->flash('upload_results', $response->getData(true));

        return redirect()->back();
    }

    public function processCsvApi($filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return response()->json(['error' => 'CSV dosyası açılamadı.'], 500);
        }

        $header = fgetcsv($handle);
        $responses = [];

        while (($row = fgetcsv($handle)) !== false) {
            $productData = array_combine($header, $row);

            $categoryName = !empty($productData['category']) ? $productData['category'] : 'Default Category';

            // CloudCart'taki kategorileri al
            $categoryResponse = Http::withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'X-CloudCart-ApiKey' => env('CLOUDCART_API_KEY'),
                'Authorization' => 'Bearer ' . env('CLOUDCART_BEARER_TOKEN'),
            ])->get('https://lkziv.cloudcart.net/api/v2/categories');

            $categories = $categoryResponse->json();
            $existingCategory = collect($categories['data'])->firstWhere('attributes.name', $categoryName);

// Eğer kategori yoksa, yeni oluştur
            if (!$existingCategory) {
                $categoryCreateResponse = Http::withHeaders([
                    'Accept' => 'application/vnd.api+json',
                    'Content-Type' => 'application/vnd.api+json',
                    'X-CloudCart-ApiKey' => env('CLOUDCART_API_KEY'),
                    'Authorization' => 'Bearer ' . env('CLOUDCART_BEARER_TOKEN'),
                ])->post('https://lkziv.cloudcart.net/api/v2/categories', [
                    'data' => [
                        'type' => 'categories',
                        'attributes' => ['name' => $categoryName]
                    ]
                ]);

                $categoryData = $categoryCreateResponse->json();
                $defaultCategoryId = $categoryData['data']['id'] ?? null; // Yeni oluşturulan kategorinin ID'sini al
            } else {
                $defaultCategoryId = $existingCategory['id']; // Var olan kategorinin ID'sini al
            }

// Eğer ID belirlenemediyse, varsayılan bir ID kullan
            if (!$defaultCategoryId) {
                return response()->json(['error' => 'Kategori ID alınamadı.'], 500);
            }

            // Ürünü veritabanına kaydet
            $product = Product::create([
                'name' => $productData['name'] ?? '',
                'sku' => $productData['sku'] ?? '',
                'price' => $productData['price'] ?? 0,
                'quantity' => $productData['quantity'] ?? 0,
                'brand' => $productData['brand'] ?? '',
                'category' => $categoryName,
                'description' => $productData['description'] ?? '',
                'image_url' => $productData['image_url'] ?? ''
            ]);

            // CloudCart API için formatı ayarla
            $payload = [
                'data' => [
                    'type' => 'products',
                    'attributes' => [
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'quantity' => $product->quantity,
                        'description' => $product->description,
                    ],
                    'relationships' => [
                        'category' => [
                            'data' => [
                                'type' => 'categories',
                                'id' => $defaultCategoryId
                            ]
                        ]
                    ]
                ]
            ];

            // CloudCart API'ye POST isteği gönder
            $endpoint = 'https://lkziv.cloudcart.net/api/v2/products';
            $response = Http::withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'X-CloudCart-ApiKey' => env('CLOUDCART_API_KEY'),
                'Authorization' => 'Bearer ' . env('CLOUDCART_BEARER_TOKEN'),
            ])->post($endpoint, $payload);

            $responseData = $response->json();
            $statusCode = $response->status();

            $responses[] = [
                'product_name' => $productData['name'] ?? 'Unknown',
                'status_code' => $statusCode,
                'response_data' => $responseData
            ];

            // API Logları Kaydet
            ApiLog::create([
                'request_type' => 'POST',
                'endpoint' => $endpoint,
                'request_data' => $payload,
                'response_data' => $responseData,
                'status_code' => $statusCode,
            ]);
        }

        fclose($handle);

        return response()->json([
            'message' => 'Ürünler işlendi ve database e kayıt edildi aynı zamanda CloudCart API\'ye gönderildi.',
            'responses' => $responses,
        ]);
    }
}
