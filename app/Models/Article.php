<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $primaryKey = 'article_id';
    
    protected $fillable = [
        'entreprise_id',
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
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('designation', true);
    }

    public function scopeByEntreprise(Builder $query, int $entrepriseId): Builder
    {
        return $query->where('entreprise_id', $entrepriseId);
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

}
