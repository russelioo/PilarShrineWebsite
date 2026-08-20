<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mass_schedule_id')->constrained()->cascadeOnDelete();
            $table->time('slot_time');
            $table->unsignedInteger('max_capacity');
            $table->unsignedInteger('current_bookings')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
            $table->unique(['mass_schedule_id', 'slot_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
