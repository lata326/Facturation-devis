<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ligne_factures extends Model
{
    use HasFactory;

    protected $fillable = [
        'facture_id', 'article_id', 'quantite', 'prix_unitaire',
        'montant_ht', 'montant_tva', 'montant_ttc'
    ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'montant_ht' => 'decimal:2',
        'montant_tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($ligne) {
            $ligne->calculateMontants();
        });
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class);
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function calculateMontants()
    {
        $this->montant_ht = $this->quantite * $this->prix_unitaire;
        $this->montant_tva = $this->montant_ht * ($this->article->taux_tva / 100);
        $this->montant_ttc = $this->montant_ht + $this->montant_tva;
    }
}
