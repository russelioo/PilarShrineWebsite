<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Non-destructively add schedule detail columns to mass_schedules
        Schema::table('mass_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('mass_schedules', 'category')) {
                $table->string('category', 100)->nullable()->after('title');
            }
            if (!Schema::hasColumn('mass_schedules', 'schedule_type')) {
                $table->string('schedule_type', 100)->nullable()->default('Holy Mass')->after('category');
            }
            if (!Schema::hasColumn('mass_schedules', 'time_display')) {
                $table->string('time_display', 150)->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('mass_schedules', 'notes')) {
                $table->string('notes', 255)->nullable()->after('location');
            }
            if (!Schema::hasColumn('mass_schedules', 'is_highlighted')) {
                $table->boolean('is_highlighted')->default(false)->after('is_active');
            }
            if (!Schema::hasColumn('mass_schedules', 'is_livestreamed')) {
                $table->boolean('is_livestreamed')->default(false)->after('is_highlighted');
            }
            if (!Schema::hasColumn('mass_schedules', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_livestreamed');
            }
        });

        // 2. Enhance existing row 1 (Sunday Mass) without changing its ID or core fields
        DB::table('mass_schedules')
            ->where('id', 1)
            ->update([
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '7:30 AM — Holy Mass',
                'notes' => 'FB Live',
                'is_livestreamed' => 1,
                'sort_order' => 6,
            ]);

        // Enhance row 2 if present
        DB::table('mass_schedules')
            ->where('id', 2)
            ->update([
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '7:30 AM — Holy Mass',
                'notes' => 'FB Live',
                'sort_order' => 6,
            ]);

        // Enhance row 3 if present
        DB::table('mass_schedules')
            ->where('id', 3)
            ->update([
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '6:00 PM — Holy Mass',
                'sort_order' => 8,
            ]);

        // 3. Migrate the existing hardcoded public schedules into mass_schedules (non-destructive check)
        $existingSchedules = [
            // Daily Mass
            [
                'title' => 'Daily Mass (Mon & Wed)',
                'day_of_week' => 'Monday & Wednesday',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Daily Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '5:00 PM — Holy Mass',
                'notes' => null,
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 1,
                'is_active' => 1,
            ],
            [
                'title' => 'Daily Mass (Tue, Thu & Fri)',
                'day_of_week' => 'Tuesday, Thursday & Friday',
                'start_time' => '06:00:00',
                'end_time' => '07:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Daily Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '6:00 AM — Holy Mass',
                'notes' => null,
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 2,
                'is_active' => 1,
            ],
            [
                'title' => 'Daily Mass (Saturday)',
                'day_of_week' => 'Saturday',
                'start_time' => '06:00:00',
                'end_time' => '07:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Daily Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '6:00 AM — Holy Mass',
                'notes' => null,
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 3,
                'is_active' => 1,
            ],
            [
                'title' => 'Anticipated Mass (Saturday)',
                'day_of_week' => 'Anticipated Mass (Saturday)',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Daily Mass',
                'schedule_type' => 'Anticipated Sunday Mass',
                'time_display' => '5:00 PM — Anticipated Sunday Mass',
                'notes' => 'Anticipated Sunday Mass',
                'is_highlighted' => 1,
                'is_livestreamed' => 0,
                'sort_order' => 4,
                'is_active' => 1,
            ],

            // Sunday Mass
            [
                'title' => 'Early Morning Sunday Mass',
                'day_of_week' => 'Sunday',
                'start_time' => '05:00:00',
                'end_time' => '06:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '5:00 AM — Holy Mass',
                'notes' => 'Early Morning',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 5,
                'is_active' => 1,
            ],
            [
                'title' => 'Afternoon (Live) Sunday Mass',
                'day_of_week' => 'Sunday',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Sunday Mass',
                'schedule_type' => 'Holy Mass',
                'time_display' => '5:00 PM — Holy Mass',
                'notes' => 'FB Live',
                'is_highlighted' => 0,
                'is_livestreamed' => 1,
                'sort_order' => 7,
                'is_active' => 1,
            ],

            // Sacrament of Reconciliation (Confession)
            [
                'title' => 'Sacrament of Reconciliation',
                'day_of_week' => 'Every First Thursday of the Month',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'location' => 'Shrine Confessional',
                'priest_in_charge' => 'Parish Priest & Confessors',
                'category' => 'Sacrament of Reconciliation',
                'schedule_type' => 'Confession',
                'time_display' => '5:00 PM — Confession',
                'notes' => 'Confession & Spiritual Healing',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 9,
                'is_active' => 1,
            ],

            // Monthly Devotion to Our Lady of the Pillar
            [
                'title' => 'Monthly Devotion Mass to Our Lady of the Pillar',
                'day_of_week' => 'Every 12th of the Month',
                'start_time' => '17:00:00',
                'end_time' => '18:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Monthly Devotion to Our Lady of the Pillar',
                'schedule_type' => 'Holy Mass',
                'time_display' => '5:00 PM — Holy Mass',
                'notes' => 'Patronal Devotional Day',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 10,
                'is_active' => 1,
            ],
            [
                'title' => 'Marian Procession',
                'day_of_week' => 'Every 12th of the Month',
                'start_time' => '18:00:00',
                'end_time' => '19:30:00',
                'location' => 'Shrine & Town Procession Route',
                'priest_in_charge' => 'Parish Clergy',
                'category' => 'Monthly Devotion to Our Lady of the Pillar',
                'schedule_type' => 'Marian Procession',
                'time_display' => '6:00 PM — Marian Procession',
                'notes' => 'Procession',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 11,
                'is_active' => 1,
            ],

            // Special Liturgical Activities
            [
                'title' => 'Healing Mass',
                'day_of_week' => 'Every First Tuesday',
                'start_time' => '06:00:00',
                'end_time' => '07:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Special Liturgical Activities',
                'schedule_type' => 'Healing Mass',
                'time_display' => '6:00 AM — Healing Mass',
                'notes' => 'Monthly Observance',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 12,
                'is_active' => 1,
            ],
            [
                'title' => 'Misa sa Campo Santo',
                'day_of_week' => 'Every First Monday',
                'start_time' => '06:00:00',
                'end_time' => '07:00:00',
                'location' => 'Campo Santo Chapel',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Special Liturgical Activities',
                'schedule_type' => 'Misa sa Campo Santo',
                'time_display' => '6:00 AM — Misa sa Campo Santo',
                'notes' => 'Campo Santo Chapel',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 13,
                'is_active' => 1,
            ],
            [
                'title' => 'Mass at Our Lady of Fatima Chapel (Banuyo)',
                'day_of_week' => 'First Saturday',
                'start_time' => '06:00:00',
                'end_time' => '07:00:00',
                'location' => 'Our Lady of Fatima Chapel (Banuyo)',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Special Liturgical Activities',
                'schedule_type' => 'Chapel Mass',
                'time_display' => '6:00 AM — Mass at Our Lady of Fatima Chapel (Banuyo)',
                'notes' => 'Barangay Chapel',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 14,
                'is_active' => 1,
            ],
            [
                'title' => 'First Friday Holy Hour',
                'day_of_week' => 'Every First Friday',
                'start_time' => '07:00:00',
                'end_time' => '08:00:00',
                'location' => 'Main Church',
                'priest_in_charge' => 'Parish Priest',
                'category' => 'Special Liturgical Activities',
                'schedule_type' => 'Holy Hour',
                'time_display' => 'Holy Hour after Holy Mass',
                'notes' => 'Holy Hour after Holy Mass',
                'is_highlighted' => 0,
                'is_livestreamed' => 0,
                'sort_order' => 15,
                'is_active' => 1,
            ],
        ];

        foreach ($existingSchedules as $item) {
            $exists = DB::table('mass_schedules')
                ->where('title', $item['title'])
                ->where('day_of_week', $item['day_of_week'])
                ->exists();

            if (!$exists) {
                DB::table('mass_schedules')->insert(array_merge($item, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mass_schedules', function (Blueprint $table) {
            $columns = ['category', 'schedule_type', 'time_display', 'notes', 'is_highlighted', 'is_livestreamed', 'sort_order'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('mass_schedules', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

