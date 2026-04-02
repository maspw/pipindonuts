<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SupplierController; 

Route::get('/halo', function () {
    return view('welcome');
});

Route::get('/supplier', [SupplierController::class, 'index']);
Route::get('/supplier/create', [SupplierController::class, 'create']);
Route::post('/supplier', [SupplierController::class, 'store']);