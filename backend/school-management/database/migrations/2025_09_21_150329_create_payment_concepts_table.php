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
        Schema::create('payment_concepts', function (Blueprint $table) {
            $table->id();
            $table->string('concept_name')->index();
            $table->text('description')->nullable();
            $table->enum('status',['activo','finalizado','desactivado','eliminado'])->default('activo')->index();
            $table->date('start_date')->index();
            $table->date('end_date')->nullable();
            $table->integer('amount')->index();
            $table->boolean('is_global')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_concepts');
    }
};
