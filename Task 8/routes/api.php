<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemController;
use Illuminate\Support\Facades\Route;

// Publik, dua ini yang jadi pintu masuk
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Sisanya wajib bawa header Authorization: Bearer <token>
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('items', ItemController::class);
});
