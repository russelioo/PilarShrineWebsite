<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminStaffManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        return User::factory()->create([
            'name' => 'Parish Administrator',
            'email' => 'admin@pilarshrine.test',
            'role' => 'admin',
            'is_verified' => true,
        ]);
    }

    public function test_admin_can_view_staff_management_page(): void
    {
        $admin = $this->createAdmin();

        $staffUser = User::factory()->create([
            'name' => 'Maria Santos',
            'email' => 'maria.staff@pilarshrine.test',
            'phone' => '0928 123 4567',
            'role' => 'staff',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff'));

        $response->assertOk();
        $response->assertSee('Staff Management');
        $response->assertSee('＋ Add New Staff');
        $response->assertSee('openStaffDrawer()', false);
        $response->assertSee('Maria Santos');
        $response->assertSee('maria.staff@pilarshrine.test');
        $response->assertSee('0928 123 4567');
        $response->assertSee('Active');
    }

    public function test_unauthenticated_user_cannot_access_or_create_staff(): void
    {
        $this->get(route('admin.staff'))->assertRedirect(route('login'));

        $this->postJson(route('admin.staff.store'), [
            'name' => 'New Staff',
            'email' => 'new@pilarshrine.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ])->assertUnauthorized();
    }

    public function test_non_admin_cannot_create_staff(): void
    {
        $parishioner = User::factory()->create([
            'role' => 'user',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($parishioner)->postJson(route('admin.staff.store'), [
            'name' => 'Attempted Staff',
            'email' => 'attempt@pilarshrine.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertForbidden();
    }

    public function test_admin_can_create_new_staff_account_via_ajax(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Bro. Antonio Luna',
            'email' => 'antonio.luna@pilarshrine.test',
            'phone' => '0917 888 9999',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Sanctuary2026!',
            'password_confirmation' => 'Sanctuary2026!',
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'message' => 'Staff account created successfully.',
            'user' => [
                'name' => 'Bro. Antonio Luna',
                'email' => 'antonio.luna@pilarshrine.test',
                'phone' => '0917 888 9999',
                'role' => 'Staff',
                'status' => 'Active',
            ],
        ]);

        // Verify password is not exposed in JSON
        $this->assertArrayNotHasKey('password', $response->json('user'));
        $this->assertArrayNotHasKey('password_hash', $response->json('user'));

        // Verify database state
        $newUser = User::where('email', 'antonio.luna@pilarshrine.test')->first();
        $this->assertNotNull($newUser);
        $this->assertSame('Bro. Antonio Luna', $newUser->name);
        $this->assertSame('staff', $newUser->role);
        $this->assertTrue($newUser->is_verified);
        $this->assertNotNull($newUser->email_verified_at);
        $this->assertTrue(Hash::check('Sanctuary2026!', $newUser->password_hash));
    }

    public function test_admin_can_create_inactive_staff_account(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Sister Clara',
            'email' => 'clara@pilarshrine.test',
            'phone' => '+639191234567',
            'role' => 'staff',
            'status' => 'inactive',
            'password' => 'ClaraPass123!',
            'password_confirmation' => 'ClaraPass123!',
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), $payload);

        $response->assertCreated();
        $response->assertJson([
            'success' => true,
            'user' => [
                'status' => 'Inactive',
                'status_raw' => 'inactive',
            ],
        ]);

        $newUser = User::where('email', 'clara@pilarshrine.test')->first();
        $this->assertNotNull($newUser);
        $this->assertFalse($newUser->is_verified);
        $this->assertNull($newUser->email_verified_at);
    }

    public function test_admin_can_create_new_admin_account(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'Assistant Rector',
            'email' => 'rector.assistant@pilarshrine.test',
            'role' => 'admin',
            'status' => 'active',
            'password' => 'AdminHolyPass2026!',
            'password_confirmation' => 'AdminHolyPass2026!',
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), $payload);

        $response->assertCreated();
        $response->assertJson([
            'user' => [
                'role'     => 'Super Admin', // admin role maps to "Super Admin" via role_badge_label
                'role_raw' => 'admin',
            ],
        ]);

        $newUser = User::where('email', 'rector.assistant@pilarshrine.test')->first();
        $this->assertSame('admin', $newUser->role);
    }

    public function test_staff_creation_with_profile_photo_upload(): void
    {
        Storage::fake('public');
        $admin = $this->createAdmin();

        $file = UploadedFile::fake()->image('staff_avatar.jpg', 200, 200);

        $payload = [
            'name' => 'Photo Staff',
            'email' => 'photo.staff@pilarshrine.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'PhotoPass123!',
            'password_confirmation' => 'PhotoPass123!',
            'profile_photo' => $file,
        ];

        $response = $this->actingAs($admin)->post(route('admin.staff.store'), $payload, [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertCreated();
        $user = User::where('email', 'photo.staff@pilarshrine.test')->first();
        $this->assertNotNull($user->avatar);
        $this->assertStringStartsWith('/storage/avatars/', $user->avatar);

        $storedPath = str_replace('/storage/', '', $user->avatar);
        Storage::disk('public')->assertExists($storedPath);
    }

    public function test_validation_fails_for_empty_required_fields(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name', 'email', 'role', 'status', 'password']);
    }

    public function test_validation_fails_for_duplicate_email(): void
    {
        $admin = $this->createAdmin();

        User::factory()->create([
            'email' => 'existing@pilarshrine.test',
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), [
            'name' => 'Duplicate Email Staff',
            'email' => 'existing@pilarshrine.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['email']);
    }

    public function test_validation_fails_for_mismatched_password(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), [
            'name' => 'Mismatch Pass Staff',
            'email' => 'mismatch@pilarshrine.test',
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['password']);
    }

    public function test_validation_fails_for_invalid_phone_number(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), [
            'name' => 'Bad Phone Staff',
            'email' => 'badphone@pilarshrine.test',
            'phone' => '123456', // Invalid PH phone
            'role' => 'staff',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['phone']);
    }

    public function test_staff_topbar_renders_dynamic_logged_in_user_profile_and_avatar(): void
    {
        $staff = User::factory()->create([
            'name' => 'John Russel Soreda',
            'email' => 'soredajohnrussel15@gmail.com',
            'role' => 'staff',
            'avatar' => '/storage/avatars/custom-staff-avatar.jpg',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.dashboard'));

        $response->assertOk();
        $response->assertSee('John Russel Soreda');
        $response->assertSee('soredajohnrussel15@gmail.com');
        $response->assertSee('/storage/avatars/custom-staff-avatar.jpg');
        $response->assertDontSee('Staff Member');
    }

    public function test_staff_profile_settings_renders_dynamic_user_data(): void
    {
        $staff = User::factory()->create([
            'name' => 'John Russel Soreda',
            'email' => 'soredajohnrussel15@gmail.com',
            'role' => 'staff',
            'phone' => '0919 555 7777',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($staff)->get(route('staff.profile-settings'));

        $response->assertOk();
        $response->assertSee('John Russel Soreda');
        $response->assertSee('soredajohnrussel15@gmail.com');
        $response->assertSee('0919 555 7777');
        $response->assertDontSee('Maria Santos');
    }

    public function test_parish_secretary_displays_proper_organization_and_dash_for_commission_ministry(): void
    {
        $admin = $this->createAdmin();

        $secretary = User::factory()->create([
            'name' => 'Angela Gwyn Mansanero',
            'email' => 'mansaneroangelagwyn22@gmail.com',
            'role' => 'parish_secretary',
            'organization' => 'parish_administration',
            'position' => 'Parish Secretary',
            'responsibilities' => 'Administrative Staff',
            'commission_id' => null,
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff'));

        $response->assertOk();
        $response->assertSee('Angela Gwyn Mansanero');
        $response->assertSee('mansaneroangelagwyn22@gmail.com');
        $response->assertSee('Parish Secretary');
        $response->assertSee('Parish Administration');
        $response->assertSee('Administrative Staff');
        $response->assertSee('<span class="commission-badge commission-none">—</span>', false);
        $this->assertFalse($secretary->hasParishWideCommissionOversight());
        $this->assertSame('—', $secretary->commission_ministry_summary);
    }

    public function test_admin_can_create_staff_with_multiple_commissions_and_ministries(): void
    {
        $admin = $this->createAdmin();

        $comm1 = \App\Models\Commission::create(['name' => 'Social Communications', 'slug' => 'soccom', 'is_active' => true]);
        $comm2 = \App\Models\Commission::create(['name' => 'Youth Commission', 'slug' => 'youth', 'is_active' => true]);
        $min1 = \App\Models\Ministry::create([
            'name' => 'Altar Servers',
            'slug' => 'altar-servers',
            'description' => 'Altar servers ministry description',
            'commission_id' => $comm1->id,
            'is_accepting_members' => true,
        ]);

        $payload = [
            'name' => 'Multi Org Member',
            'email' => 'multiorg@pilarshrine.test',
            'role' => 'commission_member',
            'organization' => 'commission',
            'position' => 'Youth Coordinator',
            'responsibilities' => 'Youth Programs & Media',
            'status' => 'active',
            'commission_ids' => [$comm1->id, $comm2->id],
            'commission_roles' => [
                $comm1->id => 'coordinator',
                $comm2->id => 'member',
            ],
            'ministry_ids' => [$min1->id],
            'ministry_roles' => [
                $min1->id => 'officer',
            ],
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), $payload);

        $response->assertCreated();

        $created = User::where('email', 'multiorg@pilarshrine.test')->first();
        $this->assertNotNull($created);
        $this->assertSame('Youth Coordinator', $created->position);
        $this->assertSame('Youth Programs & Media', $created->responsibilities);
        $this->assertCount(2, $created->commissions);
        $this->assertCount(1, $created->ministries);
        $this->assertDatabaseHas('commission_memberships', [
            'user_id' => $created->id,
            'commission_id' => $comm1->id,
            'role' => 'coordinator',
        ]);
        $this->assertDatabaseHas('ministry_memberships', [
            'user_id' => $created->id,
            'ministry_id' => $min1->id,
            'role' => 'officer',
        ]);
    }

    public function test_admin_can_sort_staff_by_name(): void
    {
        $admin = $this->createAdmin();

        User::factory()->create(['name' => 'Zara Morales', 'email' => 'zara@pilarshrine.test', 'role' => 'staff']);
        User::factory()->create(['name' => 'Albert Cruz', 'email' => 'albert@pilarshrine.test', 'role' => 'staff']);

        $response = $this->actingAs($admin)->get(route('admin.staff', ['sort' => 'name']));

        $response->assertOk();
        $staffItems = $response->viewData('staff');
        $this->assertGreaterThanOrEqual(2, $staffItems->count());
        $names = $staffItems->pluck('name')->all();
        $this->assertTrue(
            array_search('Albert Cruz', $names) < array_search('Zara Morales', $names),
            'Albert Cruz should appear before Zara Morales when sorted by name.'
        );
    }

    public function test_admin_can_export_staff_to_csv(): void
    {
        $admin = $this->createAdmin();

        User::factory()->create([
            'name' => 'Carlos Mendoza',
            'email' => 'carlos.mendoza@pilarshrine.test',
            'phone' => '09171234567',
            'role' => 'staff',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff', ['export' => 'csv']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Carlos Mendoza', $content);
        $this->assertStringContainsString('carlos.mendoza@pilarshrine.test', $content);
        $this->assertStringContainsString('09171234567', $content);
        $this->assertStringContainsString('Staff', $content);
    }

    public function test_staff_page_has_sort_dropdown_beside_export_and_no_apply_button(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('admin.staff'));

        $response->assertOk();
        $response->assertSee('id="staff-sort-select"', false);
        $response->assertSee('handleSortChange(this.value)', false);
        $response->assertSee('exportStaffData()', false);
        $response->assertDontSee('>Apply</button>', false);
    }

    public function test_admin_can_create_staff_with_module_permissions(): void
    {
        $admin = $this->createAdmin();

        $payload = [
            'name' => 'John Secretariat',
            'email' => 'john.sec@pilarshrine.test',
            'role' => 'parish_secretary',
            'status' => 'active',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'permissions' => ['messages', 'parishioners', 'ministry_requests', 'mass_schedules', 'announcements', 'donations'],
        ];

        $response = $this->actingAs($admin)->postJson(route('admin.staff.store'), $payload);
        $response->assertCreated();

        $user = User::where('email', 'john.sec@pilarshrine.test')->first();
        $this->assertNotNull($user);
        $this->assertTrue($user->hasPermission('messages'));
        $this->assertTrue($user->hasPermission('parishioners'));
        $this->assertTrue($user->hasPermission('donations'));
        $this->assertFalse($user->hasPermission('staff_management'));
        $this->assertFalse($user->hasPermission('manage_ministries'));
    }

    public function test_admin_can_get_staff_permissions(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($admin)->getJson(route('admin.staff.permissions', $staff));
        $response->assertOk();
        $response->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'role', 'role_label', 'organization', 'organization_label'],
            'current_permissions',
            'default_permissions',
            'available_permissions',
        ]);
    }

    public function test_admin_can_update_staff_permissions(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->create(['role' => 'staff']);

        $newPerms = ['view_users', 'view_announcements', 'create_announcements'];
        $response = $this->actingAs($admin)->putJson(route('admin.staff.permissions.update', $staff), [
            'permissions' => $newPerms,
        ]);

        $response->assertOk();
        $staff->refresh();
        $this->assertEquals($newPerms, $staff->permissions);
        $this->assertTrue($staff->hasPermission('create_announcements'));
        $this->assertFalse($staff->hasPermission('delete_announcements'));
    }

    public function test_admin_can_revert_staff_member_to_parishioner(): void
    {
        $admin = $this->createAdmin();
        $commission = \App\Models\Commission::create([
            'name' => 'Commission on Youth',
            'slug' => 'commission-on-youth',
            'is_active' => true,
        ]);

        $staff = User::factory()->create([
            'name' => 'Bro. Mateo Silva',
            'role' => 'staff',
            'organization' => 'commission',
            'position' => 'Commission Member',
            'responsibilities' => 'Commission Member',
            'commission_id' => $commission->id,
            'permissions' => ['view_messages', 'view_announcements'],
            'is_verified' => true,
        ]);

        \App\Models\CommissionMembership::create([
            'user_id' => $staff->id,
            'commission_id' => $commission->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->assertEquals(1, \App\Models\CommissionMembership::where('user_id', $staff->id)->count());

        $response = $this->actingAs($admin)->postJson(route('admin.staff.revert', $staff));
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $staff->id,
                'role' => 'user',
            ],
        ]);

        $staff->refresh();
        $this->assertEquals('user', $staff->role);
        $this->assertEquals('parishioner', $staff->organization);
        $this->assertEquals('Parishioner', $staff->position);
        $this->assertEquals('Parishioner', $staff->responsibilities);
        $this->assertNull($staff->commission_id);
        $this->assertNull($staff->permissions);
        $this->assertEquals(0, \App\Models\CommissionMembership::where('user_id', $staff->id)->count());
    }

    public function test_reverted_staff_appears_in_parishioners_and_not_in_staff(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->create([
            'name' => 'Sister Clara Delgado',
            'email' => 'clara.delgado@pilarshrine.test',
            'role' => 'staff',
            'is_verified' => true,
        ]);

        // Visible in staff list before revert
        $response = $this->actingAs($admin)->get(route('admin.staff'));
        $response->assertSee('Sister Clara Delgado');

        // Execute revert
        $this->actingAs($admin)->postJson(route('admin.staff.revert', $staff))->assertOk();

        // No longer in staff list
        $staffResponse = $this->actingAs($admin)->get(route('admin.staff'));
        $staffResponse->assertDontSee('Sister Clara Delgado');

        // Now present in parishioners list
        $parishionerResponse = $this->actingAs($admin)->get(route('admin.parishioners'));
        $parishionerResponse->assertSee('Sister Clara Delgado');
    }

    public function test_admin_cannot_revert_themselves_or_super_admin(): void
    {
        $admin = $this->createAdmin();
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'is_verified' => true,
        ]);

        // Attempt self-revert
        $this->actingAs($admin)->postJson(route('admin.staff.revert', $admin))
            ->assertForbidden();

        // Attempt super admin revert
        $this->actingAs($admin)->postJson(route('admin.staff.revert', $superAdmin))
            ->assertForbidden();
    }

    public function test_unauthorized_user_cannot_revert_staff(): void
    {
        $parishioner = User::factory()->create(['role' => 'user']);
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($parishioner)->postJson(route('admin.staff.revert', $staff))
            ->assertForbidden();
    }

    public function test_super_admin_and_admin_can_promote_parishioner_to_staff(): void
    {
        $admin = $this->createAdmin();
        $commission = \App\Models\Commission::create([
            'name' => 'Commission on Social Communications',
            'slug' => 'commission-on-social-communications',
            'is_active' => true,
        ]);

        $parishioner = User::factory()->create([
            'name' => 'Angela Gwyn Mansanero',
            'email' => 'mansaneroangelagwyn22@gmail.com',
            'role' => 'user',
            'organization' => 'parishioner',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->postJson(route('admin.parishioners.promote', $parishioner), [
            'role' => 'parish_secretary',
            'organization' => 'parish_administration',
            'position' => 'Parish Secretary',
            'commission_id' => $commission->id,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'user' => [
                'id' => $parishioner->id,
                'role' => 'parish_secretary',
                'position' => 'Parish Secretary',
            ],
        ]);

        $parishioner->refresh();
        $this->assertEquals('parish_secretary', $parishioner->role);
        $this->assertEquals('parish_administration', $parishioner->organization);
        $this->assertEquals('Parish Secretary', $parishioner->position);
        $this->assertEquals($commission->id, $parishioner->commission_id);
        $this->assertEquals(1, \App\Models\CommissionMembership::where('user_id', $parishioner->id)->count());
    }

    public function test_promoted_parishioner_leaves_parishioners_and_appears_in_staff(): void
    {
        $admin = $this->createAdmin();
        $parishioner = User::factory()->create([
            'name' => 'Bro. Eduardo Ramos',
            'role' => 'user',
            'is_verified' => true,
        ]);

        // Present in parishioners
        $this->actingAs($admin)->get(route('admin.parishioners'))->assertSee('Bro. Eduardo Ramos');

        // Promote
        $this->actingAs($admin)->postJson(route('admin.parishioners.promote', $parishioner), [
            'role' => 'staff',
            'organization' => 'parish_administration',
            'position' => 'Administrative Staff',
        ])->assertOk();

        // No longer in parishioners
        $this->actingAs($admin)->get(route('admin.parishioners'))->assertDontSee('Bro. Eduardo Ramos');

        // Now in staff
        $this->actingAs($admin)->get(route('admin.staff'))->assertSee('Bro. Eduardo Ramos');
    }

    public function test_non_admin_cannot_promote_parishioner(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $commissionAdmin = User::factory()->create(['role' => 'commission_admin']);
        $regularUser = User::factory()->create(['role' => 'user']);
        $target = User::factory()->create(['role' => 'user']);

        $payload = [
            'role' => 'staff',
            'organization' => 'parish_administration',
            'position' => 'Staff Member',
        ];

        // Staff cannot promote
        $this->actingAs($staff)->postJson(route('admin.parishioners.promote', $target), $payload)
            ->assertForbidden();

        // Commission Admin cannot promote
        $this->actingAs($commissionAdmin)->postJson(route('admin.parishioners.promote', $target), $payload)
            ->assertForbidden();

        // Regular user cannot promote
        $this->actingAs($regularUser)->postJson(route('admin.parishioners.promote', $target), $payload)
            ->assertForbidden();
    }

    public function test_cannot_promote_user_who_is_already_staff(): void
    {
        $admin = $this->createAdmin();
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($admin)->postJson(route('admin.parishioners.promote', $staff), [
            'role' => 'admin',
            'organization' => 'parish_administration',
        ])->assertStatus(422);
    }

    public function test_super_admin_cannot_have_permissions_viewed_or_modified(): void
    {
        $admin = $this->createAdmin();
        $superAdmin = User::factory()->create([
            'role' => 'super_admin',
            'is_verified' => true,
        ]);

        $this->actingAs($admin)->getJson(route('admin.staff.permissions', $superAdmin))
            ->assertForbidden();

        $this->actingAs($admin)->putJson(route('admin.staff.permissions.update', $superAdmin), [
            'permissions' => ['view_users'],
        ])->assertForbidden();
    }

    public function test_super_admin_has_no_action_buttons_in_staff_management_view(): void
    {
        $admin = $this->createAdmin();
        $superAdmin = User::factory()->create([
            'name' => 'Archbishop Emeritus',
            'role' => 'super_admin',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.staff'));
        $response->assertOk();
        $response->assertSee('Archbishop Emeritus');
        $response->assertSee('Super Administrator (Protected)');
    }

    public function test_non_parish_wide_admin_cannot_view_or_modify_other_commissions_permissions(): void
    {
        $comm1 = \App\Models\Commission::create(['name' => 'Comm 1', 'slug' => 'comm-1', 'is_active' => true]);
        $comm2 = \App\Models\Commission::create(['name' => 'Comm 2', 'slug' => 'comm-2', 'is_active' => true]);

        $commAdmin = User::factory()->create([
            'role' => 'commission_admin',
            'organization' => 'commission',
            'commission_id' => $comm1->id,
            'permissions' => ['modify_permissions', 'view_permissions', 'staff_management'],
            'is_verified' => true,
        ]);

        $otherStaff = User::factory()->create([
            'role' => 'staff',
            'organization' => 'commission',
            'commission_id' => $comm2->id,
            'is_verified' => true,
        ]);

        $this->actingAs($commAdmin)->getJson(route('admin.staff.permissions', $otherStaff))
            ->assertForbidden();

        $this->actingAs($commAdmin)->putJson(route('admin.staff.permissions.update', $otherStaff), [
            'permissions' => ['view_users'],
        ])->assertForbidden();
    }
}


