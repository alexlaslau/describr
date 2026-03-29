<?php

use App\Http\Controllers\Api\Products\ProductController;
use App\Http\Controllers\Api\Products\ProductDescriptionController;
use App\Http\Middleware\VerifyHmacSignature;
use Illuminate\Support\Facades\Route;

Route::middleware(VerifyHmacSignature::class)->group(function () {
    Route::post('/products', [ProductController::class, 'store'])
        ->name('api.products.store');
    Route::get('/products/{product}/description', [ProductDescriptionController::class, 'show'])
        ->name('api.products.description.show');
});
