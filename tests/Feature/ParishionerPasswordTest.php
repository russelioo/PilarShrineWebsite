<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class ParishionerPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function googleAccount(): User
    {
        return User::factory()->create(['google_id' => 'connected-google-id', 'password_hash' => null]);
    }

    private function newPassword(array $extra = []): array
    {
        return ['password' => 'NewParishPassword123!', 'password_confirmation' => 'NewParishPassword123!', ...$extra];
    }

    private function pendingConfirmation(User $user): array
    {
        return ['google_oauth_intent' => 'parish_password_setup', 'parish_password_setup_user_id' => $user->id];
    }

    private function mockGoogleIdentity(string $id): void
    {
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andReturn((new GoogleUser)->map(['id' => $id]));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_guests_cannot_change_passwords_or_start_confirmation(): void
    {
        $this->put(route('parishioner.password.update'), $this->newPassword())->assertRedirect(route('login'));
        $this->get(route('parishioner.password.confirm'))->assertRedirect(route('login'));
    }

    public function test_google_only_account_is_asked_to_confirm_before_password_setup(): void
    {
        $user = $this->googleAccount();
        $this->actingAs($user)->get(route('parishioner.profile-settings'))->assertOk()
            ->assertSee('Create parish password')->assertSee('Confirm with Google')
            ->assertDontSee('name="current_password"', false)->assertDontSee('name="password_confirmation"', false);
        $this->put(route('parishioner.password.update'), $this->newPassword())
            ->assertSessionHasErrorsIn('password', ['confirmation'])
            ->assertRedirect(route('parishioner.profile-settings').'#password-security');
        $this->assertNull($user->fresh()->password_hash);
        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_google_confirmation_starts_oauth_for_the_current_account(): void
    {
        $user = $this->googleAccount();
        config(['services.google.client_id' => 'test-client', 'services.google.client_secret' => 'test-secret']);
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('with')->with(['prompt' => 'select_account'])->once()->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/test-confirmation'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->actingAs($user)->get(route('parishioner.password.confirm'))
            ->assertRedirect('https://accounts.google.com/test-confirmation')
            ->assertSessionHas('google_oauth_intent', 'parish_password_setup')
            ->assertSessionHas('parish_password_setup_user_id', $user->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_confirmed_google_user_can_create_a_parish_password_and_use_it_to_log_in(): void
    {
        $user = $this->googleAccount();
        $this->mockGoogleIdentity($user->google_id);
        $this->actingAs($user)->withSession($this->pendingConfirmation($user))->get('/auth/google/callback')
            ->assertRedirect(route('parishioner.profile-settings').'#password-security');
        $this->get(route('parishioner.profile-settings'))->assertOk()->assertSee('name="password_confirmation"', false);
        $this->put(route('parishioner.password.update'), $this->newPassword())
            ->assertSessionHasNoErrors()->assertSessionHas('password_success')
            ->assertSessionMissing('parish_password_confirmation');
        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertTrue(Hash::check('NewParishPassword123!', $user->password_hash));
        $this->assertSame('connected-google-id', $user->google_id);
        $this->get(route('parishioner.profile-settings'))->assertOk()->assertSee('Google &amp; parish password', false)
            ->assertSee('Current parish password');
        Auth::logout();
        $this->postJson('/login', ['email' => $user->email, 'password' => 'NewParishPassword123!'])->assertOk();
        $this->assertAuthenticatedAs($user);
        $audit = AuditLog::where('action', 'password_created')->firstOrFail();
        $this->assertNull($audit->old_values);
        $this->assertNull($audit->new_values);
        $this->assertStringNotContainsString('NewParishPassword123!', $audit->toJson());
    }

    public function test_different_google_identity_cannot_change_or_switch_the_signed_in_account(): void
    {
        $user = $this->googleAccount();
        $this->mockGoogleIdentity('another-google-id');
        $this->actingAs($user)->withSession($this->pendingConfirmation($user))->get('/auth/google/callback')
            ->assertSessionHasErrorsIn('password', ['confirmation'])->assertSessionMissing('parish_password_confirmation');
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->password_hash);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_cancelled_or_invalid_oauth_confirmation_does_not_allow_password_creation(): void
    {
        $user = $this->googleAccount();
        $this->actingAs($user)->withSession($this->pendingConfirmation($user))->get('/auth/google/callback?error=access_denied')
            ->assertSessionHasErrorsIn('password', ['confirmation'])->assertSessionMissing('parish_password_confirmation');
        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
        $this->withSession($this->pendingConfirmation($user))->get('/auth/google/callback')
            ->assertSessionHasErrorsIn('password', ['confirmation'])->assertSessionMissing('parish_password_confirmation');
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->password_hash);
    }

    public function test_confirmation_expires_and_is_bound_to_the_signed_in_user(): void
    {
        $user = $this->googleAccount();
        foreach ([
            ['user_id' => $user->id, 'verified_at' => now()->subMinutes(10)->timestamp],
            ['user_id' => $user->id + 1, 'verified_at' => now()->timestamp],
        ] as $confirmation) {
            $this->actingAs($user)->withSession(['parish_password_confirmation' => $confirmation])
                ->put(route('parishioner.password.update'), $this->newPassword())
                ->assertSessionHasErrorsIn('password', ['confirmation']);
        }
        $this->assertNull($user->fresh()->password_hash);
    }

    public function test_existing_password_change_requires_current_password_and_confirmation(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('parishioner.profile-settings'))->assertOk()->assertSee('Change password');
        $this->put(route('parishioner.password.update'), $this->newPassword(['current_password' => 'wrong']))
            ->assertSessionHasErrorsIn('password', ['current_password']);
        $this->put(route('parishioner.password.update'), $this->newPassword(['current_password' => 'password', 'password_confirmation' => 'different']))
            ->assertSessionHasErrorsIn('password', ['password']);
        $this->put(route('parishioner.password.update'), ['current_password' => 'password', 'password' => 'short', 'password_confirmation' => 'short'])
            ->assertSessionHasErrorsIn('password', ['password']);
        $this->put(route('parishioner.password.update'), ['current_password' => 'password', 'password' => 'password', 'password_confirmation' => 'password'])
            ->assertSessionHasErrorsIn('password', ['password']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password_hash));
        $this->assertNull(session()->getOldInput('password'));
        $this->assertNull(session()->getOldInput('current_password'));
    }

    public function test_password_save_only_changes_own_password_and_keeps_the_session_active(): void
    {
        $user = User::factory()->create(['google_id' => 'linked-google', 'remember_token' => 'old-token']);
        $other = User::factory()->create();
        $this->actingAs($user)->put(route('parishioner.password.update'), $this->newPassword([
            'current_password' => 'password', 'user_id' => $other->id, 'role' => 'admin', 'email' => $other->email,
        ]))->assertSessionHasNoErrors()->assertRedirect(route('parishioner.profile-settings').'#password-security');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('NewParishPassword123!', $user->fresh()->password_hash));
        $this->assertTrue(Hash::check('password', $other->fresh()->password_hash));
        $this->assertSame('user', $user->fresh()->role);
        $this->assertSame($user->email, $user->fresh()->email);
        $this->assertSame('linked-google', $user->fresh()->google_id);
        $this->assertNotSame('old-token', $user->fresh()->remember_token);
        $this->get(route('parishioner.profile-settings'))->assertOk();
        $audit = AuditLog::where('action', 'password_changed')->firstOrFail();
        $this->assertNull($audit->old_values);
        $this->assertNull($audit->new_values);
    }

    public function test_existing_password_cannot_be_bypassed_using_google_confirmation(): void
    {
        $user = User::factory()->create(['google_id' => 'linked-google']);
        $this->actingAs($user)->withSession(['parish_password_confirmation' => ['user_id' => $user->id, 'verified_at' => now()->timestamp]])
            ->put(route('parishioner.password.update'), $this->newPassword())
            ->assertSessionHasErrorsIn('password', ['current_password']);
        $this->get(route('parishioner.password.confirm'))->assertForbidden();
        $this->assertTrue(Hash::check('password', $user->fresh()->password_hash));
    }

    public function test_password_attempts_are_rate_limited(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->put(route('parishioner.password.update'), $this->newPassword(['current_password' => 'wrong']))->assertRedirect();
        }
        $this->put(route('parishioner.password.update'), $this->newPassword(['current_password' => 'wrong']))->assertStatus(429);
    }
}
