<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = \App\Models\User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => $this->faker->randomElement(['admin', 'customer', 'staff', 'agent']),
            'remember_token' => Str::random(10),
        ];
    }
    public function customer()
    {
        return $this->state(fn() => [
            'role' => 'customer'
        ]);
    }

    public function admin()
    {
        return $this->state(fn() => [
            'role' => 'admin'
        ]);
    }
}