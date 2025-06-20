<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\DevisController;
use App\Http\Controllers\Api\FactureController;
use App\Http\Controllers\Api\HistoriqueController;
use App\Http\Controllers\Api\NotificationController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/
 Route::post('/factureye', [FactureController::class, 'store']);
// Routes publiques 
    Route::get('/users', [AuthController::class, 'index']);
    Route::get('/users/{id}', [AuthController::class, 'show']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/request-password-reset', [AuthController::class, 'requestPasswordReset']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    
    // Routes d'authentification
    Route::prefix('auth')->group(function () {
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
        Route::post('/resend-code', [AuthController::class, 'resendCode']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Routes des entreprises
    Route::prefix('companies')->group(function () {
        Route::get('/companies/all', [CompanyController::class, 'getAllCompanies']);
        Route::get('/companies', [CompanyController::class, 'index']);
        Route::post('/companies', [CompanyController::class, 'store']);
        Route::get('/formes-juridiques', [CompanyController::class, 'getFormes']);
        Route::get('/companies/{company}', [CompanyController::class, 'show']);
        Route::put('/companies/{company}', [CompanyController::class, 'update']);
        Route::delete('/companies/{company}', [CompanyController::class, 'destroy']);
    });
        

    // Routes pour les Clients
    Route::get('/clients', [ClientController::class, 'index']);
    Route::post('/clients', [ClientController::class, 'store']);
    Route::get('/clients/{client}', [ClientController::class, 'show']);
    Route::put('/clients/{client}', [ClientController::class, 'update']);
    Route::delete('/clients/{client}', [ClientController::class, 'destroy']);
    
    // Routes pour les ArticlesS
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::post('/articles', [ArticleController::class, 'store']);
    Route::get('/articles/{id}', [ArticleController::class, 'show']);
    Route::put('/articles/{id}', [ArticleController::class, 'update']);
    Route::delete('/articles/{id}', [ArticleController::class, 'destroy']);
    Route::get('/articles/search', [ArticleController::class, 'search']);

    
    // Routes pour les Devis
    Route::get('/devis', [DevisController::class, 'index']);
    Route::post('/devis', [DevisController::class, 'store']);
    Route::get('/devis/{devis}', [DevisController::class, 'show']);
    Route::put('/devis/{devis}', [DevisController::class, 'update']);
    Route::delete('/devis/{devis}', [DevisController::class, 'destroy']);
    Route::post('/devis/{devis}/convert-to-facture', [DevisController::class, 'convertToFacture']);
    Route::get('/devis/{devis}/export-pdf', [DevisController::class, 'exportPdf']);
    Route::get('/devis/{devis}/export-excel', [DevisController::class, 'exportExcel']);

    
    // Routes pour les Factures
    Route::get('factures', [FactureController::class, 'index']);
    Route::post('facturex', [FactureController::class, 'store']);
    Route::get('factures/{facture}', [FactureController::class, 'show']);
    Route::put('/factures/{facture}', [FactureController::class, 'update']);
    Route::delete('/factures/{facture}', [FactureController::class, 'destroy']);
    Route::patch('/factures/{facture}/cancel', [FactureController::class, 'cancel']);
    Route::get('factures/{facture}/export/pdf', [FactureController::class, 'exportPdf']);
    Route::get('factures/{facture}/export/excel', [FactureController::class, 'exportExcel']);



    Route::group(['prefix' => 'historique', 'middleware' => ['auth:sanctum']], function () {
        // Enregistrer une modification
        Route::post('/', [HistoriqueController::class, 'store']);
        
        // Historique d'un document spécifique
        Route::get('/document/{type_document}/{document_id}', [HistoriqueController::class, 'getDocumentHistory']);
        // Historique d'une entreprise
        Route::get('/company/{company_id}', [HistoriqueController::class, 'getCompanyHistory']);
        // Statistiques d'historique
        Route::get('/company/{company_id}/stats', [HistoriqueController::class, 'getStats']);
  });


    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unread']);
        Route::patch('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/trigger-check', [NotificationController::class, 'triggerCheck']);
    });

    //Gestion des routes non trouvées
    Route::fallback(function () {
        return response()->json([
            'success' => false,
            'message' => 'Endpoint non trouvé'
        ], 404);
    });




