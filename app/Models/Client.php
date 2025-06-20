<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
   use HasFactory;

    protected $table = 'clients';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nom',
        'prenom',
        'mail',
        'ville',
        'pays',
        'code_postal',
        'telephone',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function devis()
    {
        return $this->hasMany(Devis::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function getNomCompletAttribute()
    {
        return $this->prenom . ' ' . $this->nom;
    }

    // Scope pour la recherche
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nom', 'LIKE', "%{$term}%")
              ->orWhere('prenom', 'LIKE', "%{$term}%")
              ->orWhere('mail', 'LIKE', "%{$term}%")
              ->orWhere('telephone', 'LIKE', "%{$term}%");
        });
    }
    
    
}
