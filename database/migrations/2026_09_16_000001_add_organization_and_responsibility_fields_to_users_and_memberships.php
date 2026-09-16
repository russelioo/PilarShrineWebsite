<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add organization, position, responsibilities, permissions to users table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'organization')) {
                $table->string('organization')->nullable()->after('role');
            }
            if (! Schema::hasColumn('users', 'position')) {
                $table->string('position')->nullable()->after('organization');
            }
            if (! Schema::hasColumn('users', 'responsibilities')) {
                $table->text('responsibilities')->nullable()->after('position');
            }
            if (! Schema::hasColumn('users', 'permissions')) {
                $table->json('permissions')->nullable()->after('responsibilities');
            }
        });

        // 2. Add role to ministry_memberships table
        Schema::table('ministry_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('ministry_memberships', 'role')) {
                $table->string('role')->default('member')->after('status');
            }
        });

        // 3. Backfill existing users with accurate roles, organizations, and responsibilities
        // Super Admin / Parish Administrator
        DB::table('users')
            ->whereIn('role', ['admin', 'super_admin'])
            ->update([
                'organization' => 'parish_administration',
                'position' => 'Parish Administrator',
                'responsibilities' => 'Parish Administration & System Management',
                'permissions' => json_encode(['all_commissions', 'all_ministries', 'manage_users', 'manage_records', 'manage_schedules']),
            ]);

        // Parish Secretary (Angela Gwyn Mansanero)
        DB::table('users')
            ->where('role', 'parish_secretary')
            ->update([
                'organization' => 'parish_administration',
                'position' => 'Parish Secretary',
                'responsibilities' => 'Administrative Staff',
                'permissions' => json_encode(['manage_records', 'manage_schedules']),
                'commission_id' => null, // Explicitly ensure no default commission assignment
            ]);

        // Parish Priest & Parochial Vicar
        DB::table('users')
            ->where('role', 'parish_priest')
            ->update([
                'organization' => 'parish_administration',
                'position' => 'Parish Priest',
                'responsibilities' => 'Parish Oversight & Pastoral Care',
                'permissions' => json_encode(['all_commissions', 'all_ministries', 'parish_oversight']),
            ]);

        DB::table('users')
            ->where('role', 'parochial_vicar')
            ->update([
                'organization' => 'parish_administration',
                'position' => 'Parochial Vicar',
                'responsibilities' => 'Liturgical & Pastoral Care',
                'permissions' => json_encode(['all_commissions', 'all_ministries', 'parish_oversight']),
            ]);

        // Commission Admins & Coordinators
        DB::table('users')
            ->where('role', 'commission_admin')
            ->update([
                'organization' => 'commission',
                'position' => 'Commission Coordinator',
                'responsibilities' => 'Commission Coordinator',
            ]);

        // Commission Members & Staff
        DB::table('users')
            ->whereIn('role', ['commission_member', 'staff'])
            ->update([
                'organization' => 'commission',
                'position' => 'Commission Member',
                'responsibilities' => 'Commission Member',
            ]);

        // Parishioners
        DB::table('users')
            ->where('role', 'user')
            ->update([
                'organization' => 'parishioner',
                'position' => 'Parishioner',
                'responsibilities' => 'Parishioner',
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['organization', 'position', 'responsibilities', 'permissions']);
        });

        Schema::table('ministry_memberships', function (Blueprint $table) {
            $table->dropColumn(['role']);
        });
    }
};

