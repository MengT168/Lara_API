<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LogoController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\Cors;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// routes/api.php

Route::post('/register', [UserController::class, 'register']);
Route::post('/loginSubmit', [UserController::class, 'loginSubmit'])->name('login');

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
});
