<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\UserProfileController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Controllers\Api\Auth\GoogleAuthController;
use App\Http\Controllers\Api\ToitureDevisController;

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
        Route::get('/google/redirect', [GoogleAuthController::class, 'googleRedirect']);
        Route::get('/google/callback', [GoogleAuthController::class, 'googleCallback']);
            
    });

    Route::get('services',                       [ServiceController::class, 'index']);
    Route::get('services/{id}/products',         [ServiceController::class, 'products']);
    Route::get('pricing-factors',                [ServiceController::class, 'pricingFactors']);
    Route::post('password/email', [PasswordResetController::class, 'sendResetLink']);
    Route::post('password/reset', [PasswordResetController::class, 'reset']);
    Route::get('/toiture/devis/public/{token}/pdf', [ToitureDevisController::class, 'downloadPdfPublic']);

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
        
        // ── TOITURE CALCULATOR ──────────────────────────────────────────────
    
        // Calculate (preview) - does not save to database
        Route::post('toiture/calculate', [ToitureDevisController::class, 'calculate']);
        
        // ── TOITURE DEVIS CRUD ──────────────────────────────────────────────
        
        // List all devis
        Route::get('toiture/devis', [ToitureDevisController::class, 'index']);
        
        // Create new devis
        Route::post('toiture/devis', [ToitureDevisController::class, 'store']);
        
        // Get single devis
        Route::get('toiture/devis/{id}', [ToitureDevisController::class, 'show']);
        
        // Submit devis to AlaqSeal
        Route::post('toiture/devis/{id}/submit', [ToitureDevisController::class, 'submit']);
        
        // Delete devis
        Route::delete('toiture/devis/{id}', [ToitureDevisController::class, 'destroy']);
        
        // Download PDF
        Route::get('toiture/devis/{id}/download-pdf', [ToitureDevisController::class, 'downloadPdf']);
        
        // ── STATISTICS ──────────────────────────────────────────────────────
        
        // Get user statistics
        Route::get('toiture/stats', [ToitureDevisController::class, 'stats']);

});