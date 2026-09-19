@extends('layouts.admin')
@section('title', 'Website Analytics')

@section('content')
@php
    use App\Services\WebsiteAnalytics;
    $baseFilters = array_filter($filters, fn ($value) => $value !== null && $value !== '');
    $metricLabels = ['visitors' => 'Visitors', 'visits' => 'Visits', 'page_views' => 'Page views', 'logins' => 'Sign-ins', 'registrations' => 'New registrations', 'actions' => 'Actions'];
@endphp
<div class="analytics-page">
    <header class="analytics-heading">
        <div><span class="analytics-eyebrow">PARISH WEBSITE INSIGHTS</span><h1>Website Analytics</h1><p>See who visits, the pages they explore, and how they connect with the parish.</p></div>
        <a class="analytics-button secondary" href="{{ route('admin.analytics.export', $baseFilters) }}">↓ Export CSV</a>
    </header>

    <form class="analytics-panel analytics-filters" action="{{ route('admin.analytics') }}" method="get">
        <div class="analytics-presets">
            <span>Quick view</span>
            <a href="{{ route('admin.analytics', ['from' => today()->toDateString(), 'to' => today()->toDateString(), 'group' => 'day']) }}">Today</a>
            <a href="{{ route('admin.analytics', ['group' => 'day']) }}">This month</a>
            <a href="{{ route('admin.analytics', ['group' => 'month']) }}">This year</a>
            <a href="{{ route('admin.analytics', ['group' => 'year']) }}">Last 5 years</a>
        </div>
        @if($errors->any())<div class="analytics-error" role="alert">{{ $errors->first() }}</div>@endif
        @if($filters['user'])
            <div class="analytics-selection">Showing activity for {{ $selectedUser?->name ?? 'account #'.$filters['user'] }} <a href="{{ route('admin.analytics', array_diff_key($baseFilters, ['user' => true])) }}">View everyone ×</a></div>
            <input type="hidden" name="user" value="{{ $filters['user'] }}">
        @endif
        <div class="analytics-filter-grid">
            <label>From<input type="date" name="from" value="{{ $filters['from'] }}" required min="2000-01-01"></label>
            <label>To<input type="date" name="to" value="{{ $filters['to'] }}" required min="2000-01-01"></label>
            <label>Group by<select name="group"><option value="day" @selected($filters['group'] === 'day')>Day</option><option value="month" @selected($filters['group'] === 'month')>Month</option><option value="year" @selected($filters['group'] === 'year')>Year</option></select></label>
            <label>Area<select name="area">@foreach(['all' => 'All areas', 'website' => 'Public website', 'portal' => 'Parishioner portal', 'admin' => 'Admin portal'] as $key => $label)<option value="{{ $key }}" @selected($filters['area'] === $key)>{{ $label }}</option>@endforeach</select></label>
            <label>Visitors<select name="audience"><option value="all" @selected($filters['audience'] === 'all')>Everyone</option><option value="guest" @selected($filters['audience'] === 'guest')>Anonymous guests</option><option value="member" @selected($filters['audience'] === 'member')>Known accounts</option></select></label>
            <button class="analytics-button" type="submit">Update report</button>
        </div>
        <p class="analytics-note">All dates and times use Philippine time (Asia/Manila). Updated {{ now()->format('M j, Y · g:i A') }}.</p>
    </form>

    <div class="analytics-metrics">
        @foreach($metricLabels as $key => $label)
            <div class="analytics-metric {{ $loop->first ? 'featured' : '' }}"><span>{{ $label }}</span><strong>{{ number_format($totals->$key ?? 0) }}</strong><small>{{ ['visitors' => 'Distinct browsers', 'visits' => '30-minute activity sessions', 'page_views' => 'Public and portal pages', 'logins' => 'Successful account sign-ins', 'registrations' => 'Accounts registered', 'actions' => 'Clicks and submissions'][$key] }}</small></div>
        @endforeach
    </div>

    <section class="analytics-panel" aria-labelledby="analytics-trend-title">
        <div class="analytics-panel-heading"><div><h2 id="analytics-trend-title">Activity over time</h2><p>{{ \Carbon\Carbon::parse($filters['from'])->format('M j, Y') }} – {{ \Carbon\Carbon::parse($filters['to'])->format('M j, Y') }}</p></div>
            <div class="analytics-chart-controls">
                <div class="analytics-chart-types" role="group" aria-label="Graph style">
                    <button type="button" data-chart-type="line" aria-pressed="true"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M3 3v14h14M5 12l4-5 4 3 4-6"/></svg>Line</button>
                    <button type="button" data-chart-type="bar" aria-pressed="false"><svg viewBox="0 0 20 20" aria-hidden="true"><path d="M3 3v14h14M7 13V9m4 4V5m4 8V7"/></svg>Bars</button>
                </div>
                <label class="analytics-chart-selector">Graph metric<select id="analytics-metric"><option value="all" selected>All metrics</option>@foreach($metricLabels as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach</select></label>
            </div>
        </div>
        @if(!($totals->visitors ?? 0))<div class="analytics-empty">No recorded activity for this period. New visits and sign-ins will appear here as people use the website.</div>@endif
        <div class="analytics-graph-legend"><div id="analytics-legend-metrics" class="analytics-legend-metrics" aria-label="Graph legend"></div><span id="analytics-graph-peak"></span></div>
        <div class="analytics-chart" id="analytics-chart" tabindex="0" role="group" aria-label="All metrics graph" aria-describedby="analytics-chart-help">
            <svg id="analytics-chart-svg" role="img" aria-label="All metrics over time"></svg>
            <div class="analytics-chart-tooltip" id="analytics-chart-tooltip" aria-hidden="true" hidden><span class="analytics-tooltip-date"></span><dl></dl></div>
        </div>
        <div class="analytics-chart-footer"><p id="analytics-chart-detail" aria-live="polite"></p><p id="analytics-chart-help">Hover or tap to explore. Use arrow keys when the graph is focused.</p></div>
        <noscript><p class="analytics-note">Enable JavaScript to use the graph, or open the totals table below.</p></noscript>
        <details class="analytics-periods"><summary>View {{ $filters['group'] === 'day' ? 'daily' : ($filters['group'] === 'month' ? 'monthly' : 'yearly') }} totals</summary>
            <div class="analytics-table-scroll"><table><caption class="analytics-sr-only">Activity totals by period, latest first</caption><thead><tr><th aria-sort="descending">Period</th>@foreach($metricLabels as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@foreach(array_reverse($series) as $row)<tr><th scope="row">{{ $row['period'] }}</th>@foreach($metricLabels as $key => $label)<td>{{ number_format($row[$key]) }}</td>@endforeach</tr>@endforeach</tbody></table></div>
        </details>
    </section>

    <div class="analytics-columns">
        <section class="analytics-panel"><div class="analytics-panel-heading"><div><h2>Most visited pages</h2><p>The top 10 pages in this period.</p></div><span class="analytics-pill">Page views</span></div>
            @forelse($pages as $page)
                <div class="analytics-ranking"><div><span>{{ WebsiteAnalytics::pageLabel($page->page) }}</span><small>{{ number_format($page->visitors) }} {{ $page->visitors == 1 ? 'visitor' : 'visitors' }}</small></div><strong>{{ number_format($page->views) }}</strong></div>
            @empty<p class="analytics-empty">Page visits will appear here.</p>@endforelse
        </section>
        <section class="analytics-panel"><div class="analytics-panel-heading"><div><h2>Who visited</h2><p>{{ number_format($totals->accounts ?? 0) }} known accounts · {{ number_format($guestVisitors) }} guest browsers</p></div></div>
            @forelse($people as $person)
                <a class="analytics-ranking analytics-person" href="{{ route('admin.analytics', [...$baseFilters, 'user' => $person->user_id, 'audience' => 'all']) }}"><span class="analytics-avatar" aria-hidden="true">{{ mb_substr($person->user?->name ?? '?', 0, 1) }}</span><div><span>{{ $person->user?->name ?? 'Unavailable account' }}</span><small>{{ \Illuminate\Support\Str::headline($person->user?->role ?? 'user') }} · {{ \Carbon\Carbon::parse($person->last_seen)->format('M j, g:i A') }}</small></div><strong>{{ number_format($person->events) }}<small>events →</small></strong></a>
            @empty<p class="analytics-empty">Signed-in visitors will appear by name. Guests remain anonymous.</p>@endforelse
            @if($people->isNotEmpty())<p class="analytics-note">Up to 15 most recently active accounts. Select a name to view their activity.</p>@endif
        </section>
    </div>

    <div class="analytics-columns">
        <section class="analytics-panel"><div class="analytics-panel-heading"><div><h2>What visitors do</h2><p>Recorded clicks and completed submissions.</p></div></div>
            @forelse($actions as $action)<div class="analytics-ranking"><span>{{ WebsiteAnalytics::ACTIONS[$action->action] ?? 'Website activity' }}</span><strong>{{ number_format($action->total) }}</strong></div>@empty<p class="analytics-empty">Activities will appear when visitors open announcements, view photos, or submit requests.</p>@endforelse
        </section>
        <section class="analytics-panel"><div class="analytics-panel-heading"><div><h2>Account activity</h2><p>Website passwords and Google sign-ins.</p></div></div>
            @foreach(['logins' => 'Successful sign-ins', 'failed_logins' => 'Failed password sign-ins', 'logouts' => 'Sign-outs', 'registrations' => 'Registrations'] as $key => $label)<div class="analytics-ranking"><span>{{ $label }}</span><strong>{{ number_format($totals->$key ?? 0) }}</strong></div>@endforeach
        </section>
    </div>

    <section class="analytics-panel analytics-history">
        <div class="analytics-panel-heading"><div><h2>Visitor activity</h2><p>Recent activity within the selected dates and filters.</p></div>
            <form action="{{ route('admin.analytics') }}" method="get" class="analytics-history-filter">
                @foreach($baseFilters as $key => $value)@if($key !== 'event')<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach
                <label class="analytics-sr-only" for="activity-type">Activity type</label><select name="event" id="activity-type"><option value="">All activity</option>@foreach(WebsiteAnalytics::TYPES as $key => $label)<option value="{{ $key }}" @selected($filters['event'] === $key)>{{ $label }}</option>@endforeach</select><button type="submit" class="analytics-button secondary">Filter</button>
            </form>
        </div>
        <div class="analytics-table-scroll"><table><caption class="analytics-sr-only">Visitor activity in Philippine time</caption><thead><tr><th>Visitor</th><th>Activity</th><th>Page / area</th><th>Date & time</th></tr></thead><tbody>
            @forelse($activities as $activity)
                <tr><td><strong>{{ $activity->user?->name ?? ($activity->audience === 'member' ? 'Unavailable account' : 'Guest · '.substr($activity->visitor_id, 0, 6)) }}</strong><small>{{ $activity->audience === 'guest' ? 'Anonymous visitor' : \Illuminate\Support\Str::headline($activity->user?->role ?? 'account') }}</small></td>
                    <td><span class="analytics-pill {{ $activity->event_type === 'login' ? 'success' : '' }}">{{ WebsiteAnalytics::TYPES[$activity->event_type] ?? 'Activity' }}</span>@if($activity->action)<small>{{ WebsiteAnalytics::ACTIONS[$activity->action] ?? 'Website activity' }}</small>@endif</td>
                    <td>{{ WebsiteAnalytics::pageLabel($activity->page) }}<small>{{ ['website' => 'Public website', 'portal' => 'Parishioner portal', 'admin' => 'Admin portal'][$activity->area] }}</small></td>
                    <td class="analytics-time">{{ $activity->occurred_at->format('M j, Y') }}<small>{{ $activity->occurred_at->format('g:i:s A') }}</small></td></tr>
            @empty<tr><td colspan="4" class="analytics-empty">No matching activity yet.</td></tr>@endforelse
        </tbody></table></div>
        @if($activities->hasPages())<div class="analytics-pagination"><span>{{ $activities->firstItem() }}–{{ $activities->lastItem() }} of {{ number_format($activities->total()) }} events</span><div>@if($activities->previousPageUrl())<a class="analytics-button secondary" href="{{ $activities->previousPageUrl() }}">← Previous</a>@endif @if($activities->nextPageUrl())<a class="analytics-button secondary" href="{{ $activities->nextPageUrl() }}">Next →</a>@endif</div></div>@endif
    </section>
    <p class="analytics-footnote">{{ $firstEvent ? 'Recording since '.\Carbon\Carbon::parse($firstEvent)->format('M j, Y').'.' : 'Recording starts with the next website activity.' }} Earlier traffic cannot be recovered. Visitors count browsers, so one person using multiple devices may count more than once. A guest who signs in may appear in both audience groups. Passwords, private message contents, and form answers are not recorded here.</p>
</div>
@endsection

@push('styles')
<style>
.analytics-page{max-width:1500px;margin:auto;display:grid;gap:24px;color:#173152}.analytics-heading{display:flex;justify-content:space-between;align-items:center;gap:20px}.analytics-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.6px;color:#a27a22}.analytics-heading h1{font-size:30px;letter-spacing:-.8px;color:#073478;margin:5px 0 8px}.analytics-heading p,.analytics-panel-heading p{color:#718198;margin:0;line-height:1.7}.analytics-panel{background:#fff;border:1px solid #e0e8f1;border-radius:18px;padding:26px;min-width:0;box-shadow:0 5px 22px #0a347803}.analytics-button{display:inline-flex;justify-content:center;align-items:center;gap:8px;border:1px solid #0b377b;border-radius:9px;background:#0b377b;color:#fff;padding:11px 17px;font:inherit;font-weight:700;text-decoration:none;cursor:pointer;white-space:nowrap;min-height:43px}.analytics-button.secondary{background:#fff;color:#0b377b;border-color:#d9e3ef}.analytics-button:hover{filter:brightness(.96)}.analytics-page a:focus-visible,.analytics-page button:focus-visible,.analytics-page select:focus-visible,.analytics-page input:focus-visible{outline:3px solid #d8aa3c;outline-offset:3px}.analytics-presets{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-bottom:20px}.analytics-presets span{color:#738299;margin-right:7px;font-size:12px}.analytics-presets a{background:#f3f6fb;color:#16477f;text-decoration:none;border:1px solid #e4ebf4;padding:6px 13px;border-radius:7px;font-size:12px;font-weight:700}.analytics-filter-grid{display:grid;grid-template-columns:1fr 1fr .8fr 1.15fr 1.15fr auto;gap:14px;align-items:end}.analytics-page label{display:flex;flex-direction:column;gap:7px;font-size:12px;font-weight:700;color:#354d6a}.analytics-page input,.analytics-page select{font:inherit;font-size:13px;background:#fff;color:#203e60;border:1px solid #d7e1ed;border-radius:8px;padding:10px 12px;min-height:43px;min-width:0;width:100%}.analytics-note{font-size:11px;color:#7b8aa0;margin:15px 0 0;line-height:1.7}.analytics-error{color:#a12c2c;background:#fff1f1;padding:12px;border-radius:8px;margin-bottom:14px}.analytics-selection{background:#edf4ff;border-radius:8px;padding:12px;margin-bottom:16px}.analytics-selection a{margin-left:16px;color:#0b377b;font-weight:700}.analytics-metrics{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px}.analytics-metric{display:flex;flex-direction:column;gap:8px;border:1px solid #e0e8f1;border-radius:15px;padding:21px 18px;background:#fff}.analytics-metric span{font-weight:700;color:#61748d;font-size:12px}.analytics-metric strong{font-size:32px;color:#083479;line-height:1.2}.analytics-metric small{color:#8190a2;font-size:10px;line-height:1.5}.analytics-metric.featured{background:#0b3474;border-color:#0b3474}.analytics-metric.featured strong{color:#fff}.analytics-metric.featured span,.analytics-metric.featured small{color:#c7d7ed}.analytics-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:18px}.analytics-panel-heading h2{font-size:17px;color:#0b3474;margin:0 0 5px}.analytics-panel-heading p{font-size:12px}.analytics-chart-controls{display:flex;align-items:flex-end;gap:16px;flex-wrap:wrap}.analytics-chart-selector{min-width:155px}.analytics-chart-types{display:flex;gap:3px;padding:3px;background:#f2f6fb;border:1px solid #e0e8f1;border-radius:9px}.analytics-chart-types button{display:flex;align-items:center;justify-content:center;gap:6px;min-height:35px;padding:7px 11px;background:transparent;color:#637894;font:inherit;font-size:12px;font-weight:700;border:0;border-radius:6px;cursor:pointer}.analytics-chart-types button[aria-pressed=true]{background:#fff;color:#0b377b;box-shadow:0 1px 4px #0b377b18}.analytics-chart-types button:hover{color:#0b377b}.analytics-chart-types svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.7;stroke-linecap:round;stroke-linejoin:round}.analytics-graph-legend{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:24px 0 10px;font-size:12px;color:#718198}.analytics-legend-metrics{display:flex;align-items:center;gap:10px 18px;flex-wrap:wrap;flex:1}.analytics-legend-metrics>span{display:inline-flex;align-items:center;gap:7px;color:#354d6a;font-size:11px;font-weight:600}.analytics-legend-metrics svg{width:28px;height:12px;flex-shrink:0}#analytics-graph-peak{white-space:nowrap;font-size:11px}.analytics-chart{position:relative;height:320px;min-width:0;display:block;cursor:crosshair}.analytics-chart:focus-visible{outline:3px solid #d8aa3c;outline-offset:4px;border-radius:8px}#analytics-chart-svg{display:block;width:100%;height:100%;overflow:visible}.analytics-axis-label{fill:#73839a;font-family:inherit;font-size:11px}.analytics-axis-title{fill:#8795a7;font-family:inherit;font-size:10px}.analytics-chart-tooltip{position:absolute;z-index:2;width:max-content;display:flex;flex-direction:column;gap:5px;max-width:calc(100% - 16px);padding:11px 14px;border:1px solid #244b80;border-radius:9px;background:#0b3474;color:#fff;box-shadow:0 5px 18px #0b347426;pointer-events:none;font-size:12px}.analytics-chart-tooltip[hidden]{display:none}.analytics-tooltip-date{font-size:11px;color:#c8d9f0}.analytics-chart-tooltip dl{display:grid;gap:8px;margin:5px 0 0}.analytics-chart-tooltip dl>div{display:flex;align-items:center;justify-content:space-between;gap:22px}.analytics-chart-tooltip dt{display:flex;align-items:center;gap:7px;font-weight:400;font-size:11px}.analytics-chart-tooltip dt i{width:8px;height:8px;border-radius:50%;box-shadow:0 0 0 1px #ffffff70;flex-shrink:0}.analytics-chart-tooltip dd{margin:0;font-weight:700;font-variant-numeric:tabular-nums}.analytics-chart-footer{display:flex;justify-content:space-between;align-items:center;gap:8px 20px;flex-wrap:wrap;margin:17px 0 18px;padding-top:14px;border-top:1px solid #edf1f6}.analytics-chart-footer p{margin:0;font-size:11px;line-height:1.7;color:#7b8aa0}#analytics-chart-detail{color:#355778;font-size:12px;font-weight:600;min-height:21px}.analytics-periods summary{font-size:12px;color:#15487f;cursor:pointer;font-weight:700;margin:10px 0}.analytics-columns{display:grid;grid-template-columns:1fr 1fr;gap:24px}.analytics-ranking{display:flex;align-items:center;gap:12px;padding:13px 0;border-bottom:1px solid #eef2f7;font-size:13px}.analytics-ranking:last-child{border-bottom:0}.analytics-ranking>div,.analytics-ranking>span:first-child:not(.analytics-avatar){flex:1;min-width:0}.analytics-ranking strong{color:#0b3474;text-align:right;margin-left:auto}.analytics-ranking small,.analytics-history td small{display:block;font-size:11px;color:#8190a2;margin-top:4px}.analytics-person{color:#173152;text-decoration:none}.analytics-person:hover{background:#f8faff}.analytics-avatar{width:34px;height:34px;flex-shrink:0;background:#edf3fb;color:#1b4a8b;display:grid;place-items:center;border-radius:50%;font-weight:800}.analytics-pill{display:inline-block;background:#eef4fd;color:#36649c;border-radius:6px;padding:5px 9px;font-size:10px;font-weight:700;white-space:nowrap}.analytics-pill.success{background:#eaf8f1;color:#287754}.analytics-table-scroll{overflow-x:auto}.analytics-page table{border-collapse:collapse;width:100%;font-size:12px;text-align:left}.analytics-page th{font-weight:700;white-space:nowrap}.analytics-page thead th{background:#f5f8fc;color:#77889f;font-size:10px;text-transform:uppercase;letter-spacing:.5px}.analytics-page td,.analytics-page th{padding:14px 12px;border-bottom:1px solid #edf1f6}.analytics-history td{vertical-align:top;min-width:150px}.analytics-history td strong{font-weight:600}.analytics-time{white-space:nowrap}.analytics-history-filter{display:flex;gap:8px}.analytics-history-filter select{min-width:150px}.analytics-empty{color:#8996a6;padding:22px 0;font-size:12px;line-height:1.8}.analytics-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;color:#7b8aa0;font-size:11px}.analytics-pagination>div{display:flex;gap:8px}.analytics-footnote{font-size:11px;color:#7b8aa0;line-height:1.9;margin:0 0 20px}.analytics-sr-only{position:absolute!important;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:1250px){.analytics-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}.analytics-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:760px){.analytics-page{gap:17px}.analytics-heading{align-items:flex-start;flex-direction:column}.analytics-heading h1{font-size:26px}.analytics-panel{padding:19px 16px}.analytics-columns{grid-template-columns:1fr;gap:17px}.analytics-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.analytics-metric{padding:17px}.analytics-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.analytics-panel-heading{flex-wrap:wrap}.analytics-chart-controls{width:100%;gap:12px}.analytics-chart-selector{flex:1;min-width:120px}.analytics-chart-types button{padding:7px 9px}.analytics-history-filter{width:100%}.analytics-history-filter select{flex:1}.analytics-pagination{align-items:flex-start;flex-direction:column}}

</style>
@endpush

@push('scripts')
<script>
(() => {
    const series = {{ Illuminate\Support\Js::from($series) }};
    const labels = {{ Illuminate\Support\Js::from($metricLabels) }};
    const select = document.getElementById('analytics-metric');
    const chart = document.getElementById('analytics-chart');
    const svg = document.getElementById('analytics-chart-svg');
    const detail = document.getElementById('analytics-chart-detail');
    const tooltip = document.getElementById('analytics-chart-tooltip');
    const legend = document.getElementById('analytics-legend-metrics');
    const metricStyles = {
        visitors: { color: '#0f766e', dash: '10 4' },
        visits: { color: '#7c3aed', dash: '7 3' },
        page_views: { color: '#2864ac', dash: '' },
        logins: { color: '#b36b08', dash: '3 3' },
        registrations: { color: '#be185d', dash: '10 3 2 3' },
        actions: { color: '#64748b', dash: '1 4' },
    };
    const styleButtons = [...document.querySelectorAll('[data-chart-type]')];
    const namespace = 'http://www.w3.org/2000/svg';
    const number = new Intl.NumberFormat('en-PH');
    const compactNumber = new Intl.NumberFormat('en-PH', { notation: 'compact', maximumFractionDigits: 1 });
    const periodName = {{ Illuminate\Support\Js::from(['day' => 'Date', 'month' => 'Month', 'year' => 'Year'][$filters['group']]) }};
    let style = 'line';
    let selected = Math.max(0, series.length - 1);
    let geometry;
    let guide;
    let metrics = [];
    let markers = [];
    let bars = [];
    let previousBars = [];
    let tooltipValues = {};

    function element(tag, attributes = {}, text = null) {
        const node = document.createElementNS(namespace, tag);
        Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value));
        if (text !== null) node.textContent = text;
        return node;
    }

    function inspect(index, showTooltip = true) {
        if (!geometry || !series.length) return;
        selected = Math.max(0, Math.min(index, series.length - 1));
        const row = series[selected];
        const x = geometry.x(selected);
        const y = geometry.y(Math.max(...metrics.map(metric => row[metric])));
        guide.setAttribute('x1', x);
        guide.setAttribute('x2', x);
        markers.forEach(({ metric, node }) => {
            node.setAttribute('cx', x);
            node.setAttribute('cy', geometry.y(row[metric]));
        });
        previousBars.forEach(bar => bar.setAttribute('fill-opacity', '.8'));
        previousBars = bars[selected] ?? [];
        previousBars.forEach(bar => bar.setAttribute('fill-opacity', '1'));
        const description = `${row.period} · ${metrics.map(metric => `${number.format(row[metric])} ${labels[metric].toLowerCase()}`).join(' · ')}`;
        if (detail.textContent !== description) detail.textContent = description;
        tooltip.hidden = !showTooltip;
        if (showTooltip) {
            tooltip.querySelector('.analytics-tooltip-date').textContent = row.period;
            metrics.forEach(metric => { tooltipValues[metric].textContent = number.format(row[metric]); });
            const width = tooltip.offsetWidth;
            const height = tooltip.offsetHeight;
            tooltip.style.left = `${Math.max(8, Math.min(x - width / 2, geometry.width - width - 8))}px`;
            tooltip.style.top = `${Math.max(8, Math.min(y - height - 16 < 8 ? y + 16 : y - height - 16, geometry.height - height - 8))}px`;
        }
    }

    function render() {
        metrics = select.value === 'all' ? Object.keys(labels) : [select.value];
        const title = select.value === 'all' ? 'All metrics' : labels[select.value];
        const width = Math.max(260, chart.clientWidth);
        const height = width < 500 ? 260 : 320;
        const left = width < 500 ? 44 : 56;
        const right = width - 20;
        const top = 18;
        const bottom = height - 50;
        const max = Math.max(0, ...series.flatMap(row => metrics.map(metric => row[metric])));
        // Integer ticks with a zero baseline keep small event counts honest.
        const rawStep = Math.max(1, max / 4);
        const magnitude = 10 ** Math.floor(Math.log10(rawStep));
        const step = [1, 2, 5, 10].find(factor => factor * magnitude >= rawStep) * magnitude;
        const ceiling = Math.max(2 * step, Math.ceil(max / step) * step);
        const slot = (right - left) / Math.max(1, series.length);
        const x = index => style === 'bar' ? left + slot * (index + .5)
            : series.length === 1 ? (left + right) / 2 : left + index * (right - left) / (series.length - 1);
        const y = value => bottom - value / ceiling * (bottom - top);
        geometry = { width, height, left, right, x, y, slot };
        chart.style.height = `${height}px`;
        svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
        svg.setAttribute('aria-label', `${title} by ${periodName.toLowerCase()}, ${style} graph. All series use the same count scale, from zero to ${number.format(ceiling)}. Exact values are in the totals table.`);
        chart.setAttribute('aria-label', `${title} graph. Use left and right arrow keys to explore dates.`);
        svg.replaceChildren();
        bars = [];
        previousBars = [];
        markers = [];
        tooltipValues = {};
        legend.replaceChildren();
        tooltip.querySelector('dl').replaceChildren();
        metrics.forEach(metric => {
            const { color, dash } = metricStyles[metric];
            const item = document.createElement('span');
            const swatch = element('svg', { viewBox: '0 0 28 12', 'aria-hidden': 'true' });
            swatch.append(element('line', { x1: 1, x2: 27, y1: 6, y2: 6, stroke: color, 'stroke-width': 3, 'stroke-dasharray': style === 'line' ? dash : '' }));
            item.append(swatch, document.createTextNode(labels[metric]));
            legend.append(item);
            const entry = document.createElement('div');
            const term = document.createElement('dt');
            const dot = document.createElement('i');
            dot.style.backgroundColor = color;
            term.append(dot, document.createTextNode(labels[metric]));
            const value = document.createElement('dd');
            entry.append(term, value);
            tooltip.querySelector('dl').append(entry);
            tooltipValues[metric] = value;
        });
        document.getElementById('analytics-graph-peak').textContent = `${metrics.length > 1 ? 'Highest count' : 'Peak'}: ${number.format(max)}`;
        styleButtons.forEach(button => button.setAttribute('aria-pressed', String(button.dataset.chartType === style)));

        for (let tick = 0; tick <= ceiling; tick += step) {
            svg.append(element('line', { x1: left, x2: right, y1: y(tick), y2: y(tick), stroke: tick === 0 ? '#cbd9e9' : '#eaf0f7', 'stroke-width': 1 }));
            svg.append(element('text', { x: left - 12, y: y(tick) + 4, 'text-anchor': 'end', class: 'analytics-axis-label' }, compactNumber.format(tick)));
        }

        if (series.length && style === 'line') {
            metrics.forEach(metric => {
                const { color, dash } = metricStyles[metric];
                const path = series.map((row, index) => `${index === 0 ? 'M' : 'L'} ${x(index)} ${y(row[metric])}`).join(' ');
                if (series.length > 1) {
                    if (metrics.length === 1) {
                        const defs = element('defs');
                        const gradient = element('linearGradient', { id: 'analytics-area-fill', x1: 0, y1: 0, x2: 0, y2: 1 });
                        gradient.append(element('stop', { offset: '0%', 'stop-color': color, 'stop-opacity': .22 }),
                            element('stop', { offset: '100%', 'stop-color': color, 'stop-opacity': .015 }));
                        defs.append(gradient);
                        svg.append(defs, element('path', { d: `${path} L ${x(series.length - 1)} ${bottom} L ${x(0)} ${bottom} Z`, fill: 'url(#analytics-area-fill)', class: 'analytics-area' }));
                    }
                    svg.append(element('path', { d: path, fill: 'none', stroke: color, 'stroke-width': 2.5, 'stroke-dasharray': dash, 'stroke-linecap': 'round', 'stroke-linejoin': 'round', class: 'analytics-line', 'data-metric': metric }));
                }
                series.forEach((row, index) => {
                    if (series.length === 1 || (metrics.length === 1 && series.length <= 35) || row[metric] > 0) {
                        const dot = element('circle', { cx: x(index), cy: y(row[metric]), r: series.length > 90 ? 2 : 3, fill: '#fff', stroke: color, 'stroke-width': 1.8, class: 'analytics-point', 'data-metric': metric });
                        dot.append(element('title', {}, `${row.period}: ${number.format(row[metric])} ${labels[metric].toLowerCase()}`));
                        svg.append(dot);
                    }
                });
            });
        } else if (style === 'bar') {
            // Group metrics beside each other; overlapping event counts must not be added together.
            const groupWidth = Math.min(44 * metrics.length, slot * .75);
            const barSlot = groupWidth / metrics.length;
            const barWidth = barSlot * .85;
            series.forEach((row, index) => {
                bars[index] = metrics.map((metric, metricIndex) => {
                    const center = x(index) - groupWidth / 2 + barSlot * (metricIndex + .5);
                    const bar = element('rect', { x: center - barWidth / 2, y: y(row[metric]), width: barWidth, height: bottom - y(row[metric]), rx: Math.min(3, barWidth / 4), fill: metricStyles[metric].color, 'fill-opacity': .8, class: 'analytics-bar', 'data-metric': metric });
                    bar.append(element('title', {}, `${row.period}: ${number.format(row[metric])} ${labels[metric].toLowerCase()}`));
                    svg.append(bar);
                    return bar;
                });
            });
        }

        const labelCount = Math.min(series.length, Math.max(2, Math.floor((right - left) / 95)));
        const labelIndices = new Set(Array.from({ length: labelCount }, (_, index) => labelCount === 1 ? 0 : Math.round(index * (series.length - 1) / (labelCount - 1))));
        series.forEach((row, index) => {
            if (!labelIndices.has(index)) return;
            const anchor = series.length === 1 ? 'middle' : index === 0 ? 'start' : index === series.length - 1 ? 'end' : 'middle';
            svg.append(element('text', { x: x(index), y: bottom + 22, 'text-anchor': anchor, class: 'analytics-axis-label' }, row.label));
        });
        svg.append(element('text', { x: (left + right) / 2, y: height - 4, 'text-anchor': 'middle', class: 'analytics-axis-title' }, periodName));
        guide = element('line', { x1: 0, x2: 0, y1: top, y2: bottom, stroke: '#8ba8ca', 'stroke-dasharray': '4 5', 'pointer-events': 'none' });
        svg.append(guide);
        if (style === 'line') {
            markers = metrics.map(metric => {
                const node = element('circle', { r: 5, fill: '#fff', stroke: metricStyles[metric].color, 'stroke-width': 2.5, 'pointer-events': 'none' });
                svg.append(node);
                return { metric, node };
            });
        }
        inspect(selected, false);
    }

    function pointer(event) {
        if (!geometry) return;
        const bounds = svg.getBoundingClientRect();
        const position = (event.clientX - bounds.left) * geometry.width / bounds.width;
        const index = style === 'bar' ? Math.floor((position - geometry.left) / geometry.slot)
            : Math.round((position - geometry.left) / (geometry.right - geometry.left) * (series.length - 1));
        inspect(index);
    }

    chart.addEventListener('pointermove', pointer);
    chart.addEventListener('pointerdown', pointer);
    chart.addEventListener('pointerleave', () => { tooltip.hidden = true; });
    chart.addEventListener('focus', () => inspect(selected));
    chart.addEventListener('blur', () => { tooltip.hidden = true; });
    chart.addEventListener('keydown', event => {
        const next = { ArrowLeft: selected - 1, ArrowRight: selected + 1, Home: 0, End: series.length - 1 }[event.key];
        if (next === undefined) {
            if (event.key === 'Escape') tooltip.hidden = true;
            return;
        }
        event.preventDefault();
        inspect(next);
    });
    styleButtons.forEach(button => button.addEventListener('click', () => {
        style = button.dataset.chartType;
        render();
    }));
    select.addEventListener('change', render);
    let measuredWidth = 0;
    if (typeof ResizeObserver !== 'undefined') {
        new ResizeObserver(() => {
            if (Math.abs(chart.clientWidth - measuredWidth) > 1) {
                measuredWidth = chart.clientWidth;
                render();
            }
        }).observe(chart);
    } else {
        window.addEventListener('resize', render);
    }
    render();
})();
</script>
@endpush
