<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request, ProductService $products)
    {

        Log::info('REQUEST DEBUG', [
            'full_url' => request()->fullUrl(),
            'scheme' => request()->getScheme(),
            'is_secure' => request()->isSecure(),
            'server_port' => request()->server('SERVER_PORT'),
            'x_forwarded_proto' => request()->header('X-Forwarded-Proto'),
            'x_forwarded_ssl' => request()->header('X-Forwarded-Ssl'),
            'headers' => request()->headers->all(),
        ]);


        // Build filter set from request query params
        $filters = [
            'page'              => $request->get('page', 0),
            'pageSize'          => $request->get('pageSize', 20),
            'productCategoryID' => $request->get('categoryID'),
            'detailed'          => true,
        ];

        Log::info("ProductController: listing products", [
            'filters' => $filters
        ]);

        // Fetch list from service (with cache)
        $result = $products->list($filters);

        // Ensure minimal safe defaults
        $productData = $result['data'] ?? [];
        $hasMore     = $result['hasMore'] ?? false;

        return view('products.index', [
            'products'         => $productData,
            'hasMore'          => $hasMore,
            'currentPage'      => $filters['page'],
            'pageSize'         => $filters['pageSize'],
            'categories'       => $products->categories(),
            'selectedCategory' => $filters['productCategoryID'],
        ]);
    }
}
