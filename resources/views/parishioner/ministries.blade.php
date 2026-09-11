@extends('layouts.parishioner')
@section('title', 'Ministries')

@section('content')
<div class="ministries-page">
    <!-- Header Section -->
    <div class="page-header">
        <div>
            <h2>Ministries</h2>
            <p class="page-subtitle">Discover parish ministries and find opportunities to serve our community.</p>
        </div>
        <div class="header-action-wrap">
            <a href="#my-ministries-section" class="btn-scroll-my-ministries">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span>My Ministries ({{ $myMinistriesCount }})</span>
            </a>
        </div>
    </div>

    {{-- Flash Notifications --}}
    @if(session('success'))
    <div class="alert-banner alert-success" role="alert">
        <div class="alert-icon">✓</div>
        <div>
            <strong>Request Submitted</strong>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert-banner alert-error" role="alert">
        <div class="alert-icon">!</div>
        <div>
            <strong>Notice</strong>
            <p>{{ session('error') }}</p>
        </div>
    </div>
    @endif

    @if(session('info'))
    <div class="alert-banner alert-info" role="alert">
        <div class="alert-icon">ℹ</div>
        <div>
            <p>{{ session('info') }}</p>
        </div>
    </div>
    @endif

    <!-- 1. Summary Strip -->
    <div class="summary-strip">
        <article class="summary-card">
            <div class="summary-icon icon-blue">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
            <div class="summary-content">
                <strong>{{ $availableCount }}</strong>
                <span>Available Ministries</span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-icon icon-green">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <div class="summary-content">
                <strong>{{ $myMinistriesCount }}</strong>
                <span>My Ministries</span>
            </div>
        </article>

        <article class="summary-card">
            <div class="summary-icon icon-amber">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="summary-content">
                <strong>{{ $pendingRequestsCount }}</strong>
                <span>Pending Requests</span>
            </div>
        </article>
    </div>

    <!-- 2. Search & Filters Bar -->
    <div class="directory-control-bar">
        <form class="search-form" method="GET" action="{{ route('parishioner.ministries') }}">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="search-input-wrap">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input type="search" name="search" value="{{ $search }}" placeholder="Search ministries, apostolates, or coordinators...">
            </div>
            @if(!empty($search))
                <a href="{{ route('parishioner.ministries', ['filter' => $filter]) }}" class="btn-clear-search">Clear</a>
            @endif
        </form>

        <div class="filter-pills" role="tablist">
            <a href="{{ route('parishioner.ministries', ['filter' => 'all', 'search' => $search]) }}"
               class="filter-pill {{ $filter === 'all' ? 'active' : '' }}">
               All Ministries
            </a>
            <a href="{{ route('parishioner.ministries', ['filter' => 'open', 'search' => $search]) }}"
               class="filter-pill {{ $filter === 'open' ? 'active' : '' }}">
               Open for Members
            </a>
            <a href="{{ route('parishioner.ministries', ['filter' => 'my-ministries', 'search' => $search]) }}"
               class="filter-pill {{ $filter === 'my-ministries' ? 'active' : '' }}">
               My Ministries ({{ $myMinistriesCount }})
            </a>
            <a href="{{ route('parishioner.ministries', ['filter' => 'pending', 'search' => $search]) }}"
               class="filter-pill {{ $filter === 'pending' ? 'active' : '' }}">
               Pending Requests ({{ $pendingRequestsCount }})
            </a>
        </div>
    </div>

    <!-- 3. Ministries Grid -->
    <div class="ministry-cards-grid">
        @forelse($ministries as $ministry)
            @php
                $membership = $userMemberships->get($ministry->id);
                $isPending = $membership && $membership->status === 'pending';
                $isApproved = $membership && $membership->status === 'approved';
                $isRejected = $membership && $membership->status === 'rejected';
            @endphp
            <article class="ministry-card {{ $isApproved ? 'card-member' : '' }}">
                <div class="card-top">
                    <div class="ministry-symbol-badge">
                        <span>{{ $ministry->icon ?: '✝' }}</span>
                    </div>
                    <div class="tag-status-wrap">
                        <span class="category-tag">{{ $ministry->category }}</span>
                        @if($isApproved)
                            <span class="status-badge badge-approved">✓ Active Member</span>
                        @elseif($isPending)
                            <span class="status-badge badge-pending">🟡 Pending Review</span>
                        @elseif($isRejected)
                            <span class="status-badge badge-rejected">✕ Not Approved</span>
                        @elseif($ministry->is_accepting_members)
                            <span class="status-badge badge-open">● Accepting</span>
                        @else
                            <span class="status-badge badge-closed">Closed</span>
                        @endif
                    </div>
                </div>

                <div class="card-body">
                    <h3 class="ministry-title">{{ $ministry->name }}</h3>
                    <p class="ministry-desc">{{ $ministry->description }}</p>
                </div>

                <div class="card-actions">
                    <button type="button" class="btn-card btn-details" onclick="openDetailsModal({{ json_encode($ministry) }}, '{{ $isApproved ? 'approved' : ($isPending ? 'pending' : ($isRejected ? 'rejected' : 'none')) }}')">
                        View Details
                    </button>

                    @if($isApproved)
                        <span class="btn-card btn-status-locked btn-is-member" title="You are an official member of this ministry">
                            ✓ Member
                        </span>
                    @elseif($isPending)
                        <span class="btn-card btn-status-locked btn-is-pending" title="Your membership request is currently under review">
                            Pending Review
                        </span>
                    @elseif($ministry->is_accepting_members)
                        <button type="button" class="btn-card btn-join" onclick="openJoinModal({{ json_encode($ministry) }})">
                            {{ $isRejected ? 'Re-apply to Join' : 'Request to Join' }}
                        </button>
                    @else
                        <span class="btn-card btn-status-locked" title="This ministry is currently not accepting new applications">
                            Applications Closed
                        </span>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-state-wrap">
                <div class="empty-icon-circle">
                    <svg viewBox="0 0 24 24" width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.8">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
                @if($filter === 'my-ministries')
                    <h4>You have not joined any ministries yet</h4>
                    <p>Discover parish ministries above and request to join to serve our shrine community.</p>
                    <a href="{{ route('parishioner.ministries', ['filter' => 'all']) }}" class="btn-empty-action">Browse All Ministries</a>
                @elseif($filter === 'pending')
                    <h4>No pending membership requests</h4>
                    <p>You currently do not have any ministry applications waiting for review.</p>
                    <a href="{{ route('parishioner.ministries', ['filter' => 'all']) }}" class="btn-empty-action">Browse All Ministries</a>
                @else
                    <h4>No ministries found</h4>
                    <p>No parish ministries match your search criteria. Try clearing the filter.</p>
                    <a href="{{ route('parishioner.ministries', ['filter' => 'all']) }}" class="btn-empty-action">Reset Filters</a>
                @endif
            </div>
        @endforelse
    </div>

    <!-- 4. Dedicated "My Ministries" Section -->
    <section id="my-ministries-section" class="my-ministries-block">
        <div class="block-header">
            <div class="block-header-title">
                <span class="block-eyebrow">Personal Membership Tracking</span>
                <h3>My Ministries &amp; Applications</h3>
            </div>
            <span class="membership-count-badge">{{ $myMembershipsList->count() }} Total Records</span>
        </div>

        @if($myMembershipsList->isNotEmpty())
            <div class="memberships-table-wrap">
                <table class="memberships-table">
                    <thead>
                        <tr>
                            <th>Ministry</th>
                            <th>Meeting Details</th>
                            <th>Status</th>
                            <th>Date / Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($myMembershipsList as $mem)
                        <tr>
                            <td>
                                <div class="mem-title-row">
                                    <span class="mem-icon">{{ $mem->ministry->icon ?: '✝' }}</span>
                                    <div>
                                        <strong>{{ $mem->ministry->name }}</strong>
                                        <small>{{ $mem->ministry->category }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>{{ $mem->ministry->meeting_schedule ?: 'N/A' }}</div>
                                <small style="color: #64748b;">{{ $mem->ministry->meeting_location ?: 'Parish Hall' }}</small>
                            </td>
                            <td>
                                @if($mem->status === 'approved')
                                    <span class="status-badge badge-approved">✓ Active Member</span>
                                @elseif($mem->status === 'pending')
                                    <span class="status-badge badge-pending">🟡 Pending Approval</span>
                                @elseif($mem->status === 'rejected')
                                    <span class="status-badge badge-rejected">✕ Not Approved</span>
                                @else
                                    <span class="status-badge badge-inactive">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if($mem->status === 'approved' && $mem->joined_at)
                                    <div>Joined: {{ $mem->joined_at->format('F j, Y') }}</div>
                                @else
                                    <div>Applied: {{ $mem->created_at->format('F j, Y') }}</div>
                                @endif
                                @if($mem->reviewer_notes)
                                    <small style="color: #b91c1c;">Note: {{ $mem->reviewer_notes }}</small>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="my-ministries-empty">
                <p>You have not submitted any ministry membership requests yet.</p>
                <small>Select any available ministry above and click "Request to Join" to begin your service.</small>
            </div>
        @endif
    </section>
</div>

<!-- ======================================================= -->
<!-- MODAL 1: VIEW MINISTRY DETAILS -->
<!-- ======================================================= -->
<div id="details-modal" class="portal-modal-backdrop" style="display: none;" onclick="handleBackdropClick(event, 'details-modal')">
    <div class="portal-modal-card">
        <div class="modal-card-header">
            <div class="modal-title-box">
                <span id="modal-icon" class="modal-title-icon">✝</span>
                <div>
                    <span id="modal-category" class="category-tag">General</span>
                    <h3 id="modal-name">Ministry Name</h3>
                </div>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('details-modal')">&times;</button>
        </div>

        <div class="modal-card-body">
            <div class="modal-section">
                <h4>About this Ministry</h4>
                <p id="modal-about" class="modal-text"></p>
            </div>

            <div class="modal-section">
                <h4>Key Activities</h4>
                <ul id="modal-activities" class="modal-bullets"></ul>
            </div>

            <div class="modal-grid-two">
                <div class="modal-info-box">
                    <strong>Meeting Schedule</strong>
                    <p id="modal-schedule"></p>
                </div>
                <div class="modal-info-box">
                    <strong>Meeting Location</strong>
                    <p id="modal-location"></p>
                </div>
                <div class="modal-info-box">
                    <strong>Coordinator</strong>
                    <p id="modal-coordinator"></p>
                </div>
                <div class="modal-info-box">
                    <strong>Contact Details</strong>
                    <p id="modal-contact"></p>
                </div>
            </div>

            <div class="modal-section" style="margin-top: 14px;">
                <h4>Requirements for Service</h4>
                <ul id="modal-requirements" class="modal-bullets"></ul>
            </div>
        </div>

        <div class="modal-card-footer">
            <button type="button" class="btn-card btn-details" onclick="closeModal('details-modal')">Close</button>
            <div id="modal-action-slot"></div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- MODAL 2: REQUEST TO JOIN MINISTRY -->
<!-- ======================================================= -->
<div id="join-modal" class="portal-modal-backdrop" style="display: none;" onclick="handleBackdropClick(event, 'join-modal')">
    <div class="portal-modal-card modal-card-form">
        <div class="modal-card-header">
            <div>
                <span class="category-tag">Membership Application</span>
                <h3 id="join-modal-title">Request to Join Ministry</h3>
            </div>
            <button type="button" class="modal-close-btn" onclick="closeModal('join-modal')">&times;</button>
        </div>

        <form id="join-form" method="POST" action="">
            @csrf
            <div class="modal-card-body">
                <!-- Auto-prefilled applicant details (Read-only) -->
                <div class="prefilled-user-box">
                    <div class="prefilled-field">
                        <span class="prefilled-label">Applicant Name</span>
                        <strong class="prefilled-value">{{ $user->displayName }}</strong>
                    </div>
                    <div class="prefilled-field">
                        <span class="prefilled-label">Registered Email</span>
                        <strong class="prefilled-value">{{ $user->email }}</strong>
                    </div>
                    @if($user->phone)
                    <div class="prefilled-field">
                        <span class="prefilled-label">Mobile Number</span>
                        <strong class="prefilled-value">{{ $user->phone }}</strong>
                    </div>
                    @endif
                </div>

                <!-- Motivation Textarea -->
                <div class="form-group">
                    <label for="app-message" class="form-label">
                        Why would you like to join this ministry? <span class="req-star">*</span>
                    </label>
                    <textarea id="app-message" name="application_message" rows="3" class="form-textarea" required
                        placeholder="Share your inspiration, intention to serve, or what draws you to this apostolate..."></textarea>
                    <small class="form-hint">A brief statement helps the ministry coordinator welcome and place you appropriately.</small>
                </div>

                <!-- Previous Experience -->
                <div class="form-group">
                    <label for="app-experience" class="form-label">Previous experience (if any):</label>
                    <textarea id="app-experience" name="experience" rows="2" class="form-textarea"
                        placeholder="e.g. Sung in high school choir, served as altar server in parish, attended youth camps..."></textarea>
                </div>

                <!-- Additional Information -->
                <div class="form-group">
                    <label for="app-additional" class="form-label">Additional information / special talents (Optional):</label>
                    <textarea id="app-additional" name="additional_info" rows="2" class="form-textarea"
                        placeholder="e.g. Available on Saturdays, can play musical instruments, photography skills..."></textarea>
                </div>

                <!-- Terms & Approval Acknowledgment -->
                <div class="form-checkbox-group">
                    <label class="checkbox-container">
                        <input type="checkbox" name="agreed_terms" value="1" required checked>
                        <span class="checkbox-text">
                            I understand that membership is subject to review and approval by the parish and ministry coordinator.
                        </span>
                    </label>
                </div>
            </div>

            <div class="modal-card-footer">
                <button type="button" class="btn-modal-cancel" onclick="closeModal('join-modal')">Cancel</button>
                <button type="submit" class="btn-modal-submit">Submit Request</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.ministries-page {
    max-width: 1200px;
    margin: 0 auto;
}

/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    gap: 18px;
    margin-bottom: 22px;
    padding-bottom: 18px;
    border-bottom: 1px solid var(--line);
}
.page-subtitle {
    margin: 6px 0 0;
    color: var(--muted);
    font-size: 11.5px;
    max-width: 600px;
    line-height: 1.4;
}
.btn-scroll-my-ministries {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: 7px;
    background: #ffffff;
    color: var(--navy);
    border: 1px solid var(--line);
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.btn-scroll-my-ministries:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* Flash Banners */
.alert-banner {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 9px;
    margin-bottom: 20px;
    font-size: 12px;
}
.alert-banner strong { display: block; margin-bottom: 2px; }
.alert-banner p { margin: 0; line-height: 1.4; }
.alert-icon {
    width: 22px;
    height: 22px;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-weight: 800;
    flex-shrink: 0;
    font-size: 11px;
}
.alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.alert-success .alert-icon { background: #059669; color: #fff; }
.alert-error { background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; }
.alert-error .alert-icon { background: #dc2626; color: #fff; }
.alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
.alert-info .alert-icon { background: #2563eb; color: #fff; }

/* 1. Summary Strip */
.summary-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}
.summary-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 18px;
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(6, 47, 120, 0.02);
}
.summary-icon {
    width: 42px;
    height: 42px;
    border-radius: 9px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.icon-blue { background: #eff6ff; color: #2563eb; }
.icon-green { background: #ecfdf5; color: #059669; }
.icon-amber { background: #fffbeb; color: #d97706; }
.summary-content strong {
    font: 700 22px Georgia, serif;
    color: var(--navy);
    display: block;
    line-height: 1.1;
}
.summary-content span {
    font-size: 9.5px;
    color: var(--muted);
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.04em;
    display: block;
    margin-top: 4px;
}

/* 2. Control Bar (Search & Filter Pills) */
.directory-control-bar {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 24px;
}
.search-form {
    display: flex;
    align-items: center;
    gap: 8px;
}
.search-input-wrap {
    position: relative;
    flex: 1;
}
.search-input-wrap svg {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.search-input-wrap input {
    width: 100%;
    box-sizing: border-box;
    padding: 12px 14px 12px 38px;
    border: 1px solid var(--line);
    border-radius: 8px;
    background: #ffffff;
    font-size: 12px;
    color: #1e293b;
    outline: none;
    transition: border-color 0.15s ease;
}
.search-input-wrap input:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.08);
}
.btn-clear-search {
    padding: 10px 14px;
    border-radius: 8px;
    background: #f1f5f9;
    color: #475569;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.filter-pills {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.filter-pill {
    padding: 7px 14px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    background: #ffffff;
    color: var(--muted);
    border: 1px solid var(--line);
    transition: all 0.15s ease;
}
.filter-pill:hover {
    background: #f8fafc;
    color: var(--navy);
}
.filter-pill.active {
    background: var(--navy);
    color: #ffffff;
    border-color: var(--navy);
    box-shadow: 0 2px 6px rgba(6, 47, 120, 0.2);
}

/* 3. Ministry Cards Grid */
.ministry-cards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}
.ministry-card {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.ministry-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(6, 47, 120, 0.08);
}
.card-member {
    border-color: #a7f3d0;
    box-shadow: 0 2px 10px rgba(5, 150, 105, 0.08);
}

.card-top {
    padding: 18px 20px 12px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 12px;
}
.ministry-symbol-badge {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eff6ff;
    color: var(--navy);
    display: grid;
    place-items: center;
    font-size: 20px;
    border: 1px solid #dbeafe;
    flex-shrink: 0;
}
.tag-status-wrap {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
}
.category-tag {
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding: 2px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
}

.status-badge {
    font-size: 9.5px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
    white-space: nowrap;
}
.badge-approved { background: #ecfdf5; color: #065f46; border: 1px solid #a7f3d0; }
.badge-pending { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.badge-rejected { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.badge-open { background: #eff6ff; color: #1d4ed8; border: 1px solid #dbeafe; }
.badge-closed { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

.card-body {
    padding: 0 20px 18px;
    flex: 1;
    display: flex;
    flex-direction: column;
}
.ministry-title {
    margin: 0 0 8px;
    font: 700 16px Georgia, serif;
    color: var(--navy);
    line-height: 1.3;
}
.ministry-desc {
    margin: 0 0 16px;
    color: #475569;
    font-size: 11.5px;
    line-height: 1.5;
    flex: 1;
}

.ministry-meta-list {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 10px 12px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.meta-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 11px;
    gap: 8px;
}
.meta-label { color: var(--muted); font-size: 10px; font-weight: 600; }
.meta-val { color: #1e293b; text-align: right; }
.meta-val.bold { font-weight: 700; color: var(--navy); }

.card-actions {
    padding: 14px 20px;
    border-top: 1px solid #f1f5f9;
    background: #fafcff;
    display: flex;
    gap: 10px;
}
.btn-card {
    flex: 1;
    padding: 9px 12px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 700;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    border: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.btn-details {
    background: #ffffff;
    color: var(--navy);
    border: 1px solid var(--line);
}
.btn-details:hover {
    background: #f1f5f9;
    border-color: #cbd5e1;
}
.btn-join {
    background: var(--navy);
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(6, 47, 120, 0.2);
}
.btn-join:hover {
    background: #042054;
}
.btn-status-locked {
    cursor: default;
    background: #f1f5f9;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
.btn-is-member {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}
.btn-is-pending {
    background: #fffbeb;
    color: #b45309;
    border: 1px solid #fde68a;
}

/* Empty State */
.empty-state-wrap {
    grid-column: 1 / -1;
    padding: 48px 24px;
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    text-align: center;
}
.empty-icon-circle {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    display: grid;
    place-items: center;
    margin: 0 auto 14px;
}
.empty-state-wrap h4 {
    margin: 0 0 6px;
    font: 700 16px Georgia, serif;
    color: var(--navy);
}
.empty-state-wrap p {
    margin: 0 0 16px;
    color: var(--muted);
    font-size: 12px;
}
.btn-empty-action {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 7px;
    background: var(--navy);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

/* 4. Dedicated My Ministries Block */
.my-ministries-block {
    background: #ffffff;
    border: 1px solid var(--line);
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(6, 47, 120, 0.03);
    overflow: hidden;
    margin-top: 12px;
}
.block-header {
    padding: 18px 22px;
    border-bottom: 1px solid #f1f5f9;
    background: #fafcff;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.block-eyebrow {
    font-size: 9px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #d97706;
    display: block;
    margin-bottom: 2px;
}
.block-header h3 {
    margin: 0;
    font: 700 16px Georgia, serif;
    color: var(--navy);
}
.membership-count-badge {
    font-size: 10px;
    font-weight: 700;
    background: #f1f5f9;
    color: #475569;
    padding: 3px 9px;
    border-radius: 12px;
}
.memberships-table-wrap {
    overflow-x: auto;
}
.memberships-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 11.5px;
}
.memberships-table th, .memberships-table td {
    padding: 14px 20px;
    text-align: left;
    border-bottom: 1px solid #f1f5f9;
}
.memberships-table th {
    background: #f8fafc;
    color: var(--muted);
    font-size: 9px;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.04em;
}
.mem-title-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.mem-icon {
    font-size: 18px;
    width: 32px;
    height: 32px;
    border-radius: 7px;
    background: #eff6ff;
    display: grid;
    place-items: center;
}
.my-ministries-empty {
    padding: 36px 20px;
    text-align: center;
    color: var(--muted);
}
.my-ministries-empty p { margin: 0 0 4px; font-weight: 600; color: #475569; font-size: 12.5px; }
.my-ministries-empty small { font-size: 11px; }

/* Modals */
.portal-modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(15, 23, 42, 0.65);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
    backdrop-filter: blur(2px);
}
.portal-modal-card {
    background: #ffffff;
    border-radius: 14px;
    max-width: 580px;
    width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    overflow: hidden;
    animation: modalPop 0.18s ease-out;
}
.modal-card-form { max-width: 520px; }
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.96); }
    to { opacity: 1; transform: scale(1); }
}
.modal-card-header {
    padding: 18px 24px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    background: #fafcff;
}
.modal-title-box { display: flex; align-items: center; gap: 12px; }
.modal-title-icon {
    font-size: 24px;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: #eff6ff;
    display: grid;
    place-items: center;
    border: 1px solid #bfdbfe;
}
.modal-card-header h3 {
    margin: 4px 0 0;
    font: 700 17px Georgia, serif;
    color: var(--navy);
}
.modal-close-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    padding: 0 4px;
    line-height: 1;
}
.modal-close-btn:hover { color: #0f172a; }

.modal-card-body {
    padding: 20px 24px;
    overflow-y: auto;
}
.modal-section { margin-bottom: 16px; }
.modal-section h4 {
    margin: 0 0 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--navy);
}
.modal-text {
    margin: 0;
    font-size: 12px;
    color: #334155;
    line-height: 1.6;
}
.modal-bullets {
    margin: 0;
    padding-left: 18px;
    font-size: 11.5px;
    color: #334155;
    line-height: 1.5;
}
.modal-bullets li { margin-bottom: 4px; }

.modal-grid-two {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-top: 14px;
}
.modal-info-box {
    background: #f8fafc;
    border: 1px solid #f1f5f9;
    border-radius: 8px;
    padding: 10px 12px;
}
.modal-info-box strong {
    font-size: 9.5px;
    text-transform: uppercase;
    color: var(--muted);
    letter-spacing: 0.04em;
    display: block;
    margin-bottom: 2px;
}
.modal-info-box p {
    margin: 0;
    font-size: 11.5px;
    color: #0f172a;
    font-weight: 600;
}

.modal-card-footer {
    padding: 14px 24px;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

/* Form Styles */
.prefilled-user-box {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.prefilled-field {
    display: flex;
    justify-content: space-between;
    font-size: 11px;
}
.prefilled-label { color: var(--muted); }
.prefilled-value { color: var(--navy); }

.form-group { margin-bottom: 14px; }
.form-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 5px;
}
.req-star { color: #dc2626; }
.form-textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    font-size: 12px;
    font-family: inherit;
    color: #0f172a;
    resize: vertical;
    outline: none;
    transition: border-color 0.15s;
}
.form-textarea:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.08);
}
.form-hint { font-size: 10px; color: var(--muted); margin-top: 3px; display: block; }

.form-checkbox-group { margin: 16px 0 6px; }
.checkbox-container {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    cursor: pointer;
}
.checkbox-container input { margin-top: 2px; }
.checkbox-text { font-size: 11px; color: #334155; line-height: 1.4; }

.btn-modal-cancel {
    padding: 9px 16px;
    border-radius: 7px;
    background: #ffffff;
    border: 1px solid var(--line);
    color: var(--muted);
    font-weight: 700;
    font-size: 11.5px;
    cursor: pointer;
}
.btn-modal-cancel:hover { background: #f1f5f9; color: #1e293b; }
.btn-modal-submit {
    padding: 9px 18px;
    border-radius: 7px;
    background: var(--navy);
    border: none;
    color: #ffffff;
    font-weight: 700;
    font-size: 11.5px;
    cursor: pointer;
    box-shadow: 0 2px 5px rgba(6, 47, 120, 0.2);
}
.btn-modal-submit:hover { background: #042054; }

@media(max-width: 768px) {
    .summary-strip { grid-template-columns: 1fr; }
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-action-wrap { width: 100%; }
    .btn-scroll-my-ministries { width: 100%; justify-content: center; }
    .modal-grid-two { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
function openDetailsModal(ministry, userStatus) {
    document.getElementById('modal-icon').innerText = ministry.icon || '✝';
    document.getElementById('modal-name').innerText = ministry.name;
    document.getElementById('modal-category').innerText = ministry.category;
    document.getElementById('modal-about').innerText = ministry.about || ministry.description;

    // Activities
    const actList = document.getElementById('modal-activities');
    actList.innerHTML = '';
    const activities = Array.isArray(ministry.activities) ? ministry.activities : [];
    if (activities.length > 0) {
        activities.forEach(act => {
            const li = document.createElement('li');
            li.innerText = act;
            actList.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.innerText = 'Regular liturgical and pastoral activities.';
        actList.appendChild(li);
    }

    // Details boxes
    document.getElementById('modal-schedule').innerText = ministry.meeting_schedule || 'Announced in meetings';
    document.getElementById('modal-location').innerText = ministry.meeting_location || 'Parish Hall';
    document.getElementById('modal-coordinator').innerText = ministry.coordinator_name || 'Parish Office';
    document.getElementById('modal-contact').innerText = ministry.coordinator_email || ministry.coordinator_phone || 'Parish Office';

    // Requirements
    const reqList = document.getElementById('modal-requirements');
    reqList.innerHTML = '';
    const requirements = Array.isArray(ministry.requirements) ? ministry.requirements : [];
    if (requirements.length > 0) {
        requirements.forEach(req => {
            const li = document.createElement('li');
            li.innerText = req;
            reqList.appendChild(li);
        });
    } else {
        const li = document.createElement('li');
        li.innerText = 'Willingness to serve with an open and humble heart.';
        reqList.appendChild(li);
    }

    // Action button
    const actionSlot = document.getElementById('modal-action-slot');
    actionSlot.innerHTML = '';
    if (userStatus === 'approved') {
        actionSlot.innerHTML = '<span class="btn-card btn-is-member" style="padding: 9px 16px;">✓ You are a Member</span>';
    } else if (userStatus === 'pending') {
        actionSlot.innerHTML = '<span class="btn-card btn-is-pending" style="padding: 9px 16px;">🟡 Membership Request Pending</span>';
    } else if (ministry.is_accepting_members) {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-card btn-join';
        btn.style.padding = '9px 18px';
        btn.innerText = userStatus === 'rejected' ? 'Re-apply to Join' : 'Request to Join';
        btn.onclick = function() {
            closeModal('details-modal');
            openJoinModal(ministry);
        };
        actionSlot.appendChild(btn);
    } else {
        actionSlot.innerHTML = '<span class="btn-card btn-status-locked" style="padding: 9px 16px;">Applications Closed</span>';
    }

    document.getElementById('details-modal').style.display = 'flex';
}

function openJoinModal(ministry) {
    document.getElementById('join-modal-title').innerText = 'Request to Join ' + ministry.name;
    document.getElementById('join-form').action = '/parishioner/ministries/' + ministry.id + '/join';
    document.getElementById('join-modal').style.display = 'flex';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function handleBackdropClick(event, modalId) {
    if (event.target.id === modalId) {
        closeModal(modalId);
    }
}
</script>
@endpush