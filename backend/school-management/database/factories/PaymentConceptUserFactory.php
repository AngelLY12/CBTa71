<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\PaymentConcept;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsPaymentConceptUser>
 */
class PaymentConceptUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_concept_id' => PaymentConcept::factory(),
            'user_id' => User::factory()

        ];
    }
}
