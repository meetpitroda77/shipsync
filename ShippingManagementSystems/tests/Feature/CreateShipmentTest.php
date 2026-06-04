<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Addresses;
use App\Models\Recipient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Mockery;

class CreateShipmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_create_shipment()
    {
        Storage::fake('public');
        Notification::fake();

        $user = User::factory()->customer()->create();
        Sanctum::actingAs($user);

        $senderAddress = Addresses::factory()->create(['user_id' => $user->id]);
        $receiverAddress = Addresses::factory()->create();
        $recipient = Recipient::factory()->create();

        $file = UploadedFile::fake()->image('package.jpg');

        Mockery::mock('overload:\Stripe\Checkout\Session')
            ->shouldReceive('create')
            ->once()
            ->andReturn((object)[
                'url' => 'http://fake-payment-url.com'
            ]);

        $payload = [
            'sender_name' => 'Meet',
            'sender_phone' => '9876543210',
            'sender_address_id' => $senderAddress->id,

            'receiver_name' => $recipient->id,
            'receiver_phone' => '9999999999',
            'receiver_address_id' => $receiverAddress->id,

            'package_type' => 'box',

            'package_amount' => [1],
            'package_description' => ['Books'],
            'package_weight' => [2],
            'package_length' => [10],
            'package_width' => [10],
            'package_height' => [10],

            'package_notes' => ['Handle carefully'],

            'delivery_method' => 'standard',

            'package_photos' => [$file],

            'courier_company' => 'DHL',
            'shipping_mode' => 'air',
        ];

        $response = $this->postJson('/api/customer/createShipment', $payload);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                     'message' => 'Shipment created successfully',
                 ]);

        $this->assertDatabaseHas('shipments', [
            'sender_name' => 'Meet',
            'created_by' => $user->id,
            'status' => 'pending_payment'
        ]);

        $this->assertDatabaseCount('packages', 1);

        $this->assertDatabaseCount('shipment_images', 1);
    }}