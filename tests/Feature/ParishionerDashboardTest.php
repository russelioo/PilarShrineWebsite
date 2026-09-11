<?php

namespace Tests\Feature;

use App\Models\FormSubmission;
use App\Models\MassIntention;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParishionerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('parishioner.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_parishioner_sees_personalized_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Maria Santos',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria.santos@gmail.com',
            'google_id' => 'google-maria-123',
            'avatar' => 'https://lh3.googleusercontent.com/avatar-maria.jpg',
            'date_of_birth' => '1995-08-15',
            'phone' => '09171234567',
            'region' => 'Bicol Region (Region V)',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Marifosque (Pob.)',
        ]);

        $response = $this->actingAs($user)->get(route('parishioner.dashboard'));

        $response->assertOk();
        $response->assertSee('Maria Santos');
        $response->assertSee('https://lh3.googleusercontent.com/avatar-maria.jpg');
        $response->assertSee('Good'); // Good morning / afternoon / evening
        $response->assertDontSee('Parish profile is incomplete');
    }

    public function test_dashboard_displays_profile_incomplete_warning_if_details_missing(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@gmail.com',
            'google_id' => 'google-john-456',
            'date_of_birth' => null,
            'phone' => null,
            'barangay' => null,
        ]);

        $response = $this->actingAs($user)->get(route('parishioner.dashboard'));

        $response->assertOk();
        $response->assertSee('Parish profile is incomplete');
        $response->assertSee(route('parishioner.profile-settings'));
    }

    public function test_dashboard_reflects_active_sacrament_and_mass_intention_counts(): void
    {
        $user = User::factory()->create([
            'name' => 'Pedro Penduko',
            'first_name' => 'Pedro',
            'last_name' => 'Penduko',
            'date_of_birth' => '1990-01-01',
            'phone' => '09181234567',
            'barangay' => 'Abucay',
        ]);

        $schedule = \App\Models\MassSchedule::create([
            'title' => 'Sunday Mass',
            'day_of_week' => 'Sunday',
            'start_time' => '07:30:00',
            'end_time' => '08:30:00',
            'location' => 'Main Church',
            'priest_in_charge' => 'Fr. Pedro',
            'is_active' => true,
        ]);

        MassIntention::create([
            'user_id' => $user->id,
            'mass_schedule_id' => $schedule->id,
            'requested_by' => 'Pedro Penduko',
            'intention_type' => 'Thanksgiving',
            'names' => 'Family thanksgiving',
            'status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        $form = \App\Models\Form::create([
            'title' => 'Baptism Request Form',
            'description' => 'Baptism registration',
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        FormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'data' => json_encode(['child_name' => 'Baby Penduko']),
        ]);

        $response = $this->actingAs($user)->get(route('parishioner.dashboard'));

        $response->assertOk();
        $response->assertSee('Active Sacrament Requests');
        $response->assertSee('Active Mass Intentions');
    }

    public function test_portal_route_redirects_authenticated_user_to_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/portal');

        $response->assertRedirect(route('parishioner.dashboard'));
    }

    public function test_portal_route_redirects_guest_to_login(): void
    {
        $response = $this->get('/portal');

        $response->assertRedirect('/login');
    }

    public function test_parishioner_logout_redirects_to_official_website(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/parishioner/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
