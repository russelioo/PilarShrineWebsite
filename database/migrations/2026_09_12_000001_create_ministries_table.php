<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ministries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->default('General');
            $table->string('icon')->default('✝');
            $table->text('description');
            $table->text('about')->nullable();
            $table->json('activities')->nullable();
            $table->string('meeting_schedule')->nullable();
            $table->string('meeting_location')->nullable();
            $table->string('coordinator_name')->nullable();
            $table->string('coordinator_email')->nullable();
            $table->string('coordinator_phone')->nullable();
            $table->foreignId('coordinator_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('requirements')->nullable();
            $table->boolean('is_accepting_members')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ministries');
    }
};

