<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_cannot_access_admin_route()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertRedirect(); 
    }

    public function test_admin_can_access_admin_route()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }
}