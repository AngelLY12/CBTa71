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
            $table->string('stripe_payment_method_id',50)->nullable()->index();
            $table->string('last4',4)->nullable();
            $table->string('brand',20)->nullable();
            $table->string('voucher_number')->nullable()->unique();
            $table->string('spei_reference')->nullable()->unique();
            $table->text('instructions_url')->nullable();
            $table->string('type_payment_method',20)->nullable();
            $table->string('status',20);
            $table->string('payment_intent_id',50)->unique()->nullable()->index();
            $table->text('url')->nullable();
            $table->string('stripe_session_id')->nullable()->index();
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
