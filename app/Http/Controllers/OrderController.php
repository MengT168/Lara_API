<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function checkOutApi(Request $request)
    {
        $userId = $request->userId;

        $cart = Cart::where('user_id', $userId)->with(['items.product'])->first();

        if (!$cart) {
            return response()->json([
                'status' => 404,
                'message' => 'Cart not found'
            ]);
        }

        $items = $cart->items->where('status', 0)->map(function ($item) {
            return [
                'product_id'   => $item->product_id,
                'product_name' => $item->product->name,
                'thumbnail'    => $item->product->thumbnail,
                'thumbnail_url' => $item->product->thumbnail ? Storage::url($item->product->thumbnail) : null,
                'price'        => $item->price,
                'quantity'     => $item->quantity,
                'total'        => $item->price * $item->quantity,
            ];
        })->values();

        return response()->json([
            'status' => 200,
            'cart_id' => $cart->id,
            'total_amount' => $cart->total_amount,
            'items' => $items,
        ]);
    }

    public function placeOrderApi(Request $request)
    {
        $request->validate([
            'userId' => 'required|integer|exists:users,id',
            'phone' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $userId = $request->userId;
            $transactionId = now()->format('YmdHis');
            $user = User::find($userId);
            $cart = Cart::where('user_id', $userId)->first();

            if (!$cart) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Cart not found'
                ]);
            }

            $cartItems = $cart->items()->where('status', 0)->get();

            if ($cartItems->isEmpty()) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Cart is empty'
                ]);
            }

            $order = Order::create([
                'transaction_id' => $transactionId,
                'user_id' => $userId,
                'fullname' => $user->name,
                'phone' => $request->phone,
                'address' => $request->address,
                'total_amount' => $cart->total_amount,
                'status' => 'pending',
            ]);

            $orderItems = [];

            foreach ($cartItems as $cartItem) {
                $orderItems[] = [
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'price' => $cartItem->price,
                    'quantity' => $cartItem->quantity,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Product::where('id', $cartItem->product_id)
                //     ->decrement('quantity', $cartItem->quantity);
            }

            OrderItem::insert($orderItems);

            $cart->items()->where('status', 0)->update([
                'status' => 1,
                'updated_at' => now()
            ]);

            $cart->update([
                'total_amount' => 0,
                'updated_at' => now()
            ]);

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'transaction_id' => $transactionId
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 500,
                'message' => 'Failed to place order',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function myOrder()
    {
        $userId = Auth::id();

        $orders = Order::where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'status' => 200,
            'data' => $orders
        ]);
    }

    public function cancelOrder($id)
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 404,
                'message' => 'Order not found'
            ], 404);
        }

        $order->status = 'cancel';
        $order->save();

        $orderItems = OrderItem::where('order_id', $id)->get();

        foreach ($orderItems as $item) {
            Product::where('id', $item->product_id)
                ->increment('quantity', $item->quantity);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Order canceled successfully'
        ]);
    }

    
}
