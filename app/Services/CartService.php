<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItems;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Collection;

class CartService
{
    /**
     * Get cart items for the authenticated user
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCartItems($perPage = 5)
    {
        $userId = Auth::id();
        
        try {
            return ShoppingCart::with(['product' => function($query) {
                $query->select('id', 'name', 'image', 'description');
            }])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
        } catch (\Exception $e) {
            Log::error('Error fetching cart items', [
                'error' => $e->getMessage()
            ]);
            return collect()->paginate($perPage);
        }
    }

    /**
     * Update quantity of an item in the cart
     *
     * @param int $itemId
     * @param int $quantity
     * @return bool
     */
    public function updateQuantity($itemId, $quantity)
    {
        $cartItem = ShoppingCart::where('user_id', Auth::id())
            ->where('id', $itemId)
            ->first();

        if ($cartItem) {
            $cartItem->quantity = max(1, intval($quantity));
            return $cartItem->save();
        }

        return false;
    }

    /**
     * Remove an item from the cart
     *
     * @param int $itemId
     * @return bool
     */
    public function removeItem($itemId)
    {
        return ShoppingCart::where('user_id', Auth::id())
            ->where('id', $itemId)
            ->delete() > 0;
    }

    /**
     * Add a product to the cart
     *
     * @param int $productId
     * @return bool
     */
    public function addToCart($productId)
    {
        DB::beginTransaction();
        try {
            $cartItem = ShoppingCart::where('user_id', Auth::id())
                ->where('product_id', $productId)
                ->first();

            if ($cartItem) {
                $cartItem->quantity += 1;
                $cartItem->save();
            } else {
                $cartItem = ShoppingCart::create([
                    'user_id' => Auth::id(),
                    'product_id' => $productId,
                    'quantity' => 1
                ]);
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error adding to cart', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'product_id' => $productId
            ]);
            return false;
        }
    }

    /**
     * Clear the cart for the authenticated user
     *
     * @return void
     */
    public function clearCart()
    {
        ShoppingCart::where('user_id', Auth::id())->delete();
    }

    /**
     * Create a new order from cart items
     *
     * @return \App\Models\Order|null
     */
    public function createOrderFromCart()
    {
        $cartItems = ShoppingCart::with('product')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return null;
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => Auth::id(),
                'status' => 'pending'
            ]);

            foreach ($cartItems as $item) {
                OrderItems::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity
                ]);
            }

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating order', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            return null;
        }
    }
} 