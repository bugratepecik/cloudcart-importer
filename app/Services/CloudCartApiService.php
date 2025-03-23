<?php
// app/Services/CloudCartApiService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CloudCartApiService
{

    protected string $baseUrl;
    protected array $headers;

    public function __construct()
    {
        $this->baseUrl = 'https://lkziv.cloudcart.net/api/v2';
        $this->headers = [
            'Accept'        => 'application/vnd.api+json',
            'Content-Type'  => 'application/vnd.api+json',
            'X-CloudCart-ApiKey' => env('CLOUDCART_API_KEY'),
            'Authorization' => 'Bearer ' . env('CLOUDCART_BEARER_TOKEN'),
        ];
    }

    public function getProducts()
    {
        $response = Http::withHeaders($this->headers)->get("{$this->baseUrl}/products");

        return $response->json();
    }

    public function createProduct(array $data)
    {
        $response = Http::withHeaders($this->headers)->post("{$this->baseUrl}/products", $data);

        return $response->json();
    }
}
