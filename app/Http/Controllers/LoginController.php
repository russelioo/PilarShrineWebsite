<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials = [
            'email' => $validated['email'],
            'password' => $validated['password'],
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        $request->session()->regenerate();
        $user = $request->user();

        // Track login timestamps (previously only GoogleAuthController did this)
        $user->forceFill([
            'last_login' => now(),
            'last_active_at' => now(),
        ])->saveQuietly();

        $customRedirect = null;
        if ($request->filled('redirect')) {
            $candidate = $request->string('redirect')->trim()->toString();
            if (str_starts_with($candidate, '/') && !str_starts_with($candidate, '//')) {
                $customRedirect = $candidate;
            }
        }

        $redirect = match ($user->role) {
            'admin', 'super_admin', 'parish_priest', 'parochial_vicar', 'parish_secretary', 'commission_admin', 'commission_member', 'staff' => route('admin.dashboard'),
            default => $customRedirect ?: route('parishioner.dashboard'),
        };

        return response()->json(['redirect' => $redirect]);
    }
}
