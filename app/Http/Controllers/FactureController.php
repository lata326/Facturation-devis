<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use App\Models\LigneFacture;
use App\Models\Article;
use Illuminate\Http\Request;
use App\Http\Requests\FactureRequest;
use App\Services\ExportService;
use Illuminate\Support\Facades\DB;

class FactureController extends Controller
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
            ->when($request->client_id, function ($query, $clientId) {
                return $query->where('client_id', $clientId);
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

            $facture = Facture::create($request->validated());

            foreach ($request->lignes as $ligneData) {
                $article = Article::findOrFail($ligneData['article_id']);
                
                LigneFacture::create([
                    'facture_id' => $facture->id,
                    'article_id' => $article->id,
                    'quantite' => $ligneData['quantite'],
                    'prix_unitaire' => $ligneData['prix_unitaire'] ?? $article->prix_unitaire,
                ]);
            }

            $facture->load(['client', 'lignes.article']);
            
            DB::commit();
            return $this->sendResponse($facture, 'Facture créée avec succès.', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError('Erreur lors de la création de la facture', [$e->getMessage()], 500);
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

    // Note: Pas de méthodes update et destroy car les factures ne peuvent pas être modifiées/supprimées

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
}
