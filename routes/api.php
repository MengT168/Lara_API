<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


// routes/api.php

Route::post('/register', [UserController::class, 'register']);
Route::post('/loginSubmit',[UserController::class,'loginSubmit'])->name('login');

Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {
    Route::get('/get-user', [UserController::class, 'getUser']); 
});
