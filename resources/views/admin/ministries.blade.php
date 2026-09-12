@extends('layouts.admin')
@section('title', 'Manage Parish Ministries')

@section('content')
<div class="page-header">
    <div>
        <h2>Parish Ministries Directory</h2>
        <p class="page-description">Create, update, and manage parish apostolates, schedules, coordinators, and membership acceptance.</p>
    </div>
    <div class="actions">
        <button type="button" class="btn btn-primary btn-add-ministry" onclick="openAddModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ Add New Ministry</span>
        </button>
    </div>
</div>

@if(session('success'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="notice-error">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
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

<!-- Summary Counters -->
<div class="summary-grid">
    <article class="stat-box">
        <strong>{{ $totalCount }}</strong>
        <span>Total Ministries</span>
    </article>
    <article class="stat-box highlight-open">
        <strong>{{ $acceptingCount }}</strong>
        <span>Accepting Members</span>
    </article>
    <article class="stat-box highlight-closed">
        <strong>{{ $closedCount }}</strong>
        <span>Applications Closed</span>
    </article>
    <article class="stat-box highlight-members">
        <strong>{{ $totalMembersCount }}</strong>
        <span>Active Parishioner Members</span>
    </article>
</div>

<!-- Toolbar (Search & Filters) -->
<form class="toolbar" method="GET" action="{{ route('admin.ministries') }}">
    <div class="search-wrap">
        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="search" name="search" value="{{ $search }}" placeholder="Search ministry name, description, coordinator, location...">
    </div>

    <select name="category">
        <option value="">All Categories</option>
        @foreach($categoriesList as $cat)
            <option value="{{ $cat }}" @selected($category === $cat)>{{ $cat }}</option>
        @endforeach
    </select>

    <select name="status">
        <option value="all" @selected($status === 'all')>All Statuses</option>
        <option value="open" @selected($status === 'open')>Accepting Members</option>
        <option value="closed" @selected($status === 'closed')>Closed</option>
    </select>

    <button class="btn btn-primary" type="submit">Filter</button>
    @if(request()->hasAny(['search', 'category', 'status']))
        <a class="btn btn-outline" href="{{ route('admin.ministries') }}">Clear</a>
    @endif
</form>

<!-- Ministries Table -->
<div class="table-wrap">
    <table>
        <thead>
            <tr>
                <th>Ministry</th>
                <th>Category</th>
                <th>Meeting Logistics</th>
                <th>Coordinator</th>
                <th>Members</th>
                <th>Status</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ministries as $min)
                <tr>
                    <td>
                        <div class="min-name-cell">
                            <span class="min-icon-box">{{ $min->icon ?: '✝' }}</span>
                            <div>
                                <strong class="min-title">{{ $min->name }}</strong>
                                <small class="min-slug">{{ $min->slug }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="category-pill">{{ $min->category }}</span>
                    </td>
                    <td>
                        <div class="logistics-cell">
                            <strong>{{ $min->meeting_schedule ?: 'TBA' }}</strong>
                            <small>{{ $min->meeting_location ?: 'Parish Grounds' }}</small>
                        </div>
                    </td>
                    <td>
                        <div class="coord-cell">
                            <strong>{{ $min->coordinator_name ?: 'Parish Office' }}</strong>
                            <small>{{ $min->coordinator_email ?: ($min->coordinator_phone ?: 'No contact specified') }}</small>
                            @if($min->coordinator_user_id)
                                <span class="badge-staff-linked">Staff Account Linked</span>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="members-cell">
                            <span class="pill-active-count">{{ $min->active_members_count }} Active</span>
                            @if($min->pending_requests_count > 0)
                                <a href="{{ route('admin.ministry-requests', ['ministry_id' => $min->id, 'status' => 'pending']) }}" class="pill-pending-count" title="Click to review pending requests">
                                    {{ $min->pending_requests_count }} Pending
                                </a>
                            @endif
                        </div>
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.ministries.toggle', $min) }}" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            @if($min->is_accepting_members)
                                <button type="submit" class="status-btn btn-open" title="Click to close membership applications">
                                    ● Accepting
                                </button>
                            @else
                                <button type="submit" class="status-btn btn-closed" title="Click to open membership applications">
                                    ✕ Closed
                                </button>
                            @endif
                        </form>
                    </td>
                    <td>
                        <div class="actions-cell">
                            <button type="button" class="btn-sm btn-edit" onclick="openEditModal({{ json_encode($min) }})" title="Edit ministry details">
                                Edit
                            </button>
                            <a href="{{ route('admin.ministry-requests', ['ministry_id' => $min->id]) }}" class="btn-sm btn-view-requests" title="View parishioner applications">
                                Requests
                            </a>
                            <button type="button" class="btn-sm btn-delete" onclick="openDeleteModal('{{ $min->id }}', '{{ addslashes($min->name) }}')" title="Delete ministry">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 36px 18px; color: #64748b;">
                        <p style="font-size: 13px; font-weight: 600; margin: 0 0 6px;">No parish ministries found</p>
                        <small>Click the "+ Add New Ministry" button above to register a ministry.</small>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($ministries->hasPages())
    <div class="pagination-wrap">
        {{ $ministries->links() }}
    </div>
@endif

<!-- 1. ADD MINISTRY MODAL -->
<div id="add-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-header">
            <h4>+ Add New Parish Ministry</h4>
            <button type="button" class="modal-close" onclick="closeModal('add-modal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.ministries.store') }}">
            @csrf
            <div class="modal-body modal-scroll">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="add-name">Ministry Name <span class="req">*</span></label>
                        <input type="text" id="add-name" name="name" placeholder="e.g. Altar Servers Ministry" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="add-category">Category <span class="req">*</span></label>
                        <input list="category-suggestions" id="add-category" name="category" placeholder="e.g. Worship & Liturgy" required class="form-input">
                        <datalist id="category-suggestions">
                            @foreach($categoriesList as $cat)
                                <option value="{{ $cat }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="form-group">
                    <label>Ministry Icon / Symbol</label>
                    <div class="icon-picker-wrap">
                        <input type="text" id="add-icon" name="icon" value="✝" class="form-input icon-input" maxlength="10">
                        <span class="icon-picker-label">Quick Pick:</span>
                        <div class="emoji-quick-list">
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '✝')">✝</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '🎵')">🎵</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '✦')">✦</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '♡')">♡</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '📖')">📖</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '📡')">📡</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '👑')">👑</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '🕊️')">🕊️</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '🕯️')">🕯️</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('add-icon', '👥')">👥</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="add-desc">Short Summary / Description <span class="req">*</span></label>
                    <textarea id="add-desc" name="description" rows="2" placeholder="Brief 1-2 sentence overview shown on the parishioner ministry directory cards." required class="form-input"></textarea>
                </div>

                <div class="form-group">
                    <label for="add-about">Detailed Vision &amp; Mission</label>
                    <textarea id="add-about" name="about" rows="3" placeholder="Elaborate on the apostolate's mission, spirituality, and purpose." class="form-input"></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="add-schedule">Meeting Schedule</label>
                        <input type="text" id="add-schedule" name="meeting_schedule" placeholder="e.g. Every Saturday • 8:00 AM" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="add-location">Meeting Location / Venue</label>
                        <input type="text" id="add-location" name="meeting_location" placeholder="e.g. Main Sacristy & Parish Hall" class="form-input">
                    </div>
                </div>

                <div class="section-divider">
                    <span>Coordinator &amp; Contact Details</span>
                </div>

                <div class="form-group">
                    <label for="add-staff-picker">Assign Registered Staff Member (Optional)</label>
                    <select id="add-staff-picker" name="coordinator_user_id" class="form-input" onchange="autofillStaff('add', this)">
                        <option value="">-- No specific staff account / Custom coordinator --</option>
                        @foreach($staffUsers as $st)
                            <option value="{{ $st->id }}" data-name="{{ $st->displayName }}" data-email="{{ $st->email }}" data-phone="{{ $st->phone }}">
                                {{ $st->displayName }} ({{ $st->email }}) - {{ ucfirst($st->role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="add-coord-name">Coordinator Name</label>
                        <input type="text" id="add-coord-name" name="coordinator_name" placeholder="Full name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="add-coord-email">Coordinator Email</label>
                        <input type="email" id="add-coord-email" name="coordinator_email" placeholder="contact@pilarshrine.test" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="add-coord-phone">Coordinator Mobile</label>
                        <input type="tel" id="add-coord-phone" name="coordinator_phone" placeholder="0917-123-4567" class="form-input">
                    </div>
                </div>

                <div class="section-divider">
                    <span>Apostolate Activities &amp; Joining Requirements</span>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="add-activities">Activities (1 item per line)</label>
                        <textarea id="add-activities" name="activities" rows="3" placeholder="Proclaiming the Word of God&#10;Serving Sunday Holy Masses&#10;Monthly liturgical workshops" class="form-input"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="add-requirements">Requirements (1 item per line)</label>
                        <textarea id="add-requirements" name="requirements" rows="3" placeholder="Baptized and Confirmed Catholic&#10;Willingness to attend rehearsals&#10;Regular attendance" class="form-input"></textarea>
                    </div>
                </div>

                <div class="form-group-checkbox">
                    <label>
                        <input type="checkbox" name="is_accepting_members" value="1" checked>
                        <span><strong>Accepting New Members</strong> (Parishioners can submit "Request to Join" applications)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save &amp; Publish Ministry</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. EDIT MINISTRY MODAL -->
<div id="edit-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-header">
            <h4 id="edit-modal-title">Edit Parish Ministry</h4>
            <button type="button" class="modal-close" onclick="closeModal('edit-modal')">&times;</button>
        </div>
        <form id="edit-form" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="modal-body modal-scroll">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit-name">Ministry Name <span class="req">*</span></label>
                        <input type="text" id="edit-name" name="name" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit-category">Category <span class="req">*</span></label>
                        <input list="category-suggestions" id="edit-category" name="category" required class="form-input">
                    </div>
                </div>

                <div class="form-group">
                    <label>Ministry Icon / Symbol</label>
                    <div class="icon-picker-wrap">
                        <input type="text" id="edit-icon" name="icon" class="form-input icon-input" maxlength="10">
                        <span class="icon-picker-label">Quick Pick:</span>
                        <div class="emoji-quick-list">
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '✝')">✝</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '🎵')">🎵</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '✦')">✦</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '♡')">♡</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '📖')">📖</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '📡')">📡</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '👑')">👑</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '🕊️')">🕊️</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '🕯️')">🕯️</button>
                            <button type="button" class="emoji-btn" onclick="setIcon('edit-icon', '👥')">👥</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="edit-desc">Short Summary / Description <span class="req">*</span></label>
                    <textarea id="edit-desc" name="description" rows="2" required class="form-input"></textarea>
                </div>

                <div class="form-group">
                    <label for="edit-about">Detailed Vision &amp; Mission</label>
                    <textarea id="edit-about" name="about" rows="3" class="form-input"></textarea>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit-schedule">Meeting Schedule</label>
                        <input type="text" id="edit-schedule" name="meeting_schedule" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit-location">Meeting Location / Venue</label>
                        <input type="text" id="edit-location" name="meeting_location" class="form-input">
                    </div>
                </div>

                <div class="section-divider">
                    <span>Coordinator &amp; Contact Details</span>
                </div>

                <div class="form-group">
                    <label for="edit-staff-picker">Assign Registered Staff Member (Optional)</label>
                    <select id="edit-staff-picker" name="coordinator_user_id" class="form-input" onchange="autofillStaff('edit', this)">
                        <option value="">-- No specific staff account / Custom coordinator --</option>
                        @foreach($staffUsers as $st)
                            <option value="{{ $st->id }}" data-name="{{ $st->displayName }}" data-email="{{ $st->email }}" data-phone="{{ $st->phone }}">
                                {{ $st->displayName }} ({{ $st->email }}) - {{ ucfirst($st->role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-grid-3">
                    <div class="form-group">
                        <label for="edit-coord-name">Coordinator Name</label>
                        <input type="text" id="edit-coord-name" name="coordinator_name" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit-coord-email">Coordinator Email</label>
                        <input type="email" id="edit-coord-email" name="coordinator_email" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit-coord-phone">Coordinator Mobile</label>
                        <input type="tel" id="edit-coord-phone" name="coordinator_phone" class="form-input">
                    </div>
                </div>

                <div class="section-divider">
                    <span>Apostolate Activities &amp; Joining Requirements</span>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit-activities">Activities (1 item per line)</label>
                        <textarea id="edit-activities" name="activities" rows="3" class="form-input"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="edit-requirements">Requirements (1 item per line)</label>
                        <textarea id="edit-requirements" name="requirements" rows="3" class="form-input"></textarea>
                    </div>
                </div>

                <div class="form-group-checkbox">
                    <label>
                        <input type="checkbox" id="edit-accepting" name="is_accepting_members" value="1">
                        <span><strong>Accepting New Members</strong> (Parishioners can submit "Request to Join" applications)</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Ministry</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. DELETE CONFIRMATION MODAL -->
<div id="delete-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4>Delete Ministry</h4>
            <button type="button" class="modal-close" onclick="closeModal('delete-modal')">&times;</button>
        </div>
        <form id="delete-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p id="delete-modal-msg" style="font-size: 12.5px; color: #334155; line-height: 1.5; margin: 0 0 8px;"></p>
                <p style="font-size: 11px; color: #dc2626; margin: 0;">
                    Warning: This action will permanently remove this ministry and any membership records tied to it from the database.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('delete-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-description { margin: 6px 0 0; color: var(--muted); font-size: 11px; }
.btn-add-ministry { display: inline-flex; align-items: center; gap: 7px; padding: 10px 16px; border-radius: 8px; font-weight: 700; font-size: 12px; cursor: pointer; }
.notice-success { display: flex; align-items: center; gap: 8px; margin-bottom: 16px; padding: 12px 16px; border: 1px solid #b7e4c7; border-radius: 8px; background: #effaf3; color: #176b3a; font-size: 12px; font-weight: 600; }
.notice-error { margin-bottom: 16px; padding: 12px 16px; border: 1px solid #fca5a5; border-radius: 8px; background: #fee2e2; color: #b91c1c; font-size: 12px; font-weight: 600; }

/* Summary Grid */
.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.stat-box { padding: 16px 18px; border: 1px solid var(--line); border-radius: 9px; background: #fff; }
.stat-box strong, .stat-box span { display: block; }
.stat-box strong { font: 700 22px Georgia, serif; color: var(--navy); }
.stat-box span { font-size: 9.5px; color: var(--muted); text-transform: uppercase; margin-top: 4px; letter-spacing: 0.03em; }
.highlight-open strong { color: #059669; }
.highlight-closed strong { color: #64748b; }
.highlight-members strong { color: #2563eb; }

/* Toolbar */
.toolbar { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
.search-wrap { display: flex; align-items: center; gap: 8px; padding: 0 12px; border: 1px solid var(--line); border-radius: 7px; background: #fff; flex: 1; min-width: 240px; }
.search-wrap svg { color: var(--muted); flex-shrink: 0; }
.search-wrap input { border: none; outline: none; padding: 10px 0; width: 100%; font-size: 11.5px; }
.toolbar select { padding: 10px 12px; border: 1px solid var(--line); border-radius: 7px; background: #fff; font-size: 11.5px; }
.toolbar a { text-decoration: none; }

/* Table */
.table-wrap { overflow-x: auto; border: 1px solid var(--line); border-radius: 11px; background: #fff; box-shadow: 0 2px 6px rgba(6,47,120,0.03); }
table { width: 100%; border-collapse: collapse; font-size: 11px; }
th, td { padding: 13px 15px; text-align: left; border-bottom: 1px solid #edf2f7; vertical-align: middle; }
th { background: #f8fafc; color: var(--muted); font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; }

.min-name-cell { display: flex; align-items: center; gap: 10px; min-width: 200px; }
.min-icon-box { width: 34px; height: 34px; border-radius: 8px; background: #eaf2fb; color: var(--navy); display: grid; place-items: center; font-size: 16px; flex-shrink: 0; border: 1px solid #ccd8e4; }
.min-title { display: block; font-size: 12.5px; color: var(--navy); font-weight: 700; }
.min-slug { display: block; font-size: 9.5px; color: #94a3b8; font-family: monospace; }

.category-pill { display: inline-block; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.03em; padding: 2px 7px; border-radius: 5px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; white-space: nowrap; }

.logistics-cell strong { display: block; font-size: 11px; color: #1e293b; }
.logistics-cell small { display: block; color: #64748b; font-size: 10px; margin-top: 2px; }

.coord-cell strong { display: block; font-size: 11px; color: #1e293b; }
.coord-cell small { display: block; color: #64748b; font-size: 10px; margin-top: 1px; }
.badge-staff-linked { display: inline-block; font-size: 8.5px; font-weight: 700; color: #0369a1; background: #e0f2fe; padding: 1px 5px; border-radius: 4px; margin-top: 3px; }

.members-cell { display: flex; flex-direction: column; gap: 3px; }
.pill-active-count { font-size: 10px; font-weight: 700; color: #047857; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 6px; border-radius: 12px; width: fit-content; }
.pill-pending-count { font-size: 10px; font-weight: 700; color: #b45309; background: #fffbeb; border: 1px solid #fde68a; padding: 2px 6px; border-radius: 12px; width: fit-content; text-decoration: none; }
.pill-pending-count:hover { background: #fef3c7; }

.status-btn { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 12px; cursor: pointer; border: 1px solid transparent; transition: opacity 0.15s; }
.status-btn:hover { opacity: 0.85; }
.btn-open { background: #ecfdf5; color: #047857; border-color: #a7f3d0; }
.btn-closed { background: #f1f5f9; color: #64748b; border-color: #cbd5e1; }

.actions-cell { display: flex; align-items: center; justify-content: flex-end; gap: 5px; }
.btn-sm { padding: 4px 8px; border-radius: 5px; font-size: 10px; font-weight: 700; cursor: pointer; border: 1px solid var(--line); background: #fff; color: var(--navy); text-decoration: none; transition: all 0.15s ease; }
.btn-sm:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-edit { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.btn-edit:hover { background: #dbeafe; }
.btn-view-requests { background: #faf5ff; color: #7e22ce; border-color: #e9d5ff; }
.btn-view-requests:hover { background: #f3e8ff; }
.btn-delete { color: #dc2626; border-color: #fecaca; }
.btn-delete:hover { background: #fef2f2; }
.btn-danger { background: #dc2626; color: #fff; border: 1px solid #b91c1c; padding: 9px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }

/* Modals */
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px; }
.modal-dialog { background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 15px 35px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column; max-height: 90vh; }
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px; box-sizing: border-box; }
.modal-dialog { background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 15px 35px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column; max-height: calc(100vh - 40px); }
.modal-dialog-lg { max-width: 640px; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid #f1f5f9; background: #fafcff; }
.modal-dialog form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid #f1f5f9; background: #fafcff; flex-shrink: 0; }
.modal-header h4 { margin: 0; font-size: 15px; color: var(--navy); font-weight: 700; font-family: Georgia, serif; }
.modal-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1; }
.modal-close:hover { color: #0f172a; }
.modal-body { padding: 20px 22px; }
.modal-scroll { overflow-y: auto; }
.modal-footer { padding: 14px 22px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px; }
.modal-scroll { overflow-y: auto; flex: 1; min-height: 0; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9; }
.modal-scroll::-webkit-scrollbar { width: 6px; }
.modal-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
.modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.modal-footer { padding: 14px 22px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; }

/* Forms */
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; }
.form-group { margin-bottom: 14px; display: flex; flex-direction: column; gap: 5px; }
.form-group label { font-size: 11px; font-weight: 700; color: var(--navy); }
.req { color: #dc2626; }
.form-input { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 12px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.form-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(6,47,120,0.1); }
.form-group-checkbox { margin-top: 10px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; }
.form-group-checkbox label { display: flex; align-items: center; gap: 8px; font-size: 11.5px; cursor: pointer; color: #1e293b; }

.icon-picker-wrap { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.icon-input { width: 55px; text-align: center; font-size: 16px; font-weight: 700; }
.icon-picker-label { font-size: 11px; color: #64748b; font-weight: 600; }
.emoji-quick-list { display: flex; gap: 4px; flex-wrap: wrap; }
.emoji-btn { width: 28px; height: 28px; border: 1px solid #cbd5e1; border-radius: 6px; background: #ffffff; cursor: pointer; font-size: 14px; display: grid; place-items: center; transition: all 0.1s; }
.emoji-btn:hover { background: #eaf2fb; border-color: var(--navy); transform: scale(1.1); }

.section-divider { border-top: 1px solid #e2e8f0; margin: 16px 0 12px; position: relative; text-align: center; }
.section-divider span { position: relative; top: -10px; background: #fff; padding: 0 10px; font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--gold); }

.pagination-wrap { display: flex; justify-content: center; margin-top: 20px; }
@media(max-width: 900px) {
    .summary-grid { grid-template-columns: repeat(2, 1fr); }
    .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
function setIcon(targetInputId, emoji) {
    document.getElementById(targetInputId).value = emoji;
}

function openAddModal() {
    document.getElementById('add-modal').style.display = 'flex';
}

function openEditModal(ministry) {
    document.getElementById('edit-modal-title').innerText = 'Edit ' + ministry.name;
    document.getElementById('edit-form').action = '/admin/ministries/' + ministry.id;
    
    document.getElementById('edit-name').value = ministry.name || '';
    document.getElementById('edit-category').value = ministry.category || '';
    document.getElementById('edit-icon').value = ministry.icon || '✝';
    document.getElementById('edit-desc').value = ministry.description || '';
    document.getElementById('edit-about').value = ministry.about || '';
    document.getElementById('edit-schedule').value = ministry.meeting_schedule || '';
    document.getElementById('edit-location').value = ministry.meeting_location || '';
    
    // Coordinator
    document.getElementById('edit-staff-picker').value = ministry.coordinator_user_id || '';
    document.getElementById('edit-coord-name').value = ministry.coordinator_name || '';
    document.getElementById('edit-coord-email').value = ministry.coordinator_email || '';
    document.getElementById('edit-coord-phone').value = ministry.coordinator_phone || '';
    
    // Arrays to multiline string
    const activities = Array.isArray(ministry.activities) ? ministry.activities.join('\n') : '';
    document.getElementById('edit-activities').value = activities;

    const requirements = Array.isArray(ministry.requirements) ? ministry.requirements.join('\n') : '';
    document.getElementById('edit-requirements').value = requirements;

    // Accepting
    document.getElementById('edit-accepting').checked = Boolean(ministry.is_accepting_members);

    document.getElementById('edit-modal').style.display = 'flex';
}

function autofillStaff(prefix, selectEl) {
    const selected = selectEl.options[selectEl.selectedIndex];
    if (selected && selected.value) {
        document.getElementById(prefix + '-coord-name').value = selected.getAttribute('data-name') || '';
        document.getElementById(prefix + '-coord-email').value = selected.getAttribute('data-email') || '';
        document.getElementById(prefix + '-coord-phone').value = selected.getAttribute('data-phone') || '';
    }
}

function openDeleteModal(id, name) {
    document.getElementById('delete-modal-msg').innerText = 'Are you sure you want to delete the ministry "' + name + '"?';
    document.getElementById('delete-form').action = '/admin/ministries/' + id;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
</script>
@endpush

