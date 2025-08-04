<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $favoriteProducts = $user->favorites()->with('attributes')->get();

        $favoriteProducts->transform(function ($product) {
            $product->thumbnail_url = route('serve.image', ['filename' => $product->thumbnail]);
            return $product;
        });

        return response()->json(['status' => 200, 'data' => $favoriteProducts]);
    }

     public function toggle(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        
        $user = auth()->user(); 

        $user->favorites()->toggle($request->product_id);

        return response()->json([
            'status' => 200,
            'message' => 'Favorite status changed successfully',
        ]);
    }

}
