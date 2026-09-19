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

    <section class="analytics-panel">
        <div class="analytics-panel-heading"><div><h2>Activity over time</h2><p>{{ \Carbon\Carbon::parse($filters['from'])->format('M j, Y') }} – {{ \Carbon\Carbon::parse($filters['to'])->format('M j, Y') }}</p></div>
            <label class="analytics-chart-selector">Chart metric<select id="analytics-metric">@foreach($metricLabels as $key => $label)<option value="{{ $key }}" @selected($key === 'page_views')>{{ $label }}</option>@endforeach</select></label>
        </div>
        @if(!($totals->visitors ?? 0))<div class="analytics-empty">No recorded activity for this period. New visits and sign-ins will appear here as people use the website.</div>@endif
        <div class="analytics-chart" id="analytics-chart" role="img" aria-label="Page views over time"></div>
        <p id="analytics-chart-detail" class="analytics-chart-detail" aria-live="polite">Select a bar to see its exact count.</p>
        <details class="analytics-periods"><summary>View {{ $filters['group'] === 'day' ? 'daily' : ($filters['group'] === 'month' ? 'monthly' : 'yearly') }} totals</summary>
            <div class="analytics-table-scroll"><table><caption class="analytics-sr-only">Activity totals by period</caption><thead><tr><th>Period</th>@foreach($metricLabels as $label)<th>{{ $label }}</th>@endforeach</tr></thead><tbody>@foreach($series as $row)<tr><th scope="row">{{ $row['period'] }}</th>@foreach($metricLabels as $key => $label)<td>{{ number_format($row[$key]) }}</td>@endforeach</tr>@endforeach</tbody></table></div>
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
.analytics-page{max-width:1500px;margin:auto;display:grid;gap:24px;color:#173152}.analytics-heading{display:flex;justify-content:space-between;align-items:center;gap:20px}.analytics-eyebrow{font-size:11px;font-weight:800;letter-spacing:1.6px;color:#a27a22}.analytics-heading h1{font-size:30px;letter-spacing:-.8px;color:#073478;margin:5px 0 8px}.analytics-heading p,.analytics-panel-heading p{color:#718198;margin:0;line-height:1.7}.analytics-panel{background:#fff;border:1px solid #e0e8f1;border-radius:18px;padding:26px;min-width:0;box-shadow:0 5px 22px #0a347803}.analytics-button{display:inline-flex;justify-content:center;align-items:center;gap:8px;border:1px solid #0b377b;border-radius:9px;background:#0b377b;color:#fff;padding:11px 17px;font:inherit;font-weight:700;text-decoration:none;cursor:pointer;white-space:nowrap;min-height:43px}.analytics-button.secondary{background:#fff;color:#0b377b;border-color:#d9e3ef}.analytics-button:hover{filter:brightness(.96)}.analytics-page a:focus-visible,.analytics-page button:focus-visible,.analytics-page select:focus-visible,.analytics-page input:focus-visible{outline:3px solid #d8aa3c;outline-offset:3px}.analytics-presets{display:flex;align-items:center;gap:9px;flex-wrap:wrap;margin-bottom:20px}.analytics-presets span{color:#738299;margin-right:7px;font-size:12px}.analytics-presets a{background:#f3f6fb;color:#16477f;text-decoration:none;border:1px solid #e4ebf4;padding:6px 13px;border-radius:7px;font-size:12px;font-weight:700}.analytics-filter-grid{display:grid;grid-template-columns:1fr 1fr .8fr 1.15fr 1.15fr auto;gap:14px;align-items:end}.analytics-page label{display:flex;flex-direction:column;gap:7px;font-size:12px;font-weight:700;color:#354d6a}.analytics-page input,.analytics-page select{font:inherit;font-size:13px;background:#fff;color:#203e60;border:1px solid #d7e1ed;border-radius:8px;padding:10px 12px;min-height:43px;min-width:0;width:100%}.analytics-note{font-size:11px;color:#7b8aa0;margin:15px 0 0;line-height:1.7}.analytics-error{color:#a12c2c;background:#fff1f1;padding:12px;border-radius:8px;margin-bottom:14px}.analytics-selection{background:#edf4ff;border-radius:8px;padding:12px;margin-bottom:16px}.analytics-selection a{margin-left:16px;color:#0b377b;font-weight:700}.analytics-metrics{display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:14px}.analytics-metric{display:flex;flex-direction:column;gap:8px;border:1px solid #e0e8f1;border-radius:15px;padding:21px 18px;background:#fff}.analytics-metric span{font-weight:700;color:#61748d;font-size:12px}.analytics-metric strong{font-size:32px;color:#083479;line-height:1.2}.analytics-metric small{color:#8190a2;font-size:10px;line-height:1.5}.analytics-metric.featured{background:#0b3474;border-color:#0b3474}.analytics-metric.featured strong{color:#fff}.analytics-metric.featured span,.analytics-metric.featured small{color:#c7d7ed}.analytics-panel-heading{display:flex;align-items:center;justify-content:space-between;gap:15px;margin-bottom:18px}.analytics-panel-heading h2{font-size:17px;color:#0b3474;margin:0 0 5px}.analytics-panel-heading p{font-size:12px}.analytics-chart-selector{min-width:145px}.analytics-chart{height:240px;display:flex;gap:clamp(2px,.7vw,10px);align-items:stretch;overflow-x:auto;padding:15px 4px 4px;border-bottom:1px solid #e5ecf3;background:repeating-linear-gradient(to top,transparent 0,transparent 54px,#f1f5f9 55px,#f1f5f9 56px)}.analytics-chart-column{display:flex;flex:1;min-width:16px;max-width:100%;align-items:center;flex-direction:column;justify-content:end;gap:7px}.analytics-chart-bar{display:block;width:100%;max-width:65px;border:0;border-radius:4px 4px 0 0;background:#2864ac;cursor:pointer;min-height:3px;transition:height .2s,background .2s}.analytics-chart-bar:hover,.analytics-chart-bar:focus{background:#d5a539}.analytics-chart-column span{font-size:9px;color:#74869b;white-space:nowrap;min-height:14px}.analytics-chart-detail{font-size:12px;color:#55708e;min-height:20px;margin:14px 0}.analytics-periods summary{font-size:12px;color:#15487f;cursor:pointer;font-weight:700;margin:10px 0}.analytics-columns{display:grid;grid-template-columns:1fr 1fr;gap:24px}.analytics-ranking{display:flex;align-items:center;gap:12px;padding:13px 0;border-bottom:1px solid #eef2f7;font-size:13px}.analytics-ranking:last-child{border-bottom:0}.analytics-ranking>div,.analytics-ranking>span:first-child:not(.analytics-avatar){flex:1;min-width:0}.analytics-ranking strong{color:#0b3474;text-align:right;margin-left:auto}.analytics-ranking small,.analytics-history td small{display:block;font-size:11px;color:#8190a2;margin-top:4px}.analytics-person{color:#173152;text-decoration:none}.analytics-person:hover{background:#f8faff}.analytics-avatar{width:34px;height:34px;flex-shrink:0;background:#edf3fb;color:#1b4a8b;display:grid;place-items:center;border-radius:50%;font-weight:800}.analytics-pill{display:inline-block;background:#eef4fd;color:#36649c;border-radius:6px;padding:5px 9px;font-size:10px;font-weight:700;white-space:nowrap}.analytics-pill.success{background:#eaf8f1;color:#287754}.analytics-table-scroll{overflow-x:auto}.analytics-page table{border-collapse:collapse;width:100%;font-size:12px;text-align:left}.analytics-page th{font-weight:700;white-space:nowrap}.analytics-page thead th{background:#f5f8fc;color:#77889f;font-size:10px;text-transform:uppercase;letter-spacing:.5px}.analytics-page td,.analytics-page th{padding:14px 12px;border-bottom:1px solid #edf1f6}.analytics-history td{vertical-align:top;min-width:150px}.analytics-history td strong{font-weight:600}.analytics-time{white-space:nowrap}.analytics-history-filter{display:flex;gap:8px}.analytics-history-filter select{min-width:150px}.analytics-empty{color:#8996a6;padding:22px 0;font-size:12px;line-height:1.8}.analytics-pagination{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:18px;color:#7b8aa0;font-size:11px}.analytics-pagination>div{display:flex;gap:8px}.analytics-footnote{font-size:11px;color:#7b8aa0;line-height:1.9;margin:0 0 20px}.analytics-sr-only{position:absolute!important;width:1px;height:1px;padding:0;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
@media(max-width:1250px){.analytics-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}.analytics-filter-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:760px){.analytics-page{gap:17px}.analytics-heading{align-items:flex-start;flex-direction:column}.analytics-heading h1{font-size:26px}.analytics-panel{padding:19px 16px}.analytics-columns{grid-template-columns:1fr;gap:17px}.analytics-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.analytics-metric{padding:17px}.analytics-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.analytics-panel-heading{flex-wrap:wrap}.analytics-chart{height:200px}.analytics-history-filter{width:100%}.analytics-history-filter select{flex:1}.analytics-pagination{align-items:flex-start;flex-direction:column}}
@media(prefers-reduced-motion:reduce){.analytics-chart-bar{transition:none}}
</style>
@endpush

