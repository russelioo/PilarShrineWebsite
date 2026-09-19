<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileCompletionController extends Controller
{
    /**
     * Get the current authenticated user's profile status.
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['authenticated' => false], 401);
        }

        $pos = strtolower($user->position ?? '');
        $name = strtolower($user->name ?? '');
        $email = strtolower($user->email ?? '');
        $isParishAdmin = str_contains($pos, 'parish administrator')
            || str_contains($name, 'parish administrator')
            || $email === 'admin@pilarshrine.test';

        $avatar = $user->avatar;
        if (empty($avatar) && $isParishAdmin) {
            $avatar = '/images/pilar-shrine-crest.jpg';
        }

        return response()->json([
            'authenticated' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'avatar' => $avatar,
                'is_parish_administrator' => $isParishAdmin,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'phone' => $user->phone,
                'country' => $user->country ?? 'Philippines',
                'region' => $user->region,
                'province' => $user->province,
                'municipality_city' => $user->municipality_city,
                'barangay' => $user->barangay,
                'is_complete' => $user->isProfileComplete(),
            ],
        ]);
    }

    /**
     * Complete parishioner profile with personal & address details.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $validated = $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before_or_equal:today'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', 'max:100'],
            'region' => ['required', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'municipality_city' => ['required', 'string', 'max:100'],
            'barangay' => ['required', 'string', 'max:100'],
        ]);

        if (!empty($validated['first_name'])) {
            $user->first_name = $validated['first_name'];
        }
        if (!empty($validated['last_name'])) {
            $user->last_name = $validated['last_name'];
        }
        if (!empty($user->first_name) || !empty($user->last_name)) {
            $user->name = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        }

        $user->date_of_birth = $validated['date_of_birth'];
        $user->phone = $validated['phone'];
        $user->country = $validated['country'];
        $user->region = $validated['region'];
        $user->province = $validated['province'] ?? null;
        $user->municipality_city = $validated['municipality_city'];
        $user->barangay = $validated['barangay'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully!',
            'redirect' => '/?welcome=1',
        ]);
    }
}

