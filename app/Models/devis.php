<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\TrackableHistory;

class devis extends Model
{
    use HasFactory;  
    use TrackableHistory;

    protected $table = 'devis';

    protected $fillable = [
        'clients_id', 'company_id', 'numero_devis', 'date_creation',
        'date_expiration', 'date_echeance', 'montant_ht', 'montant_ttc',
        'montant_total', 'status', 'signature', 'date_signature', 'note'
    ];

    protected $casts = [
        'date_creation' => 'date',
        'date_expiration' => 'date',
        'date_echeance' => 'date',
        'date_signature' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'montant_total' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($devis) {
            if (empty($devis->numero_devis)) {
                $devis->numero_devis = self::generateNumeroDevis();
            }
            if (empty($devis->date_creation)) {
                $devis->date_creation = Carbon::now()->toDateString();
            }
        });

        static::saved(function ($devis) {
            $devis->calculateMontants();
        });
    }

    public function client()
    {
        return $this->belongsTo(Clients::class);
    }

    public function entreprise()
    {
        return $this->belongsTo(Companies::class);
    }

    public function lignes()
    {
        return $this->hasMany(LigneDevis::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    protected static function getTypeDocument(): string
    {
        return 'devis';
    }

    public static function generateNumeroDevis()
    {
        $year = Carbon::now()->year;
        $lastDevis = self::whereYear('date_creation', $year)
            ->orderBy('numero_devis', 'desc')
            ->first();

        if ($lastDevis) {
            $lastNumber = (int) substr($lastDevis->numero_devis, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'DEV' . $year . sprintf('%04d', $newNumber);
    }

    public function calculateMontants()
    {
        $montantHT = $this->lignes->sum('montant_ht');
        $montantTVA = $this->lignes->sum('montant_tva');
        $montantTTC = $montantHT + $montantTVA;

        $this->update([
            'montant_ht' => $montantHT,
            'montant_ttc' => $montantTTC,
            'montant_total' => $montantTTC
        ]);
    }

    public function convertToFacture()
    {
        if ($this->facture) {
            throw new \Exception('Ce devis a déjà été converti en facture.');
        }

        $facture = Facture::create([
            'devis_id' => $this->id,
            'client_id' => $this->client_id,
            'date_emission' => Carbon::now()->toDateString(),
            'date_echeance' => Carbon::now()->addDays(30)->toDateString(),
            'status' => 'brouillon',
            'montant_ht' => $this->montant_ht,
            'montant_ttc' => $this->montant_ttc,
            'montant_total' => $this->montant_total,
            'note' => $this->note
        ]);

        foreach ($this->lignes as $ligne) {
            LigneFacture::create([
                'facture_id' => $facture->id,
                'article_id' => $ligne->article_id,
                'quantite' => $ligne->quantite,
                'prix_unitaire' => $ligne->prix_unitaire,
                'montant_ht' => $ligne->montant_ht,
                'montant_tva' => $ligne->montant_tva,
                'montant_ttc' => $ligne->montant_ttc
            ]);
        }

        return $facture;
    }
}
