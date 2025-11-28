<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductService
{
    protected int $ttl;

    public function __construct(protected HyperApiClient $client)
    {
        // Cache ttl is controlled from config/services.php → .env
        $this->ttl = config('services.product_cache_ttl', 60);
    }

    /**
     * Fetch product list with caching.
     */
    public function list(array $filters = []): array
    {
        try {
            $filters = $this->normalizeFilters($filters);

            // Build a stable cache key
            $cacheKey = $this->makeCacheKey($filters);

            Log::info("ProductService: Using cache key {$cacheKey}");

            return Cache::remember($cacheKey, $this->ttl, function () use ($filters) {

                $start = microtime(true);

                // External API call
                $response = $this->client->getProducts($filters);

                $duration = round((microtime(true) - $start) * 1000, 2);

                Log::info("ProductService: API time {$duration}ms", [
                    'filters' => $filters
                ]);

                // Validate response shape
                if (!is_array($response) || !isset($response['data'])) {
                    Log::warning("ProductService: Unexpected API response", [
                        'response' => $response,
                    ]);

                    return [
                        'data'    => [],
                        'hasMore' => false,
                    ];
                }

                $data = $response['data'];

                // If items count equals pageSize → likely has more pages
                $hasMore = count($data) === $filters['pageSize'];

                return [
                    'data'    => $data,
                    'hasMore' => $hasMore,
                ];
            });

        } catch (Exception $e) {
            Log::error("ProductService list() failed", [
                'filters' => $filters ?? null,
                'error'   => $e->getMessage(),
            ]);

            // In case of total failure, return safe empty structure
            return [
                'data'    => [],
                'hasMore' => false,
            ];
        }
    }

    /**
     * Fetch category list with long-term caching.
     */
    public function categories(): array
    {
        try {
            $ttl = config('services.category_cache_ttl', 3600);

            return Cache::remember("categories:list", $ttl, function () {
                $raw = $this->client->getCategories();

                return collect($raw)
                    ->map(fn($c) => [
                        'id'        => $c['ProductCategoryID'],
                        'name'      => $c['CategoryName'],
                        'parent_id' => $c['ParentID'],
                    ])
                    ->sortBy('name')
                    ->values()
                    ->all();
            });

        } catch (Exception $e) {
            Log::error("ProductService categories() failed", [
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Normalize supported filters.
     */
    protected function normalizeFilters(array $filters): array
    {
        return [
            'page'              => (int)($filters['page'] ?? 1),
            'pageSize'          => (int)($filters['pageSize'] ?? 20),
            'productID'         => $filters['productID'] ?? null,
            'productCategoryID' => $filters['productCategoryID'] ?? null,
            'typeID'            => $filters['typeID'] ?? null,
            'search'            => $filters['search'] ?? null,
            'detailed'          => $filters['detailed'] ?? true,
        ];
    }

    /**
     * Create deterministic, collision-safe cache key.
     */
    protected function makeCacheKey(array $filters): string
    {
        $filters = array_filter($filters, fn($v) => $v !== null);

        ksort($filters);

        $query = http_build_query($filters);

        return "products:" . sha1($query);
    }
}
