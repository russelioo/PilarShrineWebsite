@extends('layouts.admin')
@section('title', $commission->name . ' — Workspace')

@php
    $isCoordinatorWorkspace = !empty($isWorkspace) || request()->routeIs('commission.*');
    $tabUrl = function($t) use ($commission, $isCoordinatorWorkspace) {
        if ($isCoordinatorWorkspace) {
            return match($t) {
                'overview' => route('commission.overview'),
                'members' => route('commission.members'),
                'officers' => route('commission.officers'),
                'ministries' => route('commission.ministries'),
                'projects' => route('commission.projects'),
                'documents' => route('commission.documents'),
                default => route('commission.dashboard'),
            };
        }
        return route('admin.commissions.show', [$commission, 'tab' => $t]);
    };

    $addMemberAction = $isCoordinatorWorkspace ? route('commission.members.store') : route('admin.commissions.members.store', $commission);
    $addProjectAction = $isCoordinatorWorkspace ? route('commission.projects.store') : route('admin.commissions.projects.store', $commission);
    $addDocumentAction = $isCoordinatorWorkspace ? route('commission.documents.store') : route('admin.commissions.documents.store', $commission);
    $currentTab = $tab ?? 'overview';
@endphp

@section('content')
<div class="page-header">
    <div>
        <div style="display:flex;align-items:center;gap:12px;">
            <div class="header-icon-box">
                @if($commission->icon === 'cross')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><line x1="5" y1="8" x2="19" y2="8"/></svg>
                @elseif($commission->icon === 'book-open')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                @elseif($commission->icon === 'heart-handshake')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                @elseif($commission->icon === 'building')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/></svg>
                @elseif($commission->icon === 'users')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                @elseif($commission->icon === 'home')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                @elseif($commission->icon === 'spark')
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                @else
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                @endif
            </div>
            <div>
                <h2 style="margin:0;font-size:22px;">{{ $commission->name }}</h2>
                <div style="display:flex;align-items:center;gap:8px;margin-top:4px;">
                    <span class="commission-code-pill">{{ $commission->code ?: 'COMMISSION' }}</span>
                    <span class="status-pill {{ $commission->is_active ? 'status-active' : 'status-inactive' }}">
                        {{ $commission->is_active ? 'Active' : 'Inactive' }}
                    </span>
                    @php $coordUser = $commission->coordinator ?? $commission->coordinator_user; @endphp
                    @if($coordUser)
                        <span style="font-size:11.5px;color:#64748b;">• Coord: <strong>{{ $coordUser->name }}</strong></span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="actions">
        @can('manageMembers', $commission)
        <button type="button" class="btn btn-secondary btn-sm" onclick="openAddMemberModal()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            <span>+ Add Member</span>
        </button>
        @endcan

        @can('manageProjects', $commission)
        <button type="button" class="btn btn-secondary btn-sm" onclick="openAddProjectModal()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span>+ New Project</span>
        </button>
        @endcan

        @can('manageDocuments', $commission)
        <button type="button" class="btn btn-secondary btn-sm" onclick="openUploadDocModal()">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <span>+ Upload Document</span>
        </button>
        @endcan

        @if(! $isCoordinatorWorkspace)
        <a href="{{ route('admin.commissions.index') }}" class="btn btn-outline btn-sm">
            <span>← All Commissions</span>
        </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('status'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="notice-error">
        <strong>Please correct the following errors:</strong>
        <ul style="margin: 6px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Tab Navigation Bar -->
<div class="tabs-nav-bar">
    <a href="{{ $tabUrl('overview') }}" class="tab-link {{ $currentTab === 'overview' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        <span>Overview</span>
    </a>
    <a href="{{ $tabUrl('members') }}" class="tab-link {{ $currentTab === 'members' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        <span>Members ({{ $commission->members->count() }})</span>
    </a>
    <a href="{{ $tabUrl('officers') }}" class="tab-link {{ $currentTab === 'officers' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/></svg>
        <span>Officers ({{ $commission->officers->count() }})</span>
    </a>
    <a href="{{ $tabUrl('ministries') }}" class="tab-link {{ $currentTab === 'ministries' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/></svg>
        <span>Ministries ({{ $commission->ministries->count() }})</span>
    </a>
    <a href="{{ $tabUrl('projects') }}" class="tab-link {{ $currentTab === 'projects' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        <span>Projects &amp; Activities ({{ $commission->projects->count() }})</span>
    </a>
    <a href="{{ $tabUrl('documents') }}" class="tab-link {{ $currentTab === 'documents' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>Documents &amp; Reports ({{ $commission->documents->count() }})</span>
    </a>
    <a href="{{ $tabUrl('audit-logs') }}" class="tab-link {{ $currentTab === 'audit-logs' ? 'tab-active' : '' }}">
        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        <span>Audit Trail</span>
    </a>
</div>

<!-- ==================== TAB 1: OVERVIEW ==================== -->
@if($currentTab === 'overview')
<div class="overview-layout">
    <div class="overview-main">
        <!-- Commission Details Card -->
        <div class="content-card">
            <h3 class="card-title">Pastoral Mission &amp; Scope</h3>
            <p style="font-size:13px;color:#334155;line-height:1.6;margin-top:6px;">
                {{ $commission->description ?: 'This pastoral commission coordinates dedicated apostolic programs, sacraments, and liturgical services in accordance with the Pastoral Council directives of Our Lady of the Pillar Shrine.' }}
            </p>
            <div class="kpi-mini-row">
                <div class="kpi-mini-box">
                    <strong>{{ $commission->members->count() }}</strong>
                    <span>Enrolled Members</span>
                </div>
                <div class="kpi-mini-box">
                    <strong>{{ $commission->officers->count() }}</strong>
                    <span>Council Officers</span>
                </div>
                <div class="kpi-mini-box">
                    <strong>{{ $commission->ministries->count() }}</strong>
                    <span>Assigned Ministries</span>
                </div>
                <div class="kpi-mini-box">
                    <strong>{{ $commission->projects->whereIn('status', ['planning', 'ongoing'])->count() }}</strong>
                    <span>Active Projects</span>
                </div>
                @if($commission->official_population)
                <div class="kpi-mini-box" style="border-color:#062f78;background:rgba(6,47,120,0.04);">
                    <strong style="color:#062f78;">{{ number_format($commission->official_population) }}</strong>
                    <span>Official Population</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Projects Section -->
        <div class="content-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h3 class="card-title" style="margin:0;">Recent Projects &amp; Apostolic Initiatives</h3>
                <a href="{{ $tabUrl('projects') }}" class="view-all-link">View All Projects →</a>
            </div>
            @if($commission->projects->count() > 0)
            <div class="projects-list-compact">
                @foreach($commission->projects->take(3) as $proj)
                <div class="proj-compact-item">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <h4 style="margin:0;font-size:13px;font-weight:600;color:#0f172a;">{{ $proj->title }}</h4>
                        <span class="status-pill status-{{ $proj->status }}">{{ $proj->status_label }}</span>
                    </div>
                    <p style="margin:4px 0 0;font-size:11.5px;color:#64748b;">{{ Str::limit($proj->description, 90) }}</p>
                    <div style="display:flex;gap:14px;font-size:11px;color:#94a3b8;margin-top:6px;">
                        <span>Target: {{ $proj->end_date ? $proj->end_date->format('M d, Y') : 'Ongoing' }}</span>
                        @if($proj->budget)
                            <span>Budget: ₱{{ number_format($proj->budget, 2) }}</span>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p style="font-size:12px;color:#94a3b8;font-style:italic;">No active projects recorded for this commission yet.</p>
            @endif
        </div>
    </div>

    <div class="overview-side">
        <!-- Coordinator Card -->
        <div class="content-card">
            <h3 class="card-title">Commission Coordinator</h3>
            @php $coordUser = $commission->coordinator ?? $commission->coordinator_user; @endphp
            @if($coordUser)
            <div style="display:flex;align-items:center;gap:12px;margin-top:12px;">
                <div class="coord-avatar-pill" style="width:44px;height:44px;">
                    @if($coordUser->avatar_url)
                        <img src="{{ $coordUser->avatar_url }}" alt="{{ $coordUser->name }}" class="coord-img">
                    @else
                        <span class="coord-initials" style="font-size:14px;">{{ $coordUser->initials }}</span>
                    @endif
                </div>
                <div>
                    <strong style="font-size:13.5px;color:#0f172a;display:block;">{{ $coordUser->name }}</strong>
                    <span style="font-size:11.5px;color:#64748b;">{{ $coordUser->email }}</span>
                    <span style="font-size:10.5px;color:#062f78;display:block;font-weight:600;margin-top:2px;">Head Coordinator</span>
                </div>
            </div>
            @else
            <p style="font-size:12px;color:#94a3b8;font-style:italic;margin-top:8px;">No coordinator assigned to this commission.</p>
            @endif
        </div>

        <!-- Assigned Ministries Card -->
        <div class="content-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 class="card-title" style="margin:0;">Ministries ({{ $commission->ministries->count() }})</h3>
                <a href="{{ $tabUrl('ministries') }}" class="view-all-link">View Details →</a>
            </div>
            @if($commission->ministries->count() > 0)
            <ul style="list-style:none;padding:0;margin:0;">
                @foreach($commission->ministries as $min)
                <li style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:12px;font-weight:600;color:#1e293b;">{{ $min->name }}</span>
                    <span style="font-size:10.5px;color:#64748b;background:#f8fafc;padding:2px 8px;border-radius:10px;border:1px solid #e2e8f0;">{{ $min->category }}</span>
                </li>
                @endforeach
            </ul>
            @else
            <p style="font-size:12px;color:#94a3b8;font-style:italic;">No ministries currently linked to this commission.</p>
            @endif
        </div>

        <!-- Recent Documents Repository -->
        <div class="content-card">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <h3 class="card-title" style="margin:0;">Repository ({{ $commission->documents->count() }})</h3>
                <a href="{{ $tabUrl('documents') }}" class="view-all-link">Repository →</a>
            </div>
            @if($commission->documents->count() > 0)
            <ul style="list-style:none;padding:0;margin:0;">
                @foreach($commission->documents->take(4) as $doc)
                <li style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid #f1f5f9;">
                    <span style="font-size:16px;">{{ $doc->file_icon }}</span>
                    <div style="min-width:0;flex:1;">
                        <span style="font-size:11.5px;font-weight:600;color:#1e293b;display:block;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->title }}</span>
                        <span style="font-size:10px;color:#94a3b8;">{{ $doc->category }} • {{ $doc->formatted_file_size }}</span>
                    </div>
                </li>
                @endforeach
            </ul>
            @else
            <p style="font-size:12px;color:#94a3b8;font-style:italic;">No documents uploaded yet.</p>
            @endif
        </div>
    </div>
</div>
@endif

<!-- ==================== TAB 2: MEMBERS ==================== -->
@if($currentTab === 'members')
<div class="content-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 class="card-title" style="margin:0;">Commission Membership Roster</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">All lay faithful, workers, and volunteers actively serving in this pastoral commission.</p>
        </div>
        @can('manageMembers', $commission)
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddMemberModal()">
            <span>+ Add Member</span>
        </button>
        @endcan
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Member</th>
                    <th>Email</th>
                    <th>Commission Position</th>
                    <th>Officer Status</th>
                    <th>Membership Status</th>
                    <th>Joined</th>
                    @can('manageMembers', $commission)
                    <th style="text-align:right;">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($commission->members as $member)
                @php
                    $membership = $member->pivot;
                    $coordUserId = $commission->head_user_id ?: ($commission->coordinator?->id ?: $commission->coordinator_user?->id);
                    $isMemberCoordinator = $coordUserId && (int) $coordUserId === (int) $member->id;
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="coord-avatar-pill" style="width:30px;height:30px;">
                                @if($member->avatar_url)
                                    <img src="{{ $member->avatar_url }}" alt="" class="coord-img">
                                @else
                                    <span class="coord-initials">{{ $member->initials }}</span>
                                @endif
                            </div>
                            <div>
                                <strong style="font-size:12.5px;color:#0f172a;">{{ $member->name }}</strong>
                                @if($isMemberCoordinator)
                                    <span style="font-size:9.5px;color:#062f78;font-weight:700;display:block;">(HEAD COORDINATOR)</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td style="color:#64748b;">{{ $member->email }}</td>
                    <td>
                        <span style="font-weight:600;color:#1e293b;">
                            @if($isMemberCoordinator && ($membership->position === 'Member' || empty($membership->position)))
                                {{ $member->position ?: 'Commission Coordinator' }}
                            @else
                                {{ $membership->position ?? 'Member' }}
                            @endif
                        </span>
                    </td>
                    <td>
                        @if($isMemberCoordinator)
                            <span class="status-pill" style="background:#e0e7ff;color:#062f78;font-weight:700;">Coordinator</span>
                        @elseif(!empty($membership->is_officer))
                            <span class="status-pill" style="background:#e0e7ff;color:#4338ca;">Officer</span>
                        @else
                            <span class="status-pill" style="background:#f1f5f9;color:#64748b;">General Member</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-pill {{ ($membership->status ?? 'active') === 'active' ? 'status-active' : 'status-inactive' }}">
                            {{ ucfirst($membership->status ?? 'active') }}
                        </span>
                    </td>
                    <td style="color:#64748b;">
                        {{ $membership->joined_at ? \Carbon\Carbon::parse($membership->joined_at)->format('M d, Y') : '—' }}
                    </td>
                    @can('manageMembers', $commission)
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:6px;">
                            <button type="button" class="btn-icon" title="Edit Position" onclick="openEditMemberModal({{ json_encode($membership) }}, '{{ $member->name }}')">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form method="POST" action="{{ $isCoordinatorWorkspace ? route('commission.members.destroy', $membership->id) : route('admin.commissions.members.destroy', [$commission, $membership->id]) }}" onsubmit="return confirm('Remove {{ $member->name }} from this commission?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Remove Member" style="color:#b91c1c;">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:32px;color:#94a3b8;">No members enrolled in this commission yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ==================== TAB 3: OFFICERS ==================== -->
@if($currentTab === 'officers')
<div class="content-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 class="card-title" style="margin:0;">Commission Officers &amp; Executive Board</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Leadership team responsible for directing this pastoral commission's operations.</p>
        </div>
        @can('manageMembers', $commission)
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddMemberModal(true)">
            <span>+ Appoint Officer</span>
        </button>
        @endcan
    </div>

    <div class="officers-grid">
        @forelse($commission->officers as $officer)
        @php($pivot = $officer->pivot)
        <div class="officer-card">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="coord-avatar-pill" style="width:48px;height:48px;">
                    @if($officer->avatar_url)
                        <img src="{{ $officer->avatar_url }}" alt="" class="coord-img">
                    @else
                        <span class="coord-initials" style="font-size:15px;">{{ $officer->initials }}</span>
                    @endif
                </div>
                <div style="min-width:0;flex:1;">
                    <span class="officer-position-pill">{{ $pivot->position ?? 'Officer' }}</span>
                    <h4 style="margin:3px 0 2px;font-size:14px;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $officer->name }}</h4>
                    <span style="font-size:11.5px;color:#64748b;">{{ $officer->email }}</span>
                </div>
            </div>
            @if($pivot->notes)
                <p style="font-size:11.5px;color:#475569;margin:10px 0 0;font-style:italic;line-height:1.4;">"{{ $pivot->notes }}"</p>
            @endif
            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:10px;border-top:1px solid #f1f5f9;font-size:11px;color:#94a3b8;">
                <span>Appointed: {{ $pivot->joined_at ? \Carbon\Carbon::parse($pivot->joined_at)->format('M Y') : '—' }}</span>
                <span class="status-pill status-active">Active Officer</span>
            </div>
        </div>
        @empty
        <p style="grid-column:1/-1;text-align:center;padding:32px;color:#94a3b8;">No officers recorded for this commission.</p>
        @endforelse
    </div>
</div>
@endif

<!-- ==================== TAB 4: MINISTRIES ==================== -->
@if($currentTab === 'ministries')
<div class="content-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 class="card-title" style="margin:0;">Assigned Parish Ministries</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Parish apostolates, organizations, and guilds supervised under this commission.</p>
        </div>
        <div style="display:flex;gap:8px;">
            @can('manageMinistries', $commission)
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddMinistryModal()">
                <span>+ Add Ministry</span>
            </button>
            @endcan
            @if(auth()->user() && auth()->user()->hasParishWideAccess())
            <a href="{{ route('admin.ministries') }}" class="btn btn-outline btn-sm">
                <span>Manage All Parish Ministries</span>
            </a>
            @endif
        </div>
    </div>

    <div class="ministries-cards-grid">
        @forelse($commission->ministries as $ministry)
        <div class="ministry-detail-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div style="display:flex;gap:10px;align-items:center;">
                    <span style="font-size:24px;">{{ $ministry->icon ?: '⛪' }}</span>
                    <div>
                        <h4 style="margin:0;font-size:14px;color:#0f172a;">{{ $ministry->name }}</h4>
                        <span style="font-size:11px;color:#64748b;">{{ $ministry->category }}</span>
                    </div>
                </div>
                <span class="status-pill {{ $ministry->is_accepting_members ? 'status-active' : 'status-inactive' }}">
                    {{ $ministry->is_accepting_members ? 'Accepting Members' : 'Closed' }}
                </span>
            </div>
            <p style="font-size:12px;color:#475569;line-height:1.45;margin:10px 0 8px;">{{ Str::limit($ministry->description, 110) }}</p>
            <div style="background:#f8fafc;padding:8px 10px;border-radius:8px;font-size:11px;color:#64748b;">
                <div><strong>Schedule:</strong> {{ $ministry->meeting_schedule ?: 'Announced per meeting' }}</div>
                <div><strong>Coordinator:</strong> {{ $ministry->coordinator_name ?: 'Parish Leadership' }}</div>
            </div>
        </div>
        @empty
        <div style="grid-column:1/-1;text-align:center;padding:36px;color:#94a3b8;">
            <p>No ministries currently assigned to {{ $commission->name }}.</p>
        </div>
        @endforelse
    </div>
</div>
@endif

<!-- ==================== TAB 5: PROJECTS & ACTIVITIES ==================== -->
@if($currentTab === 'projects')
<div class="content-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 class="card-title" style="margin:0;">Commission Projects &amp; Apostolic Activities</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Strategic initiatives, community missions, liturgical celebrations, and fiscal programs.</p>
        </div>
        @can('manageProjects', $commission)
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddProjectModal()">
            <span>+ New Project</span>
        </button>
        @endcan
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Project Title &amp; Scope</th>
                    <th>Status</th>
                    <th>Lead Officer</th>
                    <th>Timeline</th>
                    <th>Budget</th>
                    @can('manageProjects', $commission)
                    <th style="text-align:right;">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($commission->projects as $proj)
                <tr>
                    <td style="max-width:280px;">
                        <strong style="font-size:13px;color:#0f172a;display:block;">{{ $proj->title }}</strong>
                        <span style="font-size:11.5px;color:#64748b;line-height:1.4;">{{ Str::limit($proj->description, 100) }}</span>
                    </td>
                    <td>
                        <span class="status-pill status-{{ $proj->status }}">{{ $proj->status_label }}</span>
                    </td>
                    <td>
                        @if($proj->leadUser)
                            <span style="font-size:12px;font-weight:600;color:#1e293b;">{{ $proj->leadUser->name }}</span>
                        @else
                            <span style="font-size:11.5px;color:#94a3b8;">Unassigned</span>
                        @endif
                    </td>
                    <td style="font-size:11.5px;color:#64748b;">
                        <div>Start: {{ $proj->start_date ? $proj->start_date->format('M d, Y') : '—' }}</div>
                        <div>End: {{ $proj->end_date ? $proj->end_date->format('M d, Y') : 'Ongoing' }}</div>
                    </td>
                    <td>
                        @if($proj->budget)
                            <strong style="font-size:12.5px;color:#0f172a;">₱{{ number_format($proj->budget, 2) }}</strong>
                        @else
                            <span style="font-size:11px;color:#94a3b8;">—</span>
                        @endif
                    </td>
                    @can('manageProjects', $commission)
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:6px;">
                            <button type="button" class="btn-icon" title="Edit Project" onclick="openEditProjectModal({{ json_encode($proj) }})">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                            </button>
                            <form method="POST" action="{{ $isCoordinatorWorkspace ? route('commission.projects.destroy', $proj->id) : route('admin.commissions.projects.destroy', [$commission, $proj->id]) }}" onsubmit="return confirm('Delete this project?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Delete Project" style="color:#b91c1c;">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">No projects registered for this commission.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ==================== TAB 6: DOCUMENTS ==================== -->
@if($currentTab === 'documents')
<div class="content-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div>
            <h3 class="card-title" style="margin:0;">Document &amp; Minutes Repository</h3>
            <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Official meeting minutes, guidelines, financial statements, and reports.</p>
        </div>
        @can('manageDocuments', $commission)
        <button type="button" class="btn btn-primary btn-sm" onclick="openUploadDocModal()">
            <span>+ Upload Document</span>
        </button>
        @endcan
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Document</th>
                    <th>Category</th>
                    <th>Size</th>
                    <th>Uploaded By</th>
                    <th>Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($commission->documents as $doc)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <span style="font-size:22px;">{{ $doc->file_icon }}</span>
                            <div>
                                <strong style="font-size:12.5px;color:#0f172a;">{{ $doc->title }}</strong>
                                <span style="font-size:11px;color:#64748b;display:block;">{{ $doc->file_name }}</span>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="status-pill" style="background:#f1f5f9;color:#334155;border:1px solid #e2e8f0;">{{ $doc->category }}</span>
                    </td>
                    <td style="font-size:11.5px;color:#64748b;">{{ $doc->formatted_file_size }}</td>
                    <td style="font-size:12px;color:#1e293b;">{{ $doc->uploader->name ?? 'System' }}</td>
                    <td style="font-size:11.5px;color:#64748b;">{{ $doc->created_at->format('M d, Y') }}</td>
                    <td style="text-align:right;">
                        <div style="display:flex;justify-content:flex-end;gap:6px;">
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-outline btn-sm" style="padding:4px 8px;font-size:11px;">Download</a>
                            @can('manageDocuments', $commission)
                            <form method="POST" action="{{ $isCoordinatorWorkspace ? route('commission.documents.destroy', $doc->id) : route('admin.commissions.documents.destroy', [$commission, $doc->id]) }}" onsubmit="return confirm('Delete this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-icon" title="Delete Document" style="color:#b91c1c;">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:32px;color:#94a3b8;">No documents uploaded in this repository yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- ==================== TAB 7: AUDIT LOGS ==================== -->
@if($currentTab === 'audit-logs')
<div class="content-card">
    <div style="margin-bottom:16px;">
        <h3 class="card-title" style="margin:0;">Immutable Commission Audit Log</h3>
        <p style="font-size:12px;color:#64748b;margin:3px 0 0;">Chronological, tamper-evident security record of actions executed in this commission context.</p>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Actor</th>
                    <th>Action</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @forelse($auditLogs as $log)
                <tr>
                    <td style="font-size:11.5px;color:#64748b;white-space:nowrap;">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td style="font-size:12px;font-weight:600;color:#0f172a;">{{ $log->user->name ?? 'System' }}</td>
                    <td>
                        <span class="commission-code-pill" style="font-size:8.5px;">{{ $log->action }}</span>
                    </td>
                    <td style="font-size:12px;color:#334155;">{{ $log->description }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:32px;color:#94a3b8;">No audit entries recorded for this commission yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- MODAL: ADD MEMBER -->
<div class="modal-backdrop" id="addMemberModal" style="display:none;" onclick="if(event.target===this) closeAddMemberModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add Member to {{ $commission->name }}</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddMemberModal()">&times;</button>
        </div>
        <form method="POST" action="{{ $addMemberAction }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Select Registered User <span class="req">*</span></label>
                    <select name="user_id" required class="form-select">
                        <option value="">-- Choose User --</option>
                        @foreach($availableUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Position / Role Title <span class="req">*</span></label>
                    <input type="text" name="position" required placeholder="e.g. Member, Liturgical Lead, Catechist" class="form-input">
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_coordinator" id="add_member_is_coordinator" value="1" onchange="if(this.checked){document.getElementById('add_member_is_officer').checked=true;}">
                        <span style="font-weight:600;color:#062f78;">Designate as Head Commission Coordinator</span>
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_officer" id="add_member_is_officer" value="1">
                        <span>Designate as Commission Officer (Executive Board)</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes / Remarks</label>
                    <textarea name="notes" rows="2" placeholder="Optional notes regarding assignment or responsibilities..." class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddMemberModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Member</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT MEMBER -->
<div class="modal-backdrop" id="editMemberModal" style="display:none;" onclick="if(event.target===this) closeEditMemberModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Update Member: <span id="edit_member_name"></span></h3>
            <button type="button" class="modal-close-btn" onclick="closeEditMemberModal()">&times;</button>
        </div>
        <form method="POST" id="editMemberForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Position / Title <span class="req">*</span></label>
                    <input type="text" name="position" id="edit_member_position" required class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Status <span class="req">*</span></label>
                        <select name="status" id="edit_member_status" class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_coordinator" id="edit_member_is_coordinator" value="1" onchange="if(this.checked){document.getElementById('edit_member_is_officer').checked=true;}">
                        <span style="font-weight:600;color:#062f78;">Designate as Head Commission Coordinator</span>
                    </label>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_officer" id="edit_member_is_officer" value="1">
                        <span>Designate as Officer</span>
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="edit_member_notes" rows="2" class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditMemberModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ADD PROJECT -->
<div class="modal-backdrop" id="addProjectModal" style="display:none;" onclick="if(event.target===this) closeAddProjectModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>New Commission Project</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddProjectModal()">&times;</button>
        </div>
        <form method="POST" action="{{ $addProjectAction }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Project Title <span class="req">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. 2026 Parish Catechetical Congress" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Description &amp; Objectives</label>
                    <textarea name="description" rows="3" placeholder="Describe the apostolic goals, target participants, and venue..." class="form-textarea"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Status <span class="req">*</span></label>
                        <select name="status" required class="form-select">
                            <option value="planning">Planning</option>
                            <option value="ongoing" selected>In Progress (Ongoing)</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Budget (PHP)</label>
                        <input type="number" step="0.01" min="0" name="budget" placeholder="e.g. 25000" class="form-input">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Target Completion Date</label>
                        <input type="date" name="end_date" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Lead Officer</label>
                    <select name="lead_user_id" class="form-select">
                        <option value="">-- None / Select Officer --</option>
                        @foreach($commission->members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->pivot->position ?? 'Member' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddProjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Project</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT PROJECT -->
<div class="modal-backdrop" id="editProjectModal" style="display:none;" onclick="if(event.target===this) closeEditProjectModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Project</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditProjectModal()">&times;</button>
        </div>
        <form method="POST" id="editProjectForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Project Title <span class="req">*</span></label>
                    <input type="text" name="title" id="edit_project_title" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Description &amp; Objectives</label>
                    <textarea name="description" id="edit_project_description" rows="3" class="form-textarea"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Status <span class="req">*</span></label>
                        <select name="status" id="edit_project_status" required class="form-select">
                            <option value="planning">Planning</option>
                            <option value="ongoing">In Progress (Ongoing)</option>
                            <option value="completed">Completed</option>
                            <option value="on_hold">On Hold</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Budget (PHP)</label>
                        <input type="number" step="0.01" min="0" name="budget" id="edit_project_budget" class="form-input">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="edit_project_start_date" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Target Completion Date</label>
                        <input type="date" name="end_date" id="edit_project_end_date" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Project Lead Officer</label>
                    <select name="lead_user_id" id="edit_project_lead_user_id" class="form-select">
                        <option value="">-- None / Select Officer --</option>
                        @foreach($commission->members as $m)
                            <option value="{{ $m->id }}">{{ $m->name }} ({{ $m->pivot->position ?? 'Member' }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditProjectModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: UPLOAD DOCUMENT -->
<div class="modal-backdrop" id="uploadDocModal" style="display:none;" onclick="if(event.target===this) closeUploadDocModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Upload Commission Document</h3>
            <button type="button" class="modal-close-btn" onclick="closeUploadDocModal()">&times;</button>
        </div>
        <form method="POST" action="{{ $addDocumentAction }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Document Title <span class="req">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Minutes of Meeting - September 2026" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Category <span class="req">*</span></label>
                    <select name="category" required class="form-select">
                        <option value="Minutes">Minutes of Meeting</option>
                        <option value="Guidelines">Guidelines &amp; Policies</option>
                        <option value="Financial">Financial Statement / Budget</option>
                        <option value="Report">Pastoral Report</option>
                        <option value="Proposal">Project Proposal</option>
                        <option value="General" selected>General Resource</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">File Attachment <span class="req">*</span></label>
                    <input type="file" name="file" required class="form-input" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png">
                    <small class="form-hint">Accepted formats: PDF, Word, Excel, PowerPoint, Images (Max 20MB)</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeUploadDocModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Upload File</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: ADD MINISTRY -->
<div class="modal-backdrop" id="addMinistryModal" style="display:none;" onclick="if(event.target===this) closeAddMinistryModal()">
    <div class="modal-box" style="max-width: 580px;">
        <div class="modal-header">
            <h3>Add Ministry to {{ $commission->name }}</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddMinistryModal()">&times;</button>
        </div>
        <form method="POST" action="{{ $isCoordinatorWorkspace ? route('commission.ministries.store') : route('admin.commissions.ministries.store', $commission) }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Ministry Name <span class="req">*</span></label>
                    <input type="text" name="name" required class="form-input" placeholder="e.g. Altar Servers Guild">
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" value="{{ $commission->name }}" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Operational Status <span class="req">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Directory Visibility <span class="req">*</span></label>
                        <select name="is_public" class="form-select" required>
                            <option value="1" selected>Public (Website)</option>
                            <option value="0">Private (Internal)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description <span class="req">*</span></label>
                    <textarea name="description" rows="2" required class="form-textarea" placeholder="Brief summary of this ministry's mission and purpose."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Regular Schedule (Optional)</label>
                        <input type="text" name="meeting_schedule" class="form-input" placeholder="e.g. Every Saturday • 9:00 AM">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Venue / Meeting Location (Optional)</label>
                        <input type="text" name="meeting_location" class="form-input" placeholder="e.g. Shrine Sacristy">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Coordinator Name (Optional)</label>
                        <input type="text" name="coordinator_name" class="form-input" placeholder="Coordinator Name">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Contact Mobile (Optional)</label>
                        <input type="text" name="coordinator_phone" class="form-input" placeholder="0917-000-0000">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:0;">
                    <label style="display:flex;align-items:center;gap:8px;font-size:12px;color:#334155;cursor:pointer;">
                        <input type="checkbox" name="is_accepting_members" value="1" checked>
                        <span>Currently accepting new members</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddMinistryModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Ministry</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.breadcrumbs { display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #64748b; margin-bottom: 6px; }
.breadcrumbs a { color: #64748b; text-decoration: none; }
.breadcrumbs a:hover { color: #062f78; }
.crumb-separator { opacity: 0.5; }
.crumb-current { color: #062f78; font-weight: 600; }

.header-icon-box {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: linear-gradient(135deg, #062f78 0%, #0d4bb8 100%);
    color: #f7d27e;
    display: grid;
    place-items: center;
    flex-shrink: 0;
    box-shadow: 0 4px 10px rgba(6, 47, 120, 0.2);
}
.commission-code-pill {
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #062f78;
    background: rgba(6, 47, 120, 0.08);
    padding: 2.5px 8px;
    border-radius: 6px;
}
.status-pill { font-size: 9.5px; font-weight: 700; padding: 2.5px 8px; border-radius: 12px; }
.status-active, .status-completed { background: #dcfce7; color: #15803d; }
.status-inactive, .status-cancelled { background: #fee2e2; color: #b91c1c; }
.status-planning { background: #fef9c3; color: #854d0e; }
.status-ongoing { background: #e0e7ff; color: #4338ca; }
.status-on_hold { background: #f1f5f9; color: #475569; }

/* Tabs Bar */
.tabs-nav-bar {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #e2e8f0;
    margin: 18px 0 20px;
    overflow-x: auto;
    padding-bottom: 1px;
}
.tab-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    font-size: 12.5px;
    font-weight: 600;
    color: #64748b;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    white-space: nowrap;
    transition: all 0.15s ease;
}
.tab-link:hover { color: #062f78; }
.tab-link.tab-active {
    color: #062f78;
    border-bottom-color: #062f78;
}

/* Overview Layout */
.overview-layout {
    display: grid;
    grid-template-columns: 1fr 340px;
    gap: 18px;
}
@media (max-width: 900px) {
    .overview-layout { grid-template-columns: 1fr; }
}
.content-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 18px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.card-title { font-size: 14.5px; font-weight: 700; color: #0f172a; margin: 0 0 6px; }
.view-all-link { font-size: 11.5px; color: #062f78; font-weight: 600; text-decoration: none; }
.view-all-link:hover { text-decoration: underline; }

.kpi-mini-row {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-top: 16px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.kpi-mini-box {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    padding: 10px 12px;
    text-align: center;
}
.kpi-mini-box strong { display: block; font-size: 18px; font-weight: 700; color: #062f78; }
.kpi-mini-box span { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; }

.projects-list-compact { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
.proj-compact-item {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    padding: 12px 14px;
}

.coord-avatar-pill {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e2e8f0;
    display: grid;
    place-items: center;
    overflow: hidden;
    flex-shrink: 0;
}
.coord-img { width: 100%; height: 100%; object-fit: cover; }
.coord-initials { font-size: 11px; font-weight: 700; color: #475569; }

/* Table Wrap */
.table-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
th { text-align: left; padding: 12px 14px; background: #f8fafc; color: #64748b; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; border-bottom: 1px solid #e2e8f0; }
td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
tr:hover td { background: #fafcfe; }

/* Officers Grid */
.officers-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.officer-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
}
.officer-position-pill {
    font-size: 9.5px;
    font-weight: 800;
    color: #4338ca;
    background: #e0e7ff;
    padding: 2px 7px;
    border-radius: 6px;
    text-transform: uppercase;
}

/* Ministries Cards Grid */
.ministries-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 14px;
}
.ministry-detail-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
}

.btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    display: grid;
    place-items: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-icon:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

/* Modals */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
}
.modal-box {
    background: #ffffff;
    border-radius: 14px;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: modalSlide 0.2s ease-out;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
.modal-close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1; }
.modal-body { padding: 18px 20px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.form-group { margin-bottom: 14px; }
.form-row { display: flex; gap: 12px; }
.form-label { display: block; font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 5px; }
.form-label .req { color: #dc2626; }
.form-input, .form-select, .form-textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12.5px;
    color: #1e293b;
    outline: none;
    transition: border-color 0.15s ease;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: #062f78;
    box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.1);
}
.form-hint { display: block; font-size: 10.5px; color: #64748b; margin-top: 4px; }
</style>
@endpush

@push('scripts')
<script>
// Member modals
function openAddMemberModal(isOfficer = false, isCoordinator = false) {
    document.getElementById('add_member_is_officer').checked = Boolean(isOfficer || isCoordinator);
    const coordBox = document.getElementById('add_member_is_coordinator');
    if (coordBox) coordBox.checked = Boolean(isCoordinator);
    document.getElementById('addMemberModal').style.display = 'flex';
}
function closeAddMemberModal() {
    document.getElementById('addMemberModal').style.display = 'none';
}

function openEditMemberModal(membership, memberName) {
    const isWorkspace = {{ $isCoordinatorWorkspace ? 'true' : 'false' }};
    const form = document.getElementById('editMemberForm');
    if (isWorkspace) {
        form.action = `/commission/members/${membership.id}`;
    } else {
        form.action = `/admin/commissions/{{ $commission->id }}/members/${membership.id}`;
    }

    const isHead = (membership.role === 'head' || membership.role === 'coordinator' || {{ (int)($commission->head_user_id ?? 0) }} === membership.user_id || (membership.position && membership.position.toLowerCase().includes('coordinator')));

    document.getElementById('edit_member_name').innerText = memberName;
    document.getElementById('edit_member_position').value = membership.position || '';
    document.getElementById('edit_member_status').value = membership.status || 'active';
    document.getElementById('edit_member_is_officer').checked = Boolean(membership.is_officer || isHead);
    const coordBox = document.getElementById('edit_member_is_coordinator');
    if (coordBox) coordBox.checked = Boolean(isHead);
    document.getElementById('edit_member_notes').value = membership.notes || '';

    document.getElementById('editMemberModal').style.display = 'flex';
}
function closeEditMemberModal() {
    document.getElementById('editMemberModal').style.display = 'none';
}

// Project modals
function openAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'flex';
}
function closeAddProjectModal() {
    document.getElementById('addProjectModal').style.display = 'none';
}

function openEditProjectModal(project) {
    const isWorkspace = {{ $isCoordinatorWorkspace ? 'true' : 'false' }};
    const form = document.getElementById('editProjectForm');
    if (isWorkspace) {
        form.action = `/commission/projects/${project.id}`;
    } else {
        form.action = `/admin/commissions/{{ $commission->id }}/projects/${project.id}`;
    }

    document.getElementById('edit_project_title').value = project.title || '';
    document.getElementById('edit_project_description').value = project.description || '';
    document.getElementById('edit_project_status').value = project.status || 'ongoing';
    document.getElementById('edit_project_budget').value = project.budget || '';
    document.getElementById('edit_project_start_date').value = project.start_date ? project.start_date.substring(0, 10) : '';
    document.getElementById('edit_project_end_date').value = project.end_date ? project.end_date.substring(0, 10) : '';
    document.getElementById('edit_project_lead_user_id').value = project.lead_user_id || '';

    document.getElementById('editProjectModal').style.display = 'flex';
}
function closeEditProjectModal() {
    document.getElementById('editProjectModal').style.display = 'none';
}

// Document modal
function openUploadDocModal() {
    document.getElementById('uploadDocModal').style.display = 'flex';
}
function closeUploadDocModal() {
    document.getElementById('uploadDocModal').style.display = 'none';
}

// Ministry modal
function openAddMinistryModal() {
    document.getElementById('addMinistryModal').style.display = 'flex';
}
function closeAddMinistryModal() {
    document.getElementById('addMinistryModal').style.display = 'none';
}
</script>
@endpush

