<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\ClientController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques (sans authentification)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Routes avec authentification Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Routes d'authentification
    Route::prefix('auth')->group(function () {
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
        Route::post('/resend-code', [AuthController::class, 'resendCode']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Routes des entreprises
    Route::prefix('companies')->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/formes-juridiques', [CompanyController::class, 'getFormes']);
        Route::get('/{company}', [CompanyController::class, 'show']);
        Route::put('/{company}', [CompanyController::class, 'update']);
        Route::delete('/{company}', [CompanyController::class, 'destroy']);
    });

    // Routes CRUD pour les articles
    Route::apiResource('Article', ArticleController::class);
    
    // Route de recherche spécialisée
    Route::get('articles-search', [ArticleController::class, 'search']);    

    // Client routes
    Route::apiResource('Client', ClientController::class, [
        'parameters' => ['clients' => 'client']
    ]);

});

//Gestion des routes non trouvées
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint non trouvé'
    ], 404);
});