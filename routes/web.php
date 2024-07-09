<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/initiate-stk-push', [PaymentController::class, 'initiateSTKPush'])->name('initiate-stk-push');

Route::get('/authdaraja', [PaymentController::class,'pushStk'] )->name('authdaraja');

// Route::get('/authdaraja', [PaymentController::class,'authDaraja'])->name('authdaraja');
// Route::post('/push-stk', [PaymentController::class,'pushStk'])->name('push-stk');