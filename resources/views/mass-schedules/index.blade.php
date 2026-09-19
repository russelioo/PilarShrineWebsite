@extends('layouts.'.$portal)
@section('title', 'Mass & Confession Schedule')

@section('content')
<div class="page-header">
    <div>
        <h2>Mass &amp; Confession Schedule</h2>
        <p>Manage existing Mass times, Confession schedules, liturgical devotions, and chapel services.</p>
    </div>
    <button class="btn btn-primary" type="button" onclick="document.getElementById('schedule-form').scrollIntoView({behavior:'smooth'})">
        + Add Schedule
    </button>
</div>

@if(session('success'))
    <div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="alert error">
        <strong>Please review the following:</strong>
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="sunday-notice">
    <span aria-hidden="true">✝</span>
    <div>
        <strong>Sunday Mass Protected</strong>
        <p>The system always preserves at least one active Sunday Mass available for parishioners and liturgy records.</p>
    </div>
</div>

<!-- Category Filters Bar -->
<div class="filter-tabs-bar">
    <div class="tabs-group">
        <a href="{{ route($portal.'.mass-schedules') }}" class="tab-item {{ $selectedCategory === 'all' ? 'active' : '' }}">
            All ({{ \App\Models\MassSchedule::count() }})
        </a>
        <a href="{{ route($portal.'.mass-schedules', ['category' => 'Sunday Mass']) }}" class="tab-item {{ $selectedCategory === 'Sunday Mass' ? 'active' : '' }}">
            Sunday Mass
        </a>
        <a href="{{ route($portal.'.mass-schedules', ['category' => 'Daily Mass']) }}" class="tab-item {{ $selectedCategory === 'Daily Mass' ? 'active' : '' }}">
            Daily Mass
        </a>
        <a href="{{ route($portal.'.mass-schedules', ['category' => 'Sacrament of Reconciliation']) }}" class="tab-item {{ $selectedCategory === 'Sacrament of Reconciliation' ? 'active' : '' }}">
            Confession
        </a>
        <a href="{{ route($portal.'.mass-schedules', ['category' => 'Monthly Devotion to Our Lady of the Pillar']) }}" class="tab-item {{ $selectedCategory === 'Monthly Devotion to Our Lady of the Pillar' ? 'active' : '' }}">
            Devotion
        </a>
        <a href="{{ route($portal.'.mass-schedules', ['category' => 'Special Liturgical Activities']) }}" class="tab-item {{ $selectedCategory === 'Special Liturgical Activities' ? 'active' : '' }}">
            Special Liturgy
        </a>
    </div>

    <!-- Search input -->
    <form method="GET" action="{{ route($portal.'.mass-schedules') }}" class="search-form">
        @if($selectedCategory !== 'all')
            <input type="hidden" name="category" value="{{ $selectedCategory }}">
        @endif
        <input type="text" name="search" value="{{ $search }}" placeholder="Search schedules, days, locations..." aria-label="Search schedules">
        @if($search)
            <a href="{{ route($portal.'.mass-schedules', $selectedCategory !== 'all' ? ['category' => $selectedCategory] : []) }}" class="clear-search">&times;</a>
        @endif
        <button type="submit" class="btn btn-outline btn-sm">Search</button>
    </form>
</div>

