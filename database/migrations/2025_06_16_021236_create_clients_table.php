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
        Schema::create('clients', function (Blueprint $table) {
            $table->id('clients_id');
            $table->string('nom');
            $table->string('prenom');
            $table->string('mail');
            $table->string('ville');
            $table->string('pays');
            $table->string('code_postal', 10);
            $table->string('telephone');
            $table->timestamps();
            
            // Index pour optimiser les recherches
            $table->index(['nom', 'prenom']);
            $table->index('mail');
            $table->index('telephone');
        });
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
