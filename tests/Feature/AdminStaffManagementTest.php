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
                'role' => 'Admin',
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
}

