<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\CommissionMembership;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class CommissionSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // Official Commissions arranged in strict alphabetical order.
        // ============================================================
        $commissions = [
            [
                'name'        => 'Commission on Education',
                'slug'        => 'education',
                'code'        => 'EDUCATION',
                'description' => 'Directs faith formation, catechesis, first communion, confirmation, and adult religious education.',
            ],
            [
                'name'        => 'Commission on Family and Human Life',
                'slug'        => 'family-and-human-life',
                'code'        => 'FAMILY',
                'description' => 'Supports married couples, family pastoral care, marriage preparation, and sanctification of domestic churches.',
            ],
            [
                'name'        => 'Commission on Mission',
                'slug'        => 'mission',
                'code'        => 'MISSION',
                'description' => 'Directs missionary outreach, evangelization efforts, and formation of missionary disciples.',
            ],
            [
                'name'        => 'Commission on Service',
                'slug'        => 'service',
                'code'        => 'SERVICE',
                'description' => 'Coordinates community outreach, relief goods, healthcare assistance, and aid to marginalized families.',
            ],
            [
                'name'        => 'Commission on Social Communications and Mass Media',
                'slug'        => 'social-communications',
                'code'        => 'SOCCOM',
                'description' => 'Oversees the shrine livestreaming, official website, publications, and social media evangelization.',
            ],
            [
                'name'        => 'Commission on Temporalities',
                'slug'        => 'temporalities',
                'code'        => 'TEMPORAL',
                'description' => 'Oversees parish financial management, properties, donor stewardship, and transparent resource administration.',
            ],
            [
                'name'        => 'Commission on the Laity',
                'slug'        => 'laity',
                'code'        => 'LAITY',
                'description' => 'Engages and forms lay faithful in apostolate, parish councils, and active participation in the Church.',
            ],
            [
                'name'        => 'Commission on Worship',
                'slug'        => 'worship',
                'code'        => 'WORSHIP',
                'description' => 'Coordinates sacred celebrations, liturgical ministers, altar servers, lectors, and choir.',
            ],
            [
                'name'        => 'Commission on Youth',
                'slug'        => 'youth',
                'code'        => 'YOUTH',
                'description' => 'Fosters young parishioners in leadership, discipleship, parish activities, and youth ministries.',
            ],
        ];

        $keepSlugs = array_column($commissions, 'slug');

        // Clean up any old commissions not in the official list
        Commission::whereNotIn('slug', $keepSlugs)->each(function (Commission $old) {
            User::where('commission_id', $old->id)->update(['commission_id' => null]);
            CommissionMembership::where('commission_id', $old->id)->delete();
            $old->forceDelete();
        });

        // Upsert official commissions in alphabetical order
        $commissionModels = [];
        foreach ($commissions as $data) {
            $comm = Commission::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name'        => $data['name'],
                    'code'        => $data['code'],
                    'description' => $data['description'],
                    'is_active'   => true,
                ]
            );
            $commissionModels[$comm->slug] = $comm;
        }

        // Assign Maria Santos → Commission on Social Communications and Mass Media
        $maria = User::where('email', 'maria.staff@pilarshrine.test')->first();
        if ($maria && isset($commissionModels['social-communications'])) {
            $socCom = $commissionModels['social-communications'];
            $maria->update(['commission_id' => $socCom->id]);
            CommissionMembership::updateOrCreate(
                ['user_id' => $maria->id, 'commission_id' => $socCom->id],
                ['role' => 'staff', 'status' => 'active', 'joined_at' => now()]
            );
        }

        // Assign Pedro Cruz → Commission on Youth
        $pedro = User::where('email', 'pedro.staff@pilarshrine.test')->first();
        if ($pedro && isset($commissionModels['youth'])) {
            $youthCom = $commissionModels['youth'];
            $pedro->update(['commission_id' => $youthCom->id]);
            CommissionMembership::updateOrCreate(
                ['user_id' => $pedro->id, 'commission_id' => $youthCom->id],
                ['role' => 'staff', 'status' => 'active', 'joined_at' => now()]
            );
        }

        // Assign John Russel Soreda → Commission on Social Communications and Mass Media
        $john = User::where('email', 'soredajohnrussel15@gmail.com')->first();
        if ($john && isset($commissionModels['social-communications'])) {
            $socCom = $commissionModels['social-communications'];
            $john->update(['commission_id' => $socCom->id]);
            CommissionMembership::updateOrCreate(
                ['user_id' => $john->id, 'commission_id' => $socCom->id],
                ['role' => 'staff', 'status' => 'active', 'joined_at' => now()]
            );
        }

        // Record initialization audit log
        $admin = User::where('role', 'admin')->orWhere('role', 'super_admin')->first();
        if ($admin && ! \App\Models\AuditLog::where('action', 'commission_created')->exists()) {
            AuditLogger::log(
                action: 'commission_created',
                description: 'Initialized official parish commissions with role assignments.',
                target: $commissionModels['social-communications'] ?? null,
                commissionId: $commissionModels['social-communications']->id ?? null,
                actor: $admin,
                category: 'commission'
            );
        }

        $count = count($commissions);
        $this->command->info("✓ {$count} official commissions seeded successfully in alphabetical order.");
    }
}
