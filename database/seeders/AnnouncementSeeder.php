<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        if (!$admin) {
            return;
        }

        $items = [
            // Announcements / Advisories
            [
                'title' => 'Blessed Mother Statue Procession',
                'category' => 'Marian Devotion',
                'priority' => 'high',
                'is_pinned' => true,
                'published_at' => Carbon::now()->subDays(2),
                'content' => 'Annual Marian floral offering and solemn candlelight procession honoring Nuestra Señora del Pilar through the church grounds and town proper.',
                'image_url' => null,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Holy Week 2026 Liturgical Schedule',
                'category' => 'Liturgical Notice',
                'priority' => 'high',
                'is_pinned' => true,
                'published_at' => Carbon::now()->subDays(5),
                'content' => 'Complete schedules for Palm Sunday, Chrism Mass, Visita Iglesia, Seven Last Words, and the Solemn Easter Vigil across Pilar Shrine and barangay chapels.',
                'image_url' => null,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Parishioner Dinner & Volunteers Fellowship',
                'category' => 'Community',
                'priority' => 'medium',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(8),
                'content' => 'An evening of fraternal fellowship and thanksgiving for parish volunteers, pastoral councils, lay ministers, and choir leaders at the Parish Pastoral Center.',
                'image_url' => null,
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Parish Office Schedule & Emergency Calls',
                'category' => 'Advisory',
                'priority' => 'low',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(10),
                'content' => 'Office hours are Mon, Wed-Sat 8:00 AM - 11:30 AM & 1:00 PM - 5:00 PM; Sun 8:30 AM - 12:00 NN. Closed on Tuesdays and legal holidays. For emergency sick calls or viaticum, please contact the rectory hotline.',
                'image_url' => null,
                'created_by' => $admin->id,
            ],

            // Featured News / Stories with Images
            [
                'title' => 'May Crowning Celebration 2026',
                'category' => 'Parish Life',
                'priority' => 'high',
                'is_pinned' => true,
                'published_at' => Carbon::now()->subDays(3),
                'content' => 'Join our parish community for this sacred and joyful celebration in honor of the Blessed Virgin Mary, featuring floral offerings, Marian hymns, and community fellowship. The May Crowning is a venerable Marian tradition uniting devotees and families of our shrine in offering flowers, prayers, and hymns to Our Lady of the Pillar. Families and children are encouraged to participate in the floral offering and the community fellowship following the Holy Mass.',
                'image_url' => '/images/church-interior.png',
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Parish Fiesta Schedule 2026',
                'category' => 'Liturgical Feast',
                'priority' => 'high',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(6),
                'content' => 'Celebrate the vibrant patronal spirit of our shrine with solemn Masses, novena prayers, cultural exhibits, and thanksgiving celebrations for the whole community. The festivities bring together parishioners, pilgrims, and visitors in expressing gratitude for the continuous maternal protection of Our Lady of the Pillar over our municipality.',
                'image_url' => '/images/pilar-shrine-aerial.png',
                'created_by' => $admin->id,
            ],
            [
                'title' => 'Youth Camp & Leadership Formation 2026',
                'category' => 'Youth Ministry',
                'priority' => 'medium',
                'is_pinned' => false,
                'published_at' => Carbon::now()->subDays(9),
                'content' => 'An inspiring spiritual formation weekend for young parishioners focused on faith leadership, communal worship, and active ministry involvement. The Youth Camp provides young Catholics an opportunity to deepen their relationship with Christ, cultivate Christian fellowship, and develop active roles in shrine ministries.',
                'image_url' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?auto=format&fit=crop&w=900&q=80',
                'created_by' => $admin->id,
            ],
        ];

        foreach ($items as $item) {
            Announcement::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}

