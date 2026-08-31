<?php

namespace Tests\Feature;

use App\Models\LivestreamSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLivestreamControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_can_turn_the_livestream_banner_on(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post('/admin/livestream', [
            'is_live' => true,
            'title' => 'Sunday Holy Mass',
            'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ])->assertRedirect();

        $this->assertDatabaseHas('livestream_settings', [
            'is_live' => true,
            'title' => 'Sunday Holy Mass',
        ]);

        $this->getJson('/api/livestream-status')->assertJson([
            'is_live' => true,
            'title' => 'Sunday Holy Mass',
            'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ]);
    }

    public function test_a_parishioner_cannot_change_the_livestream_status(): void
    {
        $parishioner = User::factory()->create(['role' => 'user']);

        $this->actingAs($parishioner)->post('/admin/livestream', [
            'is_live' => true,
            'title' => 'Unauthorized broadcast',
            'url' => 'https://www.facebook.com/PilarShrineSorsogon',
        ])->assertForbidden();

        $this->assertFalse(LivestreamSetting::query()->first()->is_live);
    }
}
