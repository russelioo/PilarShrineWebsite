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

        $customRedirect = null;
        if ($request->filled('redirect')) {
            $candidate = $request->string('redirect')->trim()->toString();
            if (str_starts_with($candidate, '/') && !str_starts_with($candidate, '//')) {
                $customRedirect = $candidate;
            }
        }

        $redirect = match ($user->role) {
            'admin' => route('admin.dashboard'),
            'staff' => route('staff.dashboard'),
            default => $customRedirect ?: '/?login=success',
        };

        return response()->json(['redirect' => $redirect]);
    }
}
