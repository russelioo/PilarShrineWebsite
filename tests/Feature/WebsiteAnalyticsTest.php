<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class WebsiteAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    private function capture(array $extra = [])
    {
        return $this->postJson('/api/analytics/events', [
            'event_id' => (string) Str::uuid(), 'event_type' => 'page_view', 'page' => 'home', ...$extra,
        ]);
    }

    private function event(string $date, array $extra = []): AnalyticsEvent
    {
        return AnalyticsEvent::create([
            'occurred_at' => $date, 'visitor_id' => str_repeat('a', 64), 'visit_id' => (string) Str::uuid(),
            'audience' => 'guest', 'area' => 'website', 'event_type' => 'page_view', 'page' => 'home', ...$extra,
        ]);
    }

    public function test_guests_are_anonymous_and_duplicate_requests_are_counted_once(): void
    {
        $fakeUser = User::factory()->create();
        $eventId = (string) Str::uuid();
        $visitor = (string) Str::uuid();
        $this->withCookie('parish_visitor', $visitor);
        $this->capture(['event_id' => $eventId, 'user_id' => $fakeUser->id, 'password' => 'secret-never-store', 'url' => '/?token=private'])->assertNoContent();
        $this->capture(['event_id' => $eventId])->assertNoContent();
        $this->assertDatabaseCount('analytics_events', 1);
        $event = AnalyticsEvent::first();
        $this->assertNull($event->user_id);
        $this->assertSame('guest', $event->audience);
        $this->assertSame(64, strlen($event->visitor_id));
        $this->assertNotSame($visitor, $event->visitor_id);
        $this->assertStringNotContainsString('secret-never-store', $event->toJson());
        $this->assertStringNotContainsString('token', $event->toJson());
    }

    public function test_visits_expire_after_thirty_minutes_but_keep_the_same_browser_identity(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-20 09:00:00'));
        $this->withCookie('parish_visitor', (string) Str::uuid());
        $this->capture()->assertNoContent();
        $first = AnalyticsEvent::first();
        $this->travel(29)->minutes();
        $this->capture(['page' => 'about'])->assertNoContent();
        $this->assertSame($first->visit_id, AnalyticsEvent::latest('id')->first()->visit_id);
        $this->travel(30)->minutes();
        $this->capture(['page' => 'news'])->assertNoContent();
        $last = AnalyticsEvent::latest('id')->first();
        $this->assertNotSame($first->visit_id, $last->visit_id);
        $this->assertSame($first->visitor_id, $last->visitor_id);
    }

    public function test_only_approved_public_events_are_accepted_and_authenticated_identity_is_server_controlled(): void
    {
        $this->capture(['event_type' => 'login'])->assertUnprocessable();
        $this->capture(['page' => 'https://example.test/private'])->assertUnprocessable();
        $this->capture(['event_type' => 'action', 'action' => 'password_changed'])->assertUnprocessable();
        $this->capture(['event_type' => 'action'])->assertUnprocessable();
        $this->assertDatabaseCount('analytics_events', 0);
        $user = User::factory()->create();
        $this->actingAs($user)->capture(['event_type' => 'action', 'action' => 'livestream_click', 'user_id' => 999])->assertNoContent();
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'action', 'action' => 'livestream_click', 'user_id' => $user->id]);
    }

    public function test_polling_bots_and_analytics_dashboard_itself_do_not_inflate_page_views(): void
    {
        $this->get('/api/site-settings')->assertOk();
        $this->get('/api/livestream-status')->assertOk();
        $this->withHeader('User-Agent', 'Googlebot')->capture()->assertNoContent();
        $this->withHeader('User-Agent', 'Website Browser')->withHeader('Sec-Purpose', 'prefetch')->capture()->assertNoContent();
        $this->flushHeaders();
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/analytics')->assertOk();
        $this->assertDatabaseCount('analytics_events', 0);
    }

    public function test_sign_ins_failures_and_logout_are_tracked_without_credentials(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('TestAnalytics123!')]);
        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong-secret'])->assertUnprocessable();
        $failed = AnalyticsEvent::where('event_type', 'failed_login')->sole();
        $this->assertNull($failed->user_id);
        $this->assertStringNotContainsString($user->email, $failed->toJson());
        $this->assertStringNotContainsString('wrong-secret', $failed->toJson());
        $this->postJson('/login', ['email' => $user->email, 'password' => 'TestAnalytics123!'])->assertOk();
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'login', 'user_id' => $user->id]);
        $this->post('/parishioner/logout')->assertRedirect('/');
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'logout', 'user_id' => $user->id]);
        $this->assertSame(1, AnalyticsEvent::where('event_type', 'login')->count());
        $this->assertGuest();
    }

    public function test_google_registration_and_login_are_each_counted_once(): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn((new GoogleUser)->map([
            'id' => 'analytics-google-account', 'email' => 'analytics.google@example.test', 'name' => 'Google Visitor',
        ]));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->get('/auth/google/callback')->assertRedirect();
        $user = User::where('google_id', 'analytics-google-account')->sole();
        $this->assertSame($user->id, AnalyticsEvent::where('event_type', 'registration')->sole()->user_id);
        $this->assertSame($user->id, AnalyticsEvent::where('event_type', 'login')->sole()->user_id);
    }

    public function test_portal_views_and_successful_changes_are_tracked_but_validation_failures_are_not(): void
    {
        $user = User::factory()->create(['password_hash' => Hash::make('OldPassword123!')]);
        $this->actingAs($user)->get('/parishioner/profile-settings')->assertOk();
        $this->assertDatabaseHas('analytics_events', ['page' => 'parishioner.profile-settings', 'event_type' => 'page_view']);
        $payload = ['current_password' => 'wrong', 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
        $this->put('/parishioner/profile-settings/password', $payload)->assertSessionHasErrors();
        $this->assertSame(0, AnalyticsEvent::where('event_type', 'action')->count());
        $payload['current_password'] = 'OldPassword123!';
        $this->put('/parishioner/profile-settings/password', $payload)->assertSessionHasNoErrors();
        $this->assertSame(1, AnalyticsEvent::where('event_type', 'action')->count());
        $this->assertStringNotContainsString('Password123!', AnalyticsEvent::all()->toJson());
    }

    public function test_analytics_and_export_require_explicit_parish_wide_permission(): void
    {
        foreach (['/admin/analytics', '/admin/analytics/export'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
        foreach ([['role' => 'user'], ['role' => 'admin', 'permissions' => ['view_dashboard']], ['role' => 'commission_coordinator', 'permissions' => ['view_analytics']]] as $attributes) {
            $this->actingAs(User::factory()->create($attributes));
            $this->get('/admin/analytics')->assertForbidden();
            $this->get('/admin/analytics/export')->assertForbidden();
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/analytics')->assertOk()->assertSee('Website Analytics');
        $this->get('/admin/analytics/export')->assertOk()->assertDownload();
    }

    public function test_daily_totals_count_unique_browsers_and_visits_with_inclusive_local_dates(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-20 12:00:00'));
        $user = User::factory()->create(['name' => 'Tracked Visitor']);
        $visit = (string) Str::uuid();
        $this->event('2026-09-19 23:59:59');
        $this->event('2026-09-20 00:00:00', ['visit_id' => $visit]);
        $this->event('2026-09-20 10:00:00', ['visit_id' => $visit, 'event_type' => 'login', 'user_id' => $user->id, 'audience' => 'member']);
        $this->event('2026-09-20 23:59:59', ['visitor_id' => str_repeat('b', 64)]);
        $this->event('2026-09-21 00:00:00');
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/analytics?from=2026-09-20&to=2026-09-20');
        $response->assertOk()->assertSee('Tracked Visitor');
        $totals = $response->viewData('totals');
        $this->assertEquals(2, $totals->visitors);
        $this->assertEquals(2, $totals->visits);
        $this->assertEquals(2, $totals->page_views);
        $this->assertEquals(1, $totals->logins);
        $this->assertCount(1, $response->viewData('series'));
    }

    public function test_monthly_and_yearly_reports_fill_empty_periods_and_apply_filters_to_export(): void
    {
        $this->travelTo(CarbonImmutable::parse('2026-09-20 12:00:00'));
        $user = User::factory()->create();
        $this->event('2025-12-31 23:59:59');
        $this->event('2026-01-01 00:00:00');
        $this->event('2026-03-01 10:00:00', ['user_id' => $user->id, 'audience' => 'member', 'event_type' => 'login', 'area' => 'portal']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $response = $this->get('/admin/analytics?from=2026-01-01&to=2026-03-31&group=month');
        $response->assertOk();
        $this->assertSame([1, 0, 0], array_column($response->viewData('series'), 'page_views'));
        $this->assertSame([0, 0, 1], array_column($response->viewData('series'), 'logins'));
        $response = $this->get('/admin/analytics?from=2025-01-01&to=2026-12-31&group=year');
        $this->assertSame([1, 1], array_column($response->viewData('series'), 'page_views'));
        $filter = '?from=2026-03-01&to=2026-03-31&group=month&area=portal&audience=member&user='.$user->id;
        $response = $this->get('/admin/analytics'.$filter)->assertOk();
        $this->assertEquals(1, $response->viewData('totals')->logins);
        $this->assertEquals(0, $response->viewData('totals')->page_views);
        $csv = $this->get('/admin/analytics/export'.$filter)->assertOk()->streamedContent();
        $this->assertStringContainsString('Asia/Manila', $csv);
        $this->assertStringContainsString('2026-03,1,1,1,0,1,0,0,0,0', $csv);
        $this->assertStringNotContainsString($user->email, $csv);
    }

    public function test_bad_report_ranges_are_rejected_and_empty_reports_are_truthful(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/analytics')->assertOk()->assertSee('No recorded activity')->assertSee('Recording starts with the next website activity');
        foreach (['from=2026-10-01&to=2026-09-01', 'from=2020-01-01&to=2026-09-01&group=day', 'group=unsupported', 'from=not-a-date'] as $query) {
            $this->getJson('/admin/analytics?'.$query)->assertUnprocessable();
        }
    }

    public function test_existing_full_administrators_receive_analytics_without_expanding_restricted_permissions(): void
    {
        $full = User::factory()->create(['role' => 'admin', 'permissions' => array_values(array_diff(User::getAllPermissionKeys(), ['view_analytics']))]);
        $restricted = User::factory()->create(['role' => 'admin', 'permissions' => ['view_dashboard', 'view_reports']]);
        $upgrade = require database_path('migrations/2026_09_20_100001_enable_analytics_for_existing_full_administrators.php');
        $upgrade->up();
        $this->assertTrue($full->fresh()->hasPermission('view_analytics'));
        $this->assertFalse($restricted->fresh()->hasPermission('view_analytics'));
        $this->actingAs($full->fresh())->get('/admin/analytics')->assertOk()->assertSee('Website Analytics');
    }
}
