<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ligne_factures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facture_id')->constrained('factures')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles');
            
            // Informations de l'article
            $table->string('designation'); // ✅ Désignation de l'article
            $table->integer('quantite');
            $table->decimal('prix_unitaire', 10, 2);
            
            // TVA et calculs
            $table->decimal('taux_tva', 5, 2)->default(0); // ✅ Taux de TVA (ex: 20.00 pour 20%)
            $table->decimal('montant_ht', 10, 2); // ✅ Montant HT de la ligne
            $table->decimal('montant_tva', 10, 2)->default(0); // ✅ Montant TVA de la ligne
            $table->decimal('montant_ttc', 10, 2); // ✅ Montant TTC de la ligne
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_factures');
    }
};