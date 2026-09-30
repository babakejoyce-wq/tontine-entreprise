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
       Schema::create('cotisations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membre_id')->constrained('membres');
            $table->foreignId('cycle_id')->constrained('cycles_mensuels');
            $table->date('periode');
            $table->decimal('montant', 10, 2);
            $table->dateTime('date_versement');
            $table->timestamps();
            $table->unique(['membre_id', 'cycle_id', 'periode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cotisations');
    }
};
