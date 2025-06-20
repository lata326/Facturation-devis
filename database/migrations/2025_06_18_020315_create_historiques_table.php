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
        Schema::create('historiques', function (Blueprint $table) {
            $table->id('historique_id');

            // Ne garder que cette ligne pour company_id
            $table->foreignId('company_id')->constrained('companies');

            $table->enum('type_document', ['facture', 'devis', 'article']);
            $table->unsignedBigInteger('document_id');
            $table->enum('action', ['create', 'update', 'delete', 'restore']);
            $table->string('champ_modifie')->nullable();
            $table->text('ancienne_valeur')->nullable();
            $table->text('nouvelle_valeur')->nullable();

            $table->foreignId('user_id')->constrained('users');

            $table->string('adresse_ip', 45)->nullable();
            $table->text('raison_modification')->nullable();
            $table->timestamp('date_modification');
            $table->timestamps();

            // Index pour optimiser les requêtes
            $table->index(['company_id', 'type_document', 'document_id']);
            $table->index('date_modification');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historiques');
    }
};
