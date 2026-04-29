<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Shipment;
use App\Models\ShipmentLog;
use App\Models\ShipmentImage;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create 10 users
        User::factory(10)->create();

        // Create 20 shipments
        Shipment::factory(20)->create();

        // Create multiple logs per shipment
        Shipment::all()->each(function($shipment) {
            ShipmentLog::factory(rand(2,5))->create([
                'shipment_id' => $shipment->id
            ]);
        });

        // Create multiple images per shipment
        Shipment::all()->each(function($shipment) {
            ShipmentImage::factory(rand(1,3))->create([
                'shipment_id' => $shipment->id,
                'uploaded_by' => User::inRandomOrder()->first()?->id ?? 1
            ]);
        });
    }
}