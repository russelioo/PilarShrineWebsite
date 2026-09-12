<?php

namespace Tests\Feature;

use App\Models\MassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassAndConfessionScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed default required roles / Sunday mass
        $this->seed();
    }

    public function test_public_can_fetch_mass_and_confession_schedules(): void
    {
        $response = $this->getJson(route('api.mass-schedules'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'categories',
            'schedules',
        ]);

        $data = $response->json();
        $this->assertNotEmpty($data['categories']);
        $this->assertNotEmpty($data['schedules']);

        // Verify categories exist
        $categoryTitles = array_column($data['categories'], 'title');
        $this->assertContains('Sunday Mass', $categoryTitles);
    }

    public function test_unauthenticated_user_cannot_access_admin_mass_schedules(): void
    {
        $response = $this->get(route('admin.mass-schedules'));
        $response->assertRedirect(route('login'));
    }

    public function test_regular_parishioner_cannot_access_admin_mass_schedules(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get(route('admin.mass-schedules'));
        $response->assertStatus(403);
    }

    public function test_administrator_can_view_existing_mass_and_confession_schedules(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.mass-schedules'));
        $response->assertStatus(200);
        $response->assertSee('Mass &amp; Confession Schedule', false);
        $response->assertSee('Existing Schedules');
    }

    public function test_administrator_can_update_existing_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create or get an existing schedule
        $schedule = MassSchedule::query()->where('day_of_week', 'Sunday')->first();
        $this->assertNotNull($schedule);

        $response = $this->actingAs($admin)->put(route('admin.mass-schedules.update', $schedule), [
            'title' => 'Sunday Morning Solemn Mass',
            'category' => 'Sunday Mass',
            'schedule_type' => 'Holy Mass',
            'day_of_week' => 'Sunday',
            'time_display' => '7:30 AM — Solemn High Mass',
            'location' => 'Shrine Main Altar',
            'priest_in_charge' => 'Msgr. Bernardo',
            'notes' => 'FB Live Broadcast',
            'is_active' => true,
            'is_livestreamed' => true,
            'is_highlighted' => false,
            'sort_order' => 1,
        ]);

        $response->assertRedirect(route('admin.mass-schedules'));
        $response->assertSessionHas('success');

        $schedule->refresh();
        $this->assertEquals('Sunday Morning Solemn Mass', $schedule->title);
        $this->assertEquals('7:30 AM — Solemn High Mass', $schedule->time_display);
        $this->assertEquals('Shrine Main Altar', $schedule->location);

        // Verify updated schedule appears in public API
        $publicRes = $this->getJson(route('api.mass-schedules'));
        $publicRes->assertStatus(200);
        $publicRes->assertSee('Sunday Morning Solemn Mass');
        $publicRes->assertSee('7:30 AM — Solemn High Mass');
    }

    public function test_administrator_can_create_confession_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.mass-schedules.store'), [
            'title' => 'Saturday Penance Service',
            'category' => 'Sacrament of Reconciliation',
            'schedule_type' => 'Confession',
            'day_of_week' => 'Every Saturday',
            'time_display' => '4:00 PM — Confession',
            'location' => 'Shrine Confessional',
            'priest_in_charge' => 'Parish Confessors',
            'notes' => 'Sacrament of Reconciliation',
            'is_active' => true,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mass_schedules', [
            'title' => 'Saturday Penance Service',
            'category' => 'Sacrament of Reconciliation',
            'schedule_type' => 'Confession',
        ]);
    }

    public function test_sunday_mass_protection_prevents_deleting_last_sunday_mass(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Remove all but one Sunday mass
        $sundaySchedules = MassSchedule::query()->where('day_of_week', 'Sunday')->get();
        if ($sundaySchedules->count() > 1) {
            foreach ($sundaySchedules->slice(1) as $extra) {
                $extra->delete();
            }
        }

        $lastSunday = MassSchedule::query()->where('day_of_week', 'Sunday')->first();
        $this->assertNotNull($lastSunday);

        // Attempt deleting the only remaining Sunday Mass
        $response = $this->actingAs($admin)->delete(route('admin.mass-schedules.destroy', $lastSunday));
        $response->assertSessionHasErrors('schedule');

        $this->assertDatabaseHas('mass_schedules', [
            'id' => $lastSunday->id,
            'deleted_at' => null,
        ]);
    }
}

