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
        Schema::create('career_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_concept')->constrained('payment_concepts')->onDelete('cascade');
            $table->foreignId('id_career')->constrained('careers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('career_concepts');
    }
};
