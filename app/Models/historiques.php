<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Historique extends Model
{
    use HasFactory;

    protected $table = 'historiques';
    protected $primaryKey = 'historique_id';

    protected $fillable = [
        'company_id',
        'type_document',
        'document_id',
        'action',
        'champ_modifie',
        'ancienne_valeur',
        'nouvelle_valeur',
        'utilisateur_id',
        'adresse_ip',
        'raison_modification',
        'date_modification'
    ];

    protected $casts = [
        'date_modification' => 'datetime',
        'ancienne_valeur' => 'array', // Si tu veux stocker du JSON
        'nouvelle_valeur' => 'array', // Si tu veux stocker du JSON
    ];

    // Relations
    public function entreprise()
    {
        return $this->belongsTo(Companies::class, 'company_id');
    }

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes pour faciliter les requêtes
    public function scopeParEntreprise($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeParDocument($query, $typeDocument, $documentId)
    {
        return $query->where('type_document', $typeDocument)
                    ->where('document_id', $documentId);
    }

    public function scopeParPeriode($query, $dateDebut, $dateFin)
    {
        return $query->whereBetween('date_modification', [$dateDebut, $dateFin]);
    }
}