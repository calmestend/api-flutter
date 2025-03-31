<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\PayPalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/products', [ProductController::class, 'index']);

Route::get('/cart/{userId}', [CartController::class, 'index']);
Route::post('/cart/add/{userId}/{product}', [CartController::class, 'addProduct']);
Route::post('/cart/remove/{userId}/{product}', [CartController::class, 'removeProduct']);
Route::post('/cart/update/{userId}/{product}', [CartController::class, 'updateProduct']);

Route::get('paypal/create/{userId}', [PayPalController::class, 'createPayment'])->name('paypal.create');
Route::get('paypal/capture/{userId}', [PayPalController::class, 'capturePayment'])->name('paypal.capture');
Route::get('paypal/success', [PayPalController::class, 'success'])->name('paypal.success');
Route::get('paypal/error', [PayPalController::class, 'error'])->name('paypal.error');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['csrf-token' => csrf_token()]);
});


require __DIR__.'/auth.php';
