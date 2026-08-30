<?php

namespace Tests\Feature;

use App\Models\MassIntention;
use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassIntentionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_parishioner_can_submit_a_mass_intention(): void
    {
        $user = User::query()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password_hash' => 'secret',
            'role' => 'user',
        ]);
        $schedule = $this->schedule();

        $response = $this->actingAs($user)->post(route('parishioner.mass-intentions.store'), [
            'mass_schedule_id' => $schedule->id,
            'intention_type' => 'thanksgiving',
            'names' => 'The Santos Family',
            'offering_amount' => 200,
        ]);

        $response->assertRedirect(route('parishioner.mass-intentions'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('mass_intentions', [
            'user_id' => $user->id,
            'mass_schedule_id' => $schedule->id,
            'requested_by' => 'Maria Santos',
            'intention_type' => 'thanksgiving',
            'names' => 'The Santos Family',
            'status' => 'pending',
        ]);
    }

    public function test_parishioner_only_sees_their_own_mass_intentions(): void
    {
        $maria = User::query()->create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password_hash' => 'secret',
            'role' => 'user',
        ]);
        $juan = User::query()->create([
            'name' => 'Juan Cruz',
            'email' => 'juan@example.com',
            'password_hash' => 'secret',
            'role' => 'user',
        ]);
        $schedule = $this->schedule();

        MassIntention::query()->create([
            'user_id' => $maria->id, 'mass_schedule_id' => $schedule->id,
            'requested_by' => $maria->name, 'intention_type' => 'living',
            'names' => 'Visible family intention', 'status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);
        MassIntention::query()->create([
            'user_id' => $juan->id, 'mass_schedule_id' => $schedule->id,
            'requested_by' => $juan->name, 'intention_type' => 'deceased',
            'names' => 'Private intention', 'status' => 'pending',
            'requested_date' => now()->toDateString(),
        ]);

        $this->actingAs($maria)->get(route('parishioner.mass-intentions'))
            ->assertOk()
            ->assertSee('Visible family intention')
            ->assertDontSee('Private intention');
    }

    private function schedule(): MassSchedule
    {
        return MassSchedule::query()->create([
            'title' => 'Sunday Mass', 'day_of_week' => 'Sunday',
            'start_time' => '07:30:00', 'end_time' => '08:30:00',
            'location' => 'Main Church', 'priest_in_charge' => 'Fr. Pedro',
            'is_active' => true,
        ]);
    }
}
