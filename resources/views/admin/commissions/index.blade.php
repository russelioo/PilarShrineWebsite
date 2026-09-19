@extends('layouts.admin')
@section('title', 'Pastoral Commissions Management')

@section('content')
<div class="page-header">
    <div>
        <h2>Diocesan &amp; Pastoral Commissions</h2>
        <p class="page-description">Manage the 8 official parish pastoral commissions, authenticated coordinator accounts, officers, assigned ministries, and apostolic initiatives.</p>
    </div>
    @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
    <div class="actions">
        <button type="button" class="btn btn-secondary" onclick="openCreateAccountModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" y1="8" x2="19" y2="14"/><line x1="22" y1="11" x2="16" y2="11"/></svg>
            <span>+ Create Commission Account</span>
        </button>
        <button type="button" class="btn btn-primary" onclick="openAddCommissionModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Add Commission</span>
        </button>
    </div>
    @endif
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

<!-- Real Dynamic KPI Counters -->
<div class="summary-grid">
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(6, 47, 120, 0.08); color: #062f78;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['total_commissions'] }}</strong>
            <span>Total Commissions</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['active_commissions'] }}</strong>
            <span>Active Commissions</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(216, 170, 60, 0.12); color: #b48520;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['total_members'] }}</strong>
            <span>Commission Members</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(14, 165, 233, 0.12); color: #0284c7;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><circle cx="12" cy="9" r="2"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['active_officers'] ?? $kpi['total_officers'] }}</strong>
            <span>Active Officers</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(99, 102, 241, 0.1); color: #4f46e5;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['active_projects'] }}</strong>
            <span>Active Projects</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(14, 165, 233, 0.1); color: #0284c7;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['total_documents'] }}</strong>
            <span>Documents &amp; Reports</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(168, 85, 247, 0.1); color: #9333ea;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['total_ministries'] }}</strong>
            <span>Assigned Ministries</span>
        </div>
    </article>
</div>

<!-- Search and Filter Bar -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.commissions.index') }}" class="filter-form">
        <div class="search-input-wrap">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search commission by title, code, or focus area..." class="search-input">
        </div>
        <div class="filter-select-wrap">
            <select name="status" onchange="this.form.submit()" class="filter-select">
                <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Statuses</option>
                <option value="active" {{ request('status', 'all') === 'active' ? 'selected' : '' }}>Active Only</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
            </select>
        </div>
        <button type="submit" class="btn btn-outline">Filter</button>
        @if(request('search') || (request('status') && request('status') !== 'all'))
            <a href="{{ route('admin.commissions.index') }}" class="btn btn-link">Clear</a>
        @endif
    </form>
</div>

