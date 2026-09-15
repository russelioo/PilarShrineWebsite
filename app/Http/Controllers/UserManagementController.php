<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function parishioners(Request $request): View
    {
        $parishioners = $this->filteredUsers($request, ['user'])->paginate(10)->withQueryString();
        return view('admin.parishioners', compact('parishioners'));
    }

    public function staff(Request $request): View
    {
        $staff = $this->filteredUsers($request, ['admin', 'staff'])->paginate(10)->withQueryString();
        return view('admin.staff', compact('staff'));
    }

    public function storeStaff(Request $request): JsonResponse|RedirectResponse
    {
        abort_unless($request->user()?->role === 'admin', 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'regex:/^(09|\+639)\d{2}[\s-]?\d{3}[\s-]?\d{4}$/'],
            'role' => ['required', 'string', 'in:admin,staff'],
            'status' => ['required', 'string', 'in:active,inactive'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'name.required' => 'Full name cannot be empty.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'phone.regex' => 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX or +639XX XXX XXXX).',
            'role.required' => 'Please select a role.',
            'role.in' => 'Role must be either Staff or Admin.',
            'status.required' => 'Please select an account status.',
            'status.in' => 'Status must be either Active or Inactive.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'password.confirmed' => 'Passwords do not match.',
            'profile_photo.image' => 'The uploaded file must be an image.',
            'profile_photo.mimes' => 'Profile photo must be a file of type: jpeg, png, jpg, webp.',
            'profile_photo.max' => 'Profile photo must not exceed 2MB.',
        ]);

        $fullName = trim($validated['name']);
        $nameParts = preg_split('/\s+/', $fullName) ?: [];
        $firstName = array_shift($nameParts) ?? $fullName;
        $lastName = !empty($nameParts) ? implode(' ', $nameParts) : $firstName;

        $avatarPath = null;
        if ($request->hasFile('profile_photo')) {
            $path = $request->file('profile_photo')->store('avatars', 'public');
            $avatarPath = '/storage/' . $path;
        }

        $isActive = ($validated['status'] === 'active');

        $user = User::create([
            'name' => $fullName,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => strtolower(trim($validated['email'])),
            'phone' => !empty($validated['phone']) ? trim($validated['phone']) : null,
            'role' => $validated['role'],
            'is_verified' => $isActive,
            'email_verified_at' => $isActive ? now() : null,
            'password_hash' => Hash::make($validated['password']),
            'avatar' => $avatarPath,
        ]);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff account created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?: '—',
                    'role' => ucfirst($user->role),
                    'role_raw' => $user->role,
                    'status' => $user->is_verified ? 'Active' : 'Inactive',
                    'status_raw' => $user->is_verified ? 'active' : 'inactive',
                    'last_login' => 'Never',
                    'avatar' => $user->avatar,
                    'initials' => $user->initials,
                ],
            ], 201);
        }

        return redirect()->route('admin.staff')->with('success', 'Staff account created successfully.');
    }

    private function filteredUsers(Request $request, array $roles): Builder
    {
        $query = User::query()->whereIn('role', $roles)->whereNull('deleted_at');
        $query->when($request->filled('search'), function (Builder $query) use ($request): void {
            $search = $request->string('search')->trim()->toString();
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        });
        $role = $request->string('role')->toString();
        $query->when($role !== '' && in_array($role, $roles, true), fn (Builder $query) => $query->where('role', $role));
        $query->when($request->input('status') === 'active', fn (Builder $query) => $query->where('is_verified', true));
        $query->when(in_array($request->input('status'), ['inactive', 'pending'], true), fn (Builder $query) => $query->where('is_verified', false));

        return match ($request->input('sort')) {
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }
}
