<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParishionerProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_profile_settings(): void
    {
        $response = $this->get(route('parishioner.profile-settings'));

        $response->assertRedirect('/login');
    }

    public function test_parishioner_can_view_profile_settings_with_google_details(): void
    {
        $user = User::factory()->create([
            'name' => 'Ana Batungbakal',
            'first_name' => 'Ana',
            'last_name' => 'Batungbakal',
            'email' => 'ana.batungbakal@gmail.com',
            'google_id' => 'google-ana-99999',
            'avatar' => 'https://lh3.googleusercontent.com/avatar-ana.jpg',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->get(route('parishioner.profile-settings'));

        $response->assertOk();
        $response->assertSee('Ana Batungbakal');
        $response->assertSee('ana.batungbakal@gmail.com');
        $response->assertSee('Google Account');
        $response->assertSee('https://lh3.googleusercontent.com/avatar-ana.jpg');
        $response->assertSee('Verified Email');
        $response->assertSee('Managed by Google OAuth');
    }

    public function test_parishioner_can_update_profile_and_residence(): void
    {
        $user = User::factory()->create([
            'name' => 'Carlos Reyes',
            'first_name' => 'Carlos',
            'last_name' => 'Reyes',
            'email' => 'carlos.reyes@gmail.com',
            'google_id' => 'google-carlos-111',
            'date_of_birth' => null,
            'phone' => null,
        ]);

        $payload = [
            'first_name' => 'Carlos',
            'last_name' => 'Reyes',
            'date_of_birth' => '1992-05-20',
            'phone' => '09191234567',
            'country' => 'Philippines',
            'region' => 'Bicol Region (Region V)',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Abucay',
        ];

        $response = $this->actingAs($user)->put(route('parishioner.profile-settings.update'), $payload);

        $response->assertRedirect(route('parishioner.profile-settings'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertSame('1992-05-20', $user->date_of_birth->format('Y-m-d'));
        $this->assertSame('09191234567', $user->phone);
        $this->assertSame('Bicol Region (Region V)', $user->region);
        $this->assertSame('Sorsogon', $user->province);
        $this->assertSame('Pilar', $user->municipality_city);
        $this->assertSame('Abucay', $user->barangay);
        $this->assertTrue($user->isProfileComplete());
    }

    public function test_profile_update_validation_fails_for_future_birth_date(): void
    {
        $user = User::factory()->create();

        $payload = [
            'first_name' => 'Carlos',
            'last_name' => 'Reyes',
            'date_of_birth' => now()->addDays(5)->format('Y-m-d'),
            'phone' => '09191234567',
            'region' => 'Bicol Region (Region V)',
            'municipality_city' => 'Pilar',
            'barangay' => 'Abucay',
        ];

        $response = $this->actingAs($user)->put(route('parishioner.profile-settings.update'), $payload);

        $response->assertSessionHasErrors('date_of_birth');
    }
}
