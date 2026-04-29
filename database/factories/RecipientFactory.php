<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Recipient;

class RecipientFactory extends Factory
{
    protected $model = Recipient::class;

    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'receiver_name' => $this->faker->name(),
            'receiver_phone' => $this->faker->numerify('##########'), 
        ];
    }
}