<?php

namespace Tests\Feature;

use App\Models\Ministry;
use App\Models\MinistryMembership;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MinistryMembershipTest extends TestCase
{
    use RefreshDatabase;

    private function createSampleMinistries(?User $musicCoordinator = null, ?User $youthCoordinator = null): array
    {
        $music = Ministry::create([
            'name' => 'Music Ministry (Shrine Choirs)',
            'slug' => 'music-ministry',
            'description' => 'Leads the congregation in liturgical music and hymns.',
            'category' => 'Worship & Liturgy',
            'icon' => '🎵',
            'coordinator_user_id' => $musicCoordinator?->id,
            'coordinator_name' => 'Maria Santos',
            'coordinator_contact' => 'choir@pilarshrine.test',
            'meeting_schedule' => 'Saturdays, 3:00 PM – 5:00 PM',
            'meeting_location' => 'Choir Loft / Parish Hall',
            'requirements' => 'Singing audition, regular attendance.',
            'is_accepting_members' => true,
            'is_active' => true,
        ]);

        $youth = Ministry::create([
            'name' => 'Pilar Parish Youth Ministry (PYM)',
            'slug' => 'parish-youth-ministry',
            'description' => 'Fosters spiritual growth, fellowship, and leadership among youth.',
            'category' => 'Youth & Family',
            'icon' => '🔥',
            'coordinator_user_id' => $youthCoordinator?->id,
            'coordinator_name' => 'Pedro Cruz',
            'coordinator_contact' => 'youth@pilarshrine.test',
            'meeting_schedule' => 'Sundays, 1:00 PM – 3:30 PM',
            'meeting_location' => 'Youth Center / Parish Courtyard',
            'requirements' => 'Age 13-28 years old.',
            'is_accepting_members' => true,
            'is_active' => true,
        ]);

        return compact('music', 'youth');
    }

    public function test_guest_is_redirected_to_login_when_visiting_ministries(): void
    {
        $response = $this->get(route('parishioner.ministries'));

        $response->assertRedirect('/login');
    }

    public function test_parishioner_can_browse_ministries_page(): void
    {
        $this->createSampleMinistries();

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        $response = $this->actingAs($user)->get(route('parishioner.ministries'));

        $response->assertOk();
        $response->assertSee('Ministries');
        $response->assertSee('Music Ministry (Shrine Choirs)');
        $response->assertSee('Pilar Parish Youth Ministry (PYM)');
        $response->assertSee('Request to Join');
    }

    public function test_parishioner_can_filter_and_search_ministries(): void
    {
        $this->createSampleMinistries();

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        // Search query filter
        $response = $this->actingAs($user)->get(route('parishioner.ministries', ['q' => 'Music']));
        $response->assertOk();
        $response->assertSee('Music Ministry');
        $response->assertDontSee('Pilar Parish Youth Ministry');

        // Tab open filter
        $response = $this->actingAs($user)->get(route('parishioner.ministries', ['filter' => 'open']));
        $response->assertOk();
        $response->assertSee('Music Ministry');
    }

    public function test_parishioner_can_submit_join_request_with_pending_status(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09181234567',
            'role' => 'parishioner',
        ]);

        $response = $this->actingAs($user)->post(route('parishioner.ministries.join', $music), [
            'application_message' => 'I would love to serve the shrine community using my musical gifts in singing soprano.',
            'experience' => 'Former parish choir member for 3 years in high school.',
            'additional_info' => 'Available for Saturday rehearsals and Sunday evening Masses.',
            'agreed_terms' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // IMPORTANT CONCEPT: Parishioner must NOT automatically become official member
        $this->assertDatabaseHas('ministry_memberships', [
            'user_id' => $user->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'I would love to serve the shrine community using my musical gifts in singing soprano.',
        ]);

        // Notification created for applicant receipt
        $this->assertTrue(Notification::where('user_id', $user->id)->where('subject', 'like', '%Membership Request Submitted%')->exists());
    }

    public function test_join_request_validates_required_fields(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        // Missing application_message and agreed_terms
        $response = $this->actingAs($user)->post(route('parishioner.ministries.join', $music), [
            'application_message' => '',
            'agreed_terms' => '0',
        ]);

        $response->assertSessionHasErrors(['application_message', 'agreed_terms']);
        $this->assertDatabaseCount('ministry_memberships', 0);
    }

    public function test_cannot_submit_duplicate_request_when_pending(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        // First application
        MinistryMembership::create([
            'user_id' => $user->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'First application motivation.',
            'agreed_terms' => true,
        ]);

        // Second application attempt
        $response = $this->actingAs($user)->post(route('parishioner.ministries.join', $music), [
            'application_message' => 'Second attempt should be rejected.',
            'agreed_terms' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('ministry_memberships', 1);
    }

    public function test_cannot_submit_duplicate_request_when_already_approved_member(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        MinistryMembership::create([
            'user_id' => $user->id,
            'ministry_id' => $music->id,
            'status' => 'approved',
            'application_message' => 'I am an active member.',
            'agreed_terms' => true,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($user)->post(route('parishioner.ministries.join', $music), [
            'application_message' => 'Another application attempt.',
            'agreed_terms' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('info');
    }

    public function test_can_reapply_if_previously_rejected(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $user = User::factory()->create([
            'role' => 'parishioner',
        ]);

        $membership = MinistryMembership::create([
            'user_id' => $user->id,
            'ministry_id' => $music->id,
            'status' => 'rejected',
            'application_message' => 'Old attempt motivation.',
            'reviewer_notes' => 'Audition did not pass last year.',
            'reviewed_at' => now()->subMonths(6),
            'agreed_terms' => true,
        ]);

        $response = $this->actingAs($user)->post(route('parishioner.ministries.join', $music), [
            'application_message' => 'New application after attending 6 months of vocal training lessons.',
            'agreed_terms' => '1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $membership->refresh();
        $this->assertEquals('pending', $membership->status);
        $this->assertNull($membership->reviewer_notes);
        $this->assertNull($membership->reviewed_at);
        $this->assertEquals('New application after attending 6 months of vocal training lessons.', $membership->application_message);
    }

    public function test_admin_can_view_all_ministry_requests(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];
        $youth = $ministries['youth'];

        $admin = User::factory()->create(['role' => 'admin']);
        $applicant1 = User::factory()->create(['name' => 'Choir Hopeful']);
        $applicant2 = User::factory()->create(['name' => 'Youth Leader Candidate']);

        MinistryMembership::create([
            'user_id' => $applicant1->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'Love music.',
            'agreed_terms' => true,
        ]);

        MinistryMembership::create([
            'user_id' => $applicant2->id,
            'ministry_id' => $youth->id,
            'status' => 'pending',
            'application_message' => 'Love youth.',
            'agreed_terms' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.ministry-requests'));

        $response->assertOk();
        $response->assertSee('Choir Hopeful');
        $response->assertSee('Youth Leader Candidate');
        $response->assertSee('Ministry Membership Requests');
    }

    public function test_admin_can_approve_membership_request(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['name' => 'Choir Member']);

        $membership = MinistryMembership::create([
            'user_id' => $applicant->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'Love liturgical singing.',
            'agreed_terms' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ministry-requests.approve', $membership));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $membership->refresh();
        $this->assertEquals('approved', $membership->status);
        $this->assertEquals($admin->id, $membership->reviewed_by);
        $this->assertNotNull($membership->reviewed_at);

        // Notification created for applicant
        $this->assertDatabaseHas('notifications', [
            'user_id' => $applicant->id,
            'subject' => "Ministry Membership Approved: {$music->name}",
        ]);
    }

    public function test_admin_can_reject_membership_request_with_notes(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];

        $admin = User::factory()->create(['role' => 'admin']);
        $applicant = User::factory()->create(['name' => 'Applicant']);

        $membership = MinistryMembership::create([
            'user_id' => $applicant->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'Testing rejection.',
            'agreed_terms' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.ministry-requests.reject', $membership), [
            'reason' => 'Audition schedule was missed twice without prior notice.',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $membership->refresh();
        $this->assertEquals('rejected', $membership->status);
        $this->assertEquals('Audition schedule was missed twice without prior notice.', $membership->reviewer_notes);
        $this->assertEquals($admin->id, $membership->reviewed_by);

        // Rejection notification created
        $this->assertDatabaseHas('notifications', [
            'user_id' => $applicant->id,
            'subject' => "Ministry Membership Request Update: {$music->name}",
        ]);
    }

    public function test_staff_coordinator_can_only_view_and_approve_their_assigned_ministry(): void
    {
        $musicCoordinator = User::factory()->create(['name' => 'Music Lead', 'role' => 'staff']);
        $youthCoordinator = User::factory()->create(['name' => 'Youth Lead', 'role' => 'staff']);

        $ministries = $this->createSampleMinistries($musicCoordinator, $youthCoordinator);
        $music = $ministries['music'];
        $youth = $ministries['youth'];

        $applicant1 = User::factory()->create(['name' => 'Singer applicant']);
        $applicant2 = User::factory()->create(['name' => 'Youth applicant']);

        $musicMembership = MinistryMembership::create([
            'user_id' => $applicant1->id,
            'ministry_id' => $music->id,
            'status' => 'pending',
            'application_message' => 'Singing.',
            'agreed_terms' => true,
        ]);

        $youthMembership = MinistryMembership::create([
            'user_id' => $applicant2->id,
            'ministry_id' => $youth->id,
            'status' => 'pending',
            'application_message' => 'Youth leadership.',
            'agreed_terms' => true,
        ]);

        // Music coordinator views requests - sees music request, but not youth request
        $response = $this->actingAs($musicCoordinator)->get(route('staff.ministry-requests'));
        $response->assertOk();
        $response->assertSee('Singer applicant');
        $response->assertDontSee('Youth applicant');

        // Music coordinator can approve music request
        $approveResponse = $this->actingAs($musicCoordinator)->post(route('staff.ministry-requests.approve', $musicMembership));
        $approveResponse->assertRedirect();
        $this->assertEquals('approved', $musicMembership->fresh()->status);

        // Music coordinator CANNOT approve youth request (403 Forbidden scoped authorization)
        $unauthorizedResponse = $this->actingAs($musicCoordinator)->post(route('staff.ministry-requests.approve', $youthMembership));
        $unauthorizedResponse->assertForbidden();
    }

    public function test_parishioner_dashboard_and_profile_reflect_ministry_memberships(): void
    {
        $ministries = $this->createSampleMinistries();
        $music = $ministries['music'];
        $youth = $ministries['youth'];

        $user = User::factory()->create([
            'name' => 'Active Parishioner',
            'role' => 'parishioner',
        ]);

        // 1 approved membership, 1 pending
        MinistryMembership::create([
            'user_id' => $user->id,
            'ministry_id' => $music->id,
            'status' => 'approved',
            'application_message' => 'Active singer.',
            'agreed_terms' => true,
            'reviewed_at' => now(),
        ]);

        MinistryMembership::create([
            'user_id' => $user->id,
            'ministry_id' => $youth->id,
            'status' => 'pending',
            'application_message' => 'Youth aspirant.',
            'agreed_terms' => true,
        ]);

        // Dashboard test
        $dashResponse = $this->actingAs($user)->get(route('parishioner.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('My Ministries');
        $dashResponse->assertSee('1 Active');
        $dashResponse->assertSee('1 Pending');
        $dashResponse->assertSee('Music Ministry (Shrine Choirs)');
        $dashResponse->assertSee('Pilar Parish Youth Ministry (PYM)');

        // Profile settings test
        $profResponse = $this->actingAs($user)->get(route('parishioner.profile-settings'));
        $profResponse->assertOk();
        $profResponse->assertSee('Parish Ministries');
        $profResponse->assertSee('Member');
        $profResponse->assertSee('Pending');
    }
}
