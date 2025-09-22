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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_concept')->constrained('payment_concepts')->onDelete('cascade');
            $table->foreignId('id_payment_method')->constrained('payment_methods')->onDelete('cascade');
            $table->enum('status',['Pagado','Pendiente'])->default('Pagado');
            $table->date('transaction_date');
            $table->string('payment_intent_id',50);
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
