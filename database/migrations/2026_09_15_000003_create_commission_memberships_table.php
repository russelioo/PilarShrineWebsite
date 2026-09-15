<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('commission_id')->constrained('commissions')->cascadeOnDelete();
            $table->string('role')->default('member'); // 'head', 'admin', 'member', 'staff'
            $table->string('status')->default('active'); // 'active', 'inactive'
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'commission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_memberships');
    }
};