@push('scripts')
<script>
(() => {
    const series = {{ Illuminate\Support\Js::from($series) }};
    const labels = {{ Illuminate\Support\Js::from($metricLabels) }};
    const select = document.getElementById('analytics-metric');
    const chart = document.getElementById('analytics-chart');
    const detail = document.getElementById('analytics-chart-detail');
    function render() {
        const metric = select.value;
        const max = Math.max(1, ...series.map(row => row[metric]));
        chart.replaceChildren();
        chart.setAttribute('aria-label', labels[metric] + ' over time. Exact totals are in the table below.');
        series.forEach((row, index) => {
            const column = document.createElement('div');
            column.className = 'analytics-chart-column';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'analytics-chart-bar';
            button.style.height = Math.max(1.5, row[metric] / max * 87) + '%';
            button.style.opacity = row[metric] ? '1' : '.2';
            const description = row.period + ': ' + row[metric].toLocaleString() + ' ' + labels[metric].toLowerCase();
            button.title = description;
            button.setAttribute('aria-label', description);
            button.addEventListener('click', () => { detail.textContent = description; });
            const label = document.createElement('span');
            label.textContent = index % Math.max(1, Math.ceil(series.length / 8)) === 0 || index === series.length - 1 ? row.label : '';
            column.append(button, label);
            chart.append(column);
        });
        detail.textContent = 'Select a bar to see its exact count.';
    }
    select.addEventListener('change', render);
    render();
})();
</script>
@endpush
