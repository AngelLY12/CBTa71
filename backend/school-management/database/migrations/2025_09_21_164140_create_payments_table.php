<?php

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
            $table->string('status');
            $table->date('transaction_date');
            $table->string('payment_intent_id',50)->unique();
            $table->string('url');
            $table->timestamps();
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
