<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class company extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom_societe',
        'forme_juridique_id',
        'numero_tva',
        'adresse_postale',
        'ville',
        'pays',
        'site_internet',
        'logo_url',
        'email_entreprise',
        'telephone_entreprise',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function formeJuridique(): BelongsTo
    {
        return $this->belongsTo(FormeJuridique::class, 'forme_juridique_id');
    }

    
}
