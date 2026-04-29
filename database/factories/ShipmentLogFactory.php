<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shipment;

class ShipmentLogFactory extends Factory
{
    protected $model = \App\Models\ShipmentLog::class;

    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::inRandomOrder()->first()?->id ?? 1,
            'status' => $this->faker->randomElement([
                'created','pending_assigned','pending_payment','picked_up','in_transit','out_for_delivery','delivered','failed_delivery','delayed','canceled'
            ]),
            'location' => $this->faker->city(),
            'description' => $this->faker->sentence(),
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }
}