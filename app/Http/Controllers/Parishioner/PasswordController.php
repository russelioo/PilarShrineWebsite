<?php

namespace App\Http\Controllers\Parishioner;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class PasswordController extends Controller
{
    public static function googleConfirmed(Request $request): bool
    {
        $confirmation = $request->session()->get('parish_password_confirmation', []);
        $verifiedAt = $confirmation['verified_at'] ?? 0;

        return $request->user()?->google_id
            && ($confirmation['user_id'] ?? null) === $request->user()->id
            && $verifiedAt <= now()->timestamp
            && $verifiedAt > now()->subMinutes(10)->timestamp;
    }

    public function confirmWithGoogle(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->google_id && ! filled($user->password_hash), 403);
        $request->session()->forget('parish_password_confirmation');

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return $this->confirmationError('Google confirmation is unavailable. Please try again later.');
        }

        $request->session()->put('google_oauth_intent', 'parish_password_setup');
        $request->session()->put('parish_password_setup_user_id', $user->id);

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function completeGoogleConfirmation(Request $request): RedirectResponse
    {
        $request->session()->forget(['google_oauth_intent', 'parish_password_confirmation']);
        $expectedUserId = $request->session()->pull('parish_password_setup_user_id');
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }
        if ($expectedUserId !== $user->id || ! $user->google_id || filled($user->password_hash)) {
            return $this->confirmationError('Please start password setup again from your parish settings.');
        }
        if ($request->has('error') || $request->has('denied')) {
            return $this->confirmationError('Google confirmation was cancelled. Your account has not changed.');
        }

        try {
            // Socialite validates the OAuth state before returning the Google identity.
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return $this->confirmationError('Google confirmation failed or expired. Please try again.');
        }
        if ((string) $googleUser->getId() !== (string) $user->google_id) {
            return $this->confirmationError('Select the Google account already connected to this parish account.');
        }

        $request->session()->put('parish_password_confirmation', [
            'user_id' => $user->id, 'verified_at' => now()->timestamp,
        ]);

        return redirect()->to($this->settingsUrl());
    }

    public function update(Request $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            $hasPassword = filled($user->password_hash);
            $validator = Validator::make($request->only('current_password', 'password', 'password_confirmation'), [
                'current_password' => $hasPassword ? ['required', 'string'] : ['nullable', 'string'],
                'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
            ]);
            $validator->after(function ($validator) use ($request, $user, $hasPassword) {
                if ($hasPassword && is_string($request->input('current_password'))
                    && ! Hash::check($request->input('current_password'), $user->password_hash)) {
                    $validator->errors()->add('current_password', 'Your current parish password is incorrect.');
                }
                if (! $hasPassword && ! self::googleConfirmed($request)) {
                    $validator->errors()->add('confirmation', 'Confirm your connected Google account before creating a parish password.');
                }
            });
            if ($validator->fails()) {
                throw (new ValidationException($validator))
                    ->errorBag('password')->redirectTo($this->settingsUrl());
            }

            $user->password_hash = $validator->validated()['password'];
            $user->remember_token = Str::random(60);
            $user->save();
            AuditLogger::log($hasPassword ? 'password_changed' : 'password_created',
                $hasPassword ? 'Changed own parish account password.' : 'Created own parish account password after Google confirmation.',
                $user, category: 'settings');
        });

        $request->session()->forget('parish_password_confirmation');
        $request->session()->regenerate();

        return redirect()->to($this->settingsUrl())
            ->with('password_success', 'Your parish password has been saved. You can use it with your email to sign in.');
    }

    private function settingsUrl(): string
    {
        return route('parishioner.profile-settings').'#password-security';
    }

    private function confirmationError(string $message): RedirectResponse
    {
        return redirect()->to($this->settingsUrl())->withErrors(['confirmation' => $message], 'password');
    }
}
