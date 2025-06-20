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
        // ❌ Middleware supprimé
    }

    /**
     * Liste de toutes les entreprises
     */
    public function getAllCompanies(): JsonResponse
    {
        $companies = Company::all();

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ]);
    }

    /**
     * Liste publique sans filtrage par utilisateur
     */
    public function index(): JsonResponse
    {
        $companies = Company::all(); // anciennement filtré par utilisateur

        return response()->json([
            'success' => true,
            'data' => [
                'companies' => $companies
            ]
        ]);
    }

    /**
     * Création d'une entreprise sans utilisateur lié
     */
    public function store(CompanyRequest $request): JsonResponse
    {
        try {
            // Création sans utilisateur connecté
            $company = $this->companyService->createCompany(null, $request->validated());
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
        // ❌ Suppression de la vérification de propriété
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
