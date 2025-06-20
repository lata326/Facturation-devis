<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HistoriqueController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   protected $historiqueService;

    public function __construct(HistoriqueService $historiqueService)
    {
        $this->historiqueService = $historiqueService;
    }

    /**
     * Enregistrer une nouvelle modification
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id',
            'type_document' => 'required|in:facture,devis,article',
            'document_id' => 'required|integer',
            'action' => 'required|in:create,update,delete,restore',
            'champ_modifie' => 'nullable|string',
            'ancienne_valeur' => 'nullable',
            'nouvelle_valeur' => 'nullable',
            'raison_modification' => 'nullable|string|max:500'
        ]);

        try {
            $this->historiqueService->enregistrerModification($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Modification enregistrée avec succès'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'historique d'un document spécifique
     */
    public function getDocumentHistory(Request $request, $typeDocument, $documentId): JsonResponse
    {
        $request->validate([
            'company_id' => 'required|integer|exists:companies,id'
        ]);

        try {
            $historique = $this->historiqueService->obtenirHistoriqueDocument(
                $typeDocument,
                $documentId,
                $request->company_id
            );

            return response()->json([
                'success' => true,
                'data' => $historique
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'historique d'une entreprise avec filtres
     */
    public function getCompanyHistory(Request $request, $companyId): JsonResponse
    {
        $request->validate([
            'type_document' => 'nullable|in:facture,devis,article',
            'action' => 'nullable|in:create,update,delete,restore',
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut',
            'utilisateur_id' => 'nullable|integer|exists:users,id',
            'per_page' => 'nullable|integer|min:1|max:100'
        ]);

        try {
            $filtres = $request->only([
                'type_document', 'action', 'date_debut', 
                'date_fin', 'user_id', 'per_page'
            ]);

            $historique = $this->historiqueService->obtenirHistoriqueEntreprise(
                $companyId,
                $filtres
            );

            return response()->json([
                'success' => true,
                'data' => $historique
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'historique
     */
    public function getStats(Request $request, $companyId): JsonResponse
    {
        $request->validate([
            'date_debut' => 'nullable|date',
            'date_fin' => 'nullable|date|after_or_equal:date_debut'
        ]);

        try {
            $query = Historique::parCompanies($companyId);

            if ($request->date_debut && $request->date_fin) {
                $query->parPeriode($request->date_debut, $request->date_fin);
            }

            $stats = [
                'total_modifications' => $query->count(),
                'par_action' => $query->selectRaw('action, COUNT(*) as count')
                                    ->groupBy('action')
                                    ->pluck('count', 'action'),
                'par_type_document' => $query->selectRaw('type_document, COUNT(*) as count')
                                           ->groupBy('type_document')
                                           ->pluck('count', 'type_document'),
                'utilisateurs_actifs' => $query->distinct('utilisateur_id')->count('utilisateur_id')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques: ' . $e->getMessage()
            ], 500);
        }
    }
}
