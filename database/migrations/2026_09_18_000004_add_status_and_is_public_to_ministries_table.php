<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            if (! Schema::hasColumn('ministries', 'status')) {
                $table->string('status')->default('active')->after('description');
            }
            if (! Schema::hasColumn('ministries', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('status');
            }
            if (! Schema::hasColumn('ministries', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_accepting_members')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('ministries', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('ministries', function (Blueprint $table) {
            if (Schema::hasColumn('ministries', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
            if (Schema::hasColumn('ministries', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('ministries', 'is_public')) {
                $table->dropColumn('is_public');
            }
            if (Schema::hasColumn('ministries', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};

