<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mass_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location');
            $table->string('priest_in_charge');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mass_schedules');
    }
};
