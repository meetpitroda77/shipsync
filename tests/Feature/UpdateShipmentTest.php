<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shipment;
use App\Models\Payment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class UpdateShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_update_shipment_status()
    {
        Storage::fake('public');
        Notification::fake();

        $staff = User::factory()->create(['role' => 'staff']);
        $agent = User::factory()->create(['role' => 'agent']);

        Sanctum::actingAs($staff);

        $shipment = Shipment::factory()->create([
            'status' => 'created',
            'assigned_to' => null
        ]);

        $file = UploadedFile::fake()->image('proof.jpg');

        $response = $this->patchJson("/api/staff/shipment/{$shipment->id}/UpdateShipmentStatus", [
            'status' => 'delivered',
            'agentid' => $agent->id,
            'delivery_proof' => UploadedFile::fake()->image('proof.jpg')
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'status' => 'delivered',
            'assigned_to' => $agent->id
        ]);

        $this->assertDatabaseCount('shipment_images', 1);
        $this->assertDatabaseCount('shipment_logs', 1);
    }
}