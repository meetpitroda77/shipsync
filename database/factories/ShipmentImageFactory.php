<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shipment;
use App\Models\User;

class ShipmentImageFactory extends Factory
{
 
    protected $model = \App\Models\ShipmentImage::class;

    
    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::inRandomOrder()->first()?->id ?? 1,

            'image_path' => 'shipments/' . $this->faker->unique()->lexify('image_????') . '.jpg',

            'uploaded_by' => User::inRandomOrder()->first()?->id ?? 1,

            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),

            'updated_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}