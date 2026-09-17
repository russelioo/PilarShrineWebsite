<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\Commission;
use App\Models\CommissionDocument;
use App\Models\CommissionMembership;
use App\Models\CommissionProject;
use App\Models\Ministry;
use App\Models\PpcMember;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CommissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Canonical 8 Pastoral Commissions
        $canonicalCommissions = [
            [
                'name'        => 'Commission on Worship',
                'slug'        => 'worship',
                'code'        => 'WORSHIP',
                'icon'        => 'cross',
                'description' => 'Oversees sacred liturgy, Holy Mass celebrations, liturgical ministers, altar servers, lectors, Eucharistic ministers, and parish choirs.',
                'coord_name'  => 'Bro. Roberto Hernandez',
                'coord_email' => 'worship.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Christian Education',
                'slug'        => 'christian-education',
                'code'        => 'EDUCATION',
                'icon'        => 'book-open',
                'description' => 'Directs catechesis, sacramental preparation, Sunday school, adult faith formation, Bible study circles, and parish seminars.',
                'coord_name'  => 'Sis. Maria Santos',
                'coord_email' => 'education.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Social Concerns',
                'slug'        => 'social-concerns',
                'code'        => 'SOCIAL',
                'icon'        => 'heart-handshake',
                'description' => 'Coordinates Caritas Pilar, community relief programs, healthcare missions, prison pastoral care, ecological initiatives, and feeding ministries.',
                'coord_name'  => 'Bro. Carlos Mendoza',
                'coord_email' => 'social.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Temporalities',
                'slug'        => 'temporalities',
                'code'        => 'TEMPORAL',
                'icon'        => 'building',
                'description' => 'Administers parish financial resources, church heritage preservation, physical facilities, donor stewardship, and capital improvements.',
                'coord_name'  => 'Bro. Manuel Reyes',
                'coord_email' => 'temporalities.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Ecclesial Communities',
                'slug'        => 'ecclesial-communities',
                'code'        => 'ECCLESIAL',
                'icon'        => 'users',
                'description' => 'Nurtures Basic Ecclesial Communities (BEC), barangay pastoral councils, neighborhood gospel circles, and zonal chapels.',
                'coord_name'  => 'Sis. Angela Dizon',
                'coord_email' => 'ecclesial.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Family and Life',
                'slug'        => 'family-and-life',
                'code'        => 'FAMILY',
                'icon'        => 'home',
                'description' => 'Champions marriage preparation, pro-life advocacy, family counseling, parenting enrichment, and pastoral care for solo parents and elders.',
                'coord_name'  => 'Bro. Eduardo Ramos',
                'coord_email' => 'family.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Youth',
                'slug'        => 'youth',
                'code'        => 'YOUTH',
                'icon'        => 'spark',
                'description' => 'Empowers young parishioners through spiritual retreats, youth leadership training, campus ministry, mission trips, and worship concerts.',
                'coord_name'  => 'Bro. Pedro Cruz',
                'coord_email' => 'youth.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Clergy and Consecrated Life',
                'slug'        => 'clergy-and-consecrated-life',
                'code'        => 'CLERGY',
                'icon'        => 'shield',
                'description' => 'Supports clergy welfare, ongoing formation of religious sisters and brothers, promotion of priestly vocations, and altar sponsorship.',
                'coord_name'  => 'Bro. Fernando Ramos',
                'coord_email' => 'clergy.coord@pilarshrine.test',
            ],
            [
                'name'        => 'Commission on Social Communications and Mass Media',
                'slug'        => 'social-communications',
                'code'        => 'SOCCOM',
                'icon'        => 'spark',
                'description' => 'Directs digital evangelization, website administration, shrine live-streaming, and social media pastoral outreach.',
                'coord_name'  => 'John Russel Soreda',
                'coord_email' => 'soccom.coord@pilarshrine.test',
            ],
        ];

        $keepSlugs = array_column($canonicalCommissions, 'slug');

        // Clean up any old temporary commissions not in the official list
        Commission::whereNotIn('slug', $keepSlugs)->each(function (Commission $old) {
            User::where('commission_id', $old->id)->update(['commission_id' => null]);
            CommissionMembership::where('commission_id', $old->id)->delete();
            $old->forceDelete();
        });

        $superAdmin = User::where('role', 'super_admin')->first()
            ?: User::where('role', 'admin')->first();

        // 2. Seed PPC Executive Clergy & Officers Users
        $parishPriest = User::updateOrCreate(
            ['email' => 'fr.vicente@pilarshrine.test'],
            [
                'name'              => 'Rev. Fr. Vicente Roy',
                'role'              => 'parish_priest',
                'position'          => 'Parish Priest & Rector',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        $parochialVicar = User::updateOrCreate(
            ['email' => 'fr.jose@pilarshrine.test'],
            [
                'name'              => 'Rev. Fr. Jose Santos',
                'role'              => 'parochial_vicar',
                'position'          => 'Parochial Vicar',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        $layChair = User::updateOrCreate(
            ['email' => 'lay.chair@pilarshrine.test'],
            [
                'name'              => 'Dr. Eduardo Morales',
                'role'              => 'staff',
                'position'          => 'PPC Lay Co-Chairperson',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        $ppcSecretary = User::updateOrCreate(
            ['email' => 'ppc.secretary@pilarshrine.test'],
            [
                'name'              => 'Sis. Elena Cruz',
                'role'              => 'parish_secretary',
                'position'          => 'PPC Secretary',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        $ppcTreasurer = User::updateOrCreate(
            ['email' => 'ppc.treasurer@pilarshrine.test'],
            [
                'name'              => 'Bro. Manuel Reyes',
                'role'              => 'staff',
                'position'          => 'PPC Treasurer',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        $ppcAuditor = User::updateOrCreate(
            ['email' => 'ppc.auditor@pilarshrine.test'],
            [
                'name'              => 'Sis. Carmen Villanueva',
                'role'              => 'staff',
                'position'          => 'PPC Auditor',
                'password_hash'     => Hash::make('password123'),
                'is_verified'       => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Upsert PPC Executive Officers in `ppc_members`
        $executiveOfficers = [
            ['user' => $parishPriest,   'role' => 'Parish Priest', 'notes' => 'Presides over the PPC and acts as spiritual father of the parish community.'],
            ['user' => $parochialVicar, 'role' => 'Parochial Vicar', 'notes' => 'Assists the Parish Priest in pastoral oversight.'],
            ['user' => $layChair,       'role' => 'Lay Co-Chair', 'notes' => 'Leads the lay faithful and co-presides over PPC assemblies.'],
            ['user' => $ppcSecretary,   'role' => 'PPC Secretary', 'notes' => 'Maintains council minutes, resolutions, and official communications.'],
            ['user' => $ppcTreasurer,   'role' => 'PPC Treasurer', 'notes' => 'Oversees council appropriations and parish pastoral funds.'],
            ['user' => $ppcAuditor,     'role' => 'PPC Auditor', 'notes' => 'Performs periodic financial and inventory reviews.'],
        ];

        foreach ($executiveOfficers as $exec) {
            PpcMember::updateOrCreate(
                [
                    'user_id'       => $exec['user']->id,
                    'role_title'    => $exec['role'],
                    'commission_id' => null,
                ],
                [
                    'term_start' => '2025-01-01',
                    'term_end'   => '2027-12-31',
                    'status'     => 'active',
                    'notes'      => $exec['notes'],
                ]
            );
        }

        // 4. Create Commissions, Coordinator Users, Memberships, Projects & Documents
        foreach ($canonicalCommissions as $commData) {
            // A. Create or find Commission record
            $commission = Commission::updateOrCreate(
                ['slug' => $commData['slug']],
                [
                    'name'        => $commData['name'],
                    'code'        => $commData['code'],
                    'icon'        => $commData['icon'],
                    'description' => $commData['description'],
                    'is_active'   => true,
                ]
            );

            // B. Create Coordinator User
            $coordUser = User::updateOrCreate(
                ['email' => $commData['coord_email']],
                [
                    'name'              => $commData['coord_name'],
                    'role'              => 'commission_coordinator',
                    'commission_id'     => $commission->id,
                    'position'          => 'Commission Coordinator',
                    'password_hash'     => Hash::make('password123'),
                    'is_verified'       => true,
                    'email_verified_at' => now(),
                ]
            );

            // Set coordinator on commission
            $commission->update(['head_user_id' => $coordUser->id]);

            // C. Coordinator Commission Membership
            CommissionMembership::updateOrCreate(
                [
                    'commission_id' => $commission->id,
                    'user_id'       => $coordUser->id,
                ],
                [
                    'position'   => 'Commission Coordinator',
                    'is_officer' => true,
                    'role'       => 'head',
                    'status'     => 'active',
                    'joined_at'  => '2025-01-01',
                    'notes'      => 'Appointed pastoral commission head by the Parish Priest.',
                ]
            );

            // D. Add Coordinator to PPC as Commission Chairperson
            PpcMember::updateOrCreate(
                [
                    'user_id'       => $coordUser->id,
                    'commission_id' => $commission->id,
                ],
                [
                    'role_title' => 'Commission Chairperson',
                    'term_start' => '2025-01-01',
                    'term_end'   => '2027-12-31',
                    'status'     => 'active',
                    'notes'      => "Official representative of the {$commission->name} to the Parish Pastoral Council.",
                ]
            );

            // E. Seed Additional Officer and Members for this Commission
            $officerEmail = "{$commData['slug']}.sec@pilarshrine.test";
            $officerUser = User::updateOrCreate(
                ['email' => $officerEmail],
                [
                    'name'              => 'Bro. ' . Str::headline(str_replace('-', ' ', $commData['slug'])) . ' Secretary',
                    'role'              => 'commission_member',
                    'commission_id'     => $commission->id,
                    'position'          => 'Commission Secretary',
                    'password_hash'     => Hash::make('password123'),
                    'is_verified'       => true,
                    'email_verified_at' => now(),
                ]
            );

            CommissionMembership::updateOrCreate(
                [
                    'commission_id' => $commission->id,
                    'user_id'       => $officerUser->id,
                ],
                [
                    'position'   => 'Commission Secretary',
                    'is_officer' => true,
                    'role'       => 'officer',
                    'status'     => 'active',
                    'joined_at'  => '2025-01-15',
                    'notes'      => 'Takes minutes and manages commission communications.',
                ]
            );

            // F. Seed Sample Project
            CommissionProject::updateOrCreate(
                [
                    'commission_id' => $commission->id,
                    'title'         => "2026 {$commission->name} Annual Pastoral Plan",
                ],
                [
                    'description'  => "Strategic pastoral programs, community workshops, and apostolic initiatives scheduled for fiscal year 2026.",
                    'lead_user_id' => $coordUser->id,
                    'created_by'   => $superAdmin?->id ?? $coordUser->id,
                    'start_date'   => '2026-01-01',
                    'end_date'     => '2026-12-31',
                    'budget'       => 50000.00,
                    'status'       => 'ongoing',
                ]
            );

            // G. Seed Sample Document
            CommissionDocument::updateOrCreate(
                [
                    'commission_id' => $commission->id,
                    'title'         => "{$commission->code} Internal Operating Guidelines & Roster",
                ],
                [
                    'file_name'    => "{$commData['slug']}_guidelines.pdf",
                    'file_path'    => "commission_documents/{$commission->slug}/guidelines.pdf",
                    'file_type'    => 'pdf',
                    'file_size'    => 2048576,
                    'category'     => 'Guidelines',
                    'uploaded_by'  => $coordUser->id,
                ]
            );
        }

        // 5. Connect Existing Ministries to Appropriate Commissions
        $worshipCom = Commission::where('slug', 'worship')->first();
        if ($worshipCom) {
            Ministry::where('name', 'like', '%Choir%')
                ->orWhere('name', 'like', '%Music%')
                ->orWhere('name', 'like', '%Liturg%')
                ->update(['commission_id' => $worshipCom->id]);
        }

        $youthCom = Commission::where('slug', 'youth')->first();
        if ($youthCom) {
            Ministry::where('name', 'like', '%Youth%')
                ->update(['commission_id' => $youthCom->id]);
        }

        $socialCom = Commission::where('slug', 'social-concerns')->first();
        if ($socialCom) {
            Ministry::where('name', 'like', '%Social Communications%')
                ->orWhere('name', 'like', '%Social%')
                ->update(['commission_id' => $socialCom->id]);
        }

        // 6. Log Audit Trail
        if ($superAdmin) {
            AuditLogger::log(
                action: 'system.commissions_seeded',
                description: 'Initialized 8 official Pastoral Commissions and Parish Pastoral Council structure.',
                target: null,
                commissionId: null,
                oldValues: null,
                newValues: ['commissions_count' => 8, 'hierarchy' => 'PARISH -> PPC -> 8 COMMISSIONS'],
                actor: $superAdmin,
                category: 'system'
            );
        }

        $this->command->info("✓ Successfully seeded 8 canonical Pastoral Commissions, PPC board, and coordinator accounts.");
    }
}
