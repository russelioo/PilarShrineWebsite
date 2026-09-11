<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_successfully_for_authenticated_admin(): void
    {
        $admin = User::factory()->create([
            'name' => 'Parish Administrator',
            'email' => 'admin@pilarshrine.test',
            'role' => 'admin',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk();
        $response->assertSee('DIOCESAN SHRINE AND PARISH OF OUR LADY OF THE PILLAR');
        $response->assertSee('Welcome back, Admin!');
        $response->assertSee('Matthew 18:20');
        $response->assertSee('Parishioners');
        $response->assertSee('Pending Requests');
        $response->assertSee('Upcoming Events');
        $response->assertSee('Donations');
        $response->assertSee('Recent requests');
        $response->assertSee('Facebook Broadcast');
        $response->assertSee('Upcoming events');
        $response->assertSee('Quick Administrative Actions');
    }
}

