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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->string('code_article', 50)->unique();
            $table->string('designation', 255);
            $table->text('description')->nullable();
            $table->decimal('prix_unitaire', 10, 2)->default(0.00);
            $table->integer('taux_tva');
            $table->timestamps();
            
            $table->index(['company_id',]);
            $table->index('code_article');
            $table->index('designation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
