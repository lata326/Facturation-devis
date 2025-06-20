<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\TrackableHistory;

class Facture extends Model
{
    use HasFactory;
    use TrackableHistory;

    protected $fillable = [
        'numero_facture', 'devis_id', 'client_id', 'date_emission',
        'date_echeance', 'status', 'condition_paiement', 'mode_paiement',
        'montant_ht', 'montant_ttc', 'montant_total', 'devise',
        'signature', 'date_signature', 'note'
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_signature' => 'date',
        'montant_ht' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
        'montant_total' => 'decimal:2'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($facture) {
            if (empty($facture->numero_facture)) {
                $facture->numero_facture = self::generateNumeroFacture();
            }
        });

        static::saved(function ($facture) {
            $facture->calculateMontants();
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function devis()
    {
        return $this->belongsTo(Devis::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'facture_id');
    }

    public function lignes()
    {
        return $this->hasMany(LigneFacture::class);
    }

    public static function generateNumeroFacture()
    {
        $year = Carbon::now()->year;
        $lastFacture = self::whereYear('date_emission', $year)
            ->orderBy('numero_facture', 'desc')
            ->first();

        if ($lastFacture) {
            $lastNumber = (int) substr($lastFacture->numero_facture, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return 'FAC' . $year . sprintf('%04d', $newNumber);
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

    protected static function getTypeDocument(): string
    {
        return 'facture';
    }

    // Scopes pour les requêtes automatiques
    public function scopeEcheanceProche($query)
    {
        $dateLimit = Carbon::now()->addHours(48);
        return $query->where('date_echeance', '<=', $dateLimit)
                    ->where('date_echeance', '>', Carbon::now())
                    ->where('status', '!=', 'payee');
    }

    public function scopeEnRetard($query)
    {
        return $query->where('date_echeance', '<', Carbon::now())
                    ->where('status', '!=', 'payee');
    }

    // Vérifier si une notification a déjà été envoyée
    public function hasNotification($type)
    {
        return $this->notifications()
                   ->where('type', $type)
                   ->exists();
    }
}
