<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $props = [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ];

    if ($user = Auth::user()) {
        $props['stats'] = $user->getProductStats();
    }

    return Inertia::render('Welcome', $props);
});

Route::get('/dashboard', function () {
    return redirect()->route('products.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('products', ProductController::class)->only(['index', 'show', 'create', 'store']);
    Route::get('/products/{product}/images/download-all', [ProductController::class, 'downloadAllImages'])->name('products.images.download-all');
    Route::get('/products/{product}/images/{image}/download', [ProductController::class, 'downloadImage'])->name('products.images.download');
});

Route::fallback(function () {
    abort(404, 'The page you searched for does not exist.');
});

Route::get('/test-error', function() {
    throw new \RuntimeException("Custom exception triggered");
});

require __DIR__.'/auth.php';
