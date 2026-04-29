<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Addresses;

class AddressesFactory extends Factory
{
    protected $model = Addresses::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),

            'address' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'country' => $this->faker->country(),
            'zip_code' => $this->faker->postcode(),

            'recipient_id' => null,
        ];
    }
}