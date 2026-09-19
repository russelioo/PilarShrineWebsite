<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\CommissionMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    private function commission(): Commission
    {
        return Commission::create(['name' => 'Parish Youth', 'slug' => 'parish-youth', 'is_active' => true]);
    }

    private function member(array $attributes = []): User
    {
        $commission = $this->commission();
        $user = User::factory()->create(['role' => 'user', 'organization' => 'parishioner', 'position' => 'Parishioner', ...$attributes]);
        CommissionMembership::create(['commission_id' => $commission->id, 'user_id' => $user->id, 'role' => 'member', 'status' => 'active']);

        return $user;
    }

    public function test_parishioner_commission_member_always_gets_the_parishioner_messaging_layout(): void
    {
        $member = $this->member();
        $this->assertTrue($member->isCommissionMember());
        $this->actingAs($member);
        foreach (['/inquiries', '/parishioner/inquiries', '/parishioner/messages-inquiries'] as $path) {
            $response = $this->get($path)->assertOk()->assertViewHas('layout', 'layouts.parishioner');
            $response->assertSee('Parishioner Portal')->assertDontSee('Admin Navigation')->assertDontSee('ADMIN PORTAL');
            $response->assertSee('<h1>Messages &amp; Inquiries</h1>', false)->assertDontSee('Messages &amp;amp; Inquiries', false);
            $this->assertStringNotContainsString('/admin/', $response->getContent());
            $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        }
        $this->get('/portal')->assertRedirect(route('parishioner.dashboard'));
        $this->get('/parishioner/dashboard')->assertOk()->assertSee('Parishioner Portal');
    }

    public function test_every_administrative_route_rejects_parishioners_for_all_http_methods(): void
    {
        $member = $this->member();
        // Even stale full permissions and organization context must not override the current account role.
        $member->update(['permissions' => ['*'], 'organization' => 'parish_administration', 'position' => 'Commission Coordinator']);
        $this->actingAs($member)->withSession(['active_organization_context' => ['type' => 'parish_administration', 'name' => 'Parish Administration']]);
        $checked = 0;
        foreach (Route::getRoutes() as $route) {
            if (! preg_match('~^(admin|staff|commission)/~', $route->uri())) {
                continue;
            }
            $url = '/'.preg_replace('/\{[^}]+\}/', '999999', $route->uri());
            foreach (array_diff($route->methods(), ['HEAD', 'OPTIONS']) as $method) {
                $this->call($method, $url)->assertForbidden();
                $checked++;
            }
        }
        $this->assertGreaterThan(80, $checked);
        $this->assertAuthenticatedAs($member);
        $this->get('/inquiries')->assertOk()->assertViewHas('layout', 'layouts.parishioner');
    }

    public function test_guest_cannot_open_legacy_admin_or_staff_pages(): void
    {
        foreach (['/admin/dashboard', '/admin/appointments', '/admin/sacramental-records', '/admin/form-submissions',
            '/staff/dashboard', '/staff/parishioners', '/staff/reports', '/staff/profile-settings', '/commission/dashboard'] as $path) {
            $this->get($path)->assertRedirect(route('login'));
        }
        $this->getJson('/staff/dashboard')->assertUnauthorized();
    }

    public function test_parishioner_alias_and_unknown_roles_cannot_enter_administration(): void
    {
        foreach (['user', 'parishioner', 'unrecognized_role'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role, 'permissions' => ['*']]));
            $this->get('/admin/dashboard')->assertForbidden();
            $this->get('/admin/inquiries')->assertForbidden();
            $this->get('/staff/inquiries')->assertForbidden();
            $this->get('/commission/members')->assertForbidden();
        }
    }

    public function test_staff_roles_keep_their_existing_portal_and_module_permissions(): void
    {
        foreach (['admin', 'super_admin', 'parish_priest', 'parochial_vicar', 'parish_secretary',
            'commission_admin', 'commission_coordinator', 'commission_member', 'staff'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/inquiries')->assertOk()->assertViewHas('layout', 'layouts.admin');
        }
        $restrictedAdmin = User::factory()->create(['role' => 'admin', 'permissions' => []]);
        $this->actingAs($restrictedAdmin)->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/analytics')->assertForbidden();
    }

    public function test_members_can_still_message_the_parish_without_administration_access(): void
    {
        $member = $this->member();
        $admin = User::factory()->create(['role' => 'admin']);
        $conversation = $this->actingAs($member)->postJson('/api/messages/conversations', ['recipient_id' => $admin->id])
            ->assertCreated()->json('conversation.id');
        $this->postJson('/api/messages/conversations/'.$conversation.'/messages', ['body' => 'May I ask about the Sunday schedule?'])->assertCreated();
        $this->getJson('/api/messages/conversations/'.$conversation.'/messages')->assertOk()->assertJsonPath('messages.0.body', 'May I ask about the Sunday schedule?');
        $this->get('/admin/inquiries')->assertForbidden();
        $this->get('/parishioner/messages-inquiries')->assertOk()->assertSee('Parishioner Portal');
    }

    public function test_demoting_a_signed_in_staff_member_removes_portal_access_on_the_next_request(): void
    {
        $member = $this->member(['role' => 'staff', 'permissions' => ['*']]);
        $this->actingAs($member)->get('/admin/inquiries')->assertOk();
        $member->update(['role' => 'user']);
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/inquiries')->assertForbidden();
        $this->get('/inquiries')->assertOk()->assertViewHas('layout', 'layouts.parishioner');
        $this->get('/parishioner/profile-settings')->assertOk();
    }
}
