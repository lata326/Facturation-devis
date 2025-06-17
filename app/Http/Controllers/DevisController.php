<?php

namespace App\Http\Controllers;

use App\Models\Devis;
use App\Models\LigneDevis;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Requests\DevisRequest;
use App\Services\ExportService;
use Illuminate\Support\Facades\DB;


class DevisController extends Controller
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
        $devis = Devis::with(['client', 'entreprise'])
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->client_id, function ($query, $clientId) {
                return $query->where('client_id', $clientId);
            })
            ->when($request->search, function ($query, $search) {
                return $query->where('numero_devis', 'like', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return $this->sendResponse($devis, 'Devis récupérés avec succès.');
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(DevisRequest $request)
    {
        try {
            DB::beginTransaction();

            $devis = Devis::create($request->validated());

            foreach ($request->lignes as $ligneData) {
                $article = Article::findOrFail($ligneData['article_id']);
                
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'article_id' => $article->id,
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'] ?? $article->prix_unitaire,
                ]);
            }

            $devis->load(['client', 'entreprise', 'lignes.article']);
            
            DB::commit();
            return $this->sendResponse($devis, 'Devis créé avec succès.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la création du devis', [$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
     public function show(Devis $devis)
    {
        $devis->load(['client', 'entreprise', 'lignes.article']);
        return $this->sendResponse($devis, 'Devis récupéré avec succès.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DevisRequest $request, Devis $devis)
    {
       try {
            DB::beginTransaction();

            $devis->update($request->validated());

            // Supprimer les anciennes lignes
            $devis->lignes()->delete();

            // Créer les nouvelles lignes
            foreach ($request->lignes as $ligneData) {
                $article = Article::findOrFail($ligneData['article_id']);
                
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'article_id' => $article->id,
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'] ?? $article->prix_unitaire,
                ]);
            }

            $devis->load(['client', 'entreprise', 'lignes.article']);
            
            DB::commit();
            return $this->sendResponse($devis, 'Devis mis à jour avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la mise à jour du devis', [$e->getMessage()], 500);
        } 
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Devis $devis)
    {
         if ($devis->facture) {
            return $this->sendError('Impossible de supprimer un devis converti en facture.', [], 400);
        }

        $devis->delete();
        return $this->sendResponse([], 'Devis supprimé avec succès.');
    }

    public function convertToFacture(Devis $devis)
    {
        try {
            $facture = $devis->convertToFacture();
            $facture->load(['client', 'lignes.article']);
            
            return $this->sendResponse($facture, 'Devis converti en facture avec succès.');
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de la conversion', [$e->getMessage()], 400);
        }
    }

    public function exportPdf(Devis $devis)
    {
        try {
            $devis->load(['client', 'entreprise', 'lignes.article']);
            $pdf = $this->exportService->exportDevisToPdf($devis);
            
            return response($pdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="devis_' . $devis->numero_devis . '.pdf"'
            ]);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'export PDF', [$e->getMessage()], 500);
        }
    }

    public function exportExcel(Devis $devis)
    {
        try {
            $devis->load(['client', 'entreprise', 'lignes.article']);
            $filePath = $this->exportService->exportDevisToExcel($devis);
            
            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return $this->sendError('Erreur lors de l\'export Excel', [$e->getMessage()], 500);
        }
    }
}
