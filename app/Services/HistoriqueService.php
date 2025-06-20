<?php

namespace App\Services;

use App\Models\Historique;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HistoriqueService
{
    public function enregistrerModification(array $donnees)
    {
        $historique = new Historique();
        $historique->company_id = $donnees['company_id'];
        $historique->type_document = $donnees['type_document'];
        $historique->document_id = $donnees['document_id'];
        $historique->action = $donnees['action'];
        $historique->champ_modifie = $donnees['champ_modifie'] ?? null;
        $historique->ancienne_valeur = $donnees['ancienne_valeur'] ?? null;
        $historique->nouvelle_valeur = $donnees['nouvelle_valeur'] ?? null;
        $historique->utilisateur_id = Auth::id();
        $historique->adresse_ip = request()->ip();
        $historique->raison_modification = $donnees['raison_modification'] ?? null;
        $historique->date_modification = now();
        
        return $historique->save();
    }

    public function obtenirHistoriqueDocument($typeDocument, $documentId, $companyId)
    {
        return Historique::parDocument($typeDocument, $documentId)
                        ->parcompanies($companyId)
                        ->with(['utilisateur:id,name,email'])
                        ->orderBy('date_modification', 'desc')
                        ->get();
    }

    public function obtenirHistoriqueEntreprise($companyId, $filtres = [])
    {
        $query = Historique::parEntreprise($companyId)
                          ->with(['utilisateur:id,name,email']);

        if (isset($filtres['type_document'])) {
            $query->where('type_document', $filtres['type_document']);
        }

        if (isset($filtres['action'])) {
            $query->where('action', $filtres['action']);
        }

        if (isset($filtres['date_debut']) && isset($filtres['date_fin'])) {
            $query->parPeriode($filtres['date_debut'], $filtres['date_fin']);
        }

        if (isset($filtres['utilisateur_id'])) {
            $query->where('utilisateur_id', $filtres['utilisateur_id']);
        }

        return $query->orderBy('date_modification', 'desc')
                    ->paginate($filtres['per_page'] ?? 20);
    }
}