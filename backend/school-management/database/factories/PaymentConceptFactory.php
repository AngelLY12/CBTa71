<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PaymentConcept>
 */
class PaymentConceptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'concept_name' => fake()->word(),
            'description' => fake()->sentence(),
            'status' => fake()->randomElement(['Activo', 'Finalizado']),
            'start_date' => fake()->date(),
            'end_date' => fake()->optional()->date(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'is_global' => fake()->boolean(),
        ];
    }
}
