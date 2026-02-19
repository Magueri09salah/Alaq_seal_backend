<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\DevisController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('v1')->group(function () {
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    });

    Route::get('services',                       [ServiceController::class, 'index']);
    Route::get('services/{id}/products',         [ServiceController::class, 'products']);
    Route::get('pricing-factors',                [ServiceController::class, 'pricingFactors']);

});

// Protected routes (require authentication)
Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::post('/auth/logout', [LogoutController::class, 'logout']);
    Route::get('/auth/me', [UserProfileController::class, 'show']);

    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::put('/profile', [UserProfileController::class, 'update']);
        Route::put('/password', [UserProfileController::class, 'changePassword']);
    });

        Route::get('devis/stats',          [DevisController::class, 'stats']);   // before {id} to avoid conflict
        Route::post('devis/calculate',     [DevisController::class, 'calculate']);
        Route::get('devis',                [DevisController::class, 'index']);
        Route::post('devis',               [DevisController::class, 'store']);
        Route::get('devis/{id}',           [DevisController::class, 'show']);
        Route::post('devis/{id}/submit',   [DevisController::class, 'submit']);
        Route::delete('devis/{id}',        [DevisController::class, 'destroy']);

});