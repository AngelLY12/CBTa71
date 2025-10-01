<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Career;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'last_name'=> fake()->lastName(),
            'n_control'=>fake()->unique()->numerify('########'),
            'semestre'=>fake()->numberBetween(1, 12),
            'phone_number'=>fake()->numerify('##########'),
            'birthdate'=>fake()->date('Y-m-d', '2005-12-31'),
            'gender'=>fake()->randomElement(['Hombre', 'Mujer']),
            'curp'=>strtoupper(fake()->bothify('????######??????##')),
            'address'=>fake()->address(),
            'state'=>fake()->state(),
            'municipality'=>fake()->city(),
            'career_id'=>Career::factory(),
            'stripe_customer_id'=>fake()->unique()->bothify('##########'),
            'registration_date'=>fake()->dateTimeBetween('-4 years', 'now'),
            'status'=>fake()->boolean(80)
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
