<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LivestreamSetting;
use App\Models\MassSchedule;
use App\Models\Notification;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): User
    {
        return User::factory()->create(['role' => 'admin', ...$attributes]);
    }

    private function notification(User $user, array $attributes = []): Notification
    {
        return Notification::create([
            'user_id' => $user->id, 'type' => 'email', 'subject' => 'Ministry application received',
            'message' => 'A new request is ready for review.', 'status' => 'pending', ...$attributes,
        ]);
    }

    public function test_guests_cannot_read_or_change_settings(): void
    {
        $this->get('/admin/settings')->assertRedirect(route('login'));
        $this->get('/admin/notifications')->assertRedirect(route('login'));
        $this->get('/admin/settings/notifications/export')->assertRedirect(route('login'));
        foreach (['account', 'password', 'website', 'livestream'] as $section) {
            $this->put('/admin/settings/'.$section, [])->assertRedirect(route('login'));
        }
    }

    public function test_old_notifications_address_redirects_to_settings_with_filters(): void
    {
        $this->actingAs($this->admin())->get('/admin/notifications')
            ->assertStatus(301)->assertRedirect(route('admin.settings'));
        $filters = ['search' => 'Parish update', 'status' => 'pending', 'page' => 2];
        $this->get('/admin/notifications?'.http_build_query($filters))
            ->assertStatus(301)->assertRedirect(route('admin.settings', $filters));
    }

    public function test_settings_permissions_are_enforced_for_reads_and_writes(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->actingAs($user)->get('/admin/settings')->assertForbidden();
        $this->get('/admin/settings/notifications/export')->assertForbidden();
        foreach (['account', 'password', 'website', 'livestream'] as $section) {
            $this->put('/admin/settings/'.$section, [])->assertForbidden();
        }

        $viewer = $this->admin(['permissions' => ['view_settings']]);
        $this->actingAs($viewer)->get('/admin/settings')->assertOk()->assertSee('You have viewing access.');
        foreach (['account', 'password', 'website', 'livestream'] as $section) {
            $this->put('/admin/settings/'.$section, [])->assertForbidden();
        }
        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_edit_permission_alone_can_access_settings(): void
    {
        $this->actingAs($this->admin(['permissions' => ['edit_settings']]))
            ->get('/admin/settings')->assertOk()->assertSee('Save website settings');
    }

    public function test_settings_load_real_records_and_an_honest_empty_state(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertSee('Account &amp; website settings', false)->assertSee('No notifications yet.')
            ->assertDontSee('Sample records')->assertDontSee('CRUD actions will be connected later.');
        $this->notification($admin, ['subject' => 'Actual parish update']);
        $this->notification($this->admin(), ['subject' => 'Private update for another account']);
        $this->get('/admin/settings')->assertOk()->assertSee('Actual parish update')
            ->assertDontSee('Private update for another account')
            ->assertViewHas('notificationCounts', fn ($counts) => $counts->sum() === 1);
    }

    public function test_profile_updates_only_the_signed_in_account_and_allowed_fields(): void
    {
        $admin = $this->admin(['permissions' => ['view_settings', 'edit_settings']]);
        $other = $this->admin(['name' => 'Another account']);
        $this->actingAs($admin)->put('/admin/settings/account', [
            'name' => 'Updated Administrator', 'phone' => '09123456789', 'email' => $admin->email,
            'user_id' => $other->id, 'role' => 'super_admin', 'permissions' => ['*'],
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.settings').'#account');
        $admin->refresh();
        $this->assertSame('Updated Administrator', $admin->name);
        $this->assertSame('09123456789', $admin->phone);
        $this->assertSame('admin', $admin->role);
        $this->assertSame(['view_settings', 'edit_settings'], $admin->permissions);
        $this->assertSame('Another account', $other->fresh()->name);
        $this->get('/admin/settings')->assertSee('Updated Administrator');
    }

    public function test_email_change_requires_current_password_and_a_unique_address(): void
    {
        $admin = $this->admin();
        $data = ['name' => $admin->name, 'email' => 'updated@example.com'];
        $this->actingAs($admin)->from('/admin/settings')->put('/admin/settings/account', $data)
            ->assertSessionHasErrorsIn('account', ['current_password']);
        $this->assertNotSame('updated@example.com', $admin->fresh()->email);
        $other = $this->admin();
        $this->put('/admin/settings/account', [...$data, 'email' => $other->email, 'current_password' => 'password'])
            ->assertSessionHasErrorsIn('account', ['email']);
        $this->put('/admin/settings/account', [...$data, 'current_password' => 'password'])
            ->assertSessionHasNoErrors();
        $this->assertSame('updated@example.com', $admin->fresh()->email);
        $this->assertNull($admin->fresh()->email_verified_at);
    }

    public function test_password_change_validates_current_password_and_confirmation(): void
    {
        $admin = $this->admin();
        $data = ['current_password' => 'incorrect', 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
        $this->actingAs($admin)->from('/admin/settings')->put('/admin/settings/password', $data)
            ->assertSessionHasErrorsIn('password', ['current_password']);
        $this->assertTrue(Hash::check('password', $admin->fresh()->password_hash));
        $this->put('/admin/settings/password', [...$data, 'current_password' => 'password', 'password_confirmation' => 'mismatch'])
            ->assertSessionHasErrorsIn('password', ['password']);
        $this->put('/admin/settings/password', [...$data, 'current_password' => 'password'])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.settings').'#security');
        $this->assertTrue(Hash::check('NewPassword123!', $admin->fresh()->password_hash));
        $this->assertNotSame('NewPassword123!', $admin->fresh()->password_hash);
        $log = AuditLog::where('action', 'password_changed')->firstOrFail();
        $this->assertNull($log->old_values);
        $this->assertNull($log->new_values);
        $this->assertStringNotContainsString('NewPassword123!', $log->toJson());
    }

    public function test_google_login_credentials_are_not_changed_by_settings(): void
    {
        $admin = $this->admin(['google_id' => 'google-test']);
        $this->actingAs($admin)->get('/admin/settings')->assertSee('Manage Google security');
        $this->from('/admin/settings')->put('/admin/settings/account', [
            'name' => $admin->name, 'email' => 'different@example.com', 'current_password' => 'password',
        ])->assertSessionHasErrorsIn('account', ['email']);
        $this->put('/admin/settings/password', [])->assertForbidden();
    }

    public function test_website_changes_persist_and_are_returned_on_the_public_site(): void
    {
        $admin = $this->admin();
        $values = [...SiteSetting::defaults(), 'phone' => '0912-345-6789', 'email' => 'office@example.com',
            'office_hours' => "Monday to Friday: 9 AM to 4 PM\nWeekends: Closed"];
        $this->actingAs($admin)->put('/admin/settings/website', [...$values, 'updated_by' => 999])
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.settings').'#website');
        $this->assertDatabaseHas('site_settings', ['id' => 1, 'email' => 'office@example.com', 'updated_by' => $admin->id]);
        $this->getJson('/api/site-settings')->assertOk()->assertExactJson($values);
        $this->get('/')->assertOk()->assertSee('window.__SITE_SETTINGS__', false)->assertSee('office@example.com');
        $this->get('/admin/settings')->assertOk()->assertSee('office@example.com');
        $this->put('/admin/settings/website', [...$values, 'phone' => '09999999999'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('site_settings', 1);
    }

    public function test_public_bootstrap_escapes_text_that_contains_script_tags(): void
    {
        $admin = $this->admin(['name' => '</script><script>alert(1)</script>']);
        $values = [...SiteSetting::defaults(), 'address' => '</script><script>alert(2)</script>'];
        $this->actingAs($admin)->put('/admin/settings/website', $values)->assertSessionHasNoErrors();
        $this->get('/')->assertOk()->assertDontSee('</script><script>alert(', false);
        $this->getJson('/api/site-settings')->assertExactJson($values);
    }

    public function test_website_rejects_invalid_urls_without_changing_saved_values(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->from('/admin/settings')->put('/admin/settings/website', [
            ...SiteSetting::defaults(), 'facebook_url' => 'javascript:alert(1)', 'email' => 'invalid',
        ])->assertSessionHasErrorsIn('website', ['facebook_url', 'email']);
        $this->assertDatabaseCount('site_settings', 0);
    }

    public function test_broadcast_details_save_without_a_switch_and_schedule_controls_visibility(): void
    {
        Http::fake();
        config()->set('services.facebook.page_access_token', null);
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-09-20 07:30:00', 'Asia/Manila'));
        MassSchedule::query()->update(['is_livestreamed' => false]);
        MassSchedule::create([
            'title' => 'Sunday Holy Mass', 'day_of_week' => 'Sunday', 'start_time' => '07:30:00',
            'end_time' => '08:30:00', 'location' => 'Main Church', 'is_active' => true, 'is_livestreamed' => true,
        ]);
        Cache::put('facebook-live-status', ['is_live' => false], 60);
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertSee('Automatic livestream')->assertDontSee('id="livestream-live"', false);
        $this->put('/admin/settings/livestream', [
            'title' => 'Sunday Holy Mass', 'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.settings').'#livestream');
        $this->assertAuthenticatedAs($admin);
        $this->assertNull(Cache::get('facebook-live-status'));
        $this->getJson('/api/livestream-status')->assertJson([
            'is_live' => true, 'title' => 'Sunday Holy Mass', 'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ]);
        $this->travelTo(\Carbon\CarbonImmutable::parse('2026-09-20 08:50:00', 'Asia/Manila'));
        // Even a stale form sending the old manual flag cannot keep the button on.
        $this->put('/admin/settings/livestream', [
            'is_live' => 1, 'title' => 'Sunday Holy Mass', 'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ])->assertSessionHasNoErrors();
        $this->getJson('/api/livestream-status')->assertJson(['is_live' => false]);
        $this->assertDatabaseCount('livestream_settings', 1);
        $this->assertFalse(LivestreamSetting::firstOrFail()->is_live);
        Http::assertNothingSent();
    }

    public function test_livestream_rejects_unsafe_urls_and_preserves_saved_state(): void
    {
        $this->actingAs($this->admin())->from('/admin/settings')->put('/admin/settings/livestream', [
            'is_live' => 1, 'title' => 'Mass', 'url' => 'javascript:alert(1)',
        ])->assertSessionHasErrorsIn('livestream', ['url']);
        $this->assertFalse(LivestreamSetting::firstOrFail()->is_live);
    }

    public function test_notification_search_status_and_export_are_scoped_to_the_account(): void
    {
        $admin = $this->admin();
        $this->notification($admin, ['subject' => 'Matching pending update']);
        $this->notification($admin, ['subject' => 'Matching sent update', 'status' => 'sent']);
        $this->notification($this->admin(), ['subject' => 'Matching private update']);
        $this->actingAs($admin)->get('/admin/settings?search=Matching&status=pending')->assertOk()
            ->assertViewHas('notifications', fn ($rows) => $rows->pluck('subject')->all() === ['Matching pending update'])
            ->assertDontSee('Matching private update');
        $export = $this->get('/admin/settings/notifications/export?search=Matching&status=pending');
        $export->assertOk()->assertDownload();
        $csv = $export->streamedContent();
        $this->assertStringContainsString('Matching pending update', $csv);
        $this->assertStringNotContainsString('Matching sent update', $csv);
        $this->assertStringNotContainsString('Matching private update', $csv);
    }

    public function test_csv_export_escapes_spreadsheet_formulas(): void
    {
        $admin = $this->admin();
        $this->notification($admin, ['subject' => '=1+1']);
        $csv = $this->actingAs($admin)->get('/admin/settings/notifications/export')->streamedContent();
        $this->assertStringContainsString("'=1+1", $csv);
    }

    public function test_notifications_paginate_without_showing_other_accounts_records(): void
    {
        $admin = $this->admin();
        for ($i = 1; $i <= 12; $i++) {
            $this->notification($admin, ['subject' => 'Saved notice '.$i]);
        }
        $this->actingAs($admin)->get('/admin/settings')->assertOk()
            ->assertViewHas('notifications', fn ($rows) => $rows->total() === 12 && $rows->count() === 10);
        $this->get('/admin/settings?page=2')->assertOk()
            ->assertViewHas('notifications', fn ($rows) => $rows->count() === 2);
    }
}
