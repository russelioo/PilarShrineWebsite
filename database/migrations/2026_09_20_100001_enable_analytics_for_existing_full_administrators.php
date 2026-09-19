<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Older full administrators have a saved copy of every permission rather than '*'.
        // Preserve restricted/custom grants; only extend a previously complete permission set.
        $previousPermissions = array_values(array_diff(User::getAllPermissionKeys(), ['view_analytics']));
        foreach (DB::table('users')->where('role', 'admin')->whereNotNull('permissions')->get(['id', 'permissions']) as $user) {
            $permissions = json_decode($user->permissions, true);
            if (is_array($permissions) && ! in_array('view_analytics', $permissions, true)
                && ! array_diff($previousPermissions, $permissions)) {
                DB::table('users')->where('id', $user->id)->update([
                    'permissions' => json_encode([...$permissions, 'view_analytics']),
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep permission assignments: they may have been explicitly edited after this upgrade.
    }
};
