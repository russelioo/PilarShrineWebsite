<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Check if an active user exists with this email
        $existingActive = User::where('email', $request->input('email'))->first();
        if ($existingActive) {
            $request->validate([
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            ]);
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality_city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $name = trim($validated['first_name'] . ' ' . $validated['last_name']);

        // Check if a soft-deleted user existed with this email
        $trashedUser = User::withTrashed()->where('email', $validated['email'])->first();

        if ($trashedUser) {
            $trashedUser->restore();
            $trashedUser->update([
                'name' => $name,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'phone' => $validated['phone'],
                'country' => $validated['country'],
                'region' => $validated['region'],
                'province' => $validated['province'] ?? null,
                'municipality_city' => $validated['municipality_city'],
                'barangay' => $validated['barangay'],
                'password_hash' => Hash::make($validated['password']),
                'role' => 'user',
                'organization' => 'parishioner',
                'position' => 'Parishioner',
                'responsibilities' => 'Parishioner',
                'commission_id' => null,
                'permissions' => null,
                'is_verified' => false,
            ]);
            $user = $trashedUser;
        } else {
            $user = User::create([
                'name' => $name,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country' => $validated['country'],
                'region' => $validated['region'],
                'province' => $validated['province'] ?? null,
                'municipality_city' => $validated['municipality_city'],
                'barangay' => $validated['barangay'],
                'password_hash' => Hash::make($validated['password']),
                'role' => 'user',
                'organization' => 'parishioner',
                'position' => 'Parishioner',
                'responsibilities' => 'Parishioner',
                'is_verified' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully! You may now sign in.',
            'redirect' => '/#/login',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ], 201);
    }
}
