<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParishionerAvatarTest extends TestCase
{
    use RefreshDatabase;

    public function test_angela_mansanero_has_no_crest_logo(): void
    {
        $angela = User::create([
            'name' => 'Angela Gwyn Mansanero',
            'first_name' => 'Angela Gwyn',
            'last_name' => 'Mansanero',
            'email' => 'mansaneroangelagwyn22@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'parish_secretary',
            'position' => 'Parish Secretary',
            'avatar' => null,
        ]);

        $response = $this->actingAs($angela)->get('/');
        $response->assertStatus(200);

        $response->assertDontSee('/images/pilar-shrine-crest.jpg', false);

        // Test API profile status
        $apiResponse = $this->actingAs($angela)->getJson('/api/user/profile-status');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson([
            'authenticated' => true,
            'user' => [
                'name' => 'Angela Gwyn Mansanero',
                'avatar' => null,
                'is_parish_administrator' => false,
            ],
        ]);
    }

    public function test_parish_administrator_has_crest_logo(): void
    {
        $admin = User::create([
            'name' => 'Parish Administrator',
            'first_name' => 'Parish',
            'last_name' => 'Administrator',
            'email' => 'admin@pilarshrine.test',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'position' => 'Parish Administrator',
            'avatar' => null,
        ]);

        $response = $this->actingAs($admin)->get('/');
        $response->assertStatus(200);
        $response->assertSee('/images/pilar-shrine-crest.jpg', false);

        $apiResponse = $this->actingAs($admin)->getJson('/api/user/profile-status');
        $apiResponse->assertStatus(200);
        $apiResponse->assertJson([
            'authenticated' => true,
            'user' => [
                'avatar' => '/images/pilar-shrine-crest.jpg',
                'is_parish_administrator' => true,
            ],
        ]);
    }

    public function test_guest_has_no_avatar_or_logo(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('/images/pilar-shrine-crest.jpg', false);
        $response->assertSee('window.__AUTH_USER__ = null;', false);
    }
}
