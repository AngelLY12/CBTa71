<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\PaymentConcept;
use App\Models\PaymentMethod;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'payment_concept_id' => PaymentConcept::factory(),
            'stripe_payment_method_id' => 'pm_' . fake()->bothify('##########'),
            'status' => fake()->randomElement(['Pagado','Pendiente']),
            'payment_intent_id' => 'pi_' . fake()->unique()->bothify('##########'),
            'url' => fake()->url(),
        ];
    }
}
