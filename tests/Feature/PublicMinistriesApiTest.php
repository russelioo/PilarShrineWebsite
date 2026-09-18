<?php

namespace Tests\Feature;

use App\Models\Commission;
use App\Models\Ministry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicMinistriesApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_can_fetch_commissions_and_defaults_to_zero_when_no_ministries_exist(): void
    {
        $response = $this->getJson(route('api.ministries'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'commissions' => [
                '*' => [
                    'id',
                    'name',
                    'slug',
                    'code',
                    'icon',
                    'description',
                    'ministries_count',
                ],
            ],
            'ministries',
            'total_count',
            'filtered_count',
        ]);

        $data = $response->json();
        $this->assertCount(9, $data['commissions']);
        $this->assertEquals(0, $data['total_count']);
        $this->assertEquals(0, $data['filtered_count']);
        $this->assertEmpty($data['ministries']);

        // Every commission must have 0 ministries initially
        foreach ($data['commissions'] as $c) {
            $this->assertEquals(0, $c['ministries_count'], "Commission {$c['slug']} should have 0 ministries initially.");
        }
    }

    public function test_all_nine_canonical_commissions_exist(): void
    {
        $response = $this->getJson(route('api.ministries'));
        $response->assertStatus(200);
        $data = $response->json();

        // 9 Canonical Pastoral Commissions
        $this->assertCount(9, $data['commissions']);
        $expectedCommissions = [
            'worship',
            'christian-education',
            'social-concerns',
            'temporalities',
            'ecclesial-communities',
            'family-and-life',
            'youth',
            'clergy-and-consecrated-life',
            'social-communications',
        ];
        $seededSlugs = array_column($data['commissions'], 'slug');
        foreach ($expectedCommissions as $slug) {
            $this->assertContains($slug, $seededSlugs, "Expected commission '{$slug}' to be present.");
        }
    }

    public function test_ministry_appears_only_when_created_in_database(): void
    {
        $worship = Commission::where('slug', 'worship')->first();
        $this->assertNotNull($worship);

        Ministry::create([
            'name' => 'Sancta Maria Choir',
            'slug' => 'sancta-maria-choir',
            'commission_id' => $worship->id,
            'category' => 'Music & Liturgy',
            'description' => 'Parish liturgical choir for Holy Masses.',
            'status' => 'active',
            'is_public' => true,
            'is_accepting_members' => true,
        ]);

        $response = $this->getJson(route('api.ministries'));
        $response->assertStatus(200);
        $data = $response->json();

        $this->assertEquals(1, $data['total_count']);
        $this->assertEquals(1, $data['filtered_count']);
        $this->assertCount(1, $data['ministries']);
        $this->assertEquals('Sancta Maria Choir', $data['ministries'][0]['name']);
        $this->assertEquals('worship', $data['ministries'][0]['commission']['slug']);

        // Worship commission count should be 1, others 0
        $worshipComm = collect($data['commissions'])->firstWhere('slug', 'worship');
        $this->assertEquals(1, $worshipComm['ministries_count']);

        $youthComm = collect($data['commissions'])->firstWhere('slug', 'youth');
        $this->assertEquals(0, $youthComm['ministries_count']);
    }

    public function test_inactive_or_private_ministries_are_not_shown_in_public_directory(): void
    {
        $worship = Commission::where('slug', 'worship')->first();

        // Inactive ministry
        Ministry::create([
            'name' => 'Archived Ministry',
            'slug' => 'archived-ministry',
            'commission_id' => $worship->id,
            'category' => 'Worship',
            'description' => 'Archived ministry description.',
            'status' => 'inactive',
            'is_public' => true,
        ]);

        // Private / internal ministry
        Ministry::create([
            'name' => 'Private Council',
            'slug' => 'private-council',
            'commission_id' => $worship->id,
            'category' => 'Worship',
            'description' => 'Internal council description.',
            'status' => 'active',
            'is_public' => false,
        ]);

        $response = $this->getJson(route('api.ministries'));
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(0, $data['total_count']);
        $this->assertEmpty($data['ministries']);
        $worshipComm = collect($data['commissions'])->firstWhere('slug', 'worship');
        $this->assertEquals(0, $worshipComm['ministries_count']);
    }

    public function test_search_and_commission_filters_work_on_real_database_records(): void
    {
        $worship = Commission::where('slug', 'worship')->first();
        $social = Commission::where('slug', 'social-concerns')->first();

        Ministry::create([
            'name' => 'Shrine Sanctuary Choir',
            'slug' => 'shrine-sanctuary-choir',
            'commission_id' => $worship->id,
            'category' => 'Liturgy',
            'description' => 'Vocal choir for feast masses.',
            'status' => 'active',
            'is_public' => true,
        ]);

        Ministry::create([
            'name' => 'Caritas Outreach Guild',
            'slug' => 'caritas-outreach-guild',
            'commission_id' => $social->id,
            'category' => 'Outreach',
            'description' => 'Medical missions and food distribution.',
            'status' => 'active',
            'is_public' => true,
        ]);

        // Search test
        $searchResponse = $this->getJson(route('api.ministries', ['search' => 'Choir']));
        $searchResponse->assertStatus(200);
        $searchData = $searchResponse->json();
        $this->assertEquals(1, $searchData['filtered_count']);
        $this->assertEquals('Shrine Sanctuary Choir', $searchData['ministries'][0]['name']);

        // Commission filter test
        $commResponse = $this->getJson(route('api.ministries', ['commission' => 'social-concerns']));
        $commResponse->assertStatus(200);
        $commData = $commResponse->json();
        $this->assertEquals(1, $commData['filtered_count']);
        $this->assertEquals('Caritas Outreach Guild', $commData['ministries'][0]['name']);
    }

    public function test_optional_fields_are_null_when_not_provided(): void
    {
        $worship = Commission::where('slug', 'worship')->first();

        Ministry::create([
            'name' => 'Minimal Ministry',
            'slug' => 'minimal-ministry',
            'commission_id' => $worship->id,
            'description' => 'Minimal description.',
            'status' => 'active',
            'is_public' => true,
            'meeting_schedule' => null,
            'meeting_location' => null,
            'coordinator_name' => null,
        ]);

        $response = $this->getJson(route('api.ministries'));
        $response->assertStatus(200);
        $ministry = $response->json()['ministries'][0];

        $this->assertNull($ministry['meeting_schedule']);
        $this->assertNull($ministry['meeting_location']);
        $this->assertNull($ministry['coordinator_name']);
        $this->assertNull($ministry['coordinator_email']);
        $this->assertNull($ministry['coordinator_phone']);
    }

    public function test_sanitized_fields_do_not_leak_private_admin_data(): void
    {
        $response = $this->getJson(route('api.ministries'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('remember_token', $content);
    }
}

