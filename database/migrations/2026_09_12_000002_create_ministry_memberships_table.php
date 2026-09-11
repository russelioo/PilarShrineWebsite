<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ministry_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ministry_id')->constrained('ministries')->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'inactive'])->default('pending');
            $table->text('application_message')->nullable();
            $table->text('experience')->nullable();
            $table->text('additional_info')->nullable();
            $table->boolean('agreed_terms')->default(true);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('reviewer_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'ministry_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministry_memberships');
    }
};

