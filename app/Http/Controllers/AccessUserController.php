<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessUserController extends Controller
{
    public function accessSubmit($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->status = 'complete';
            $order->updated_at = now();
            $order->save();

            $orderItems = OrderItem::where('order_id', $id)->get();

            foreach ($orderItems as $item) {
                Product::where('id', $item->product_id)
                    ->decrement('quantity', $item->quantity);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Order marked as complete and product stock updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function rejectOrder($id)
    {
    

        try {
            DB::beginTransaction();

            $order = Order::find($id);

            if (!$order) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Order not found'
                ], 404);
            }

            $order->status = 'reject';
            $order->updated_at = now();
            $order->save();

            foreach ($order->items as $item) {
                Product::where('id', $item->product_id)
                    ->increment('quantity', $item->quantity);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'message' => 'Order rejected and product stock restored successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 500,
                'message' => 'Failed to reject order',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function listOrder()
    {
        try {
            $orders = Order::with('items')->get();

            $pendingCount = Order::where('status', 'pending')->count();

            return response()->json([
                'status' => 200,
                'message' => 'Orders retrieved successfully',
                'pending_count' => $pendingCount,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ]);
        }
    }
}
