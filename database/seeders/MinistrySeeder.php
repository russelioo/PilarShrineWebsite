<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Per parish architectural policy, ministries are strictly database-driven
     * and must be created by authorized administrators or commission coordinators.
     * No fabricated or placeholder ministries are seeded.
     */
    public function run(): void
    {
        // Zero fabricated ministries. Real ministries will appear once registered in admin.
    }
}
