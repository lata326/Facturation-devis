<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TrackableHistory;

class Article extends Model
{
        use HasFactory;
        use TrackableHistory;

    protected $primaryKey = 'article_id';
    
    protected $fillable = [
        'company_id',
        'code_article',
        'designation',
        'description',
        'prix_unitaire',
        'taux_tva',
     ];

    protected $casts = [
        'prix_unitaire' => 'decimal:2',
        'taux_tva' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

      public function ligneDevis()
    {
        return $this->hasMany(LigneDevis::class);
    }

    public function ligneFactures()
    {
        return $this->hasMany(LigneFacture::class);
    }

    protected $hidden = [];

    // Scopes pour les recherches

    public function scopeByEntreprise(Builder $query, int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('designation', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%")
              ->orWhere('code_article', 'LIKE', "%{$search}%");
        });
    }

    public function scopeByDateRange(Builder $query, string $startDate = null, string $endDate = null): Builder
    {
        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }
        return $query;
    }

    protected static function getTypeDocument(): string
    {
        return 'article';
    }

}



