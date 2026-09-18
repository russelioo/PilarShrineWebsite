<?php

namespace Database\Seeders;

use App\Models\Commission;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;

class CommissionSeeder extends Seeder
{
    /**
     * Seed the official pastoral commissions.
     *
     * Per Diocesan and parish structure, only official organizational commissions
     * are initialized. No fake coordinators, members, projects, documents, or ministries
     * are created. These must be registered by authorized parish administrators.
     */
    public function run(): void
    {
        $superAdmin = User::whereIn('role', ['admin', 'super_admin'])->first();

        // 9 Official Pastoral Commissions
        $canonicalCommissions = [
            [
                'name'        => 'Commission on Worship',
                'slug'        => 'worship',
                'code'        => 'WORSHIP',
                'icon'        => 'cross',
                'description' => 'Oversees sacred liturgy, Holy Mass celebrations, and liturgical services.',
            ],
            [
                'name'        => 'Commission on Christian Education',
                'slug'        => 'christian-education',
                'code'        => 'EDUCATION',
                'icon'        => 'book-open',
                'description' => 'Directs catechesis, sacramental preparation, Sunday school, and adult faith formation.',
            ],
            [
                'name'        => 'Commission on Social Concerns',
                'slug'        => 'social-concerns',
                'code'        => 'SOCIAL',
                'icon'        => 'heart-handshake',
                'description' => 'Coordinates Caritas Pilar, community relief programs, and parish social action.',
            ],
            [
                'name'        => 'Commission on Temporalities',
                'slug'        => 'temporalities',
                'code'        => 'TEMPORAL',
                'icon'        => 'building',
                'description' => 'Administers parish financial resources, physical facilities, and temporal assets.',
            ],
            [
                'name'        => 'Commission on Ecclesial Communities',
                'slug'        => 'ecclesial-communities',
                'code'        => 'ECCLESIAL',
                'icon'        => 'users',
                'description' => 'Nurtures Basic Ecclesial Communities and parish lay communities.',
            ],
            [
                'name'        => 'Commission on Family and Life',
                'slug'        => 'family-and-life',
                'code'        => 'FAMILY',
                'icon'        => 'home',
                'description' => 'Promotes family enrichment, marriage preparation, and the sanctity of human life.',
            ],
            [
                'name'        => 'Commission on Youth',
                'slug'        => 'youth',
                'code'        => 'YOUTH',
                'icon'        => 'spark',
                'description' => 'Accompanies young parishioners through spiritual growth, fellowship, and formation.',
            ],
            [
                'name'        => 'Commission on Clergy and Consecrated Life',
                'slug'        => 'clergy-and-consecrated-life',
                'code'        => 'CLERGY',
                'icon'        => 'shield',
                'description' => 'Supports clergy welfare, priestly vocations, and religious life formation.',
            ],
            [
                'name'        => 'Commission on Social Communications and Mass Media',
                'slug'        => 'social-communications',
                'code'        => 'SOCCOM',
                'icon'        => 'radio',
                'description' => 'Evangelizes digital spaces through Holy Mass livestreams, parish media, and public communications.',
            ],
        ];

        foreach ($canonicalCommissions as $commData) {
            Commission::updateOrCreate(
                ['slug' => $commData['slug']],
                [
                    'name'        => $commData['name'],
                    'code'        => $commData['code'],
                    'icon'        => $commData['icon'],
                    'description' => $commData['description'],
                    'is_active'   => true,
                ]
            );
        }

        // Audit Trail
        if ($superAdmin) {
            AuditLogger::log(
                action: 'system.commissions_seeded',
                description: 'Initialized 9 official Pastoral Commissions structure without mock operational data.',
                target: null,
                commissionId: null,
                oldValues: null,
                newValues: ['commissions_count' => 9],
                actor: $superAdmin,
                category: 'system'
            );
        }

        $this->command->info("✓ Successfully initialized 9 official Pastoral Commissions.");
    }
}
