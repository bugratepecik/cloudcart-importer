<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ApiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class CloudCartController extends Controller
{
    /**
     * Display the upload form.
     */
    public function showUploadForm()
    {
        return view('cloudcart.upload');
    }

    /**
     * Handle the CSV file upload and initiate processing.
     */
    public function handleUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->storeAs('csv_uploads', $file->getClientOriginalName());

        $response = $this->processCsvApi(Storage::path($path));

        // Store success and error messages in the session
        session()->flash('upload_results', json_decode($response->getContent(), true));

        return redirect()->back();
    }

    /**
     * Process the uploaded CSV file and send data to the CloudCart API.
     */
    public function processCsvApi($filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return response()->json([
                'responses' => [['product_name' => 'CSV Upload', 'status_code' => 500, 'message' => 'Failed to open CSV file.']]
            ], 500);
        }

        $header = fgetcsv($handle);
        $responses = [];

        while (($row = fgetcsv($handle)) !== false) {
            $productData = array_combine($header, $row);
            $sku = $productData['sku'] ?? '';

            if (empty($sku)) {
                $responses[] = [
                    'product_name' => $productData['name'] ?? 'Unknown',
                    'status_code' => 400,
                    'message' => "SKU in this product does not exist!"
                ];
                continue;
            }

            // Check if SKU already exists
            if (Product::where('sku', $sku)->exists()) {
                $responses[] = [
                    'product_name' => $productData['name'] ?? 'Unknown',
                    'status_code' => 400,
                    'message' => "SKU ({$sku}) already exists!"
                ];
                continue;
            }

            // Category check
            $categoryName = !empty($productData['category']) ? $productData['category'] : 'Default Category';

            $categoryResponse = Http::withHeaders([
                'Accept' => 'application/vnd.api+json',
                'Content-Type' => 'application/vnd.api+json',
                'X-CloudCart-ApiKey' => env('CLOUDCART_API_KEY'),
                'Authorization' => 'Bearer ' . env('CLOUDCART_BEARER_TOKEN'),
            ])->get('https://lkziv.cloudcart.net/api/v2/categories');

            $categories = $categoryResponse->json();
            $existingCategory = collect($categories['data'])->firstWhere('attributes.name', $categoryName);

            // Create category if it does not exist
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

                if (!$categoryCreateResponse->successful()) {
                    $responses[] = [
                        'product_name' => $productData['name'] ?? 'Unknown',
                        'status_code' => 500,
                        'message' => "Failed to create category: {$categoryName}"
                    ];
                    continue;
                }

                $categoryData = $categoryCreateResponse->json();
                $defaultCategoryId = $categoryData['data']['id'] ?? null;
            } else {
                $defaultCategoryId = $existingCategory['id'];
            }

            // Ensure category ID is available
            if (!$defaultCategoryId) {
                $responses[] = [
                    'product_name' => $productData['name'] ?? 'Unknown',
                    'status_code' => 500,
                    'message' => "Failed to retrieve category ID."
                ];
                continue;
            }

            // Save product to database
            try {
                $product = Product::create([
                    'name' => $productData['name'] ?? '',
                    'sku' => $sku,
                    'price' => $productData['price'] ?? 0,
                    'quantity' => $productData['quantity'] ?? 0,
                    'brand' => $productData['brand'] ?? '',
                    'category' => $categoryName,
                    'description' => $productData['description'] ?? '',
                    'image_url' => $productData['image_url'] ?? ''
                ]);
            } catch (\Exception $e) {
                $responses[] = [
                    'product_name' => $productData['name'] ?? 'Unknown',
                    'status_code' => 500,
                    'message' => "Database error: SKU ({$sku}) could not be added."
                ];
                continue;
            }

            // Prepare API payload
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

            // Send product to CloudCart API
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
                'response_data' => $responseData,
                'message' => $response->status() === 201 ? "Successfully added!" : "API error!",
            ];

            // Save API logs
            ApiLog::create([
                'request_type' => 'POST',
                'endpoint' => $endpoint,
                'request_data' => $payload,
                'response_data' => $responseData,
                'status_code' => $statusCode,
            ]);
        }

        fclose($handle);

        return response()->json(['responses' => $responses]);
    }
}
