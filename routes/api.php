<?php

use App\Http\Controllers\AccessUserController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FacebookAuthController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Cors;
use App\Models\Category;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// routes/api.php

Route::get('/home', [HomeController::class, 'home']);

Route::post('/register', [UserController::class, 'register']);
Route::post('/loginSubmit', [UserController::class, 'loginSubmit'])->name('login');
Route::get('/products/detail/{slug}', [ProductController::class, 'productDetail']);
Route::get('/products/search', [ProductController::class, 'searchProducts']);
Route::get('get-logo',[LogoController::class,'getLogo']);

Route::middleware('web')->group(function () {
    Route::get('/auth/facebook/redirect', [FacebookAuthController::class, 'redirect']);
    Route::get('/auth/facebook/callback', [FacebookAuthController::class, 'callback']);
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [HomeController::class, 'currentUser']);
    Route::post('/cart/add', [CartController::class, 'addCart']);
    Route::get('/cart/items', [CartController::class, 'cartItems']);
    Route::get('/checkout', [OrderController::class, 'checkOutApi']);
    Route::get('/cart-item/{id}', [CartController::class, 'removeCartItemApi']);
    Route::post('/place-order', [OrderController::class, 'placeOrderApi']);
    Route::get('/my-orders', [OrderController::class, 'myOrder']);
    Route::post('/cancel-order/{id}', [OrderController::class, 'cancelOrder']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle', [FavoriteController::class, 'toggle']);

    Route::post('/cart-item/increase/{id}', [CartController::class, 'increaseQuantity']);
    Route::post('/cart-item/decrease/{id}', [CartController::class, 'decreaseQuantity']);
});

Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/current-user', [UserController::class, 'currentUser']);
    Route::get('admin/all-users', [UserController::class, 'getAllUsers']);
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
    Route::post('/admin/product/update/{id}', [ProductController::class, 'updateProductSubmit']);
    Route::delete('/admin/product/delete/{id}', [ProductController::class, 'deleteProduct']);

    Route::get('/admin/access-order/{id}',    [AccessUserController::class, 'accessSubmit']);
    Route::get('/admin/list-order',[AccessUserController::class,'listOrder']);
    Route::get('/admin/list-all-complete-order',[AccessUserController::class,'listAllOrder']);
    Route::get('/admin/total-earning',[AccessUserController::class,'totalEarning']);
    Route::post('/admin/reject-order/{id}',[AccessUserController::class,'rejectOrder']);


});
