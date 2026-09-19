<?php

namespace Tests\Feature;

use App\Models\LivestreamSetting;
use App\Models\MassSchedule;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FacebookLivestreamStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        MassSchedule::query()->update(['is_livestreamed' => false]);
        config()->set('services.facebook.page_access_token', null);
        Http::fake();
    }

    private function schedule(array $attributes = []): MassSchedule
    {
        return MassSchedule::create([
            'title' => 'Sunday Holy Mass', 'day_of_week' => 'Sunday', 'start_time' => '07:30:00',
            'end_time' => '08:30:00', 'location' => 'Main Church', 'is_active' => true,
            'is_livestreamed' => true, ...$attributes,
        ]);
    }

    private function at(string $time): void
    {
        $this->travelTo(CarbonImmutable::parse($time, 'Asia/Manila'));
    }

    public function test_button_opens_five_minutes_before_and_closes_eighty_minutes_after_mass_start(): void
    {
        $this->schedule();
        foreach ([
            '07:24:59' => false, '07:25:00' => true, '07:30:00' => true,
            '08:30:00' => true, '08:49:59' => true, '08:50:00' => false,
        ] as $time => $visible) {
            $this->at('2026-09-20 '.$time);
            $this->getJson('/api/livestream-status')->assertOk()->assertJsonPath('is_live', $visible);
        }
        Http::assertNothingSent();
    }

    public function test_afternoon_mass_has_its_own_window_and_the_button_is_hidden_between_masses(): void
    {
        $this->schedule();
        $this->schedule(['start_time' => '17:00:00', 'end_time' => '18:00:00']);
        foreach (['12:00:00' => false, '16:54:59' => false, '16:55:00' => true, '18:19:59' => true, '18:20:00' => false] as $time => $visible) {
            $this->at('2026-09-20 '.$time);
            $this->getJson('/api/livestream-status')->assertJsonPath('is_live', $visible);
        }
    }

    public function test_window_uses_philippine_time_even_when_the_server_clock_is_in_utc(): void
    {
        $this->schedule();
        $this->travelTo(CarbonImmutable::parse('2026-09-19 23:25:00', 'UTC'));
        $this->getJson('/api/livestream-status')->assertOk()->assertJson([
            'is_live' => true,
            'opens_at' => '2026-09-20T07:25:00+08:00',
            'closes_at' => '2026-09-20T08:50:00+08:00',
            'server_time' => '2026-09-20T07:25:00+08:00',
        ])->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_only_active_non_deleted_livestream_schedules_can_show_the_button(): void
    {
        $this->at('2026-09-20 07:30:00');
        $this->schedule(['is_active' => false]);
        $this->schedule(['is_livestreamed' => false]);
        $this->schedule()->delete();
        $this->schedule(['day_of_week' => 'Monday']);
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
    }

    public function test_old_manual_switch_and_facebook_credentials_cannot_override_the_schedule(): void
    {
        $this->schedule();
        LivestreamSetting::query()->firstOrFail()->update(['is_live' => true]);
        config()->set('services.facebook.page_access_token', 'test-token');
        $this->at('2026-09-20 08:50:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
        Http::assertNothingSent();
    }

    public function test_saved_broadcast_details_are_used_and_schedule_changes_take_effect_immediately(): void
    {
        $schedule = $this->schedule();
        LivestreamSetting::query()->firstOrFail()->update([
            'title' => 'Watch Sunday Mass', 'url' => 'https://www.facebook.com/PilarShrineSorsogon/live',
        ]);
        $this->at('2026-09-20 07:30:00');
        $this->getJson('/api/livestream-status')->assertJson([
            'is_live' => true, 'title' => 'Watch Sunday Mass', 'url' => 'https://www.facebook.com/PilarShrineSorsogon/live',
        ]);
        $schedule->update(['start_time' => '09:00:00']);
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
        $this->at('2026-09-20 08:55:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', true);
        $schedule->update(['is_active' => false]);
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
    }

    public function test_midnight_windows_are_evaluated_against_the_mass_date(): void
    {
        $schedule = $this->schedule(['start_time' => '00:02:00']);
        $this->at('2026-09-19 23:56:59');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
        $this->at('2026-09-19 23:57:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', true);
        $this->at('2026-09-20 01:22:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
        $schedule->update(['start_time' => '23:30:00']);
        $this->at('2026-09-21 00:49:59');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', true);
        $this->at('2026-09-21 00:50:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
    }

    #[DataProvider('frequencies')]
    public function test_existing_schedule_frequencies_are_respected(string $frequency, string $date, bool $visible): void
    {
        $this->schedule(['day_of_week' => $frequency]);
        $this->at($date.' 07:30:00');
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', $visible);
    }

    public static function frequencies(): array
    {
        return [
            ['Monday & Wednesday', '2026-09-21', true],
            ['Monday & Wednesday', '2026-09-22', false],
            ['Tuesday, Thursday & Friday', '2026-09-24', true],
            ['Anticipated Mass (Saturday)', '2026-09-19', true],
            ['Monday to Saturday', '2026-09-20', false],
            ['Monday to Saturday', '2026-09-21', true],
            ['Every First Tuesday', '2026-09-01', true],
            ['Every First Tuesday', '2026-09-08', false],
            ['Every 12th of the Month', '2026-10-12', true],
            ['Every 12th of the Month', '2026-10-13', false],
            ['Every Last Friday of the Month', '2026-09-25', true],
            ['Every Last Friday of the Month', '2026-09-18', false],
            ['2026-09-20', '2026-09-20', true],
            ['2026-09-20', '2026-09-27', false],
            ['Every day', '2026-09-20', true],
            ['Sunday on request', '2026-09-20', false],
        ];
    }

    public function test_invalid_or_missing_start_times_do_not_activate_the_button(): void
    {
        $this->at('2026-09-20 07:30:00');
        foreach (['25:00', 'after Mass', ''] as $time) {
            $this->schedule(['start_time' => $time]);
        }
        $this->getJson('/api/livestream-status')->assertJsonPath('is_live', false);
    }
}
