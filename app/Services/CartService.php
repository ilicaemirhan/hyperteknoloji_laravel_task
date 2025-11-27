<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Str;

class CartService
{
    /**
     * Find or create a cart tied to the current session.
     */
    public function getCart(): Cart
    {
        // Use a session-level UUID as cart identifier
        $sessionId = session()->get('cart_session_id');

        if (!$sessionId) {
            $sessionId = Str::uuid()->toString();
            session()->put('cart_session_id', $sessionId);
        }

        // Each session has one cart
        return Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Add product to cart or increase quantity if it already exists.
     */
    public function addItem(array $data): CartItem
    {
        $cart = $this->getCart();

        // Check if the item already exists
        $existing = $cart->items()
            ->where('product_id', $data['product_id'])
            ->first();

        if ($existing) {
            $existing->increment('qty');
            return $existing;
        }

        // Create new item
        return $cart->items()->create([
            'product_id' => $data['product_id'],
            'name'       => $data['name'],
            'price'      => $data['price'],
            'image_url'  => $data['image_url'] ?? null,
            'qty'        => 1,
        ]);
    }

    /**
     * Fetch an item belonging to the current user's cart.
     */
    protected function getItemInCart(int $itemId): CartItem
    {
        $cart = $this->getCart();

        return $cart->items()
            ->where('id', $itemId)
            ->firstOrFail();
    }

    /**
     * Increase quantity of a specific cart item.
     */
    public function incrementItem($itemId): void
    {
        $item = $this->getItemInCart($itemId);
        $item->increment('qty');
    }

    /**
     * Decrease quantity or remove item if quantity becomes zero.
     */
    public function decrementItem($itemId): void
    {
        $item = $this->getItemInCart($itemId);

        if ($item->qty <= 1) {
            $item->delete();
            return;
        }

        $item->decrement('qty');
    }

    /**
     * Remove a single item from cart.
     */
    public function removeItem($itemId): void
    {
        $item = $this->getItemInCart($itemId);
        $item->delete();
    }

    /**
     * Completely clear cart.
     */
    public function clear(): void
    {
        $cart = $this->getCart();
        $cart->items()->delete();
    }

    /**
     * List all items in the cart.
     */
    public function items()
    {
        return $this->getCart()->items()->get();
    }

    /**
     * Calculate total cart price.
     */
    public function total(): float
    {
        return $this->getCart()
            ->items
            ->sum(fn($i) => $i->qty * $i->price);
    }

    /**
     * Total quantity of all items (sum of qty).
     */
    public function quantity(): int
    {
        return $this->getCart()
            ->items()
            ->sum('qty');
    }
}
