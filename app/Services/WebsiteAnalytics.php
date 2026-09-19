<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class WebsiteAnalytics
{
    public const PAGES = [
        'home' => 'Home', 'about' => 'History & Heritage', 'schedule' => 'Mass & Confession Schedule',
        'sacraments' => 'Sacraments', 'news' => 'News & Announcements', 'novenas' => 'Novenas',
        'novena-details' => 'Novena Prayers', 'rosary' => 'Holy Rosary', 'ministries' => 'Ministries',
        'store' => 'Shrine Store', 'contact' => 'Contact', 'forms' => 'Mass Intention Form',
        'donations' => 'Donations', 'login' => 'Sign In', 'register' => 'Registration',
        'complete-profile' => 'Complete Profile',
    ];

    public const CLICKS = [
        'livestream_click' => 'Opened livestream', 'announcement_open' => 'Opened announcement',
        'decree_open' => 'Viewed coronation decree', 'gallery_open' => 'Viewed heritage photo',
    ];

    public const ACTIONS = [
        ...self::CLICKS,
        'inquiries.store' => 'Submitted inquiry', 'api.messages.send' => 'Sent message',
        'parishioner.mass-intentions.store' => 'Requested Mass intention',
        'parishioner.sacrament-requests.store' => 'Requested sacrament',
        'parishioner.donations.store' => 'Submitted donation request',
        'parishioner.ministries.join' => 'Applied to ministry',
        'parishioner.profile-settings.update' => 'Updated profile',
        'parishioner.password.update' => 'Updated website password',
        'parishioner.complete-profile' => 'Completed profile',
    ];

    public const TYPES = [
        'page_view' => 'Page view', 'action' => 'Activity', 'login' => 'Signed in',
        'logout' => 'Signed out', 'failed_login' => 'Failed sign-in', 'registration' => 'Account registered',
    ];

    public function prepare(Request $request): ?array
    {
        if (! $request->hasSession() || $request->isMethod('HEAD')
            || preg_match('/bot|crawler|spider|slurp|facebookexternalhit/i', $request->userAgent() ?? '')
            || str_contains(strtolower($request->header('Sec-Purpose', $request->header('Purpose', ''))), 'prefetch')) {
            return null;
        }

        if ($request->attributes->has('analytics_context')) {
            return $request->attributes->get('analytics_context');
        }

        $visitor = $request->cookie('parish_visitor');
        if (! is_string($visitor) || ! Str::isUuid($visitor)) {
            $visitor = $request->session()->get('analytics_visitor', (string) Str::uuid());
            Cookie::queue(cookie('parish_visitor', $visitor, 525600, '/', null, $request->isSecure(), true, false, 'lax'));
        }
        $request->session()->put('analytics_visitor', $visitor);
        $visit = $request->session()->get('analytics_visit');
        if (! $visit || now()->timestamp - (int) $request->session()->get('analytics_last_seen', 0) >= 1800) {
            $visit = (string) Str::uuid();
        }
        $request->session()->put('analytics_visit', $visit);
        $context = ['visitor_id' => hash_hmac('sha256', $visitor, config('app.key')), 'visit_id' => $visit];
        $request->attributes->set('analytics_context', $context);

        return $context;
    }

    public function record(Request $request, string $type, string $page, ?string $action = null,
        ?User $user = null, ?string $eventId = null, ?string $area = null): void
    {
        try {
            $context = $this->prepare($request);
            if (! $context || ! Schema::hasTable('analytics_events')) {
                return;
            }
            $user ??= $request->user();
            // Failed credentials never identify a visitor as the account they tried to use.
            if ($type === 'failed_login') {
                $user = null;
            }
            $request->session()->put('analytics_last_seen', now()->timestamp);
            $attributes = [
                ...$context, 'occurred_at' => now(), 'user_id' => $user?->id,
                'audience' => $user ? 'member' : 'guest',
                'area' => $area ?? ($request->is('admin/*', 'staff/*', 'commission/*') ? 'admin' : ($request->is('parishioner/*', 'inquiries', 'api/messages/*') ? 'portal' : 'website')),
                'event_type' => $type, 'page' => $page, 'action' => $action,
                'deduplication_key' => $eventId ? hash('sha256', $context['visitor_id'].$eventId) : null,
            ];
            if ($eventId) {
                AnalyticsEvent::firstOrCreate(['deduplication_key' => $attributes['deduplication_key']], $attributes);
            } else {
                AnalyticsEvent::create($attributes);
            }
        } catch (\Throwable $exception) {
            // Recording must never interrupt signing in, requests, or navigation. No payloads in logs.
            Log::warning('Website analytics could not record an event.', ['exception' => $exception::class]);
        }
    }

    public static function pageLabel(string $page): string
    {
        if (isset(self::PAGES[$page])) {
            return self::PAGES[$page];
        }
        if (str_starts_with($page, 'api.messages.')) {
            return 'Messages & Inquiries';
        }
        if ($page === 'parishioner.password.update') {
            return 'Account Settings';
        }
        $page = preg_replace('/^(admin|parishioner|staff|commission)\./', '', $page);
        $page = preg_replace('/\.(store|update|join|index)$/', '', $page);

        return Str::headline(str_replace('.', ' ', $page));
    }
}
