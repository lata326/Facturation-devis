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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('nom_societe');
            $table->foreignId('forme_juridique_id')->constrained('formes_juridiques');
            $table->string('numero_tva', 50)->nullable();
            $table->string('adresse_postale');
            $table->string('ville', 100);
            $table->string('pays', 100);
            $table->string('site_internet')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('email_entreprise')->unique();
            $table->string('telephone_entreprise', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }

    
};
