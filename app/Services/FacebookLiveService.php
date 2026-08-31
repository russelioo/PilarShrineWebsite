<?php

namespace App\Services;

use App\Models\LivestreamSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class FacebookLiveService
{
    /**
     * Return the currently active Facebook Live broadcast, if one exists.
     *
     * @return array{is_live: bool, title: ?string, url: string}
     */
    public function status(): array
    {
        $pageUrl = config('services.facebook.page_url');

        try {
            $manual = LivestreamSetting::query()->first();

            if ($manual?->is_live) {
                return [
                    'is_live' => true,
                    'title' => $manual->title,
                    'url' => $manual->url ?: $pageUrl,
                ];
            }
        } catch (Throwable) {
            // Allow the public site to load before the settings migration is run.
        }

        $pageId = config('services.facebook.page_id');
        $accessToken = config('services.facebook.page_access_token');

        if (! $pageId || ! $accessToken) {
            return $this->offline($pageUrl);
        }

        return Cache::remember('facebook-live-status', now()->addSeconds(45), function () use ($pageUrl, $pageId, $accessToken) {
            try {
                $response = Http::connectTimeout(3)
                    ->timeout(8)
                    ->retry(2, 200)
                    ->get(sprintf(
                        'https://graph.facebook.com/%s/%s/live_videos',
                        config('services.facebook.graph_version'),
                        $pageId
                    ), [
                        'access_token' => $accessToken,
                        'broadcast_status' => 'LIVE',
                        'fields' => 'id,title,status,permalink_url',
                        'limit' => 1,
                    ]);

                if (! $response->successful()) {
                    return $this->offline($pageUrl);
                }

                $video = $response->json('data.0');

                if (! is_array($video) || ($video['status'] ?? null) !== 'LIVE') {
                    return $this->offline($pageUrl);
                }

                return [
                    'is_live' => true,
                    'title' => $video['title'] ?? 'Pilar Shrine is live',
                    'url' => $this->videoUrl($video, $pageUrl),
                ];
            } catch (Throwable) {
                return $this->offline($pageUrl);
            }
        });
    }

    private function videoUrl(array $video, string $pageUrl): string
    {
        $permalink = $video['permalink_url'] ?? null;

        if (is_string($permalink) && $permalink !== '') {
            return str_starts_with($permalink, 'http')
                ? $permalink
                : 'https://www.facebook.com'.$permalink;
        }

        return isset($video['id'])
            ? 'https://www.facebook.com/watch/?v='.$video['id']
            : $pageUrl;
    }

    /** @return array{is_live: false, title: null, url: string} */
    private function offline(string $pageUrl): array
    {
        return ['is_live' => false, 'title' => null, 'url' => $pageUrl];
    }
}
