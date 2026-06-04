<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Addresses;

class ShipmentFactory extends Factory
{
    protected $model = \App\Models\Shipment::class;

    public function definition(): array
    {
        return [
            'tracking_id' => strtoupper($this->faker->bothify('TRK###???')),

            'sender_name' => $this->faker->name(),
            'sender_phone' => $this->faker->numerify('##########'),

            'sender_address_id' => Addresses::factory(),

            'receiver_name' => $this->faker->name(),
            'receiver_phone' => $this->faker->numerify('##########'),

            'receiver_address_id' => Addresses::factory(),

            'package_type' => $this->faker->randomElement([
                'document','parcel','box'
            ]),

            'notes' => $this->faker->sentence(),
            'delivery_method' => $this->faker->randomElement(['standard','express']),
            'status' => 'created',

            'estimated_delivery_date' => now()->addDays(5),

            'created_by' => User::factory(),
            'assigned_to' => User::factory(),

            'courier_company' => 'DHL',
            'shipping_mode' => 'air',
        ];
    }
}