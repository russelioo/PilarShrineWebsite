<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_google_redirect_informs_user_when_credentials_not_configured(): void
    {
        config(['services.google.client_id' => '']);
        config(['services.google.client_secret' => '']);

        $response = $this->get('/auth/google?intent=login');

        $response->assertRedirect();
        $this->assertStringContainsString('/#/login?error=', $response->headers->get('Location'));
    }

    public function test_google_redirect_initiates_oauth_when_credentials_configured(): void
    {
        config(['services.google.client_id' => 'test-client-id']);
        config(['services.google.client_secret' => 'test-client-secret']);

        $response = $this->get('/auth/google?intent=register');

        $response->assertSessionHas('google_oauth_intent', 'register');
        $this->assertTrue($response->isRedirection());
    }

    public function test_google_callback_handles_denied_or_cancelled_state(): void
    {
        $response = $this->withSession(['google_oauth_intent' => 'login'])
            ->get('/auth/google/callback?error=access_denied');

        $response->assertRedirect();
        $this->assertStringContainsString('cancelled', urldecode($response->headers->get('Location')));
    }

    public function test_google_callback_logs_in_existing_user_and_links_google_id(): void
    {
        $existing = User::create([
            'name' => 'Maria Santos',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'maria.santos@gmail.com',
            'date_of_birth' => '1992-06-15',
            'phone' => '0917 123 4567',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'Poblacion',
            'role' => 'user',
            'is_verified' => false,
        ]);

        $mockSocialiteUser = Mockery::mock(SocialiteUser::class);
        $mockSocialiteUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $mockSocialiteUser->shouldReceive('getEmail')->andReturn('maria.santos@gmail.com');
        $mockSocialiteUser->shouldReceive('getName')->andReturn('Maria Santos');
        $mockSocialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($mockSocialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->withSession(['google_oauth_intent' => 'login'])
            ->get('/auth/google/callback');

        $this->assertAuthenticatedAs($existing);

        $existing->refresh();
        $this->assertSame('google-unique-id-12345', $existing->google_id);
        $this->assertSame('https://lh3.googleusercontent.com/avatar.jpg', $existing->avatar);
        $this->assertTrue($existing->is_verified);
        $this->assertNotNull($existing->email_verified_at);

        $response->assertRedirect(route('parishioner.dashboard'));
    }

    public function test_google_callback_for_unregistered_user_on_login_auto_creates_account(): void
    {
        $mockSocialiteUser = Mockery::mock(SocialiteUser::class);
        $mockSocialiteUser->shouldReceive('getId')->andReturn('google-new-999');
        $mockSocialiteUser->shouldReceive('getEmail')->andReturn('newparishioner@gmail.com');
        $mockSocialiteUser->shouldReceive('getName')->andReturn('Juan Dela Cruz');
        $mockSocialiteUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/photo.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($mockSocialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->withSession(['google_oauth_intent' => 'login'])
            ->get('/auth/google/callback');

        $user = User::where('email', 'newparishioner@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-new-999', $user->google_id);
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('Dela Cruz', $user->last_name);
        $this->assertSame('https://lh3.googleusercontent.com/photo.jpg', $user->avatar);
        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/#/complete-profile?new=1');
    }

    public function test_confirm_register_creates_account_and_redirects_to_complete_profile(): void
    {
        $response = $this->withSession([
            'pending_google_user' => [
                'google_id' => 'google-new-999',
                'email' => 'newparishioner@gmail.com',
                'name' => 'Juan Dela Cruz',
                'avatar' => 'https://lh3.googleusercontent.com/photo.jpg',
            ],
        ])->post('/auth/google/confirm-register');

        $user = User::where('email', 'newparishioner@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-new-999', $user->google_id);
        $this->assertSame('Juan', $user->first_name);
        $this->assertSame('Dela Cruz', $user->last_name);
        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/#/complete-profile?new=1');
    }

    public function test_google_callback_for_register_intent_auto_creates_account_and_redirects_to_complete_profile(): void
    {
        $mockSocialiteUser = Mockery::mock(SocialiteUser::class);
        $mockSocialiteUser->shouldReceive('getId')->andReturn('google-reg-777');
        $mockSocialiteUser->shouldReceive('getEmail')->andReturn('registering.user@gmail.com');
        $mockSocialiteUser->shouldReceive('getName')->andReturn('Teresa Aquino');
        $mockSocialiteUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($mockSocialiteUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->withSession(['google_oauth_intent' => 'register'])
            ->get('/auth/google/callback');

        $user = User::where('email', 'registering.user@gmail.com')->first();
        $this->assertNotNull($user);
        $this->assertSame('google-reg-777', $user->google_id);
        $this->assertSame('Teresa', $user->first_name);
        $this->assertSame('Aquino', $user->last_name);
        $this->assertAuthenticatedAs($user);

        $response->assertRedirect('/#/complete-profile?new=1');
    }

    public function test_profile_completion_endpoint_saves_parishioner_address_and_dob(): void
    {
        $user = User::create([
            'name' => 'Teresa Aquino',
            'first_name' => 'Teresa',
            'last_name' => 'Aquino',
            'email' => 'teresa@gmail.com',
            'google_id' => 'google-reg-777',
            'role' => 'user',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($user)->postJson('/api/parishioner/complete-profile', [
            'first_name' => 'Teresa',
            'last_name' => 'Aquino',
            'date_of_birth' => '1994-08-22',
            'phone' => '0919 555 1234',
            'country' => 'Philippines',
            'region' => 'Bicol Region',
            'province' => 'Sorsogon',
            'municipality_city' => 'Pilar',
            'barangay' => 'San Antonio',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'redirect' => route('parishioner.dashboard'),
            ]);

        $user->refresh();
        $this->assertSame('1994-08-22', $user->date_of_birth->format('Y-m-d'));
        $this->assertSame('0919 555 1234', $user->phone);
        $this->assertSame('Bicol Region', $user->region);
        $this->assertSame('Sorsogon', $user->province);
        $this->assertSame('Pilar', $user->municipality_city);
        $this->assertSame('San Antonio', $user->barangay);
        $this->assertTrue($user->isProfileComplete());
    }

    public function test_profile_status_endpoint_returns_authenticated_user_status(): void
    {
        $user = User::create([
            'name' => 'Pedro Penduko',
            'email' => 'pedro@gmail.com',
            'google_id' => 'pedro-gid',
            'avatar' => 'https://example.com/avatar.jpg',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->getJson('/api/user/profile-status');

        $response->assertOk()
            ->assertJson([
                'authenticated' => true,
                'user' => [
                    'name' => 'Pedro Penduko',
                    'email' => 'pedro@gmail.com',
                    'avatar' => 'https://example.com/avatar.jpg',
                    'is_complete' => false,
                ],
            ]);
    }
}

