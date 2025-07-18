<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Cors;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// routes/api.php

Route::get('/home', [HomeController::class, 'home']);

Route::post('/register', [UserController::class, 'register']);
Route::post('/loginSubmit', [UserController::class, 'loginSubmit'])->name('login');
Route::get('/products/detail/{slug}', [ProductController::class, 'productDetail']);





Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [HomeController::class, 'currentUser']);
    Route::post('/cart/add', [CartController::class, 'addCart']);
    Route::get('/cart/items', [CartController::class, 'cartItems']);
    Route::get('/checkout', [OrderController::class, 'checkOutApi']);
    Route::get('/cart-item/{id}', [CartController::class, 'removeCartItemApi']);
    Route::post('/place-order', [OrderController::class, 'placeOrderApi']);
});

Route::middleware(['auth:sanctum', 'is_admin', Cors::class])->group(function () {
    Route::get('/current-user', [UserController::class, 'currentUser']);
    Route::get('/get-user', [UserController::class, 'getUser']);

    Route::get('/admin/list-category', [CategoryController::class, 'listCategory']);
    Route::post('/admin/add-category', [CategoryController::class, 'addCategorySubmit']);
    Route::put('/admin/category/update/{id}', [CategoryController::class, 'updateCategorySubmit']);
    Route::delete('/admin/category/delete/{id}', [CategoryController::class, 'deleteCategory']);

    Route::post('/admin/add-attribute-submit',   [AttributeController::class, 'addAttributeSubmit']);
    Route::get('/admin/list-attribute', [AttributeController::class, 'listAttribute']);
    Route::put('/admin/attribute/update/{id}', [AttributeController::class, 'updateAttribute']);
    Route::delete('/admin/attribute/delete/{id}', [AttributeController::class, 'deleteAttribute']);

    Route::get('/admin/list-logo', [LogoController::class, 'listLogos']);
    Route::post('/admin/add-logo', [LogoController::class, 'addLogoSubmit']);
    Route::patch('/admin/logo/toggle-status/{id}', [LogoController::class, 'toggleLogoStatus']);
    Route::patch('/admin/logo/update/{id}', [LogoController::class, 'updateLogoSubmit']);
    Route::delete('/admin/logo/delete/{id}', [LogoController::class, 'deleteLogo']);

    Route::post('/admin/add-product-submit', [ProductController::class, 'addProductSubmit']);
    Route::get('/admin/list-product', [ProductController::class, 'listProduct']);
    Route::patch('/admin/product/update/{id}', [ProductController::class, 'updateProductSubmit']);
    Route::delete('/admin/product/delete/{id}', [ProductController::class, 'deleteProduct']);
});
