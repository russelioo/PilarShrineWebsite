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
        $response->assertSee('Parish Ministries');
        $response->assertSee('Announcements');
        $response->assertSee('Donations');
        $response->assertSee('Recent requests');
        $response->assertSee('Mass schedules');
        $response->assertSee('Quick Administrative Actions');
        $response->assertSee('<h1 class="topbar-page-title">Dashboard</h1>', false);
        $response->assertSee('global-search-input');
        $response->assertSee('topbar-title-divider');
        $response->assertSee('notification-btn');
        $response->assertSee('profile-trigger-btn');
    }

    public function test_admin_topbar_dynamically_displays_correct_module_titles(): void
    {
        $admin = User::factory()->create([
            'name' => 'Parish Administrator',
            'email' => 'admin@pilarshrine.test',
            'role' => 'admin',
        ]);

        $modules = [
            '/admin/parishioners'   => 'Parishioners',
            '/admin/staff'          => 'Users',
            '/admin/donations'      => 'Donations',
            '/admin/ministries'     => 'Ministries',
            '/admin/announcements'  => 'Announcements',
            '/admin/notifications'  => 'Settings',
            '/admin/appointments'   => 'Appointments',
        ];

        foreach ($modules as $uri => $expectedTitle) {
            $res = $this->actingAs($admin)->get($uri);
            $res->assertOk();
            $res->assertSee('<h1 class="topbar-page-title">' . $expectedTitle . '</h1>', false);
            $res->assertSee('global-search-input');
        }
    }

    public function test_dashboard_displays_live_and_accurate_data_not_static(): void
    {
        $admin = User::factory()->create([
            'name' => 'Parish Administrator',
            'email' => 'admin@pilarshrine.test',
            'role' => 'admin',
        ]);

        // 1. When no donations or requests exist, verify no static mock data is shown
        $res = $this->actingAs($admin)->get('/admin/dashboard');
        $res->assertOk();
        $res->assertSee('₱0.00');
        $res->assertDontSee('₱45,230');
        $res->assertDontSee('Maria Santos');
        $res->assertDontSee('Roberto Cruz');
        $res->assertDontSee('Music Ministry Application');
        $res->assertSee('No requests recorded yet');

        // 2. Create actual live data
        $parishioner = User::factory()->create([
            'name' => 'Juan Pedro',
            'email' => 'juan.pedro@example.com',
            'role' => 'user',
        ]);

        $ministry = \App\Models\Ministry::create([
            'name' => 'Youth Choir Apostolate',
            'slug' => 'youth-choir-apostolate',
            'description' => 'Parish youth choir and music ministry',
            'category' => 'Music',
            'is_accepting_members' => true,
        ]);

        $membership = \App\Models\MinistryMembership::create([
            'user_id' => $parishioner->id,
            'ministry_id' => $ministry->id,
            'status' => 'pending',
            'application_message' => 'Want to sing for the Lord',
            'agreed_terms' => true,
        ]);

        \App\Models\Donation::create([
            'user_id' => $parishioner->id,
            'donor_name' => 'Juan Pedro',
            'amount' => 1500.50,
            'method' => 'gcash',
            'payment_reference' => 'GCASH-12345',
            'status' => 'verified',
            'payment_status' => 'completed',
        ]);

        // 3. Re-fetch dashboard and assert live values
        $resAfter = $this->actingAs($admin)->get('/admin/dashboard');
        $resAfter->assertOk();
        $resAfter->assertSee('₱1,500.50');
        $resAfter->assertDontSee('₱45,230');
        $resAfter->assertSee('Youth Choir Apostolate');
        $resAfter->assertSee('Juan Pedro');
        $resAfter->assertSee('#MEM-' . str_pad((string) $membership->id, 4, '0', STR_PAD_LEFT));
        $resAfter->assertDontSee('Ref: #REQ-');
    }
}

