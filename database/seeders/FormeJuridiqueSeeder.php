<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FormeJuridique;

class FormeJuridiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    
       public function run(): void
        {
            $formes = [
                ['code' => 'SARL', 'libelle' => 'Société à Responsabilité Limitée'],
                ['code' => 'SAS', 'libelle' => 'Société par Actions Simplifiée'],
                ['code' => 'SASU', 'libelle' => 'Société par Actions Simplifiée Unipersonnelle'],
                ['code' => 'EURL', 'libelle' => 'Entreprise Unipersonnelle à Responsabilité Limitée'],
                ['code' => 'SA', 'libelle' => 'Société Anonyme'],
                ['code' => 'SNC', 'libelle' => 'Société en Nom Collectif'],
                ['code' => 'EI', 'libelle' => 'Entreprise Individuelle'],
                ['code' => 'MICRO', 'libelle' => 'Micro-entreprise'],
                ['code' => 'SCEA', 'libelle' => 'Société Civile d\'Exploitation Agricole'],
                ['code' => 'SCI', 'libelle' => 'Société Civile Immobilière'],
            ];

            foreach ($formes as $forme) {
                FormeJuridique::create($forme);
            }
       }
        
}
