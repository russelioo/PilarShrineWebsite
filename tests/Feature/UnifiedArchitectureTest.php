<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Ministry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnifiedArchitectureTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $secretary;
    protected User $commCoordinator;
    protected Commission $commissionLiturgical;
    protected Commission $commissionCatechesis;
    protected Ministry $ministryChoir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commissionLiturgical = Commission::create([
            'name' => 'Commission on Liturgy and Worship',
            'slug' => 'commission-on-liturgy-and-worship',
            'code' => 'CLW',
            'description' => 'Liturgical services',
        ]);

        $this->commissionCatechesis = Commission::create([
            'name' => 'Commission on Catechesis and Education',
            'slug' => 'commission-on-catechesis-and-education',
            'code' => 'CCE',
            'description' => 'Faith formation',
        ]);

        $this->ministryChoir = Ministry::create([
            'name' => 'Parish Music Ministry',
            'slug' => 'parish-music-ministry',
            'code' => 'PMM',
            'commission_id' => $this->commissionLiturgical->id,
            'description' => 'Music and choir',
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Administrator',
            'first_name' => 'Super',
            'last_name' => 'Administrator',
            'email' => 'superadmin@pilarshrine.test',
            'role' => 'super_admin',
            'organization' => 'parish_administration',
            'position' => 'System Administrator',
            'responsibilities' => 'Overall Portal & Database Governance',
            'is_verified' => true,
            'password_hash' => bcrypt('Password123!'),
        ]);

        $this->secretary = User::create([
            'name' => 'Angela Gwyn Mansanero',
            'first_name' => 'Angela Gwyn',
            'last_name' => 'Mansanero',
            'email' => 'angela.mansanero@pilarshrine.test',
            'role' => 'parish_secretary',
            'organization' => 'parish_administration',
            'position' => 'Parish Secretary',
            'responsibilities' => 'Parish Records & Sacramental Documents',
            'commission_id' => null,
            'is_verified' => true,
            'password_hash' => bcrypt('Password123!'),
        ]);

        $this->commCoordinator = User::create([
            'name' => 'Liturgy Coordinator User',
            'first_name' => 'Liturgy',
            'last_name' => 'Coordinator',
            'email' => 'liturgy.coord@pilarshrine.test',
            'role' => 'commission_admin',
            'organization' => 'commission',
            'position' => 'Commission Coordinator',
            'responsibilities' => 'Liturgical Schedule Coordination',
            'commission_id' => $this->commissionLiturgical->id,
            'is_verified' => true,
            'password_hash' => bcrypt('Password123!'),
        ]);
    }

    public function test_angela_gwyn_mansanero_displays_no_commission_instead_of_all_commissions(): void
    {
        // Assert on model scope
        $this->assertFalse($this->secretary->hasParishWideCommissionOversight(), 'Parish Secretary should not have parish-wide commission administrative oversight.');
        $this->assertSame('—', $this->secretary->commission_ministry_summary);

        // Assert on Staff view output
        $response = $this->actingAs($this->superAdmin)->get(route('admin.staff'));
        $response->assertOk();

        // The view should render the commission-none badge for Angela Gwyn Mansanero
        $response->assertSee('Angela Gwyn Mansanero');
        $response->assertSee('<span class="commission-badge commission-none">—</span>', false);

        // Super Admin, on the other hand, has parish-wide commission oversight
        $this->assertTrue($this->superAdmin->hasParishWideCommissionOversight());
        $response->assertSee('<span class="commission-badge commission-all">All Commissions</span>', false);
    }

    public function test_can_fetch_user_permissions_catalog_and_defaults(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('admin.staff.permissions', $this->secretary));

        $response->assertOk();
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'role_label',
                'organization',
                'organization_label',
                'position',
                'responsibilities',
                'is_super_admin',
            ],
            'current_permissions',
            'default_permissions',
            'available_permissions',
        ]);

        $response->assertJsonPath('user.name', 'Angela Gwyn Mansanero');
        $response->assertJsonPath('user.role', 'parish_secretary');
        $this->assertArrayHasKey('dashboard', $response->json('available_permissions'));
        $this->assertArrayHasKey('documents', $response->json('available_permissions'));
    }

    public function test_super_admin_can_update_user_permissions_and_audit_log_is_recorded(): void
    {
        $newPerms = [
            'view_dashboard',
            'view_documents',
            'issue_certificates',
            'view_announcements',
        ];

        $response = $this->actingAs($this->superAdmin)
            ->putJson(route('admin.staff.permissions.update', $this->secretary), [
                'permissions' => $newPerms,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $this->secretary->id,
                'permissions_count' => 4,
            ],
        ]);

        $this->secretary->refresh();
        $this->assertEqualsCanonicalizing($newPerms, $this->secretary->permissions);
        $this->assertTrue($this->secretary->hasPermission('issue_certificates'));
        $this->assertFalse($this->secretary->hasPermission('delete_users'));

        // Verify Audit Log entry
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'permission_updated',
            'user_id' => $this->superAdmin->id,
            'target_id' => $this->secretary->id,
        ]);
    }

    public function test_unauthorized_user_cannot_update_permissions(): void
    {
        $response = $this->actingAs($this->commCoordinator)
            ->putJson(route('admin.staff.permissions.update', $this->secretary), [
                'permissions' => ['view_dashboard'],
            ]);

        $response->assertStatus(403);
    }

    public function test_can_fetch_user_details_modal_payload(): void
    {
        // Connect secretary to a commission and ministry to test connections in details
        $this->secretary->commissions()->attach($this->commissionLiturgical->id, ['role' => 'member']);
        $this->secretary->ministries()->attach($this->ministryChoir->id, ['role' => 'officer']);

        $response = $this->actingAs($this->superAdmin)
            ->getJson(route('admin.staff.show', $this->secretary));

        $response->assertOk();
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'organization',
                'position',
                'responsibilities',
                'commissions',
                'ministries',
                'permissions_summary' => ['granted', 'restricted'],
            ],
        ]);

        $response->assertJsonPath('user.name', 'Angela Gwyn Mansanero');
        $response->assertJsonCount(1, 'user.commissions');
        $response->assertJsonCount(1, 'user.ministries');
        $response->assertJsonPath('user.commissions.0.name', 'Commission on Liturgy and Worship');
        $response->assertJsonPath('user.ministries.0.name', 'Parish Music Ministry');
    }

    public function test_multi_organization_context_switching(): void
    {
        // Connect user to multiple organizations
        $this->secretary->commissions()->attach($this->commissionLiturgical->id, ['role' => 'member']);
        $this->secretary->commissions()->attach($this->commissionCatechesis->id, ['role' => 'coordinator']);

        $available = $this->secretary->getAvailableOrganizations();
        $this->assertCount(3, $available, 'Should have Parish Admin + 2 Commissions available.');

        // Switch active context to Liturgy Commission
        $response = $this->actingAs($this->secretary)
            ->postJson(route('admin.organization-context.switch'), [
                'organization_type' => 'commission',
                'organization_id' => $this->commissionLiturgical->id,
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'context' => [
                'type' => 'commission',
                'id' => $this->commissionLiturgical->id,
                'name' => 'Commission on Liturgy and Worship',
            ],
        ]);

        $response->assertSessionHas('active_organization_context');
    }

    public function test_user_cannot_switch_to_unassigned_organization(): void
    {
        // Secretary is not attached to Catechesis Commission
        $response = $this->actingAs($this->secretary)
            ->postJson(route('admin.organization-context.switch'), [
                'organization_type' => 'commission',
                'organization_id' => $this->commissionCatechesis->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_user_without_audit_log_permission_cannot_access_audit_logs_or_see_sidebar_link(): void
    {
        // Custom permissions with view_audit_logs omitted
        $this->secretary->permissions = ['view_dashboard', 'view_messages', 'parishioners', 'view_users'];
        $this->secretary->save();

        $this->assertFalse($this->secretary->hasPermission('view_audit_logs'));
        $this->assertFalse($this->secretary->hasPermission('audit_logs'));

        // Direct navigation forbidden
        $response = $this->actingAs($this->secretary)->get(route('admin.audit-logs'));
        $response->assertStatus(403);

        // Sidebar should NOT show Audit Logs link
        $pageResponse = $this->actingAs($this->secretary)->get(route('admin.parishioners'));
        $pageResponse->assertOk();
        $pageResponse->assertDontSee(route('admin.audit-logs'));
    }

    public function test_user_with_audit_log_permission_can_access_audit_logs_and_sees_sidebar_link(): void
    {
        // Custom permissions with view_audit_logs included
        $this->secretary->permissions = ['view_dashboard', 'view_audit_logs', 'parishioners', 'view_users'];
        $this->secretary->save();

        $this->assertTrue($this->secretary->hasPermission('view_audit_logs'));

        // Direct navigation allowed
        $response = $this->actingAs($this->secretary)->get(route('admin.audit-logs'));
        $response->assertOk();

        // Sidebar should show Audit Logs link
        $pageResponse = $this->actingAs($this->secretary)->get(route('admin.parishioners'));
        $pageResponse->assertOk();
        $pageResponse->assertSee(route('admin.audit-logs'));
    }

    public function test_permission_alias_bidirectional_resolution(): void
    {
        $testUser = User::create([
            'name' => 'Alias Tester',
            'first_name' => 'Alias',
            'last_name' => 'Tester',
            'email' => 'alias@pilarshrine.test',
            'role' => 'staff',
            'permissions' => ['view_users', 'create_announcements', 'messages'],
            'is_verified' => true,
            'password_hash' => bcrypt('password'),
        ]);

        // Dot notation and underscore notation
        $this->assertTrue($testUser->hasPermission('users.view'));
        $this->assertTrue($testUser->hasPermission('view_users'));
        $this->assertTrue($testUser->hasPermission('announcements.create'));
        $this->assertTrue($testUser->hasPermission('create_announcements'));

        // High level module alias
        $this->assertTrue($testUser->hasPermission('messages'));
        $this->assertTrue($testUser->hasPermission('view_messages'));
        $this->assertTrue($testUser->hasPermission('parishioners')); // aliases view_users

        // Negative checks
        $this->assertFalse($testUser->hasPermission('delete_users'));
        $this->assertFalse($testUser->hasPermission('users.delete'));
    }

    public function test_module_access_controlled_by_configured_permission(): void
    {
        $user = User::create([
            'name' => 'Custom Perm User',
            'first_name' => 'Custom',
            'last_name' => 'User',
            'email' => 'custom@pilarshrine.test',
            'role' => 'staff',
            'permissions' => ['view_dashboard'], // Only dashboard
            'is_verified' => true,
            'password_hash' => bcrypt('password'),
        ]);

        // Announcements: denied without view_announcements
        $response = $this->actingAs($user)->get(route('admin.announcements'));
        $response->assertStatus(403);
        $response->assertSee('Access Restricted');

        // Ministries: denied without view_ministries
        $response = $this->actingAs($user)->get(route('admin.ministries'));
        $response->assertStatus(403);
        $response->assertSee('Access Restricted');

        // Donations: denied without donations
        $response = $this->actingAs($user)->get(route('admin.donations'));
        $response->assertStatus(403);
        $response->assertSee('Access Restricted');

        // Mass Schedules: denied without mass_schedules
        $response = $this->actingAs($user)->get(route('admin.mass-schedules'));
        $response->assertStatus(403);

        // Grant the permissions
        $user->permissions = [
            'view_dashboard',
            'view_announcements',
            'view_ministries',
            'donations',
            'mass_schedules',
            'view_messages',
        ];
        $user->save();

        // Now all modules are accessible
        $this->actingAs($user)->get(route('admin.announcements'))->assertOk();
        $this->actingAs($user)->get(route('admin.ministries'))->assertOk();
        $this->actingAs($user)->get(route('admin.donations'))->assertOk();
        $this->actingAs($user)->get(route('admin.mass-schedules'))->assertOk();
        $this->actingAs($user)->get(route('inquiries.index'))->assertOk();
    }

    public function test_action_level_permissions_render_buttons_conditionally(): void
    {
        // User with only view permission
        $viewer = User::create([
            'name' => 'Viewer Only',
            'first_name' => 'Viewer',
            'last_name' => 'Only',
            'email' => 'viewer@pilarshrine.test',
            'role' => 'staff',
            'permissions' => ['view_dashboard', 'view_announcements', 'view_ministries', 'view_users', 'staff_management'],
            'is_verified' => true,
            'password_hash' => bcrypt('password'),
        ]);

        // In announcements view: has view_announcements but NOT create_announcements
        $res = $this->actingAs($viewer)->get(route('admin.announcements'));
        $res->assertOk();
        $res->assertDontSee('+ New Announcement');

        // In ministries view: has view_ministries but NOT create_ministries
        $res = $this->actingAs($viewer)->get(route('admin.ministries'));
        $res->assertOk();
        $res->assertDontSee('+ Add New Ministry');

        // In staff view: has staff_management/view_users but NOT create_users
        $res = $this->actingAs($viewer)->get(route('admin.staff'));
        $res->assertOk();
        $res->assertDontSee('＋ Add New Staff');

        // Grant create permissions
        $creator = User::create([
            'name' => 'Creator User',
            'first_name' => 'Creator',
            'last_name' => 'User',
            'email' => 'creator@pilarshrine.test',
            'role' => 'staff',
            'permissions' => ['view_dashboard', 'view_announcements', 'create_announcements', 'view_ministries', 'create_ministries', 'staff_management', 'create_users'],
            'is_verified' => true,
            'password_hash' => bcrypt('password'),
        ]);

        $res = $this->actingAs($creator)->get(route('admin.announcements'));
        $res->assertOk();
        $res->assertSee('+ New Announcement');

        $res = $this->actingAs($creator)->get(route('admin.ministries'));
        $res->assertOk();
        $res->assertSee('+ Add New Ministry');

        $res = $this->actingAs($creator)->get(route('admin.staff'));
        $res->assertOk();
        $res->assertSee('＋ Add New Staff');
    }

    public function test_super_admin_bypass_retains_full_access(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.announcements'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.ministries'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.donations'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.mass-schedules'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.audit-logs'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.parishioners'))->assertOk();
        $this->actingAs($this->superAdmin)->get(route('admin.staff'))->assertOk();
    }

    public function test_unauthorized_access_renders_branded_403_page(): void
    {
        $restrictedUser = User::create([
            'name' => 'Restricted User',
            'first_name' => 'Restricted',
            'last_name' => 'User',
            'email' => 'restricted@pilarshrine.test',
            'role' => 'user',
            'permissions' => [],
            'is_verified' => true,
            'password_hash' => bcrypt('password'),
        ]);

        $response = $this->actingAs($restrictedUser)->get(route('admin.announcements'));
        $response->assertStatus(403);
        $response->assertSee('Access Restricted');
        $response->assertSee('Return to Portal');
    }
}

