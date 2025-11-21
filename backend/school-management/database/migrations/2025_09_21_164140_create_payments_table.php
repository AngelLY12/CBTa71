<?php

use App\Core\Domain\Enum\Payment\PaymentStatus;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Models\PaymentMethod;
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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained('users')->onDelete('cascade');
            $table->foreignIdFor(PaymentConcept::class)->nullable()->constrained('payment_concepts')->onDelete('set null');
            $table->foreignIdFor(PaymentMethod::class)->nullable()->constrained('payment_methods')->onDelete('set null');
            $table->string('stripe_payment_method_id',50)->nullable()->index();
            $table->string('concept_name');
            $table->decimal('amount', 7,2)->index();
            $table->json('payment_method_details');
            $table->string('status',20)->default(PaymentStatus::UNPAID->value);
            $table->string('payment_intent_id',50)->unique()->nullable();
            $table->text('url')->nullable();
            $table->string('stripe_session_id')->nullable()->unique();
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
