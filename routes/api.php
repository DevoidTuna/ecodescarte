<?php

use App\Http\Controllers\Admin\CollectionPointController as AdminCollectionPointController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CollectionPointController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/points', [CollectionPointController::class, 'index']);
Route::post('/points', [CollectionPointController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);

// Team routes (require a token via Authorization: Bearer <token>)
Route::middleware(AuthenticateApiToken::class)->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/admin/points', [AdminCollectionPointController::class, 'index']);
    Route::get('/admin/points/pending', [AdminCollectionPointController::class, 'pending']);
    Route::patch('/admin/points/{point}/approve', [AdminCollectionPointController::class, 'approve']);
    Route::patch('/admin/points/{point}', [AdminCollectionPointController::class, 'update']);
    Route::delete('/admin/points/{point}', [AdminCollectionPointController::class, 'destroy']);
});
