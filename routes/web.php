<?php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

 Route::get('/image/{filename}', function ($filename) {
    $path = public_path('uploads/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, OPTIONS',
        'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
    ]);
})->name('serve.image');

