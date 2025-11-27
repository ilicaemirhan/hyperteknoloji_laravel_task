<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Services\HyperApiClient;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [ProductController::class, 'index'])->name('products.index');

Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::post('/cart/increment/{id}', [CartController::class, 'increment'])->name('cart.increment');
Route::post('/cart/decrement/{id}', [CartController::class, 'decrement'])->name('cart.decrement');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');



Route::get('/debug-products', function (HyperApiClient $client) {
    $data = $client->getProducts();

    dd($data); // Şimdilik sadece ham JSON görelim
});
