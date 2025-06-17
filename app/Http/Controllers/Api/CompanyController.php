<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyRequest;
use App\Services\CompanyService;
use App\Models\Company;
use App\Models\FormeJuridique;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
        $this->middleware('auth:sanctum');
    }

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $companies = $this->companyService->getUserCompanies($user);

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ]);
    }

    public function store(CompanyRequest $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $company = $this->companyService->createCompany($user, $request->validated());
            $company->load('formeJuridique');

            return response()->json([
                'success' => true,
                'message' => 'Entreprise créée avec succès',
                'data' => [
                    'company' => $company
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function show(Company $company): JsonResponse
    {
        // Vérifier que l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $company->load('formeJuridique');

        return response()->json([
            'success' => true,
            'data' => [
                'company' => $company
            ]
        ]);
    }

    public function update(CompanyRequest $request, Company $company): JsonResponse
    {
        // Vérifier que l'entreprise appartient à l'utilisateur connecté
        if ($company->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $company = $this->companyService->updateCompany($company, $request->validated());
            $company->load('formeJuridique');

            return response()->json([
                'success' => true,
                'message' => 'Entreprise mise à jour avec succès',
                'data' => [
                    'company' => $company
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function destroy(Company $company): JsonResponse
    {
        // Vérifier que l'entreprise appartient à l'utilisateur connecté
        
        if ($company->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $this->companyService->deleteCompany($company);

            return response()->json([
                'success' => true,
                'message' => 'Entreprise supprimée avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getFormes(): JsonResponse
    {
        $formes = FormeJuridique::all();

        return response()->json([
            'success' => true,
            'data' => [
                'formes_juridiques' => $formes
            ]
        ]);
    }

}

