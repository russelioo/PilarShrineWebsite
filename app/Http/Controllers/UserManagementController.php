<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
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
        $query->when($request->input('status') === 'pending', fn (Builder $query) => $query->where('is_verified', false));

        return match ($request->input('sort')) {
            'name' => $query->orderBy('name'),
            'oldest' => $query->oldest(),
            default => $query->latest(),
        };
    }
}
