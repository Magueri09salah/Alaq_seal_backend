<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\PriceModifierController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Controllers\Api\UserProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::group(function () {
    
    // Authentication
    Route::prefix('auth')->group(function () {
        Route::post('/register', [RegisterController::class, 'register']);
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);
        Route::post('/reset-password', [PasswordResetController::class, 'resetPassword']);
    });

    // Public services (read-only)
    Route::get('/services', [ServiceController::class, 'index']);
    Route::get('/services/{id}', [ServiceController::class, 'show']);
    Route::get('/services/{id}/options', [ServiceController::class, 'options']);

    // Public price modifiers (read-only)
    Route::get('/price-modifiers', [PriceModifierController::class, 'index']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication
    Route::post('/auth/logout', [LogoutController::class, 'logout']);
    Route::get('/auth/me', [UserProfileController::class, 'show']);

    // User Profile
    Route::prefix('user')->group(function () {
        Route::get('/profile', [UserProfileController::class, 'show']);
        Route::put('/profile', [UserProfileController::class, 'update']);
        Route::put('/password', [UserProfileController::class, 'changePassword']);
    });

    // Devis
    Route::prefix('devis')->group(function () {
        Route::get('/', [DevisController::class, 'index']);
        Route::post('/', [DevisController::class, 'store']);
        Route::get('/{id}', [DevisController::class, 'show']);
        Route::put('/{id}', [DevisController::class, 'update']);
        Route::delete('/{id}', [DevisController::class, 'destroy']);
        
        // Special actions
        Route::post('/calculate', [DevisController::class, 'calculate']);
        Route::post('/{id}/submit', [DevisController::class, 'submit']);
        Route::get('/{id}/pdf', [DevisController::class, 'downloadPdf']); // We'll add this
    });
});