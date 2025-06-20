<?php

namespace App\Http\Controllers\Api;

use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Http\Requests\FactureRequest;
use App\Services\ExportService;
use Illuminate\Support\Facades\DB;

class FactureController extends BaseController
{
   
   protected $exportService;

    public function __construct(ExportService $exportService)
    {
        $this->exportService = $exportService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $factures = Facture::with(['client', 'devis'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->id, function ($query, $clientId) {
                return $query->where('id', $clientId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('numero_facture', 'like', "%{$search}%");
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->where('date_emission', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->where('date_emission', '<=', $dateTo);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->sendResponse($factures, 'Factures récupérées avec succès.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FactureRequest $request)
    {
        try {
            DB::beginTransaction();

            // Préparer les données de la facture
            $factureData = $request->validated();
            
            // Générer le numéro de facture automatiquement si non fourni
            if (!isset($factureData['numero_facture'])) {
                $factureData['numero_facture'] = $this->generateNumeroFacture();
            }

            // Définir les valeurs par défaut si non fournies
            $factureData['status'] = $factureData['status'] ?? 'brouillon';
            $factureData['condition_paiement'] = $factureData['condition_paiement'] ?? '30 jours';
            $factureData['mode_paiement'] = $factureData['mode_paiement'] ?? 'Non spécifié';
            $factureData['devise'] = $factureData['devise'] ?? 'EUR';

            $facture = Facture::create($factureData);

            $montantTotal = 0;
            $montantTva = 0;

            foreach ($request->lignes as $ligneData) {
                $article = Article::findOrFail($ligneData['article_id']);
                
                // Récupérer les données de la ligne
                $quantite = $ligneData['quantite'];
                $prixUnitaire = $ligneData['prix_unitaire'] ?? $article->prix_unitaire;
                $taux_tva = $ligneData['taux_tva'] ?? $article->taux_tva ?? 0; // TVA optionnelle
                
                // Calculs
                $montantHt = $quantite * $prixUnitaire;
                $montantTvaLigne = $montantHt * ($taux_tva / 100);
                $montantTtc = $montantHt + $montantTvaLigne;
                
                LigneFacture::create([
                    'facture_id' => $facture->id,
                    'article_id' => $article->id,
                    'designation' => $article->designation, // Désignation de l'article
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'taux_tva' => $taux_tva,
                    'montant_ht' => $montantHt,
                    'montant_tva' => $montantTvaLigne,
                    'montant_ttc' => $montantTtc,
                ]);

                $montantTotal += $montantTtc;
                $montantTva += $montantTvaLigne;
            }

            // Mettre à jour le montant total de la facture
            $facture->update([
                'montant_ht' => $montantTotal - $montantTva,
                'montant_tva' => $montantTva,
                'montant_ttc' => $montantTotal,
            ]);

            $facture->load(['client', 'lignes.article']);
            
            DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Facture créée avec succès.',
                    'data' => $facture
                ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de la facture',
                'errors' => [$e->getMessage()]
            ], 500);

        }
    }

    /**
     * Display the specified resource.
     */
   public function show(Facture $facture)
    {
        $facture->load(['client', 'devis', 'lignes.article']);
        return $this->sendResponse($facture, 'Facture récupérée avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FactureRequest $request, Facture $facture)
    {
        try {
            // Vérifier si la facture peut être modifiée (par exemple, si elle n'est pas payée)
            if ($facture->status === 'payee') {
                return $this->sendError('Une facture payée ne peut pas être modifiée.', [], 422);
            }

            DB::beginTransaction();

            // Préparer les données de mise à jour
            $factureData = $request->validated();
            
            // Conserver les valeurs existantes si non fournies
            $factureData['status'] = $factureData['status'] ?? $facture->status;
            $factureData['condition_paiement'] = $factureData['condition_paiement'] ?? $facture->condition_paiement;
            $factureData['mode_paiement'] = $factureData['mode_paiement'] ?? $facture->mode_paiement;
            $factureData['devise'] = $factureData['devise'] ?? $facture->devise;

            // Mettre à jour les données de la facture (sans les montants, calculés après)
            $facture->update(collect($factureData)->except(['montant_ht', 'montant_tva', 'montant_ttc'])->toArray());

            // Supprimer les anciennes lignes de facture
            $facture->lignes()->delete();

            $montantTotal = 0;
            $montantTva = 0;

            // Créer les nouvelles lignes avec calculs
            foreach ($request->lignes as $ligneData) {
                $article = Article::findOrFail($ligneData['article_id']);
                
                // Récupérer les données de la ligne
                $quantite = $ligneData['quantite'];
                $prixUnitaire = $ligneData['prix_unitaire'] ?? $article->prix_unitaire;
                $taux_tva = $ligneData['taux_tva'] ?? $article->taux_tva ?? 0;
                
                // Calculs
                $montantHt = $quantite * $prixUnitaire;
                $montantTvaLigne = $montantHt * ($taux_tva / 100);
                $montantTtc = $montantHt + $montantTvaLigne;
                
                LigneFacture::create([
                    'facture_id' => $facture->id,
                    'article_id' => $article->id,
                    'designation' => $article->designation,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'taux_tva' => $taux_tva,
                    'montant_ht' => $montantHt,
                    'montant_tva' => $montantTvaLigne,
                    'montant_ttc' => $montantTtc,
                ]);

                $montantTotal += $montantTtc;
                $montantTva += $montantTvaLigne;
            }

            // Mettre à jour le montant total de la facture
            $facture->update([
                'montant_ht' => $montantTotal - $montantTva,
                'montant_tva' => $montantTva,
                'montant_ttc' => $montantTotal,
            ]);

            $facture->load(['client', 'lignes.article']);
            
            DB::commit();
            return $this->sendResponse($facture, 'Facture modifiée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la modification de la facture', [$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Facture $facture)
    {
        try {
            // Vérifier si la facture peut être supprimée
            if ($facture->status === 'payee') {
                return $this->sendError('Une facture payée ne peut pas être supprimée.', [], 422);
            }

            DB::beginTransaction();

            // Supprimer les lignes de facture associées
            $facture->lignes()->delete();
            
            // Supprimer la facture
            $facture->delete();
            
            DB::commit();
            return $this->sendResponse(null, 'Facture supprimée avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la suppression de la facture', [$e->getMessage()], 500);
        }
    }

    /**
     * Signer une facture
     */
   public function signer(Request $request, Facture $facture)
{
    try {
        $request->validate([
            'signature' => 'nullable|string',
        ]);

        $facture->update([
            'signature' => $request->signature,
            'date_signature' => $request->signature ? now()->format('Y-m-d') : null,
        ]);

        return $this->sendResponse($facture, 'Signature enregistrée avec succès.');
    } catch (\Exception $e) {
        return $this->sendError('Erreur lors de l\'enregistrement de la signature.', [$e->getMessage()], 500);
    }
}


    /**
     * Changer le statut d'une facture
     */
    public function changerStatut(Request $request, Facture $facture)
    {
        try {
            $request->validate([
                'status' => 'required|in:brouillon,envoyee,payee,impayee,annulee'
            ]);

            $facture->update(['status' => $request->status]);

            return $this->sendResponse($facture, 'Statut de la facture mis à jour avec succès.');
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors du changement de statut', [$e->getMessage()], 500);
        }
    }

    public function exportPdf(Facture $facture)
    {
        try {
            $facture->load(['client', 'lignes.article']);
            $pdf = $this->exportService->exportFactureToPdf($facture);
            
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="facture_' . $facture->numero_facture . '.pdf"'
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'export PDF', [$e->getMessage()], 500);
        }
    }

    public function exportExcel(Facture $facture)
    {
        try {
            $facture->load(['client', 'lignes.article']);
            $filePath = $this->exportService->exportFactureToExcel($facture);
            
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'export Excel', [$e->getMessage()], 500);
        }
    }

    /**
     * Générer un numéro de facture unique
     */
    private function generateNumeroFacture()
    {
        $year = date('Y');
        $lastFacture = Facture::whereYear('created_at', $year)
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($lastFacture) {
            // Extraire le numéro de la dernière facture
            $lastNumber = intval(substr($lastFacture->numero_facture, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return 'FAC' . $year . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

   

}