<!-- Schedules Table / List View -->
<div class="table-card">
    <div class="table-card-header">
        <h3>Existing Schedules</h3>
        <span class="count-badge">{{ $schedules->count() }} record{{ $schedules->count() === 1 ? '' : 's' }}</span>
    </div>

    <div class="table-responsive">
        <table class="schedule-table">
            <thead>
                <tr>
                    <th>Day / Frequency</th>
                    <th>Time</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Notes / Badges</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                <tr class="{{ $editing && $editing->id === $schedule->id ? 'row-editing' : '' }} {{ $schedule->category === 'Sunday Mass' ? 'row-sunday' : '' }}">
                    <td class="col-day">
                        <strong>{{ $schedule->day_of_week }}</strong>
                        @if($schedule->title && $schedule->title !== $schedule->day_of_week && $schedule->title !== $schedule->schedule_type)
                            <small class="sub-label">{{ $schedule->title }}</small>
                        @endif
                    </td>
                    <td class="col-time">
                        <span class="time-badge">{{ $schedule->time_display ?: ($schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') : '—') }}</span>
                    </td>
                    <td class="col-type">
                        <span class="type-pill type-{{ \Illuminate\Support\Str::slug($schedule->schedule_type ?: 'mass') }}">
                            {{ $schedule->schedule_type ?: 'Holy Mass' }}
                        </span>
                    </td>
                    <td class="col-cat">
                        <span class="category-text">{{ $schedule->category ?: 'General' }}</span>
                    </td>
                    <td class="col-location">
                        <span>{{ $schedule->location ?: 'Main Church' }}</span>
                    </td>
                    <td class="col-notes">
                        @if($schedule->is_livestreamed)
                            <span class="badge-live">FB Live</span>
                        @endif
                        @if($schedule->is_highlighted)
                            <span class="badge-highlight">Highlighted</span>
                        @endif
                        @if($schedule->notes && !in_array($schedule->notes, ['FB Live', 'Anticipated Sunday Mass']))
                            <small class="notes-text">{{ $schedule->notes }}</small>
                        @endif
                    </td>
                    <td class="col-status">
                        <span class="status {{ $schedule->is_active ? 'active' : 'inactive' }}">
                            {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="col-actions text-right">
                        <a class="btn btn-sm btn-outline" href="{{ route($portal.'.mass-schedules', array_merge(request()->query(), ['edit' => $schedule->id])) }}#schedule-form">
                            Edit
                        </a>
                        <form method="POST" action="{{ route($portal.'.mass-schedules.destroy', $schedule) }}" class="inline-form" onsubmit="return confirm('Delete schedule \'{{ $schedule->title }}\'?')">
                            @csrf
                            @method('DELETE')
                            <button class="delete-btn" type="submit" title="Delete schedule">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center empty-state">
                        <p>No schedules found matching your criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Edit / Add Schedule Form -->
<section class="form-card" id="schedule-form">
    <div class="form-heading">
        <div>
            <span>{{ $editing ? 'Modify Existing Schedule' : 'Create New Schedule' }}</span>
            <h3>{{ $editing ? 'Edit: ' . ($editing->title ?: $editing->day_of_week) : 'Add a Mass or Confession Schedule' }}</h3>
        </div>
        @if($editing)
            <a href="{{ route($portal.'.mass-schedules', request()->except('edit')) }}" class="btn-cancel">Cancel editing</a>
        @endif
    </div>

    <form method="POST" action="{{ $editing ? route($portal.'.mass-schedules.update', $editing) : route($portal.'.mass-schedules.store') }}">
        @csrf
        @if($editing)
            @method('PUT')
        @endif

        <div class="fields">
            <!-- Title -->
            <label class="wide">
                <span>Schedule Title / Identifier *</span>
                <input name="title" required maxlength="150" value="{{ old('title', $editing?->title) }}" placeholder="e.g. Daily Mass (Mon &amp; Wed) or Sunday Morning Mass">
            </label>

            <!-- Category -->
            <label>
                <span>Category *</span>
                <select name="category" required>
                    @foreach([
                        'Daily Mass',
                        'Sunday Mass',
                        'Sacrament of Reconciliation',
                        'Monthly Devotion to Our Lady of the Pillar',
                        'Special Liturgical Activities'
                    ] as $cat)
                        <option value="{{ $cat }}" @selected(old('category', $editing?->category) === $cat)>{{ $cat }}</option>
                    @endforeach
                </select>
            </label>

            <!-- Schedule Type -->
            <label>
                <span>Schedule Type *</span>
                <input name="schedule_type" required maxlength="100" value="{{ old('schedule_type', $editing?->schedule_type ?? 'Holy Mass') }}" placeholder="e.g. Holy Mass, Confession, Marian Procession">
            </label>

            <!-- Day / Frequency -->
            <label class="wide">
                <span>Day / Frequency *</span>
                <input name="day_of_week" required maxlength="150" value="{{ old('day_of_week', $editing?->day_of_week) }}" placeholder="e.g. Sunday, Monday &amp; Wednesday, Every First Thursday of the Month">
            </label>

            <!-- Time Display -->
            <label class="wide">
                <span>Time / Display Text *</span>
                <input name="time_display" required maxlength="150" value="{{ old('time_display', $editing?->time_display) }}" placeholder="e.g. 5:00 PM — Holy Mass, 6:00 AM — Holy Mass, or Holy Hour after Holy Mass">
            </label>

            <!-- Location -->
            <label class="wide">
                <span>Location *</span>
                <input name="location" required maxlength="150" value="{{ old('location', $editing?->location ?? 'Main Church') }}" placeholder="e.g. Main Church, Shrine Confessional, Campo Santo Chapel">
            </label>

            <!-- Priest in Charge -->
            <label>
                <span>Priest / Minister in Charge</span>
                <input name="priest_in_charge" maxlength="150" value="{{ old('priest_in_charge', $editing?->priest_in_charge ?? 'Parish Priest') }}" placeholder="e.g. Parish Priest">
            </label>

            <!-- Notes -->
            <label class="wide">
                <span>Notes / Liturgical Subtitle</span>
                <input name="notes" maxlength="255" value="{{ old('notes', $editing?->notes) }}" placeholder="e.g. FB Live, Confession &amp; Spiritual Healing, Anticipated Sunday Mass">
            </label>

            <!-- Sort Order -->
            <label>
                <span>Sort Order</span>
                <input type="number" name="sort_order" value="{{ old('sort_order', $editing?->sort_order ?? 0) }}" placeholder="0">
            </label>

            <!-- Checkboxes Row -->
            <div class="checkboxes-row wide">
                <label class="check">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $editing?->is_active ?? true))>
                    <span>Active and displayed on public website</span>
                </label>

                <label class="check">
                    <input type="checkbox" name="is_livestreamed" value="1" @checked(old('is_livestreamed', $editing?->is_livestreamed ?? false))>
                    <span>Broadcast Live (FB Live badge)</span>
                </label>

                <label class="check">
                    <input type="checkbox" name="is_highlighted" value="1" @checked(old('is_highlighted', $editing?->is_highlighted ?? false))>
                    <span>Highlighted entry</span>
                </label>
            </div>
        </div>

        <div class="submit-row">
            @if($editing)
                <a href="{{ route($portal.'.mass-schedules', request()->except('edit')) }}" class="btn btn-outline">Cancel</a>
            @endif
            <button class="btn btn-primary" type="submit">
                {{ $editing ? 'Save Schedule Changes' : 'Create Schedule' }}
            </button>
        </div>
    </form>
</section>
@endsection

@push('styles')
<style>
.page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.page-header > div h2 { margin: 0; font-size: 22px; color: var(--navy); font-family: var(--font-heading); }
.page-header > div p { margin: 6px 0 0; color: var(--muted); font-size: 13px; }
.alert { margin-bottom: 16px; padding: 12px 16px; border-radius: 8px; font-size: 12px; }
.alert.success { border: 1px solid #a8dfbb; background: #edfaf2; color: #176b3a; }
.alert.error { border: 1px solid #f0b6b6; background: #fff4f4; color: #9a2525; }
.alert ul { margin: 7px 0 0; padding-left: 18px; }

.sunday-notice { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 14px 18px; border: 1px solid #ead69e; border-radius: 10px; background: #fffaeb; }
.sunday-notice > span { width: 32px; height: 32px; display: grid; place-items: center; border-radius: 50%; background: #fff0bb; color: #9c6f00; font-size: 16px; font-weight: bold; flex-shrink: 0; }
.sunday-notice strong { color: #765500; font-size: 13px; }
.sunday-notice p { margin: 3px 0 0; color: #887241; font-size: 12px; }

.filter-tabs-bar { display: flex; justify-content: space-between; align-items: center; gap: 15px; margin-bottom: 20px; flex-wrap: wrap; }
.tabs-group { display: flex; gap: 6px; flex-wrap: wrap; }
.tab-item { padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; text-decoration: none; color: var(--muted); background: #eef3f9; transition: all 0.15s ease; }
.tab-item:hover { background: #dfe8f4; color: var(--navy); }
.tab-item.active { background: var(--navy); color: #fff; }

.search-form { display: flex; align-items: center; gap: 6px; position: relative; }
.search-form input { padding: 7px 12px; border: 1px solid #ccd8e4; border-radius: 6px; font-size: 12px; width: 240px; }
.clear-search { position: absolute; right: 75px; color: #888; text-decoration: none; font-size: 16px; line-height: 1; }

.table-card { border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04); margin-bottom: 30px; overflow: hidden; }
.table-card-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 20px; border-bottom: 1px solid #edf2f7; }
.table-card-header h3 { margin: 0; font-size: 16px; color: var(--navy); font-weight: 700; }
.count-badge { padding: 3px 10px; border-radius: 12px; background: #edf2f7; color: #475569; font-size: 11px; font-weight: 700; }

.table-responsive { overflow-x: auto; }
.schedule-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
.schedule-table th { padding: 12px 16px; background: #f8fafc; color: #475569; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
.schedule-table td { padding: 13px 16px; border-bottom: 1px solid #edf2f7; vertical-align: middle; color: #1e293b; }
.schedule-table tr:hover { background: #f8fafc; }
.schedule-table tr.row-editing { background: #eff6ff; }
.schedule-table tr.row-sunday { background: #fafbff; }

.col-day strong { display: block; color: var(--navy); font-size: 13px; }
.col-day .sub-label { display: block; color: var(--muted); font-size: 11px; margin-top: 2px; }
.time-badge { font-weight: 600; color: #0f172a; font-size: 12px; background: #f1f5f9; padding: 3px 8px; border-radius: 6px; display: inline-block; white-space: nowrap; }

.type-pill { padding: 3px 8px; border-radius: 10px; font-size: 11px; font-weight: 700; display: inline-block; white-space: nowrap; background: #e0f2fe; color: #0369a1; }
.type-pill.type-confession { background: #fef3c7; color: #92400e; }
.type-pill.type-marian-procession, .type-pill.type-devotion { background: #fce7f3; color: #9d174d; }
.type-pill.type-healing-mass { background: #dcfce7; color: #166534; }
.type-pill.type-holy-hour { background: #ede9fe; color: #5b21b6; }

.category-text { font-size: 11px; color: #64748b; font-weight: 600; }
.badge-live { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 800; background: #fee2e2; color: #b91c1c; text-transform: uppercase; }
.badge-highlight { display: inline-block; padding: 2px 7px; border-radius: 10px; font-size: 10px; font-weight: 800; background: #fef3c7; color: #b45309; }
.notes-text { display: block; color: #64748b; font-size: 11px; }

.status { padding: 4px 10px; border-radius: 15px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.status.active { background: #dcfce7; color: #15803d; }
.status.inactive { background: #f1f5f9; color: #64748b; }

.inline-form { display: inline-block; margin-left: 4px; }
.delete-btn { background: none; border: none; color: #dc2626; font-size: 12px; cursor: pointer; padding: 4px 6px; }
.delete-btn:hover { text-decoration: underline; }

.btn { display: inline-block; padding: 8px 16px; border-radius: 6px; font-size: 12px; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; }
.btn-sm { padding: 5px 10px; font-size: 11px; }
.btn-primary { background: var(--navy); color: #fff; }
.btn-primary:hover { background: #0b3d8f; }
.btn-outline { background: #fff; border-color: #cbd5e1; color: #334155; }
.btn-outline:hover { background: #f8fafc; border-color: #94a3b8; }
.btn-cancel { color: #64748b; font-size: 12px; text-decoration: underline; }

.form-card { padding: 24px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04); }
.form-heading { display: flex; justify-content: space-between; align-items: center; padding-bottom: 16px; margin-bottom: 20px; border-bottom: 1px solid #edf2f7; }
.form-heading span { color: var(--gold); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; display: block; }
.form-heading h3 { margin: 4px 0 0; color: var(--navy); font-size: 18px; font-family: var(--font-heading); }

.fields { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
.fields label { display: flex; flex-direction: column; gap: 6px; }
.fields label > span { font-size: 11px; font-weight: 700; color: #334155; }
.fields input, .fields select { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; background: #fff; font-size: 12px; }
.fields input:focus, .fields select:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 2px rgba(21, 92, 180, 0.15); }
.fields .wide { grid-column: span 2; }
.checkboxes-row { display: flex; gap: 24px; align-items: center; padding-top: 8px; flex-wrap: wrap; }
.checkboxes-row .check { flex-direction: row; align-items: center; gap: 8px; cursor: pointer; }
.checkboxes-row .check input { width: 16px; height: 16px; cursor: pointer; }
.checkboxes-row .check span { font-size: 12px; font-weight: 600; color: #334155; }

.submit-row { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }

@media(max-width: 900px) {
    .fields { grid-template-columns: 1fr; }
    .fields .wide { grid-column: auto; }
    .filter-tabs-bar { flex-direction: column; align-items: stretch; }
    .search-form input { width: 100%; }
}
</style>
@endpush
