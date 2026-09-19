<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            $intent = $request->query('intent', 'login');
            $target = $intent === 'register' ? '/#/register' : '/#/login';
            return redirect($target . '?error=' . urlencode('Google Sign-In is not configured yet. Please configure GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in your .env file.'));
        }

        $intent = $request->query('intent', 'login');
        if (!in_array($intent, ['login', 'register'])) {
            $intent = 'login';
        }

        session(['google_oauth_intent' => $intent]);

        if ($request->filled('redirect')) {
            $candidate = $request->string('redirect')->trim()->toString();
            if (str_starts_with($candidate, '/') && !str_starts_with($candidate, '//')) {
                session(['google_oauth_redirect' => $candidate]);
            }
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback(Request $request): RedirectResponse
    {
        if ($request->session()->get('google_oauth_intent') === 'parish_password_setup') {
            return app(\App\Http\Controllers\Parishioner\PasswordController::class)->completeGoogleConfirmation($request);
        }

        if ($request->has('error') || $request->has('denied')) {
            $intent = session()->pull('google_oauth_intent', 'login');
            $target = $intent === 'register' ? '/#/register' : '/#/login';
            return redirect($target . '?error=' . urlencode('Google authentication was cancelled or denied.'));
        }

        try {
            /** @var \Laravel\Socialite\Two\User $googleUser */
            $googleUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage());
            $intent = session()->pull('google_oauth_intent', 'login');
            $target = $intent === 'register' ? '/#/register' : '/#/login';
            return redirect($target . '?error=' . urlencode('Unable to authenticate with Google. Please try again or use email/password.'));
        }

        $intent = session()->pull('google_oauth_intent', 'login');

        // Look up by google_id first, then by email
        $existingUser = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($existingUser) {
            // Link google_id if not yet linked
            if (empty($existingUser->google_id)) {
                $existingUser->google_id = $googleUser->getId();
            }

            // Update avatar if present
            if (!empty($googleUser->getAvatar())) {
                $existingUser->avatar = $googleUser->getAvatar();
            }

            // If email is not verified yet, Google verified it
            if (empty($existingUser->email_verified_at)) {
                $existingUser->email_verified_at = now();
                $existingUser->is_verified = true;
            }

            $existingUser->last_login = now();
            $existingUser->save();

            Auth::login($existingUser, true);
            $request->session()->regenerate();

            // If user's profile is incomplete, redirect to complete-profile
            if (!$existingUser->isProfileComplete()) {
                return redirect('/#/complete-profile');
            }

            // Redirect to appropriate destination based on role
            // Parishioners return to the Official Public Website homepage
            $targetRedirect = session()->pull('google_oauth_redirect');
            return match ($existingUser->role) {
                'admin', 'super_admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'commission_member', 'staff' => redirect()->route('admin.dashboard'),
                default => redirect($targetRedirect ?: '/?login=success'),
            };
        }

        // If user does not exist in parish records, automatically create account with verified Google identity
        return $this->registerGoogleUser($request, [
            'google_id' => $googleUser->getId(),
            'email' => $googleUser->getEmail(),
            'name' => $googleUser->getName(),
            'avatar' => $googleUser->getAvatar(),
        ]);
    }

    /**
     * Confirm registration for unregistered Google user from login prompt.
     */
    public function confirmRegister(Request $request): RedirectResponse
    {
        $pending = session()->pull('pending_google_user');

        if (!$pending || empty($pending['email']) || empty($pending['google_id'])) {
            return redirect('/#/login?error=' . urlencode('Google session expired. Please sign in with Google again.'));
        }

        // Check if an account was created in the meantime
        $existingUser = User::where('google_id', $pending['google_id'])
            ->orWhere('email', $pending['email'])
            ->first();

        if ($existingUser) {
            Auth::login($existingUser, true);
            $request->session()->regenerate();
            if (!$existingUser->isProfileComplete()) {
                return redirect('/#/complete-profile');
            }
            return redirect('/?login=success');
        }

        return $this->registerGoogleUser($request, $pending);
    }

    /**
     * Helper to create a user from Google payload, log them in, and redirect to complete-profile.
     *
     * @param array{google_id: string, email: string, name: ?string, avatar: ?string} $data
     */
    protected function registerGoogleUser(Request $request, array $data): RedirectResponse
    {
        $fullName = trim($data['name'] ?? '');
        $parts = explode(' ', $fullName, 2);
        $firstName = $parts[0] ?? '';
        $lastName = $parts[1] ?? '';

        $user = User::create([
            'name' => $fullName ?: 'Parishioner',
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $data['email'],
            'google_id' => $data['google_id'],
            'avatar' => $data['avatar'] ?? null,
            'role' => 'user',
            'country' => 'Philippines',
            'is_verified' => true,
            'email_verified_at' => now(),
            'last_login' => now(),
        ]);

        app(\App\Services\WebsiteAnalytics::class)->record($request, 'registration', 'register', user: $user);
        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect('/#/complete-profile?new=1');
    }
}

