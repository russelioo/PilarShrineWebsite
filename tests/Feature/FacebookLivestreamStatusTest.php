<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FacebookLivestreamStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget('facebook-live-status');
        config()->set('services.facebook.page_id', '123456');
        config()->set('services.facebook.page_url', 'https://www.facebook.com/PilarShrineSorsogon');
        config()->set('services.facebook.page_access_token', 'test-token');
        config()->set('services.facebook.graph_version', 'v23.0');
    }

    public function test_it_returns_the_active_facebook_livestream(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(['data' => [[
                'id' => '987654',
                'title' => 'Sunday Holy Mass',
                'status' => 'LIVE',
                'permalink_url' => '/PilarShrineSorsogon/videos/987654/',
            ]]]),
        ]);

        $this->getJson('/api/livestream-status')
            ->assertOk()
            ->assertJson([
                'is_live' => true,
                'title' => 'Sunday Holy Mass',
                'url' => 'https://www.facebook.com/PilarShrineSorsogon/videos/987654/',
            ]);
    }

    public function test_it_returns_offline_when_facebook_has_no_live_video(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['data' => []])]);

        $this->getJson('/api/livestream-status')
            ->assertOk()
            ->assertJson([
                'is_live' => false,
                'title' => null,
                'url' => 'https://www.facebook.com/PilarShrineSorsogon',
            ]);
    }

    public function test_it_does_not_contact_facebook_without_credentials(): void
    {
        config()->set('services.facebook.page_access_token');
        Http::fake();

        $this->getJson('/api/livestream-status')
            ->assertOk()
            ->assertJson(['is_live' => false]);

        Http::assertNothingSent();
    }
}
