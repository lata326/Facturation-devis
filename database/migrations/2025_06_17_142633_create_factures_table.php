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
        Schema::create('factures', function (Blueprint $table) {
            $table->id();
            $table->string('numero_facture')->unique();
            $table->foreignId('devis_id')->nullable()->constrained('devis');
            $table->foreignId('client_id')->constrained('clients');
            $table->date('date_emission');
            $table->date('date_echeance');
            $table->enum('status', ['brouillon', 'envoyee', 'payee', 'impayee', 'annulee'])->default('brouillon');
            $table->string('condition_paiement')->default('30 jours');
            $table->enum('mode_paiement', ['Non spécifié', 'Espèces', 'Virement bancaire', 'Chèques', 'Carte bancaire', 'PayPal', 'momo'])->default('Non spécifié');
            
            // Montants détaillés
            $table->decimal('montant_ht', 10, 2)->default(0);
            $table->decimal('montant_tva', 10, 2)->default(0); // ✅ Ajouté pour la TVA
            $table->decimal('montant_ttc', 10, 2)->default(0);
            // $table->decimal('montant_total', 10, 2)->default(0); // ❌ Supprimé car redondant avec montant_ttc
            
            $table->enum('devise', ['FCFA', 'USD', 'EUR'])->default('EUR');
            $table->text('signature')->nullable();
            $table->date('date_signature')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('factures');
    }
};
