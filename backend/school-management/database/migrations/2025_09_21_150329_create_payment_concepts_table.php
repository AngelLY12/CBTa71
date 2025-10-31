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
            $table->string('concept_name');
            $table->text('description')->nullable();
            $table->enum('status',['activo','finalizado','desactivado','eliminado'])->default('activo');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('amount');
            $table->string('applies_to',25)->default('todos');
            $table->boolean('is_global')->default(false);
            $table->timestamps();
            $table->index(['status', 'created_at','is_global','applies_to', 'start_date', 'end_date', 'concept_name']);
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
