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
        Schema::create('cycles_mensuels', function (Blueprint $table) {
            $table->id();
            $table->char('mois', 7)->unique();
            $table->enum('statut', ['OUVERT', 'DISTRIBUE'])->default('OUVERT');
            $table->decimal('montant_total', 10, 2)->nullable();
            $table->date('date_cloture')->nullable();
            $table->foreignId('beneficiaire_id')->nullable()->constrained('membres');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycles_mensuels');
    }
};
