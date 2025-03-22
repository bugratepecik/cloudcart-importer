<?php
// app/Services/CloudCartApiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudCartApiService
{
protected $baseUrl = 'https://lkziv.cloudcart.net/api/v2';  // Site URL'si
protected $apiKey = 'BMVDNQON85LM0ZF6GJWBRD77DX6KLEE3QX0EZTB31R8LWCDQ1LOBUCTWFHX4XNPU';  // API Key

public function getProducts()
{
$response = Http::withHeaders([
'Accept' => 'application/vnd.api+json',
'Content-Type' => 'application/vnd.api+json',
'X-CloudCart-ApiKey' => $this->apiKey,
])
->get("{$this->baseUrl}/products");

return $response->json();  // JSON cevabı döndürüyoruz
}
}
