<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $hasSundayMass = DB::table('mass_schedules')
            ->where('day_of_week', 'Sunday')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->exists();

        if (! $hasSundayMass) {
            DB::table('mass_schedules')->insert([
                'title' => 'Sunday Mass',
                'day_of_week' => 'Sunday',
                'start_time' => '07:30:00',
                'end_time' => '08:30:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void {}
};
