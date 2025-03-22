<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CloudCartApiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


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

        // Dosyayı storage içine kaydet
        $path = $file->storeAs('csv_uploads', $file->getClientOriginalName());

        // CSV dosyasını işle
        return $this->processCsv(Storage::path($path));
    }

    private function processCsv($filePath)
    {
        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return dd('CSV dosyası açılamadı.');
        }

        $header = fgetcsv($handle); // İlk satırı başlık olarak al
        $products = [];

        while (($row = fgetcsv($handle)) !== false) {
            $products[] = array_combine($header, $row); // CSV başlıkları ile veriyi eşleştir
        }

        fclose($handle);

        // API çağrısı yerine dd() ile kontrol edelim
        return dd($products);
    }
}
