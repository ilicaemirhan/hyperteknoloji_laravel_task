<?php

namespace App\Http\Controllers;

use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Add a new item to the cart.
     */
    public function add(Request $request, CartService $cart)
    {
        // Data comes directly from form submit
        $cart->addItem([
            'product_id' => $request->product_id,
            'name'       => $request->name,
            'price'      => $request->price,
            'image_url'  => $request->image_url,
        ]);

        return redirect()->back()->with('success', 'Ürün sepete eklendi!');
    }

    /**
     * Display the current cart.
     */
    public function show(CartService $cart)
    {
        return view('cart.index', [
            'items' => $cart->items(),
            'total' => $cart->total(),
        ]);
    }

    /**
     * Increase quantity of selected item.
     */
    public function increment($id, CartService $cart)
    {
        $cart->incrementItem($id);
        return back();
    }

    /**
     * Decrease quantity or remove item if qty becomes zero.
     */
    public function decrement($id, CartService $cart)
    {
        $cart->decrementItem($id);
        return back();
    }

    /**
     * Completely remove item from cart.
     */
    public function remove($id, CartService $cart)
    {
        $cart->removeItem($id);
        return back();
    }
}
