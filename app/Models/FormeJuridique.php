<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class FormeJuridique extends Model
{
    use HasFactory; 


    protected $table = 'formes_juridiques';
    
     protected $fillable = [
        'code',
        'libelle',
    ];

    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }
}
