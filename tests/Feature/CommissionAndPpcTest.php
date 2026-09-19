<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionDocument;
use App\Models\CommissionMembership;
use App\Models\CommissionProject;
use App\Models\PpcMember;
use App\Models\User;
use Database\Seeders\CommissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CommissionAndPpcTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private Commission $commissionWorship;
    private Commission $commissionYouth;
    private User $coordinatorWorship;
    private User $coordinatorYouth;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create Super Admin
        $this->superAdmin = User::create([
            'name'              => 'Super Administrator',
            'email'             => 'superadmin@pilarshrine.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'super_admin',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        // Create Commission on Worship
        $this->commissionWorship = Commission::create([
            'name'        => 'Commission on Worship',
            'slug'        => 'worship',
            'code'        => 'WORSHIP',
            'icon'        => 'cross',
            'description' => 'Oversees sacred liturgy, liturgical ministers, and music.',
            'is_active'   => true,
        ]);

        // Create Commission on Youth
        $this->commissionYouth = Commission::create([
            'name'        => 'Commission on Youth',
            'slug'        => 'youth',
            'code'        => 'YOUTH',
            'icon'        => 'spark',
            'description' => 'Fosters young parishioners in leadership and discipleship.',
            'is_active'   => true,
        ]);

        // Create Coordinator for Worship
        $this->coordinatorWorship = User::create([
            'name'              => 'Bro. Worship Coordinator',
            'email'             => 'worship.coord@pilarshrine.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'commission_coordinator',
            'commission_id'     => $this->commissionWorship->id,
            'position'          => 'Commission Coordinator',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        $this->commissionWorship->update(['head_user_id' => $this->coordinatorWorship->id]);

        CommissionMembership::create([
            'commission_id' => $this->commissionWorship->id,
            'user_id'       => $this->coordinatorWorship->id,
            'position'      => 'Commission Coordinator',
            'is_officer'    => true,
            'role'          => 'head',
            'status'        => 'active',
            'joined_at'     => now(),
        ]);

        // Create Coordinator for Youth
        $this->coordinatorYouth = User::create([
            'name'              => 'Bro. Youth Coordinator',
            'email'             => 'youth.coord@pilarshrine.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'commission_coordinator',
            'commission_id'     => $this->commissionYouth->id,
            'position'          => 'Commission Coordinator',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        $this->commissionYouth->update(['head_user_id' => $this->coordinatorYouth->id]);

        CommissionMembership::create([
            'commission_id' => $this->commissionYouth->id,
            'user_id'       => $this->coordinatorYouth->id,
            'position'      => 'Commission Coordinator',
            'is_officer'    => true,
            'role'          => 'head',
            'status'        => 'active',
            'joined_at'     => now(),
        ]);
    }

    /**
     * Scenario 1: Super Admin can view all commissions.
     */
    public function test_super_admin_can_view_all_commissions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.commissions.index'));

        $response->assertOk();
        $response->assertViewIs('admin.commissions.index');
        $response->assertSee('Commission on Worship');
        $response->assertSee('Commission on Youth');
        $response->assertSee('Total Commissions');
    }

    /**
     * Scenario 1b: Super admin can access commission workspace by slug, by ID, and by model.
     */
    public function test_super_admin_can_access_commission_by_slug_and_id(): void
    {
        // 1. By slug string
        $responseSlug = $this->actingAs($this->superAdmin)
            ->get(route('admin.commissions.show', $this->commissionWorship->slug));
        $responseSlug->assertOk();
        $responseSlug->assertViewIs('admin.commissions.show');
        $responseSlug->assertSee('Commission on Worship');

        // 2. By integer ID
        $responseId = $this->actingAs($this->superAdmin)
            ->get(route('admin.commissions.show', $this->commissionWorship->id));
        $responseId->assertOk();
        $responseId->assertViewIs('admin.commissions.show');
        $responseId->assertSee('Commission on Worship');

        // 3. By model instance
        $responseModel = $this->actingAs($this->superAdmin)
            ->get(route('admin.commissions.show', $this->commissionWorship));
        $responseModel->assertOk();
        $responseModel->assertViewIs('admin.commissions.show');
        $responseModel->assertSee('Commission on Worship');

        // 4. Non-existent slug returns 404
        $response404 = $this->actingAs($this->superAdmin)
            ->get('/admin/commissions/non-existent-commission-slug');
        $response404->assertNotFound();
    }

    /**
     * Scenario 2: Commission Coordinator A can access only Commission A workspace.
     */
    public function test_coordinator_can_access_own_commission_workspace(): void
    {
        $response = $this->actingAs($this->coordinatorWorship)->get(route('commission.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.commissions.show');
        $response->assertSee('Commission on Worship');
        $response->assertDontSee('Commission on Youth');
    }

    /**
     * Scenario 3: Commission Coordinator A is denied (403) when attempting to access Commission B.
     */
    public function test_coordinator_is_denied_access_to_other_commission(): void
    {
        // Worship coordinator attempts to view Youth commission details directly
        $response = $this->actingAs($this->coordinatorWorship)
            ->get(route('admin.commissions.show', $this->commissionYouth));

        $response->assertForbidden();
    }

    /**
     * Scenario 4: Commission Coordinator cannot view or edit members of another commission.
     */
    public function test_coordinator_cannot_manage_members_of_another_commission(): void
    {
        $parishioner = User::create([
            'name'              => 'Test Parishioner',
            'email'             => 'parishioner@test.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'parishioner',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        // Worship coordinator attempts to add a member to Youth commission
        $response = $this->actingAs($this->coordinatorWorship)
            ->post(route('admin.commissions.members.store', $this->commissionYouth), [
                'user_id'  => $parishioner->id,
                'position' => 'Youth Leader',
            ]);

        $response->assertForbidden();

        // Youth coordinator adds member to Youth commission
        CommissionMembership::create([
            'commission_id' => $this->commissionYouth->id,
            'user_id'       => $parishioner->id,
            'position'      => 'Youth Leader',
            'is_officer'    => false,
            'status'        => 'active',
        ]);

        $membership = CommissionMembership::where('commission_id', $this->commissionYouth->id)->first();

        // Worship coordinator attempts to update youth member
        $response = $this->actingAs($this->coordinatorWorship)
            ->put(route('commission.members.update', $membership), [
                'position' => 'Hacked Position',
                'status'   => 'active',
            ]);

        $response->assertForbidden();
    }

    /**
     * Scenario 5: Commission Coordinator cannot access PPC management (/admin/ppc).
     */
    public function test_coordinator_cannot_access_ppc_management(): void
    {
        $response = $this->actingAs($this->coordinatorWorship)->get(route('admin.ppc.index'));

        $response->assertForbidden();
    }

    /**
     * Scenario 6: Super Admin can appoint a user to the PPC.
     */
    public function test_super_admin_can_appoint_user_to_ppc(): void
    {
        $candidate = User::create([
            'name'              => 'Dr. Jose Rizal',
            'email'             => 'jose.rizal@pilarshrine.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'staff',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ppc.members.store'), [
                'user_id'       => $candidate->id,
                'role_title'    => 'Lay Co-Chair',
                'commission_id' => null,
                'term_start'    => '2026-01-01',
                'term_end'      => '2028-12-31',
                'notes'         => 'Elected lay representative.',
            ]);

        $response->assertRedirect(route('admin.ppc.index'));
        $this->assertDatabaseHas('ppc_members', [
            'user_id'    => $candidate->id,
            'role_title' => 'Lay Co-Chair',
            'status'     => 'active',
        ]);
    }

    /**
     * Scenario 7: Super Admin can assign a commission chairperson in the PPC.
     */
    public function test_super_admin_can_assign_commission_chairperson_in_ppc(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->post(route('admin.ppc.members.store'), [
                'user_id'       => $this->coordinatorWorship->id,
                'role_title'    => 'Commission Chairperson',
                'commission_id' => $this->commissionWorship->id,
                'term_start'    => '2026-01-01',
                'term_end'      => '2027-12-31',
                'notes'         => 'Head of Commission on Worship.',
            ]);

        $response->assertRedirect(route('admin.ppc.index'));
        $this->assertDatabaseHas('ppc_members', [
            'user_id'       => $this->coordinatorWorship->id,
            'commission_id' => $this->commissionWorship->id,
            'role_title'    => 'Commission Chairperson',
        ]);
    }

    /**
     * Scenario 8: Commission Coordinator can add a project and document to their own commission.
     */
    public function test_coordinator_can_add_project_and_document_to_own_commission(): void
    {
        // 1. Add Project
        $projResponse = $this->actingAs($this->coordinatorWorship)
            ->post(route('commission.projects.store'), [
                'title'        => 'Holy Week Choir Workshop',
                'description'  => 'Intensive musical formation and rehearsal for the sacred Triduum celebrations.',
                'status'       => 'ongoing',
                'start_date'   => '2026-03-01',
                'end_date'     => '2026-03-15',
                'budget'       => 15000.00,
                'lead_user_id' => $this->coordinatorWorship->id,
            ]);

        $projResponse->assertRedirect(route('commission.projects'));
        $this->assertDatabaseHas('commission_projects', [
            'commission_id' => $this->commissionWorship->id,
            'title'         => 'Holy Week Choir Workshop',
            'status'        => 'ongoing',
        ]);

        // 2. Upload Document
        $file = UploadedFile::fake()->create('liturgy_guidelines.pdf', 1024, 'application/pdf');

        $docResponse = $this->actingAs($this->coordinatorWorship)
            ->post(route('commission.documents.store'), [
                'title'    => 'Liturgical Handbook 2026',
                'category' => 'Guidelines',
                'file'     => $file,
            ]);

        $docResponse->assertRedirect(route('commission.documents'));
        $this->assertDatabaseHas('commission_documents', [
            'commission_id' => $this->commissionWorship->id,
            'title'         => 'Liturgical Handbook 2026',
            'category'      => 'Guidelines',
        ]);
    }

    /**
     * Scenario 9: All 9 official commissions exist in the database with correct codes and active status.
     */
    public function test_all_eight_official_commissions_seeded_with_correct_codes(): void
    {
        $this->seed(CommissionSeeder::class);

        $expectedCommissions = [
            'WORSHIP'   => 'Commission on Worship',
            'EDUCATION' => 'Commission on Christian Education',
            'SOCIAL'    => 'Commission on Social Concerns',
            'TEMPORAL'  => 'Commission on Temporalities',
            'ECCLESIAL' => 'Commission on Ecclesial Communities',
            'FAMILY'    => 'Commission on Family and Life',
            'YOUTH'     => 'Commission on Youth',
            'CLERGY'    => 'Commission on Clergy and Consecrated Life',
            'SOCCOM'    => 'Commission on Social Communications and Mass Media',
        ];

        foreach ($expectedCommissions as $code => $name) {
            $this->assertDatabaseHas('commissions', [
                'code'      => $code,
                'name'      => $name,
                'is_active' => true,
            ]);
        }
    }

    /**
     * Scenario 10: Audit logs are written for commission and PPC modifications.
     */
    public function test_audit_logs_are_written_for_modifications(): void
    {
        // 1. Commission creation by Super Admin writes audit log
        $this->actingAs($this->superAdmin)->post(route('admin.commissions.store'), [
            'name'        => 'Commission on Sacred Heritage',
            'code'        => 'HERITAGE',
            'description' => 'Parish heritage museum and historical artifacts preservation.',
            'icon'        => 'building',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'commission_created',
        ]);

        // 2. PPC appointment by Super Admin writes audit log
        $candidate = User::create([
            'name'              => 'Secretary Candidate',
            'email'             => 'sec.candidate@pilarshrine.test',
            'password_hash'     => Hash::make('password123'),
            'role'              => 'staff',
            'is_verified'       => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($this->superAdmin)->post(route('admin.ppc.members.store'), [
            'user_id'    => $candidate->id,
            'role_title' => 'PPC Secretary',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'ppc.member_added',
        ]);
    }

    /**
     * Scenario 11: Creating a staff account as Commission Coordinator sets head_user_id and displays as Coordinator.
     */
    public function test_creating_staff_as_commission_coordinator_sets_head_user_and_displays_properly(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.staff.store'), [
            'name'                  => 'Mario Marbella',
            'email'                 => 'mario.test@pilarshrine.test',
            'role'                  => 'commission_admin',
            'organization'          => 'commission',
            'position'              => 'Commission Coordinator',
            'commission_ids'        => [$this->commissionYouth->id],
            'commission_roles'      => [$this->commissionYouth->id => 'coordinator'],
            'status'                => 'active',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect();

        $user = User::where('email', 'mario.test@pilarshrine.test')->firstOrFail();
        $this->commissionYouth->refresh();

        // Commission head_user_id must point to Mario Marbella
        $this->assertEquals($user->id, $this->commissionYouth->head_user_id);
        $this->assertEquals($user->id, $this->commissionYouth->coordinator->id);

        // Commission membership must reflect coordinator position and officer status
        $this->assertDatabaseHas('commission_memberships', [
            'user_id'       => $user->id,
            'commission_id' => $this->commissionYouth->id,
            'position'      => 'Commission Coordinator',
            'is_officer'    => true,
            'role'          => 'coordinator',
        ]);

        // Verify show view displays Coordinator properly
        $showResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.commissions.show', [$this->commissionYouth, 'tab' => 'members']));
        $showResponse->assertOk();
        $showResponse->assertSee('Mario Marbella');
        $showResponse->assertSee('HEAD COORDINATOR');
        $showResponse->assertSee('Commission Coordinator');

        // Verify index view displays Coordinator
        $indexResponse = $this->actingAs($this->superAdmin)
            ->get(route('admin.commissions.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Mario Marbella');
        $indexResponse->assertSee('Coordinator');
    }
}