<!-- Commissions Grid View -->
<div class="commissions-grid">
    @forelse($commissions as $commission)
    <div class="commission-card {{ ! $commission->is_active ? 'commission-inactive' : '' }}">
        <div class="commission-card-top">
            <div class="commission-badge-row">
                <span class="commission-code-pill">{{ $commission->code ?: 'COMM' }}</span>
                <span class="status-pill {{ $commission->is_active ? 'status-active' : 'status-inactive' }}">
                    {{ $commission->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <div class="commission-title-wrap">
                <div class="commission-avatar-box">
                    @if($commission->icon === 'cross')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="2" x2="12" y2="22"/><line x1="5" y1="8" x2="19" y2="8"/></svg>
                    @elseif($commission->icon === 'book-open')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    @elseif($commission->icon === 'heart-handshake')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    @elseif($commission->icon === 'building')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><line x1="9" y1="22" x2="9" y2="22.01"/><line x1="15" y1="22" x2="15" y2="22.01"/><line x1="9" y1="6" x2="9" y2="6.01"/><line x1="15" y1="6" x2="15" y2="6.01"/><line x1="9" y1="10" x2="9" y2="10.01"/><line x1="15" y1="10" x2="15" y2="10.01"/><line x1="9" y1="14" x2="9" y2="14.01"/><line x1="15" y1="14" x2="15" y2="14.01"/><line x1="9" y1="18" x2="9" y2="18.01"/><line x1="15" y1="18" x2="15" y2="18.01"/></svg>
                    @elseif($commission->icon === 'users')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    @elseif($commission->icon === 'home')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    @elseif($commission->icon === 'spark')
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    @endif
                </div>
                <div>
                    <h3 class="commission-name">
                        <a href="{{ route('admin.commissions.show', $commission) }}">{{ $commission->name }}</a>
                    </h3>
                    <p class="commission-desc">{{ Str::limit($commission->description ?: 'Parish pastoral commission serving the Christian faithful of Our Lady of the Pillar Shrine.', 110) }}</p>
                </div>
            </div>
        </div>

        <!-- Coordinator Info Card -->
        @php $coordUser = $commission->coordinator ?? $commission->coordinator_user; @endphp
        <div class="commission-coordinator-box">
            @if($coordUser)
            <div class="coord-avatar-pill">
                @if($coordUser->avatar_url)
                    <img src="{{ $coordUser->avatar_url }}" alt="{{ $coordUser->name }}" class="coord-img">
                @else
                    <span class="coord-initials">{{ $coordUser->initials }}</span>
                @endif
            </div>
            <div class="coord-meta">
                <span class="coord-label">Coordinator</span>
                <span class="coord-name">{{ $coordUser->name }}</span>
                <span class="coord-email">{{ $coordUser->email }}</span>
            </div>
            @else
            <div class="coord-empty">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>No coordinator assigned</span>
            </div>
            @endif
        </div>

        <!-- Metric Badges Row -->
        <div class="commission-metrics">
            <div class="metric-item" title="Members in roster">
                <strong>{{ $commission->members->count() }}</strong>
                <span>Members</span>
            </div>
            <div class="metric-item" title="Officers in leadership">
                <strong>{{ $commission->officers->count() }}</strong>
                <span>Officers</span>
            </div>
            <div class="metric-item" title="Ministries under this commission">
                <strong>{{ $commission->ministries->count() }}</strong>
                <span>Ministries</span>
            </div>
            <div class="metric-item" title="Active &amp; Completed projects">
                <strong>{{ $commission->projects->count() }}</strong>
                <span>Projects</span>
            </div>
            @if($commission->official_population)
            <div class="metric-item" title="Official total population / membership count" style="color:#062f78;">
                <strong>{{ number_format($commission->official_population) }}</strong>
                <span>Population</span>
            </div>
            @endif
        </div>

        <!-- Footer Actions -->
        <div class="commission-card-footer">
            <a href="{{ route('admin.commissions.show', $commission) }}" class="btn btn-primary btn-sm btn-block">
                <span>Manage Commission Workspace</span>
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            </a>
            @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin'], true))
            <div class="card-action-row">
                <button type="button" class="btn-icon" title="Edit Commission" onclick="openEditCommissionModal({{ json_encode($commission) }})">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.commissions.status', $commission) }}" style="display:inline;" onsubmit="return confirm('Change status for this commission?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-icon" title="{{ $commission->is_active ? 'Deactivate' : 'Activate' }}">
                        @if($commission->is_active)
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="color:#d97706;"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                        @else
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" style="color:#16a34a;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        @endif
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="empty-state">
        <svg viewBox="0 0 24 24" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        <h3>No Commissions Found</h3>
        <p>No pastoral commissions match your search criteria. You can clear the search filter or initialize the official 8 commissions.</p>
        <a href="{{ route('admin.commissions.index') }}" class="btn btn-outline" style="margin-top: 12px;">Reset Filters</a>
    </div>
    @endforelse
</div>

<!-- MODAL: ADD COMMISSION -->
<div class="modal-backdrop" id="addCommissionModal" style="display:none;" onclick="if(event.target===this) closeAddCommissionModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Add New Pastoral Commission</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddCommissionModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.commissions.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Commission Name <span class="req">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Commission on Worship" class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Code / Acronym</label>
                        <input type="text" name="code" placeholder="e.g. WORSHIP" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Icon Motif</label>
                        <select name="icon" class="form-select">
                            <option value="cross">Cross / Altar</option>
                            <option value="book-open">Book / Bible</option>
                            <option value="heart-handshake">Heart / Caritas</option>
                            <option value="building">Building / Temporalities</option>
                            <option value="users">People / Ecclesial Communities</option>
                            <option value="home">Home / Family</option>
                            <option value="spark">Spark / Youth</option>
                            <option value="shield">Shield / Clergy</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Coordinator</label>
                    <select name="head_user_id" class="form-select">
                        <option value="">-- Appoint Later / None --</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                    <small class="form-hint">Selected user will receive head coordinator access for this commission.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Commission Members / Population</label>
                    <input type="number" name="official_population" min="0" placeholder="e.g. 45" class="form-input">
                    <small class="form-hint">Official headcount of all members under this commission.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Description &amp; Pastoral Focus</label>
                    <textarea name="description" rows="3" placeholder="Outline the primary apostolic mission and liturgical scope..." class="form-textarea"></textarea>
                </div>

                {{-- Optional: Shrine Ministries under this Commission --}}
                <div class="form-group" style="margin-top:4px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label class="form-label" style="margin:0;">Shrine Ministries Under This Commission <span style="font-size:11px;color:#64748b;font-weight:400;">(Optional)</span></label>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleMinistrySection('add')" id="addMinistryToggleBtn">
                            + Add Ministry Names
                        </button>
                    </div>
                    <div id="addMinistrySection" style="display:none;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-top:4px;">
                            <p style="font-size:11.5px;color:#64748b;margin:0 0 10px;">Add ministry names, head coordinators, and official membership population. Leave empty if not needed.</p>
                            <div id="addMinistryRows">
                                {{-- Dynamic rows injected by JS --}}
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;" onclick="addMinistryRow('add')">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                + Add Ministry Row
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddCommissionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Commission</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: CREATE DEDICATED COMMISSION ACCOUNT -->
<div class="modal-backdrop" id="createAccountModal" style="display:none;" onclick="if(event.target===this) closeCreateAccountModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Create Dedicated Commission Account</h3>
            <button type="button" class="modal-close-btn" onclick="closeCreateAccountModal()">&times;</button>
        </div>
        <form method="POST" id="createAccountForm" action="">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Target Commission <span class="req">*</span></label>
                    <select id="accountCommissionSelect" class="form-select" required onchange="updateAccountFormAction(this.value)">
                        <option value="">-- Choose Commission --</option>
                        @foreach($commissions as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Officer / Member Full Name <span class="req">*</span></label>
                        <input type="text" name="name" required placeholder="e.g. Bro. Roberto Hernandez" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Position / Title <span class="req">*</span></label>
                        <input type="text" name="position" required placeholder="e.g. Commission Coordinator" class="form-input">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Official Email <span class="req">*</span></label>
                        <input type="email" name="email" required placeholder="e.g. worship.coord@pilarshrine.test" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Initial Password <span class="req">*</span></label>
                        <input type="password" name="password" required minlength="8" placeholder="At least 8 characters" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Contact Phone</label>
                    <input type="text" name="phone" placeholder="e.g. 0917-123-4567" class="form-input">
                </div>
                <div class="form-group" style="margin-top:10px;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_officer" value="1" checked>
                        <span>Designate as Commission Officer (Executive access within workspace)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeCreateAccountModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT COMMISSION -->
<div class="modal-backdrop" id="editCommissionModal" style="display:none;" onclick="if(event.target===this) closeEditCommissionModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Commission Details</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditCommissionModal()">&times;</button>
        </div>
        <form method="POST" id="editCommissionForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Commission Name <span class="req">*</span></label>
                    <input type="text" name="name" id="edit_name" required class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Code / Acronym</label>
                        <input type="text" name="code" id="edit_code" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Icon Motif</label>
                        <select name="icon" id="edit_icon" class="form-select">
                            <option value="cross">Cross / Altar</option>
                            <option value="book-open">Book / Bible</option>
                            <option value="heart-handshake">Heart / Caritas</option>
                            <option value="building">Building / Temporalities</option>
                            <option value="users">People / Ecclesial Communities</option>
                            <option value="home">Home / Family</option>
                            <option value="spark">Spark / Youth</option>
                            <option value="shield">Shield / Clergy</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Assigned Coordinator</label>
                    <select name="head_user_id" id="edit_head_user_id" class="form-select">
                        <option value="">-- None / Unassigned --</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Commission Members / Population</label>
                    <input type="number" name="official_population" id="edit_official_population" min="0" placeholder="e.g. 45" class="form-input">
                    <small class="form-hint">Official headcount of all members under this commission.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Description &amp; Scope</label>
                    <textarea name="description" id="edit_description" rows="3" class="form-textarea"></textarea>
                </div>
                <div class="form-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:12.5px;">
                        <input type="checkbox" name="is_active" id="edit_is_active" value="1">
                        <span>Commission is Active in Parish</span>
                    </label>
                </div>

                {{-- Optional: Add more Shrine Ministries --}}
                <div class="form-group" style="margin-top:4px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                        <label class="form-label" style="margin:0;">Shrine Ministries <span style="font-size:11px;color:#64748b;font-weight:400;">(Optional — add new rows only)</span></label>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleMinistrySection('edit')" id="editMinistryToggleBtn">
                            + Add Ministry Names
                        </button>
                    </div>
                    {{-- Read-only list of existing ministries --}}
                    <div id="editExistingMinistries" style="display:none;margin-bottom:8px;"></div>
                    <div id="editMinistrySection" style="display:none;">
                        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:12px 14px;margin-top:4px;">
                            <p style="font-size:11.5px;color:#64748b;margin:0 0 10px;">New ministry rows entered here will be saved under this commission. Existing ministries are managed from the Ministries tab.</p>
                            <div id="editMinistryRows">
                                {{-- Dynamic rows injected by JS --}}
                            </div>
                            <button type="button" class="btn btn-outline btn-sm" style="margin-top:8px;" onclick="addMinistryRow('edit')">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                + Add Ministry Row
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditCommissionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.breadcrumbs {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11.5px;
    color: var(--muted, #64748b);
    margin-bottom: 6px;
}
.crumb-separator { opacity: 0.5; }
.crumb-current { color: #062f78; font-weight: 600; }
.page-description { font-size: 12.5px; color: #64748b; margin: 4px 0 0; }

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
    gap: 12px;
    margin: 18px 0 20px;
}
.stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.stat-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
}
.stat-icon-wrap {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.stat-box strong {
    display: block;
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.stat-box span {
    font-size: 11px;
    color: #64748b;
    font-weight: 500;
}

.filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 22px;
}
.filter-form {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}
.search-input-wrap {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    padding: 7px 12px;
    flex: 1;
    min-width: 240px;
}
.search-input {
    border: none;
    background: transparent;
    font-size: 12.5px;
    color: #1e293b;
    width: 100%;
    outline: none;
}
.filter-select {
    padding: 7px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    background: #ffffff;
    font-size: 12px;
    color: #1e293b;
}

.commissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(330px, 1fr));
    gap: 18px;
}
.commission-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 18px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    box-shadow: 0 2px 6px rgba(0,0,0,0.03);
}
.commission-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(6, 47, 120, 0.08);
    border-color: #cbd5e1;
}
.commission-card.commission-inactive {
    opacity: 0.75;
    background: #fcfcfc;
}
.commission-badge-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}
.commission-code-pill {
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    color: #062f78;
    background: rgba(6, 47, 120, 0.08);
    padding: 3px 8px;
    border-radius: 6px;
}
.status-pill {
    font-size: 9.5px;
    font-weight: 700;
    padding: 2.5px 8px;
    border-radius: 12px;
}
.status-active { background: #dcfce7; color: #15803d; }
.status-inactive { background: #fee2e2; color: #b91c1c; }

.commission-title-wrap {
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.commission-avatar-box {
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
.commission-name {
    font-size: 15px;
    font-weight: 700;
    line-height: 1.3;
    margin: 0 0 4px;
}
.commission-name a {
    color: #0f172a;
    text-decoration: none;
    transition: color 0.15s ease;
}
.commission-name a:hover { color: #062f78; }
.commission-desc {
    font-size: 11.5px;
    color: #64748b;
    line-height: 1.45;
    margin: 0;
}

.commission-coordinator-box {
    margin: 14px 0 12px;
    padding: 10px 12px;
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
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
.coord-meta { display: flex; flex-direction: column; min-width: 0; }
.coord-label { font-size: 8.5px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; }
.coord-name { font-size: 12px; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.coord-email { font-size: 10.5px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.coord-empty { display: flex; align-items: center; gap: 6px; font-size: 11px; color: #94a3b8; font-style: italic; }

.commission-metrics {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
    padding: 10px 0;
    border-top: 1px solid #f1f5f9;
    border-bottom: 1px solid #f1f5f9;
    margin-bottom: 14px;
    text-align: center;
}
.metric-item strong { display: block; font-size: 14px; font-weight: 700; color: #0f172a; }
.metric-item span { font-size: 9.5px; color: #94a3b8; text-transform: uppercase; font-weight: 600; }

.commission-card-footer {
    display: flex;
    gap: 8px;
    align-items: center;
}
.btn-block { flex: 1; justify-content: center; }
.card-action-row { display: flex; gap: 4px; }
.btn-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    display: grid;
    place-items: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-icon:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}

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
    max-width: 540px;
    width: 100%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: modalSlide 0.2s ease-out;
}
@keyframes modalSlide {
    from { opacity: 0; transform: translateY(12px) scale(0.98); }
    to { opacity: 1; transform: translateY(0) scale(1); }
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
.modal-body { padding: 18px 20px; max-height: 75vh; overflow-y: auto; }
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
.empty-state {
    grid-column: 1 / -1;
    text-align: center;
    padding: 48px 20px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 14px;
    color: #64748b;
}
.empty-state h3 { margin: 12px 0 6px; font-size: 16px; color: #0f172a; }
.empty-state p { font-size: 12.5px; max-width: 440px; margin: 0 auto; }
</style>
@endpush

@push('scripts')
<script>
function openAddCommissionModal() {
    document.getElementById('addCommissionModal').style.display = 'flex';
    // Reset ministry section
    document.getElementById('addMinistrySection').style.display = 'none';
    document.getElementById('addMinistryRows').innerHTML = '';
    document.getElementById('addMinistryToggleBtn').textContent = '+ Add Ministry Names';
    _ministryRowCounters.add = 0;
}
function closeAddCommissionModal() {
    document.getElementById('addCommissionModal').style.display = 'none';
}

function openCreateAccountModal() {
    document.getElementById('createAccountModal').style.display = 'flex';
}
function closeCreateAccountModal() {
    document.getElementById('createAccountModal').style.display = 'none';
}
function updateAccountFormAction(commissionId) {
    const form = document.getElementById('createAccountForm');
    if (commissionId) {
        form.action = `/admin/commissions/${commissionId}/account`;
    } else {
        form.action = '';
    }
}

function openEditCommissionModal(commission) {
    const modal = document.getElementById('editCommissionModal');
    const form = document.getElementById('editCommissionForm');
    form.action = `/admin/commissions/${commission.id}`;

    document.getElementById('edit_name').value = commission.name || '';
    document.getElementById('edit_code').value = commission.code || '';
    document.getElementById('edit_icon').value = commission.icon || 'cross';
    document.getElementById('edit_head_user_id').value = commission.head_user_id || '';
    document.getElementById('edit_official_population').value = commission.official_population || '';
    document.getElementById('edit_description').value = commission.description || '';
    document.getElementById('edit_is_active').checked = Boolean(commission.is_active);

    // Reset ministry section
    document.getElementById('editMinistrySection').style.display = 'none';
    document.getElementById('editMinistryRows').innerHTML = '';
    document.getElementById('editMinistryToggleBtn').textContent = '+ Add Ministry Names';

    // Show existing ministries with × delete button
    const existingDiv = document.getElementById('editExistingMinistries');
    existingDiv.innerHTML = '';
    existingDiv.style.display = 'none';
    if (commission.ministries && commission.ministries.length > 0) {
        existingDiv.style.display = 'block';
        let html = '<p style="font-size:11.5px;font-weight:600;color:#334155;margin:0 0 6px;">Existing Ministries:</p>';
        html += '<div id="existingMinistriesList" style="display:flex;flex-direction:column;gap:4px;">';
        commission.ministries.forEach(function(m) {
            html += `<div id="ministry-row-${m.id}" style="background:#f1f5f9;border-radius:6px;padding:6px 10px;font-size:12px;color:#1e293b;display:flex;align-items:center;justify-content:space-between;gap:8px;">
                <span>
                    <strong>${m.name}</strong>`;
            if (m.coordinator_name) html += ` <span style="color:#64748b;">— ${m.coordinator_name}</span>`;
            if (m.official_population) html += ` <span style="color:#64748b;">(${m.official_population} members)</span>`;
            html += `</span>
                <button
                    type="button"
                    onclick="removeCommissionMinistry(${m.id}, this)"
                    title="Remove this ministry"
                    style="flex-shrink:0;width:22px;height:22px;border:1px solid #fca5a5;background:#fee2e2;border-radius:5px;cursor:pointer;color:#dc2626;font-size:14px;line-height:1;display:flex;align-items:center;justify-content:center;">×</button>
            </div>`;
        });
        html += '</div>';
        existingDiv.innerHTML = html;
    }

    modal.style.display = 'flex';
}
function closeEditCommissionModal() {
    document.getElementById('editCommissionModal').style.display = 'none';
}

function removeCommissionMinistry(ministryId, btnEl) {
    if (!confirm('Remove this ministry from the commission? This cannot be undone.')) return;

    const row = document.getElementById('ministry-row-' + ministryId);
    btnEl.disabled = true;
    btnEl.textContent = '…';

    fetch(`/admin/ministries/${ministryId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Content-Type': 'application/json',
        },
    })
    .then(function(res) {
        if (res.ok || res.status === 204 || res.status === 200) {
            if (row) {
                row.style.transition = 'opacity 0.2s';
                row.style.opacity = '0';
                setTimeout(function() { row.remove(); }, 200);
            }
        } else {
            alert('Could not remove ministry. Please try again.');
            btnEl.disabled = false;
            btnEl.textContent = '×';
        }
    })
    .catch(function() {
        alert('Network error. Please try again.');
        btnEl.disabled = false;
        btnEl.textContent = '×';
    });
}

// ---- Shrine Ministry Row Helpers ----

let _ministryRowCounters = { add: 0, edit: 0 };

function toggleMinistrySection(prefix) {
    const section = document.getElementById(prefix + 'MinistrySection');
    const btn = document.getElementById(prefix + 'MinistryToggleBtn');
    const existingDiv = prefix === 'edit' ? document.getElementById('editExistingMinistries') : null;
    const isHidden = section.style.display === 'none';
    section.style.display = isHidden ? 'block' : 'none';
    if (existingDiv) existingDiv.style.display = isHidden ? 'block' : 'none';
    btn.textContent = isHidden ? '− Hide Ministry Section' : '+ Add Ministry Names';
    // Auto-add first row when opening
    if (isHidden && document.getElementById(prefix + 'MinistryRows').children.length === 0) {
        addMinistryRow(prefix);
    }
}

function addMinistryRow(prefix) {
    const container = document.getElementById(prefix + 'MinistryRows');
    const idx = _ministryRowCounters[prefix]++;
    const row = document.createElement('div');
    row.id = prefix + 'MinistryRow_' + idx;
    row.style.cssText = 'display:grid;grid-template-columns:1fr 1fr auto auto;gap:8px;align-items:center;margin-bottom:8px;';
    row.innerHTML = `
        <input type="text"
               name="shrine_ministries[${idx}][name]"
               placeholder="Ministry Name"
               class="form-input"
               style="min-width:0;">
        <input type="text"
               name="shrine_ministries[${idx}][head]"
               placeholder="Head / Coordinator"
               class="form-input"
               style="min-width:0;">
        <input type="number"
               name="shrine_ministries[${idx}][population]"
               placeholder="Population"
               min="0"
               class="form-input"
               style="width:100px;">
        <button type="button"
                onclick="removeMinistryRow('${prefix}MinistryRow_${idx}')"
                title="Remove row"
                style="width:30px;height:30px;border:1px solid #e2e8f0;background:#fff;border-radius:6px;cursor:pointer;color:#dc2626;font-size:16px;display:grid;place-items:center;flex-shrink:0;">×</button>
    `;
    container.appendChild(row);
}

function removeMinistryRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) row.remove();
}
</script>
@endpush

