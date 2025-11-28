<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class HyperApiClient
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $token;

    public function __construct()
    {
        // Load API credentials from config/services.php
        $this->baseUrl = rtrim(config('services.hypertech.base_url'), '/');
        $this->apiKey  = config('services.hypertech.api_key');
        $this->token   = config('services.hypertech.token');
    }

    public function getProducts(array $filters = []): array
    {
        // Filters normalized before sending to API
        $filters = $this->normalizeFilters($filters);

        // Remove null values from query
        $filters = array_filter($filters, fn($v) => $v !== null);

        // API expects boolean as string
        if (isset($filters['detailed'])) {
            $filters['detailed'] = $filters['detailed'] ? 'true' : 'false';
        }

        // Build URL with query string
        $url = $this->baseUrl . '/Products/List?' . http_build_query($filters);

        Log::info("HyperApiClient request", ['url' => $url]);

        $start = microtime(true);

        try {
            // Call API using POST as required by docs
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'x-api-key'     => $this->apiKey,
                'Accept'        => 'application/json'
            ])->post($url);

            $duration = round((microtime(true) - $start) * 1000, 2);

            Log::info("HyperApiClient response", [
                'status'   => $response->status(),
                'duration' => $duration . ' ms'
            ]);

            // Throw error if non-200
            $response->throw();

            return $response->json();

        } catch (Exception $e) {

            // Error details logged for debugging
            Log::error("HyperApiClient error", [
                'url'     => $url,
                'filters' => $filters,
                'error'   => $e->getMessage(),
            ]);

            // Controller can detect error easily
            return [
                'data'    => [],
                'error'   => true,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getCategories(): array
    {
        $url = $this->baseUrl . '/Categories';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->token,
                'x-api-key'     => $this->apiKey,
            ])->get($url);

            $response->throw();

            return $response->json('data') ?? [];

        } catch (Exception $e) {
            Log::error("HyperApiClient categories error", [
                'url'   => $url,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    // Converts incoming request parameters into API-friendly format
    protected function normalizeFilters(array $filters): array
    {
        return [
            'page'              => isset($filters['page']) ? (int)$filters['page'] : 1,
            'pageSize'          => isset($filters['pageSize']) ? (int)$filters['pageSize'] : 20,
            'productCategoryID' => $filters['productCategoryID'] ?? null,
            'productID'         => $filters['productID'] ?? null,
            'detailed'          => $filters['detailed'] ?? true,
        ];
    }
}
