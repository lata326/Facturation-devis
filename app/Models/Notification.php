<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'facture_id', 'type', 'titre', 'message', 'lu', 'envoye_at'
    ];

    protected $casts = [
        'envoye_at' => 'datetime',
        'lu' => 'boolean',
    ];

    // Relations
        public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function facture()
    {
        return $this->belongsTo(Facture::class, 'facture_id');
    }
}