<?php

use App\Core\Domain\Enum\PaymentConcept\PaymentConceptAppliesTo;
use App\Core\Domain\Enum\PaymentConcept\PaymentConceptStatus;
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
            $table->string('status',20)->default(PaymentConceptStatus::ACTIVO->value)->index();
            $table->date('start_date')->index();
            $table->date('end_date')->nullable()->index();
            $table->decimal('amount', 7,2);
            $table->string('applies_to',25)->default(PaymentConceptAppliesTo::TODOS->value)->index();
            $table->boolean('is_global')->default(false)->index();
            $table->timestamps();
            $table->index(['created_at']);
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
