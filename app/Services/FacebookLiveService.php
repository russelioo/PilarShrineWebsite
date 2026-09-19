<?php

namespace App\Services;

use App\Models\LivestreamSetting;
use App\Models\SiteSetting;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Schema;

class FacebookLiveService
{
    public function __construct(private MassLivestreamSchedule $schedule) {}

    /**
     * Show the Facebook broadcast link only during a scheduled livestream window.
     * No administrator action or Facebook API credentials are needed for each Mass.
     *
     * @return array{is_live: bool, title: ?string, url: string, opens_at: ?string, closes_at: ?string, server_time: string}
     */
    public function status(): array
    {
        $now = CarbonImmutable::now(MassLivestreamSchedule::TIMEZONE);
        $pageUrl = SiteSetting::publicValues()['facebook_url'];
        $settings = Schema::hasTable('livestream_settings') ? LivestreamSetting::query()->orderBy('id')->first() : null;
        $window = Schema::hasTable('mass_schedules') ? $this->schedule->currentWindow($now) : null;

        return [
            'is_live' => $window !== null,
            'title' => $window ? ($settings?->title ?: $window['schedule']->title) : null,
            'url' => $settings?->url ?: $pageUrl,
            'opens_at' => $window ? $window['opens_at']->toIso8601String() : null,
            'closes_at' => $window ? $window['closes_at']->toIso8601String() : null,
            'server_time' => $now->toIso8601String(),
        ];
    }
}
