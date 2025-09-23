<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Career;
use App\Models\PaymentConcept;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CareerPaymentConcept>
 */
class CareerPaymentConceptFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'career_id' => Career::factory(),
            'payment_concept_id' => PaymentConcept::factory(),
        ];
    }
}
