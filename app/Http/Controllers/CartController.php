<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use Illuminate\Http\Request;


class CartController extends Controller
{
    public function addCart(Request $request)
    {
        $request->validate([
            'qty' => 'required|integer|min:1',
            'proId' => 'required|integer|exists:products,id'
        ]);

        try {
            $userId = auth()->id();
            $qty = $request->qty;
            $productId = $request->proId;

            $cart = Cart::firstOrCreate(
                ['user_id' => $userId],
                ['total_amount' => 0, 'created_at' => now()]
            );

            $product = Product::findOrFail($productId);
            $price = $product->sale_price > 0 ? $product->sale_price : $product->regular_price;

            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $productId)
                ->where('status', 0)
                ->first();

            if ($cartItem) {
                $cartItem->update([
                    'price' => $price,
                    'quantity' => $cartItem->quantity + $qty,
                    'updated_at' => now()
                ]);
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'price' => $price,
                    'quantity' => $qty,
                    'status' => 0,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            $cart->total_amount += ($price * $qty);
            $cart->updated_at = now();
            $cart->save();

            return response()->json([
                'status' => 200,
                'message' => 'Product added to cart successfully',
                'cart_total' => $cart->total_amount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to add product to cart',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function cartItems(Request $request)
    {
        $userId = auth()->id();

        $cart = Cart::where('user_id', $userId)->with('items.product')->first();

        if (!$cart) {
            return response()->json([
                'status' => 404,
                'message' => 'Cart not found for user',
                'items' => [],
            ]);
        }

        $items = $cart->items->where('status', 0)->map(function ($item) {
            return [
                'cart_item_id' => $item->id,
                'product_id'   => $item->product->id,
                'product_name' => $item->product->name,
                'thumbnail'    => $item->product->thumbnail,
                'thumbnail_url' => route('serve.image', ['filename' =>  $item->product->thumbnail]),
                'regular_price' => $item->product->regular_price,
                'sale_price'   => $item->product->sale_price,
                'price'        => $item->price,
                'quantity'     => $item->quantity,
                'total'        => $item->price * $item->quantity
            ];
        })->values();

        return response()->json([
            'status' => 200,
            'message' => 'Cart items fetched successfully',
            'total_amount' => $cart->total_amount,
            'items' => $items,
        ]);
    }


    public function removeCartItemApi($id)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'status' => 401,
                'message' => 'Unauthorized. Please log in.'
            ], 401);
        }

        $cartItem = CartItem::find($id);
        if (!$cartItem) {
            return response()->json([
                'status' => 404,
                'message' => 'Cart item not found.'
            ], 404);
        }

        $cart = Cart::find($cartItem->cart_id);
        if (!$cart) {
            return response()->json([
                'status' => 404,
                'message' => 'Cart not found.'
            ], 404);
        }

        if ($cart->user_id !== $user->id) {
            return response()->json([
                'status' => 403,
                'message' => 'Access denied. You do not own this cart.'
            ], 403);
        }

        $cartItem->delete();

        $cartItems = $cart->cartItems;
        $totalAmount = 0;
        if ($cartItems && $cartItems->count() > 0) {
            $totalAmount = $cartItems->sum(function ($item) {
                return ($item->price ?? 0) * ($item->quantity ?? 0);
            });
        }

        $cart->total_amount = $totalAmount;
        $cart->updated_at = now();
        $cart->save();

        return response()->json([
            'status' => 200,
            'message' => 'Cart item removed successfully.',
            'total_amount' => $totalAmount,
            'remaining_items' => $cartItems ? $cartItems->count() : 0,
            'cart_empty' => $cartItems ? $cartItems->count() === 0 : true
        ]);
    }

      public function increaseQuantity($id)
    {
        return $this->updateQuantity($id, 1);
    }

    public function decreaseQuantity($id)
    {
        return $this->updateQuantity($id, -1);
    }

    private function updateQuantity($cartItemId, $amount)
    {
        $userId = auth()->id();
        $cartItem = CartItem::where('id', $cartItemId)
                            ->whereHas('cart', function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            })
                            ->first();

        if (!$cartItem) {
            return response()->json(['message' => 'Cart item not found'], 404);
        }
        
        if ($cartItem->quantity + $amount < 1) {
            return response()->json(['message' => 'Quantity cannot be less than 1'], 400);
        }

        $cartItem->quantity += $amount;
        $cartItem->save();
        
        $cart = $cartItem->cart;
        $cart->load('items');
        $totalAmount = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $cart->total_amount = $totalAmount;
        $cart->save();

        return response()->json(['status' => 200, 'message' => 'Quantity updated']);
    }
}
