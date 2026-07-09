<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('products/summary/pdf', [ProductController::class, 'summaryPdf'])->name('products.summary.pdf');

// Minimal auth routes to support redirects from `auth` middleware
use App\Http\Controllers\AuthController;
Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::get('register', [AuthController::class, 'showRegister'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return redirect()->route('products.index');
    });
    Route::get('products/summary/excel', [ProductController::class, 'summaryExcel'])->name('products.summary.excel');
    Route::post('products/{product}/adjust', [ProductController::class, 'adjustStock'])->name('products.adjust');
    Route::get('products/{product}/logs', [ProductController::class, 'logs'])->name('products.logs');
    Route::resource('products', ProductController::class);
});
