<?php

use App\Http\Controllers\Seller\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')
    ->prefix('seller')
    ->name('seller.')
    ->group(function (): void {
        Route::resource('products', ProductController::class);
    });