<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add icon to commissions table if not present
        Schema::table('commissions', function (Blueprint $table) {
            if (! Schema::hasColumn('commissions', 'icon')) {
                $table->string('icon')->nullable()->default('cross')->after('description');
            }
        });

        // 2. Add position, is_officer, notes to commission_memberships table
        Schema::table('commission_memberships', function (Blueprint $table) {
            if (! Schema::hasColumn('commission_memberships', 'position')) {
                $table->string('position')->default('Member')->after('role');
            }
            if (! Schema::hasColumn('commission_memberships', 'is_officer')) {
                $table->boolean('is_officer')->default(false)->after('position');
            }
            if (! Schema::hasColumn('commission_memberships', 'notes')) {
                $table->text('notes')->nullable()->after('status');
            }
        });

        // 3. Add commission_id to ministries table
        Schema::table('ministries', function (Blueprint $table) {
            if (! Schema::hasColumn('ministries', 'commission_id')) {
                $table->foreignId('commission_id')->nullable()->after('slug')->constrained('commissions')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('commissions', function (Blueprint $table) {
            if (Schema::hasColumn('commissions', 'icon')) {
                $table->dropColumn('icon');
            }
        });

        Schema::table('commission_memberships', function (Blueprint $table) {
            $table->dropColumn(['position', 'is_officer', 'notes']);
        });

        Schema::table('ministries', function (Blueprint $table) {
            if (Schema::hasColumn('ministries', 'commission_id')) {
                $table->dropConstrainedForeignId('commission_id');
            }
        });
    }
};

