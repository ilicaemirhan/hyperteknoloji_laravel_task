<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

class CartService
{
    /**
     * Find or create a cart tied to the current session.
     */
    public function getCart(): Cart
    {
        try {
            // Use a session-level UUID as cart identifier
            $sessionId = session()->get('cart_session_id');

            if (!$sessionId) {
                $sessionId = Str::uuid()->toString();
                session()->put('cart_session_id', $sessionId);
            }

            // Each session has one cart
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        } catch (Exception $e) {
            Log::error('CartService@getCart', ['error' => $e->getMessage()]);
            throw new Exception("Unable to get cart.");
        }
    }

    /**
     * Add product to cart or increase quantity if it already exists.
     */
    public function addItem(array $data): CartItem
    {
        try {
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
        } catch (Exception $e) {
            Log::error('CartService@addItem', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to add item.");
        }
    }

    /**
     * Fetch an item belonging to the current user's cart.
     */
    protected function getItemInCart(int $itemId): CartItem
    {
        try {
            $cart = $this->getCart();

            return $cart->items()
                ->where('id', $itemId)
                ->firstOrFail();
        } catch (Exception $e) {
            Log::error('CartService@getItemInCart', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Cart item not found.");
        }
    }

    /**
     * Increase quantity of a specific cart item.
     */
    public function incrementItem($itemId): void
    {
        try {
            $item = $this->getItemInCart($itemId);
            $item->increment('qty');
        } catch (Exception $e) {
            Log::error('CartService@incrementItem', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to increment quantity.");
        }
    }

    /**
     * Decrease quantity or remove item if quantity becomes zero.
     */
    public function decrementItem($itemId): void
    {
        try {
            $item = $this->getItemInCart($itemId);

            if ($item->qty <= 1) {
                $item->delete();
                return;
            }

            $item->decrement('qty');
        } catch (Exception $e) {
            Log::error('CartService@decrementItem', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to decrement quantity.");
        }
    }

    /**
     * Remove a single item from cart.
     */
    public function removeItem($itemId): void
    {
        try {
            $item = $this->getItemInCart($itemId);
            $item->delete();
        } catch (Exception $e) {
            Log::error('CartService@removeItem', [
                'item_id' => $itemId,
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to remove item.");
        }
    }

    /**
     * Completely clear cart.
     */
    public function clear(): void
    {
        try {
            $cart = $this->getCart();
            $cart->items()->delete();
        } catch (Exception $e) {
            Log::error('CartService@clear', [
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to clear cart.");
        }
    }

    /**
     * List all items in the cart.
     */
    public function items()
    {
        try {
            return $this->getCart()->items()->get();
        } catch (Exception $e) {
            Log::error('CartService@items', [
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to get cart items.");
        }
    }

    /**
     * Calculate total cart price.
     */
    public function total(): float
    {
        try {
            return $this->getCart()
                ->items
                ->sum(fn($i) => $i->qty * $i->price);
        } catch (Exception $e) {
            Log::error('CartService@total', [
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to calculate total.");
        }
    }

    /**
     * Total quantity of all items (sum of qty).
     */
    public function quantity(): int
    {
        try {
            return $this->getCart()
                ->items()
                ->sum('qty');
        } catch (Exception $e) {
            Log::error('CartService@quantity', [
                'error' => $e->getMessage()
            ]);
            throw new Exception("Unable to calculate quantity.");
        }
    }
}
