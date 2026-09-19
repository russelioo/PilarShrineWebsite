<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\User;
use App\Services\WebsiteAnalytics;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnalyticsController extends Controller
{
    private function filters(Request $request): array
    {
        abort_unless($request->user()->hasParishWideAccess() && $request->user()->hasPermission('view_analytics'), 403);
        $data = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:2000-01-01'],
            'group' => ['nullable', Rule::in(['day', 'month', 'year'])],
            'area' => ['nullable', Rule::in(['all', 'website', 'portal', 'admin'])],
            'audience' => ['nullable', Rule::in(['all', 'guest', 'member'])],
            'event' => ['nullable', Rule::in(array_keys(WebsiteAnalytics::TYPES))],
            'user' => ['nullable', 'integer', 'min:1'],
        ]);
        $group = $data['group'] ?? 'day';
        $today = CarbonImmutable::today();
        $from = isset($data['from']) ? CarbonImmutable::parse($data['from']) : match ($group) {
            'month' => $today->startOfYear(), 'year' => $today->subYears(4)->startOfYear(), default => $today->startOfMonth(),
        };
        $to = isset($data['to']) ? CarbonImmutable::parse($data['to']) : $today;
        if ($from->gt($to) || $from->diffInDays($to) > ($group === 'day' ? 365 : 3652)) {
            throw ValidationException::withMessages(['from' => 'Choose up to 366 days for daily reports, or 10 years for monthly and yearly reports. The end date must follow the start date.']);
        }

        return ['from' => $from->toDateString(), 'to' => $to->toDateString(), 'group' => $group,
            'area' => $data['area'] ?? 'all', 'audience' => $data['audience'] ?? 'all',
            'event' => $data['event'] ?? '', 'user' => $data['user'] ?? null];
    }

    private function query(array $filters)
    {
        return AnalyticsEvent::query()
            ->where('occurred_at', '>=', $filters['from'].' 00:00:00')
            ->where('occurred_at', '<', CarbonImmutable::parse($filters['to'])->addDay()->toDateTimeString())
            ->when($filters['area'] !== 'all', fn ($q) => $q->where('area', $filters['area']))
            ->when($filters['audience'] !== 'all', fn ($q) => $q->where('audience', $filters['audience']))
            ->when($filters['user'], fn ($q) => $q->where('user_id', $filters['user']));
    }

    private function summarySql(): string
    {
        return "COUNT(DISTINCT visitor_id) AS visitors, COUNT(DISTINCT visit_id) AS visits,
            COUNT(DISTINCT user_id) AS accounts,
            SUM(CASE WHEN event_type = 'page_view' THEN 1 ELSE 0 END) AS page_views,
            SUM(CASE WHEN event_type = 'login' THEN 1 ELSE 0 END) AS logins,
            SUM(CASE WHEN event_type = 'registration' THEN 1 ELSE 0 END) AS registrations,
            SUM(CASE WHEN event_type = 'action' THEN 1 ELSE 0 END) AS actions,
            SUM(CASE WHEN event_type = 'logout' THEN 1 ELSE 0 END) AS logouts,
            SUM(CASE WHEN event_type = 'failed_login' THEN 1 ELSE 0 END) AS failed_logins";
    }

    private function series(array $filters): array
    {
        $length = ['day' => 10, 'month' => 7, 'year' => 4][$filters['group']];
        $rows = $this->query($filters)->selectRaw("SUBSTR(occurred_at, 1, {$length}) AS period, ".$this->summarySql())
            ->groupBy('period')->orderBy('period')->get()->keyBy('period');
        $date = CarbonImmutable::parse($filters['from']);
        $date = match ($filters['group']) {
            'month' => $date->startOfMonth(), 'year' => $date->startOfYear(), default => $date
        };
        $end = CarbonImmutable::parse($filters['to']);
        $series = [];
        while ($date->lte($end)) {
            $period = substr($date->toDateString(), 0, $length);
            $row = $rows->get($period);
            $values = [];
            foreach (['visitors', 'visits', 'accounts', 'page_views', 'logins', 'registrations', 'actions', 'logouts', 'failed_logins'] as $metric) {
                $values[$metric] = (int) ($row?->$metric ?? 0);
            }
            $series[] = ['period' => $period, 'label' => $date->format(['day' => 'M j', 'month' => 'M Y', 'year' => 'Y'][$filters['group']]), ...$values];
            $date = match ($filters['group']) {
                'month' => $date->addMonth(), 'year' => $date->addYear(), default => $date->addDay()
            };
        }

        return $series;
    }

    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->query($filters);
        $totals = (clone $query)->selectRaw($this->summarySql())->first();
        $series = $this->series($filters);
        $pages = (clone $query)->where('event_type', 'page_view')->select('page')
            ->selectRaw('COUNT(*) AS views, COUNT(DISTINCT visitor_id) AS visitors')->groupBy('page')->orderByDesc('views')->limit(10)->get();
        $people = (clone $query)->whereNotNull('user_id')->select('user_id')
            ->selectRaw('COUNT(*) AS events, MAX(occurred_at) AS last_seen')->groupBy('user_id')
            ->orderByDesc('last_seen')->with('user:id,name,role')->limit(15)->get();
        $actions = (clone $query)->where('event_type', 'action')->select('action')
            ->selectRaw('COUNT(*) AS total')->groupBy('action')->orderByDesc('total')->get();
        $activities = (clone $query)->when($filters['event'], fn ($q) => $q->where('event_type', $filters['event']))
            ->with('user:id,name,role')->orderByDesc('occurred_at')->orderByDesc('id')->paginate(20)->withQueryString();
        $firstEvent = AnalyticsEvent::min('occurred_at');
        $selectedUser = $filters['user'] ? User::find($filters['user']) : null;
        $guestVisitors = (clone $query)->where('audience', 'guest')->distinct()->count('visitor_id');

        return response()->view('admin.analytics', compact('filters', 'totals', 'series', 'pages', 'people', 'actions',
            'activities', 'firstEvent', 'selectedUser', 'guestVisitors'))->header('Cache-Control', 'private, no-store');
    }

    public function export(Request $request)
    {
        $filters = $this->filters($request);
        $series = $this->series($filters);

        return response()->streamDownload(function () use ($series, $filters) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Timezone', config('app.timezone'), 'From', $filters['from'], 'To', $filters['to'], 'Area', $filters['area'], 'Audience', $filters['audience'], 'Account ID', $filters['user'] ?? 'All'], escape: '');
            fputcsv($file, ['Period', 'Visitors', 'Visits', 'Active accounts', 'Page views', 'Sign-ins', 'Registrations', 'Actions', 'Sign-outs', 'Failed sign-ins'], escape: '');
            foreach ($series as $row) {
                unset($row['label']);
                fputcsv($file, array_values($row), escape: '');
            }
            fclose($file);
        }, 'parish-analytics-'.$filters['from'].'-'.$filters['to'].'.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }
}
