<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PixFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::uuid(),
            'status' => fake()->randomElement(['generated', 'paid', 'expired']),
            'amount' => fake()->randomFloat(2, 10, 1000),
            'expires_at' => fake()->dateTimeBetween('-1 hour', '+1 hour'),
            'paid_at' => null
        ];
    }

    public function generated(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'generated',
            'expires_at' => now()->addMinutes(10),
            'paid_at' => null
        ]);
    }

    public function paid(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => now()
        ]);
    }

    public function expired(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => now()->subMinutes(10)
        ]);
    }
}
