<?php

namespace Tests\Feature;

use App\Models\Ministry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMinistryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get(route('admin.ministries'));
        $response->assertRedirect('/login');
    }

    public function test_parishioner_cannot_access_ministry_management(): void
    {
        $parishioner = User::factory()->create(['role' => 'parishioner']);

        $response = $this->actingAs($parishioner)->get(route('admin.ministries'));
        $response->assertForbidden();
    }

    public function test_admin_can_view_ministries_management_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Ministry::create([
            'name' => 'Altar Servers Guild',
            'slug' => 'altar-servers-guild',
            'category' => 'Worship & Liturgy',
            'icon' => '✝',
            'description' => 'Serving at the altar during Holy Mass.',
            'meeting_schedule' => 'Saturdays, 8:00 AM',
            'meeting_location' => 'Main Sacristy',
            'is_accepting_members' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ministries'));

        $response->assertOk();
        $response->assertSee('Parish Ministries Directory');
        $response->assertSee('+ Add New Ministry');
        $response->assertSee('Altar Servers Guild');
        $response->assertSee('Worship & Liturgy');
    }

    public function test_admin_can_create_new_ministry_without_code(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staffCoordinator = User::factory()->create([
            'name' => 'Bro. Francis',
            'email' => 'francis.staff@pilarshrine.test',
            'role' => 'staff',
        ]);

        $payload = [
            'name' => 'Knights of the Altar',
            'category' => 'Worship & Liturgy',
            'icon' => '🕯️',
            'description' => 'Dedicated young men and boys assisting priests in the sanctuary.',
            'about' => 'Deepening reverence for the sacred mysteries and liturgical service.',
            'activities' => "Serving daily and Sunday Masses\nAltar vestment care\nMonthly recollection and formation",
            'meeting_schedule' => 'Every Saturday • 8:30 AM',
            'meeting_location' => 'Shrine Sacristy',
            'coordinator_user_id' => $staffCoordinator->id,
            'coordinator_name' => 'Bro. Francis',
            'coordinator_email' => 'francis.staff@pilarshrine.test',
            'coordinator_phone' => '0919-876-5432',
            'requirements' => "Baptized and received First Holy Communion\nRegular attendance\nMust be at least 9 years old",
            'is_accepting_members' => '1',
        ];

        $response = $this->actingAs($admin)->post(route('admin.ministries.store'), $payload);

        $response->assertRedirect(route('admin.ministries'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('ministries', [
            'name' => 'Knights of the Altar',
            'slug' => 'knights-of-the-altar',
            'category' => 'Worship & Liturgy',
            'icon' => '🕯️',
            'meeting_schedule' => 'Every Saturday • 8:30 AM',
            'coordinator_name' => 'Bro. Francis',
            'coordinator_user_id' => $staffCoordinator->id,
            'is_accepting_members' => 1,
        ]);

        $created = Ministry::where('slug', 'knights-of-the-altar')->first();
        $this->assertIsArray($created->activities);
        $this->assertCount(3, $created->activities);
        $this->assertEquals('Serving daily and Sunday Masses', $created->activities[0]);

        $this->assertIsArray($created->requirements);
        $this->assertCount(3, $created->requirements);
    }

    public function test_newly_created_ministry_appears_in_parishioner_directory(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $parishioner = User::factory()->create(['role' => 'parishioner']);

        // Admin creates ministry
        $this->actingAs($admin)->post(route('admin.ministries.store'), [
            'name' => 'Legion of Mary (Praesidium)',
            'category' => 'Lay Apostolates',
            'icon' => '👑',
            'description' => 'Devotion to the Blessed Mother through apostolic prayer and home visitation.',
            'is_accepting_members' => '1',
        ]);

        // Parishioner browses ministries
        $response = $this->actingAs($parishioner)->get(route('parishioner.ministries'));

        $response->assertOk();
        $response->assertSee('Legion of Mary (Praesidium)');
        $response->assertSee('Lay Apostolates');
        $response->assertSee('Request to Join');
    }

    public function test_admin_can_update_ministry_details(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ministry = Ministry::create([
            'name' => 'Original Ministry Name',
            'slug' => 'original-ministry-name',
            'category' => 'General',
            'icon' => '✝',
            'description' => 'Original description.',
            'meeting_schedule' => 'Sunday morning',
            'meeting_location' => 'Courtyard',
            'is_accepting_members' => true,
        ]);

        $updatePayload = [
            'name' => 'Updated Ministry Name',
            'category' => 'Sacred Music',
            'icon' => '🎵',
            'description' => 'Updated description with new liturgical responsibilities.',
            'about' => 'Updated about text.',
            'activities' => "Choral rehearsal\nSunday Mass",
            'meeting_schedule' => 'Saturdays • 4:00 PM',
            'meeting_location' => 'Choir Loft',
            'requirements' => "Vocal audition\nPunctuality",
            'is_accepting_members' => '1',
        ];

        $response = $this->actingAs($admin)->put(route('admin.ministries.update', $ministry), $updatePayload);

        $response->assertRedirect(route('admin.ministries'));
        $response->assertSessionHas('success');

        $ministry->refresh();
        $this->assertEquals('Updated Ministry Name', $ministry->name);
        $this->assertEquals('Sacred Music', $ministry->category);
        $this->assertEquals('🎵', $ministry->icon);
        $this->assertEquals('Saturdays • 4:00 PM', $ministry->meeting_schedule);
        $this->assertEquals('Choir Loft', $ministry->meeting_location);
    }

    public function test_admin_can_toggle_membership_acceptance_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ministry = Ministry::create([
            'name' => 'Media Apostolate',
            'slug' => 'media-apostolate',
            'category' => 'Media & Communications',
            'icon' => '📡',
            'description' => 'Livestream team.',
            'is_accepting_members' => true,
        ]);

        // Toggle from open (true) to closed (false)
        $response = $this->actingAs($admin)->patch(route('admin.ministries.toggle', $ministry));
        $response->assertRedirect();

        $this->assertFalse($ministry->fresh()->is_accepting_members);

        // Toggle back to open (true)
        $response = $this->actingAs($admin)->patch(route('admin.ministries.toggle', $ministry));
        $response->assertRedirect();

        $this->assertTrue($ministry->fresh()->is_accepting_members);
    }

    public function test_admin_can_delete_ministry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $ministry = Ministry::create([
            'name' => 'Obsolete Ministry',
            'slug' => 'obsolete-ministry',
            'category' => 'General',
            'icon' => '✝',
            'description' => 'To be deleted.',
            'is_accepting_members' => false,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.ministries.destroy', $ministry));

        $response->assertRedirect(route('admin.ministries'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('ministries', [
            'id' => $ministry->id,
        ]);
    }
}

