<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shipment;

class PackageFactory extends Factory
{
    protected $model = \App\Models\Package::class;

    public function definition(): array
    {
        return [
            'shipment_id' => Shipment::factory(),
            'amount' => 1,
            'description' => 'Test package',
            'weight' => 2,
            'length' => 10,
            'width' => 10,
            'height' => 10,
        ];
    }
}