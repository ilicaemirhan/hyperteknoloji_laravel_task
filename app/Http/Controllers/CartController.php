<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class CartController extends Controller
{
    /**
     * Add a new item to the cart.
     */
    public function add(Request $request, CartService $cart)
    {
        try {
            // Data comes directly from form submit
            $cart->addItem([
                'product_id' => $request->product_id,
                'name'       => $request->name,
                'price'      => $request->price,
                'image_url'  => $request->image_url,
            ]);

            return redirect()->back()->with('success', 'Ürün sepete eklendi!');
        } catch (Exception $e) {
            Log::error('CartController@add failed', [
                'input' => $request->all(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Ürün sepete eklenemedi, lütfen tekrar deneyin.');
        }
    }

    /**
     * Display the current cart.
     */
    public function show(CartService $cart)
    {
        try {
            return view('cart.index', [
                'items' => $cart->items(),
                'total' => $cart->total(),
            ]);
        } catch (Exception $e) {
            Log::error('CartController@show failed', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Sepet görüntülenirken bir hata oluştu.');
        }
    }

    /**
     * Increase quantity of selected item.
     */
    public function increment($id, CartService $cart)
    {
        try {
            $cart->incrementItem($id);
            return back();
        } catch (Exception $e) {
            Log::error('CartController@increment failed', [
                'item_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Ürün adedi artırılamadı.');
        }
    }

    /**
     * Decrease quantity or remove item if qty becomes zero.
     */
    public function decrement($id, CartService $cart)
    {
        try {
            $cart->decrementItem($id);
            return back();
        } catch (Exception $e) {
            Log::error('CartController@decrement failed', [
                'item_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Ürün adedi azaltılamadı.');
        }
    }

    /**
     * Completely remove item from cart.
     */
    public function remove($id, CartService $cart)
    {
        try {
            $cart->removeItem($id);
            return back();
        } catch (Exception $e) {
            Log::error('CartController@remove failed', [
                'item_id' => $id,
                'error'   => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Ürün sepetten kaldırılamadı.');
        }
    }
}
