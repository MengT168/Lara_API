<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function home()
{
    $newProducts = Product::with('attributes')
        ->orderByDesc('id')
        ->limit(4)
        ->get();

    $promotionProducts = Product::with('attributes')
        ->where('sale_price', '>', 0)
        ->limit(4)
        ->get();

    $popularProducts = Product::with('attributes')
        ->orderByDesc('viewer')
        ->limit(4)
        ->get();

    return response()->json([
        'status' => 200,
        'data' => [
            'new_products' => $this->transformProducts($newProducts),
            'promotion_products' => $this->transformProducts($promotionProducts),
            'popular_products' => $this->transformProducts($popularProducts),
        ]
    ]);
}

public function currentUser(){
    return Auth::user();
}

}
