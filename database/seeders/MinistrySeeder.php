<?php

namespace Database\Seeders;

use App\Models\Ministry;
use App\Models\User;
use Illuminate\Database\Seeder;

class MinistrySeeder extends Seeder
{
    public function run(): void
    {
        $maria = User::where('email', 'maria.staff@pilarshrine.test')->first();
        $pedro = User::where('email', 'pedro.staff@pilarshrine.test')->first();

        $ministries = [
            [
                'name' => 'Music & Choir Ministry',
                'slug' => 'music-and-choir-ministry',
                'category' => 'Sacred Music',
                'icon' => '🎵',
                'description' => 'Serve the parish through music, choir singing, musical accompaniment, and sacred liturgical worship.',
                'about' => 'The Music & Choir Ministry enriches Sunday Holy Masses, novenas, and solemn shrine celebrations through sacred choral pieces and liturgical songs. We invite vocalists, cantors, pianists, guitarists, and choir members.',
                'activities' => [
                    'Liturgical singing at Sunday Holy Masses',
                    'Vocal and choral workshops',
                    'Choir accompaniment during novenas and fiestas',
                    'Weekly rehearsal and spiritual fellowship',
                ],
                'meeting_schedule' => 'Every Saturday • 3:00 PM',
                'meeting_location' => 'Shrine Choir Loft & Parish Hall',
                'coordinator_name' => 'Maria Santos',
                'coordinator_email' => 'maria.staff@pilarshrine.test',
                'coordinator_phone' => '0928-123-4567',
                'coordinator_user_id' => $maria?->id,
                'requirements' => [
                    'Willingness to serve with dedication and humility',
                    'Basic musical ear, vocal interest, or instrument skill',
                    'Attendance at weekly Saturday choir rehearsals',
                    'Punctuality during scheduled liturgical celebrations',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Liturgical Ministry',
                'slug' => 'liturgical-ministry',
                'category' => 'Worship & Liturgy',
                'icon' => '✝',
                'description' => 'Assist priests at the altar of God as Lectors & Commentators, Extraordinary Ministers of Holy Communion, Altar Servers, and Ushers.',
                'about' => 'The Commission on Liturgy oversees the reverent execution of holy celebrations at Our Lady of the Pillar Shrine. Ministry members undergo ongoing formation on liturgy, scripture, and sacramental reverence.',
                'activities' => [
                    'Proclaiming the Word of God as Lectors',
                    'Assisting in the distribution of Holy Communion',
                    'Serving at the Holy Altar during Masses',
                    'Welcoming pilgrims and parishioners as Shrine Ushers',
                ],
                'meeting_schedule' => 'Every 1st & 3rd Saturday • 9:00 AM',
                'meeting_location' => 'Sacristy & Main Altar',
                'coordinator_name' => 'Parish Commission on Liturgy',
                'coordinator_email' => 'liturgy@pilarshrine.test',
                'coordinator_phone' => '0946-869-1254',
                'coordinator_user_id' => null,
                'requirements' => [
                    'Baptized and Confirmed practicing Catholic',
                    'Good moral character and reverence for the Holy Eucharist',
                    'Willingness to attend regular liturgical formation seminars',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Youth Apostolate (PYM)',
                'slug' => 'youth-apostolate-pym',
                'category' => 'Youth Formation',
                'icon' => '✦',
                'description' => 'Forming passionate young Christian disciples through retreats, faith leadership, fellowship, and dynamic shrine apostolates.',
                'about' => 'The Parish Youth Ministry (PYM) serves young people aged 13 to 28, creating a synodal space where youth grow in prayer, friendship, and joyful community service within Our Lady of the Pillar Shrine.',
                'activities' => [
                    'Monthly Youth Fellowship and Praise & Worship',
                    'Youth camps, recollection, and leadership summits',
                    'Active participation in shrine fiestas and processions',
                    'Community outreach and feeding programs',
                ],
                'meeting_schedule' => '2nd & 4th Sunday of the Month • 2:00 PM',
                'meeting_location' => 'Parish Youth Center',
                'coordinator_name' => 'Pedro Cruz',
                'coordinator_email' => 'pedro.staff@pilarshrine.test',
                'coordinator_phone' => '0918-123-4567',
                'coordinator_user_id' => $pedro?->id,
                'requirements' => [
                    'Ages 13 to 28 years old',
                    'Eager to grow in faith and fellowship',
                    'Commitment to attend monthly youth gatherings',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Caritas & Social Action',
                'slug' => 'caritas-and-social-action',
                'category' => 'Community Service',
                'icon' => '♡',
                'description' => 'Living out Gospel mercy through feeding missions, disaster relief, medical support, and pastoral care for impoverished communities.',
                'about' => 'The Social Action Commission serves as the hands and heart of Christ in Pilar, Sorsogon. Members coordinate relief supplies, community pantries, and outreach programs for marginalized families.',
                'activities' => [
                    'Relief distribution during calamities and typhoons',
                    'Weekly feeding and nutritional programs for children',
                    'Medical and dental mission coordination',
                    'Livelihood seminars and social development initiatives',
                ],
                'meeting_schedule' => 'First Sunday of the Month • 10:00 AM',
                'meeting_location' => 'Social Action Office',
                'coordinator_name' => 'Ana Flores',
                'coordinator_email' => 'caritas@pilarshrine.test',
                'coordinator_phone' => '0939-555-1234',
                'coordinator_user_id' => null,
                'requirements' => [
                    'Compassion for the poor and marginalized',
                    'Availability for field outreach and community visits',
                    'Collaborative and service-oriented heart',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Catechetical Ministry',
                'slug' => 'catechetical-ministry',
                'category' => 'Faith Formation',
                'icon' => '📖',
                'description' => 'Teaching Catholic doctrine and preparing children, youth, and adults for Holy Baptism, First Communion, and Confirmation.',
                'about' => 'Our catechists bring the light of the Gospel to public schools and barangay chapels across Pilar, forming hearts in prayer, Catholic doctrine, and sacramental life.',
                'activities' => [
                    'Classroom catechesis in public elementary and high schools',
                    'First Communion and Confirmation preparation modules',
                    'Adult Rite of Christian Initiation (RCIA)',
                    'Bible sharing circles and catechetical exhibits',
                ],
                'meeting_schedule' => 'Every Saturday • 1:30 PM',
                'meeting_location' => 'Catechetical Training Center',
                'coordinator_name' => 'Commission on Catechesis',
                'coordinator_email' => 'catechesis@pilarshrine.test',
                'coordinator_phone' => '0946-869-1254',
                'coordinator_user_id' => null,
                'requirements' => [
                    'Fidelity to the Magisterium and Catholic doctrine',
                    'Willingness to complete catechist accreditation',
                    'Patience and love for children and learners',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Social Communications & Mass Media',
                'slug' => 'social-communications-and-mass-media',
                'category' => 'Parish Media',
                'icon' => '📡',
                'description' => 'Evangelizing digital spaces through official Mass livestreams, photography, parish website curation, and social media announcements.',
                'about' => 'The SocCom ministry connects homebound parishioners, OFWs, and devotees worldwide to Our Lady of the Pillar Shrine through reliable livestreams, bulletin design, video production, and sound system engineering.',
                'activities' => [
                    'Livestream broadcasting of Sunday and fiesta Holy Masses',
                    'Photography and videography of parish celebrations',
                    'Managing official parish social media channels',
                    'Parish bulletin and visual content design',
                ],
                'meeting_schedule' => 'Sunday 7:00 AM & Wednesday 6:00 PM',
                'meeting_location' => 'Media & Broadcast Room',
                'coordinator_name' => 'Parish SocCom Team',
                'coordinator_email' => 'media@pilarshrine.test',
                'coordinator_phone' => '0946-869-1254',
                'coordinator_user_id' => null,
                'requirements' => [
                    'Interest in livestreaming, photography, writing, or design',
                    'Willingness to serve during weekend Masses and events',
                    'Basic tech-savviness or desire to learn broadcast tools',
                ],
                'is_accepting_members' => true,
            ],
            [
                'name' => 'Mandated Marian Organizations',
                'slug' => 'mandated-marian-organizations',
                'category' => 'Lay Apostolates',
                'icon' => '♛',
                'description' => 'United Catholic lay apostolates including the Legion of Mary, Catholic Women’s League (CWL), Knights of Columbus, and Mother Butler Guild.',
                'about' => 'Our mandated lay organizations provide structured spiritual development, Marian consecration, family life advocacy, and church altar care.',
                'activities' => [
                    'Daily and weekly Rosary crusades and home visitations',
                    'Altar care, vestment laundering, and sanctuary flowers',
                    'Charity fund-raising and educational assistance',
                    'Guard of Honor during Eucharistic processions',
                ],
                'meeting_schedule' => 'First Saturday of the Month • 8:00 AM',
                'meeting_location' => 'Parish Pastoral Hall',
                'coordinator_name' => 'Council of Lay Apostolates',
                'coordinator_email' => 'apostolates@pilarshrine.test',
                'coordinator_phone' => '0946-869-1254',
                'coordinator_user_id' => null,
                'requirements' => [
                    'Practicing Catholic in good standing',
                    'Commitment to specific organization rules and meetings',
                ],
                'is_accepting_members' => true,
            ],
        ];

        foreach ($ministries as $data) {
            Ministry::updateOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }
    }
}

