<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActionLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserActionLog>
 */
class UserActionLogFactory extends Factory
{
    protected $model = UserActionLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Métodos HTTP comunes
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

        // URLs comunes de la aplicación
        $urls = [
            '/api/auth/login',
            '/api/auth/register',
            '/api/auth/logout',
            '/api/user/profile',
            '/api/user/update',
            '/api/students',
            '/api/payments',
            '/api/courses',
            '/api/grades',
            '/api/attendance',
            '/api/dashboard',
            '/api/reports',
            '/api/settings',
        ];

        // Roles comunes
        $rolesList = [
            ['estudiante'],
            ['padre'],
            ['admin'],
            ['profesor'],
            ['coordinador'],
            ['estudiante', 'padre'], // Usuario con múltiples roles
        ];

        return [
            'user_id' => $this->faker->optional(0.9)->randomElement([ // 90% con usuario, 10% null
                User::factory(),
                null
            ]),
            'roles' => $this->faker->optional(0.8)->randomElement($rolesList), // 80% con roles
            'ip' => $this->faker->ipv4(),
            'method' => $this->faker->randomElement($methods),
            'url' => $this->faker->randomElement($urls) .
                ($this->faker->boolean(30) ? '?' . $this->generateQueryParams() : ''), // 30% con query params
            'created_at' => $this->faker->dateTimeBetween('-30 days', 'now'),
            'updated_at' => fn (array $attributes) => $attributes['created_at'],
        ];
    }

    /**
     * Generate random query parameters for URLs.
     */
    private function generateQueryParams(): string
    {
        $params = [];
        $paramCount = $this->faker->numberBetween(1, 3);

        for ($i = 0; $i < $paramCount; $i++) {
            $params[] = $this->faker->word() . '=' . $this->faker->word();
        }

        return implode('&', $params);
    }

    /**
     * Indicate that the log is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'roles' => $user->roles->pluck('name')->toArray() ?? null,
        ]);
    }

    /**
     * Indicate that the log is for an anonymous user.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
            'roles' => null,
        ]);
    }

    /**
     * Indicate that the log is for a GET request.
     */
    public function getRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'GET',
        ]);
    }

    /**
     * Indicate that the log is for a POST request.
     */
    public function postRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'POST',
        ]);
    }

    /**
     * Indicate that the log is for a PUT/PATCH request.
     */
    public function putRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => $this->faker->randomElement(['PUT', 'PATCH']),
        ]);
    }

    /**
     * Indicate that the log is for a DELETE request.
     */
    public function deleteRequest(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'DELETE',
        ]);
    }

    /**
     * Indicate that the log is for an authentication endpoint.
     */
    public function authEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->randomElement([
                '/api/auth/login',
                '/api/auth/register',
                '/api/auth/logout',
                '/api/auth/refresh',
            ]),
        ]);
    }

    /**
     * Indicate that the log is for a user profile endpoint.
     */
    public function profileEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->randomElement([
                '/api/user/profile',
                '/api/user/update',
                '/api/user/password',
            ]),
        ]);
    }

    /**
     * Indicate that the log is for a student endpoint.
     */
    public function studentEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->randomElement([
                '/api/students',
                '/api/students/enroll',
                '/api/students/grades',
                '/api/students/attendance',
            ]),
        ]);
    }

    /**
     * Indicate that the log is for an admin endpoint.
     */
    public function adminEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->randomElement([
                '/api/admin/users',
                '/api/admin/reports',
                '/api/admin/settings',
                '/api/admin/logs',
            ]),
        ]);
    }

    /**
     * Indicate that the log is for a payment endpoint.
     */
    public function paymentEndpoint(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->randomElement([
                '/api/payments',
                '/api/payments/history',
                '/api/payments/methods',
                '/api/payments/invoices',
            ]),
        ]);
    }

    /**
     * Indicate that the log has specific roles.
     */
    public function withRoles(array $roles): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => $roles,
        ]);
    }

    /**
     * Indicate that the log is for a student role.
     */
    public function asStudent(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['estudiante'],
        ]);
    }

    /**
     * Indicate that the log is for an admin role.
     */
    public function asAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['admin'],
        ]);
    }

    /**
     * Indicate that the log is for a parent role.
     */
    public function asParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['padre'],
        ]);
    }

    /**
     * Indicate that the log is for a teacher role.
     */
    public function asTeacher(): static
    {
        return $this->state(fn (array $attributes) => [
            'roles' => ['profesor'],
        ]);
    }

    /**
     * Indicate that the log has a specific IP address.
     */
    public function fromIp(string $ip): static
    {
        return $this->state(fn (array $attributes) => [
            'ip' => $ip,
        ]);
    }

    /**
     * Indicate that the log is from a local IP.
     */
    public function fromLocal(): static
    {
        return $this->state(fn (array $attributes) => [
            'ip' => '127.0.0.1',
        ]);
    }

    /**
     * Indicate that the log is recent (last 24 hours).
     */
    public function recent(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-24 hours', 'now'),
        ]);
    }

    /**
     * Indicate that the log is old (more than 30 days).
     */
    public function old(): static
    {
        return $this->state(fn (array $attributes) => [
            'created_at' => $this->faker->dateTimeBetween('-1 year', '-30 days'),
        ]);
    }

    /**
     * Indicate that the log is for a specific URL.
     */
    public function url(string $url): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $url,
        ]);
    }
}
