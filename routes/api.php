<?php

use App\Http\Controllers\AttributeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// routes/api.php

Route::post('/register', [UserController::class, 'register']);
Route::post('/loginSubmit',[UserController::class,'loginSubmit'])->name('login');

Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/current-user',[UserController::class,'currentUser']);
    Route::get('/get-user', [UserController::class, 'getUser']); 

    Route::get('/admin/list-category',[CategoryController::class,'listCategory']);
    Route::post('/admin/add-category',[CategoryController::class,'addCategorySubmit']);
    Route::put('/admin/category/update/{id}', [CategoryController::class, 'updateCategorySubmit']);
    Route::delete('/admin/category/delete/{id}', [CategoryController::class, 'deleteCategory']);

    Route::post('/admin/add-attribute-submit',   [AttributeController::class, 'addAttributeSubmit']);
    Route::get('/admin/list-attribute',[AttributeController::class,'listAttribute']);
    Route::put('/admin/attribute/update/{id}', [AttributeController::class, 'updateAttribute']);
    Route::delete('/admin/attribute/delete/{id}', [AttributeController::class, 'deleteAttribute']);

});
