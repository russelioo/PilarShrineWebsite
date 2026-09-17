@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')

    <div class="page-header">
        <h2>Staff Management</h2>
        <div class="actions">
            <select name="sort" id="staff-sort-select" class="sort-select-btn" onchange="handleSortChange(this.value)" aria-label="Sort by">
                <option value="">Sort by</option>
                <option value="name" @selected(request('sort') === 'name')>Name</option>
                <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
            </select>
            @if($actor->hasPermission('export_reports') || $actor->hasPermission('view_users'))
            <button class="btn btn-outline" type="button" onclick="exportStaffData()">📥 Export</button>
            @endif
            @if($actor->hasPermission('create_users'))
            <button class="btn btn-primary" type="button" onclick="openStaffDrawer()">＋ Add New Staff</button>
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="alert-banner alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form class="toolbar" id="staff-filter-form" method="GET" action="{{ route('admin.staff') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone...">
        <select name="role" onchange="this.form.submit()">
            <option value="">All Roles</option>
            @if($actor->hasParishWideAccess())
                <option value="admin" @selected(request('role') === 'admin')>Super Admin</option>
                <option value="parish_priest" @selected(request('role') === 'parish_priest')>Parish Priest</option>
                <option value="parochial_vicar" @selected(request('role') === 'parochial_vicar')>Parochial Vicar</option>
                <option value="parish_secretary" @selected(request('role') === 'parish_secretary')>Parish Secretary</option>
                <option value="commission_admin" @selected(request('role') === 'commission_admin')>Commission Admin</option>
                <option value="commission_member" @selected(request('role') === 'commission_member')>Commission Member</option>
                <option value="staff" @selected(request('role') === 'staff')>Staff</option>
            @else
                <option value="commission_admin" @selected(request('role') === 'commission_admin')>Commission Admin</option>
                <option value="commission_member" @selected(request('role') === 'commission_member')>Commission Member</option>
                <option value="staff" @selected(request('role') === 'staff')>Staff</option>
            @endif
        </select>
        <select name="status" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        @if($actor->hasParishWideAccess())
        <select name="commission_id" onchange="this.form.submit()">
            <option value="">All Commissions</option>
            @foreach($commissions as $commission)
                <option value="{{ $commission->id }}" @selected(request('commission_id') == $commission->id)>{{ $commission->name }}</option>
            @endforeach
        </select>
        @elseif($actor->commission_id)
        <select name="commission_id" disabled title="Locked to your assigned commission">
            @foreach($commissions as $commission)
                <option value="{{ $commission->id }}" selected>{{ $commission->name }}</option>
            @endforeach
        </select>
        @endif
        <input type="hidden" name="sort" id="staff-hidden-sort" value="{{ request('sort') }}">
        @if(request()->hasAny(['search', 'role', 'status', 'commission_id', 'sort']))
            <a class="btn btn-outline" href="{{ route('admin.staff') }}">Clear</a>
        @endif
    </form>

    <div class="table-wrap">
        @php($canRevertStaff = $actor->hasPermission('edit_users') || $actor->hasPermission('delete_users') || $actor->hasPermission('staff_management') || $actor->canManagePermissions())
        <table id="staff-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Staff Member</th>
                    <th>Email Address</th>
                    <th>Role</th>
                    <th>Organization</th>
                    <th>Responsibilities</th>
                    <th>Commission / Ministry</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="staff-tbody">
                @forelse($staff as $index => $s)
                @php($status = $s->is_verified ? 'Active' : 'Inactive')
                <tr data-user-id="{{ $s->id }}">
                    <td class="row-index">{{ $staff->firstItem() + $index }}</td>
                    <td>
                        <div class="staff-cell-name">
                            @if($s->avatar)
                                <img src="{{ $s->avatar }}" alt="" class="staff-mini-avatar" />
                            @endif
                            <div>
                                <strong>{{ $s->name }}</strong>
                                @if($s->phone)<br><small style="color:var(--muted);font-size:10px;">{{ $s->phone }}</small>@endif
                            </div>
                        </div>
                    </td>
                    <td>{{ $s->email }}</td>
                    <td>
                        <span class="role-badge role-{{ $s->role }}">
                            {{ $s->role_badge_label }}
                        </span>
                    </td>
                    <td>
                        <span class="org-badge org-{{ $s->organization ?? 'parish_administration' }}">
                            {{ $s->organization_label }}
                        </span>
                    </td>
                    <td>
                        <span class="resp-badge" title="{{ $s->responsibilities_label }}">{{ $s->responsibilities_label }}</span>
                    </td>
                    <td>
                        @if($s->hasParishWideCommissionOversight())
                            <span class="commission-badge commission-all">All Commissions</span>
                        @elseif($s->commission_ministry_summary !== '—')
                            <span class="commission-badge commission-specific" title="{{ $s->commission_ministry_summary }}">{{ $s->commission_ministry_summary }}</span>
                        @else
                            <span class="commission-badge commission-none">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($status) }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td class="action-icons">
                        @if($s->isSuperAdmin())
                            <span style="color:var(--muted);font-size:12px;font-style:italic;" title="Super Administrator (Protected)">—</span>
                        @else
                            <button type="button" class="btn-action-icon" title="View Details"
                               onclick="openUserDetailsModal(event, {{ $s->id }})">👁</button>
                            @if($actor->hasPermission('modify_permissions') || $actor->hasPermission('assign_permissions') || $actor->canManagePermissions())
                            <button type="button" class="btn-action-icon btn-action-perm" title="Manage Permissions"
                               onclick="openPermissionsModal(event, {{ $s->id }})">🛡️</button>
                            @endif
                            <a href="#" title="View Activity" class="btn-action-icon action-view-activity"
                               data-user-id="{{ $s->id }}"
                               data-user-name="{{ $s->name }}"
                               onclick="openActivityModal(event, {{ $s->id }}, '{{ addslashes($s->name) }}')">📋</a>
                            @if($canRevertStaff && $s->id !== $actor->id)
                            <button type="button" class="btn-action-icon btn-action-revert" title="Remove from Staff (Revert to Parishioner)"
                               onclick="confirmRevertToParishioner(event, {{ $s->id }}, '{{ addslashes($s->name) }}')">👤↩️</button>
                            @endif
                        @endif
                    </td>
                </tr>
                @empty
                <tr id="empty-staff-row"><td colspan="9" class="empty-cell">No staff members found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span id="staff-count-label">Showing {{ $staff->firstItem() ?? 0 }}-{{ $staff->lastItem() ?? 0 }} of {{ $staff->total() }} staff members</span>
        {{ $staff->onEachSide(1)->links() }}
    </div>

    <!-- ============================================== -->
    <!-- ADD NEW STAFF RIGHT-SIDE DRAWER -->
    <!-- ============================================== -->
    <div id="staff-drawer-backdrop" class="staff-drawer-backdrop" onclick="closeStaffDrawer()" aria-hidden="true"></div>

    <aside id="staff-drawer" class="staff-drawer" role="dialog" aria-modal="true" aria-labelledby="staff-drawer-title" aria-hidden="true">
        <form id="staff-create-form" method="POST" action="{{ route('admin.staff.store') }}" enctype="multipart/form-data" novalidate>
            @csrf

            <!-- Drawer Header -->
            <div class="drawer-header">
                <div>
                    <h3 id="staff-drawer-title" class="drawer-title">Add New Staff</h3>
                    <p class="drawer-subtitle">Create a new staff account for the parish administration.</p>
                </div>
                <button type="button" class="drawer-close-btn" onclick="closeStaffDrawer()" aria-label="Close drawer">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Drawer Scrollable Body -->
            <div class="drawer-body">
                <!-- General Error Banner -->
                <div id="drawer-general-alert" class="alert-banner alert-danger" style="display:none;"></div>

                <!-- Profile Photo Upload (Optional) -->
                <div class="form-group photo-upload-section">
                    <label class="field-label">Profile Photo <span class="optional-badge">(Optional)</span></label>
                    <div class="photo-uploader-box">
                        <div class="avatar-preview-circle" id="avatar-preview-circle">
                            <span id="avatar-placeholder-icon" class="avatar-placeholder-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                    <circle cx="12" cy="7" r="4"></circle>
                                </svg>
                            </span>
                            <img id="avatar-preview-img" class="avatar-preview-img" src="" alt="Avatar preview" style="display:none;" />
                        </div>
                        <div class="photo-uploader-details">
                            <div class="photo-uploader-actions">
                                <label for="profile_photo_input" class="btn btn-outline btn-sm upload-btn-label">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                        <polyline points="17 8 12 3 7 8"></polyline>
                                        <line x1="12" y1="3" x2="12" y2="15"></line>
                                    </svg>
                                    <span>Upload Photo</span>
                                </label>
                                <input type="file" id="profile_photo_input" name="profile_photo" accept="image/jpeg,image/png,image/jpg,image/webp" style="display:none;" onchange="handlePhotoSelect(event)">
                                <button type="button" id="remove-photo-btn" class="btn-link-danger" style="display:none;" onclick="removeSelectedPhoto()">Remove</button>
                            </div>
                            <span class="field-hint">JPG, PNG, or WebP up to 2MB.</span>
                        </div>
                    </div>
                    <div class="field-error-text" id="error-profile_photo"></div>
                </div>

                <!-- Full Name -->
                <div class="form-group">
                    <label for="staff_name" class="field-label">Full Name <span class="required-star">*</span></label>
                    <input type="text" id="staff_name" name="name" class="form-control" placeholder="Enter full name" autocomplete="name" required>
                    <div class="field-error-text" id="error-name"></div>
                </div>

                <!-- Email Address -->
                <div class="form-group">
                    <label for="staff_email" class="field-label">Email Address <span class="required-star">*</span></label>
                    <input type="email" id="staff_email" name="email" class="form-control" placeholder="Enter email address" autocomplete="email" required>
                    <div class="field-error-text" id="error-email"></div>
                </div>

                <!-- Phone Number -->
                <div class="form-group">
                    <label for="staff_phone" class="field-label">Phone Number <span class="optional-badge">(Optional)</span></label>
                    <input type="tel" id="staff_phone" name="phone" class="form-control" placeholder="09XX XXX XXXX" autocomplete="tel">
                    <div class="field-error-text" id="error-phone"></div>
                    <span class="field-hint">Accepts Philippine format (e.g., 0918 123 4567 or +639181234567).</span>
                </div>

                <!-- Organization & Role Selection -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="staff_organization" class="field-label">Organization <span class="required-star">*</span></label>
                        <select id="staff_organization" name="organization" class="form-control" required onchange="handleOrganizationChange(this.value)">
                            @if($actor->hasParishWideAccess())
                                <option value="parish_administration" selected>Parish Administration</option>
                                <option value="commission">Commission</option>
                                <option value="ministry">Ministry</option>
                            @else
                                <option value="commission" selected>Commission</option>
                            @endif
                        </select>
                        <div class="field-error-text" id="error-organization"></div>
                    </div>

                    <div class="form-group">
                        <label for="staff_role" class="field-label">Position / Role <span class="required-star">*</span></label>
                        <select id="staff_role" name="role" class="form-control" required onchange="handleRoleChange(this.value)">
                            <!-- Populated dynamically via handleOrganizationChange() -->
                        </select>
                        <input type="hidden" id="staff_position" name="position" value="">
                        <div class="field-error-text" id="error-role"></div>
                    </div>
                </div>

                <!-- Specific Responsibilities -->
                <div class="form-group">
                    <label for="staff_responsibilities" class="field-label">Responsibilities <span class="optional-badge">(Optional)</span></label>
                    <input type="text" id="staff_responsibilities" name="responsibilities" class="form-control" placeholder="e.g., Administrative Staff, Sacramental Records, Coordinator" autocomplete="off">
                    <div class="field-error-text" id="error-responsibilities"></div>
                </div>

                <!-- Commission Memberships Section (Shown only when Organization is 'commission') -->
                <div class="form-group" id="commission-section-wrap" style="display: none;">
                    <label class="field-label">Commission Connections <span class="required-star">*</span></label>
                    <div class="org-connections-box" id="commission-checkboxes-container">
                        @foreach($commissions as $comm)
                            <div class="org-connection-row">
                                <label class="org-checkbox-label">
                                    <input type="checkbox" name="commission_ids[]" value="{{ $comm->id }}" class="comm-checkbox" onchange="toggleOrgRoleSelect(this, 'comm-role-{{ $comm->id }}')">
                                    <span class="org-name-text">{{ $comm->name }}</span>
                                </label>
                                <select name="commission_roles[{{ $comm->id }}]" id="comm-role-{{ $comm->id }}" class="form-control form-control-xs org-role-select" disabled>
                                    <option value="member" selected>Member</option>
                                    <option value="officer">Officer</option>
                                    <option value="coordinator">Coordinator</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="field-error-text" id="error-commission_ids"></div>
                    <span class="field-hint">Connect this account to one or multiple commissions and specify their role.</span>
                </div>

                <!-- Ministry Memberships Section (Shown only when Organization is 'ministry') -->
                <div class="form-group" id="ministry-section-wrap" style="display: none;">
                    <label class="field-label">Ministry Connections <span class="required-star">*</span></label>
                    <div class="org-connections-box" id="ministry-checkboxes-container">
                        @foreach($ministries as $min)
                            <div class="org-connection-row">
                                <label class="org-checkbox-label">
                                    <input type="checkbox" name="ministry_ids[]" value="{{ $min->id }}" class="min-checkbox" onchange="toggleOrgRoleSelect(this, 'min-role-{{ $min->id }}')">
                                    <span class="org-name-text">{{ $min->name }}</span>
                                </label>
                                <select name="ministry_roles[{{ $min->id }}]" id="min-role-{{ $min->id }}" class="form-control form-control-xs org-role-select" disabled>
                                    <option value="member" selected>Member</option>
                                    <option value="officer">Officer</option>
                                    <option value="coordinator">Coordinator</option>
                                </select>
                            </div>
                        @endforeach
                    </div>
                    <div class="field-error-text" id="error-ministry_ids"></div>
                    <span class="field-hint">Connect this account to one or multiple ministries and specify their role.</span>
                </div>


                <!-- Account Status -->
                <div class="form-group">
                    <label for="staff_status" class="field-label">Account Status <span class="required-star">*</span></label>
                    <select id="staff_status" name="status" class="form-control" required>
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                    <div class="field-error-text" id="error-status"></div>
                </div>

                @if($actor->hasParishWideAccess())
                <!-- Permissions / Privileges Matrix -->
                <div class="form-group" id="permissions-field-wrap">
                    <div class="field-header-row">
                        <label class="field-label" style="margin-bottom:0;">Permissions / Privileges <span class="required-star">*</span></label>
                        <div class="perm-header-actions">
                            <button type="button" class="btn-perm-toggle" onclick="setAllPermissions(true)">Select All</button>
                            <span class="perm-action-divider">·</span>
                            <button type="button" class="btn-perm-toggle" onclick="setAllPermissions(false)">Clear All</button>
                        </div>
                    </div>
                    <span class="field-hint" style="margin-bottom:8px;display:block;">Configure access privileges for system sections and sidebar modules.</span>

                    <div class="permissions-checklist-wrap">
                        <!-- COMMUNICATION -->
                        <div class="perm-group">
                            <div class="perm-group-header">Communication</div>
                            <div class="perm-group-items">
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="messages" id="perm-messages" class="staff-perm-checkbox" checked>
                                    <span>Messages</span>
                                </label>
                            </div>
                        </div>

                        <!-- USER MANAGEMENT -->
                        <div class="perm-group">
                            <div class="perm-group-header">User Management</div>
                            <div class="perm-group-items">
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="parishioners" id="perm-parishioners" class="staff-perm-checkbox" checked>
                                    <span>Parishioners</span>
                                </label>
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="staff_management" id="perm-staff-management" class="staff-perm-checkbox">
                                    <span>Staff Management</span>
                                </label>
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="audit_logs" id="perm-audit-logs" class="staff-perm-checkbox">
                                    <span>Audit Logs</span>
                                </label>
                            </div>
                        </div>

                        <!-- PARISH MINISTRIES -->
                        <div class="perm-group">
                            <div class="perm-group-header">Parish Ministries</div>
                            <div class="perm-group-items">
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="manage_ministries" id="perm-manage-ministries" class="staff-perm-checkbox">
                                    <span>Manage Ministries</span>
                                </label>
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="ministry_requests" id="perm-ministry-requests" class="staff-perm-checkbox" checked>
                                    <span>Ministry Requests</span>
                                </label>
                            </div>
                        </div>

                        <!-- LITURGY & RECORDS -->
                        <div class="perm-group">
                            <div class="perm-group-header">Liturgy &amp; Records</div>
                            <div class="perm-group-items">
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="mass_schedules" id="perm-mass-schedules" class="staff-perm-checkbox" checked>
                                    <span>Mass &amp; Confession Schedule</span>
                                </label>
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="announcements" id="perm-announcements" class="staff-perm-checkbox" checked>
                                    <span>Announcements</span>
                                </label>
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="donations" id="perm-donations" class="staff-perm-checkbox" checked>
                                    <span>Donations</span>
                                </label>
                            </div>
                        </div>

                        <!-- OVERSIGHT -->
                        <div class="perm-group">
                            <div class="perm-group-header">Oversight</div>
                            <div class="perm-group-items">
                                <label class="perm-checkbox-item">
                                    <input type="checkbox" name="permissions[]" value="all_commissions" id="perm-all-commissions" class="staff-perm-checkbox">
                                    <span>Parish-wide Commission Oversight</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="field-error-text" id="error-permissions"></div>
                </div>
                @endif

                <!-- Password -->
                <div class="form-group">
                    <label for="staff_password" class="field-label">Password <span class="required-star">*</span></label>
                    <div class="input-with-icon-wrap">
                        <input type="password" id="staff_password" name="password" class="form-control" placeholder="Enter password (min. 8 characters)" autocomplete="new-password" required oninput="handlePasswordInput(this.value)">
                        <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('staff_password', this)" aria-label="Toggle password visibility">
                            <svg class="eye-open-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="field-error-text" id="error-password"></div>

                    <!-- Password Strength UX -->
                    <div class="password-strength-container" id="strength-meter-box" style="display:none;">
                        <div class="strength-track">
                            <div class="strength-bar-fill" id="strength-fill"></div>
                        </div>
                        <div class="strength-feedback-row">
                            <span class="strength-status-text">Strength: <strong id="strength-status-word">Too short</strong></span>
                            <span class="strength-hint-text" id="strength-hint-text">Minimum 8 characters</span>
                        </div>
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="staff_password_confirmation" class="field-label">Confirm Password <span class="required-star">*</span></label>
                    <div class="input-with-icon-wrap">
                        <input type="password" id="staff_password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm password" autocomplete="new-password" required oninput="handlePasswordConfirmInput()">
                        <button type="button" class="btn-toggle-eye" onclick="togglePasswordVisibility('staff_password_confirmation', this)" aria-label="Toggle confirm password visibility">
                            <svg class="eye-open-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-closed-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="field-error-text" id="error-password_confirmation"></div>
                    <div class="password-match-tag" id="password-match-tag" style="display:none;"></div>
                </div>
            </div>

            <!-- Drawer Sticky Footer -->
            <div class="drawer-footer">
                <button type="button" class="btn btn-outline btn-cancel" onclick="closeStaffDrawer()">Cancel</button>
                <button type="submit" id="staff-submit-btn" class="btn btn-primary btn-submit">
                    <span class="btn-spinner" id="submit-spinner" style="display:none;">
                        <svg class="spinner-svg" viewBox="0 0 24 24">
                            <circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle>
                        </svg>
                    </span>
                    <span id="submit-btn-text">Create Staff</span>
                </button>
            </div>
        </form>
    </aside>

    <!-- Floating Success/Error Toast -->
    <div id="staff-toast" class="staff-toast" role="status" aria-live="polite" style="display:none;">
        <div class="toast-indicator-icon" id="toast-indicator-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
        </div>
        <div class="toast-body">
            <strong id="toast-title" class="toast-title">Notification</strong>
            <p id="toast-message" class="toast-message">Action completed.</p>
        </div>
        <button type="button" class="toast-close-btn" onclick="hideToast()" aria-label="Close notification">&times;</button>
    </div>

    <!-- ============================================== -->
    <!-- ACTIVITY HISTORY MODAL -->
    <!-- ============================================== -->
    <div id="activity-modal-backdrop" class="activity-modal-backdrop" onclick="closeActivityModal()" style="display:none;" aria-hidden="true"></div>
    <div id="activity-modal" class="activity-modal" role="dialog" aria-modal="true" aria-labelledby="activity-modal-title" style="display:none;" aria-hidden="true">
        <div class="activity-modal-header">
            <div>
                <h3 id="activity-modal-title" class="activity-modal-title">Activity History</h3>
                <p id="activity-modal-subtitle" class="activity-modal-subtitle">Recent account actions</p>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closeActivityModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="activity-modal-body" id="activity-modal-body">
            <div class="activity-loading" id="activity-loading">
                <svg class="spinner-svg" viewBox="0 0 24 24" width="30" height="30"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
                <span>Loading activity...</span>
            </div>
            <div id="activity-timeline" class="activity-timeline" style="display:none;"></div>
            <div id="activity-empty" class="activity-empty-state" style="display:none;">No activity records found for this staff member.</div>
            <div id="activity-error" class="activity-error-state" style="display:none;"></div>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- DEDICATED PERMISSION MANAGEMENT MODAL -->
    <!-- ============================================== -->
    <div id="permissions-modal-backdrop" class="modal-backdrop-common" onclick="closePermissionsModal()" style="display:none;" aria-hidden="true"></div>
    <div id="permissions-modal" class="custom-modal perm-modal" role="dialog" aria-modal="true" aria-labelledby="perm-modal-title" style="display:none;" aria-hidden="true">
        <div class="custom-modal-header">
            <div>
                <div class="modal-pretitle">Permission Management</div>
                <h3 id="perm-modal-title" class="custom-modal-title">Manage Account Permissions</h3>
                <div id="perm-modal-user-meta" class="perm-user-meta"></div>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closePermissionsModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="perm-modal-toolbar">
            <span class="perm-modal-hint">Select granted privileges by functional module. Changes are recorded in audit logs.</span>
            <div class="perm-toolbar-actions">
                <button type="button" class="btn-perm-tool" onclick="setAllManagePermissions(true)">Select All</button>
                <span class="perm-action-divider">·</span>
                <button type="button" class="btn-perm-tool" onclick="setAllManagePermissions(false)">Clear All</button>
                <span class="perm-action-divider">·</span>
                <button type="button" class="btn-perm-tool" onclick="resetManagePermissionsToDefaults()">Reset to Role Defaults</button>
            </div>
        </div>

        <div class="custom-modal-body" id="perm-modal-body">
            <div class="activity-loading" id="perm-loading">
                <svg class="spinner-svg" viewBox="0 0 24 24" width="30" height="30"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
                <span>Loading permissions...</span>
            </div>
            <form id="perm-manage-form" onsubmit="saveUserPermissions(event)" style="display:none;">
                <input type="hidden" id="perm_target_user_id" value="">
                <div id="perm-modules-container" class="perm-modules-grid"></div>
            </form>
        </div>

        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closePermissionsModal()">Cancel</button>
            <button type="button" class="btn-modal-save" id="btn-save-permissions" onclick="document.getElementById('perm-manage-form').dispatchEvent(new Event('submit', {cancelable: true, bubbles: true}))">
                <span id="btn-save-perm-text">Save Permissions</span>
                <svg id="spinner-save-perm" class="spinner-svg" viewBox="0 0 24 24" width="16" height="16" style="display:none;"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
            </button>
        </div>
    </div>

    <!-- ============================================== -->
    <!-- USER DETAILS MODAL -->
    <!-- ============================================== -->
    <div id="user-details-modal-backdrop" class="modal-backdrop-common" onclick="closeUserDetailsModal()" style="display:none;" aria-hidden="true"></div>
    <div id="user-details-modal" class="custom-modal user-details-modal" role="dialog" aria-modal="true" aria-labelledby="user-details-modal-title" style="display:none;" aria-hidden="true">
        <div class="custom-modal-header">
            <div>
                <div class="modal-pretitle">Account Overview</div>
                <h3 id="user-details-modal-title" class="custom-modal-title">User Details</h3>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closeUserDetailsModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="custom-modal-body" id="user-details-modal-body">
            <div class="activity-loading" id="user-details-loading">
                <svg class="spinner-svg" viewBox="0 0 24 24" width="30" height="30"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
                <span>Loading user details...</span>
            </div>
            <div id="user-details-content" style="display:none;"></div>
        </div>
        <div class="custom-modal-footer" id="user-details-modal-footer" style="display:none;">
            <button type="button" class="btn-modal-cancel" onclick="closeUserDetailsModal()">Close</button>
            <button type="button" class="btn-modal-action-secondary" id="btn-details-activity" onclick="openActivityFromDetails()">View Activity</button>
            @if($actor->hasPermission('modify_permissions') || $actor->hasPermission('assign_permissions') || $actor->canManagePermissions())
            <button type="button" class="btn-modal-save" id="btn-details-perm" onclick="openPermissionsFromDetails()">Manage Permissions</button>
            @endif
            @if($canRevertStaff)
            <button type="button" class="btn-modal-danger" id="btn-details-revert" onclick="revertFromDetails()" style="background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;padding:8px 14px;border-radius:6px;font-size:12px;font-weight:600;cursor:pointer;margin-left:auto;">👤 Remove from Staff</button>
            @endif
        </div>
    </div>

    <!-- ============================================== -->
    <!-- REVERT TO PARISHIONER CONFIRMATION MODAL -->
    <!-- ============================================== -->
    <div id="revert-modal-backdrop" class="modal-backdrop-common" onclick="closeRevertModal()" style="display:none;" aria-hidden="true"></div>
    <div id="revert-modal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="revert-modal-title" style="display:none; max-width: 490px;" aria-hidden="true">
        <div class="custom-modal-header">
            <div>
                <div class="modal-pretitle">Staff Roster Management</div>
                <h3 id="revert-modal-title" class="custom-modal-title">Remove from Staff</h3>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closeRevertModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="custom-modal-body" style="padding: 20px 24px;">
            <p style="margin: 0 0 14px; font-size: 13px; line-height: 1.5; color: #1e293b;">
                Are you sure you want to remove <strong id="revert-target-name"></strong> from staff and commissions?
            </p>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #3b82f6; padding: 12px 14px; border-radius: 6px; font-size: 12px; color: #475569; line-height: 1.5;">
                <strong style="color:#1e293b;">Effect of this action:</strong>
                <ul style="margin: 6px 0 0; padding-left: 18px;">
                    <li>Account will transition to a regular <strong>Parishioner</strong>.</li>
                    <li>All commission connections and staff privileges are revoked.</li>
                    <li>The user will no longer appear in Staff Management.</li>
                    <li>Their parishioner account remains intact for Mass intentions, donations, and portal requests.</li>
                </ul>
            </div>
            <input type="hidden" id="revert-target-id" value="">
        </div>
        <div class="custom-modal-footer">
            <button type="button" class="btn-modal-cancel" onclick="closeRevertModal()">Cancel</button>
            <button type="button" class="btn-modal-save" id="btn-confirm-revert" style="background: #dc2626; border-color: #dc2626;" onclick="submitRevertToParishioner()">
                <span id="text-confirm-revert">Remove & Revert to Parishioner</span>
                <svg id="spinner-revert" class="spinner-svg" viewBox="0 0 24 24" width="16" height="16" style="display:none;"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
            </button>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .sort-select-btn {
            display: inline-flex;
            align-items: center;
            height: 38px;
            padding: 0 34px 0 14px;
            border-radius: var(--radius-sm, 8px);
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-secondary, #334155);
            background-color: #fff;
            border: 1px solid var(--border, #cbd5e1);
            cursor: pointer;
            transition: all 0.18s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 13px;
            outline: none;
        }
        .sort-select-btn:hover {
            background-color: #f8fafc;
            border-color: var(--muted-light, #94a3b8);
        }
        .sort-select-btn:focus {
            border-color: var(--navy, #062f78);
            box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.1);
        }
        .page-header .actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }
        .toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
        .toolbar input{flex:1;min-width:200px;padding:10px 14px;border:1px solid var(--border);border-radius:7px;font-size:12px;background:#fff}
        .toolbar select{padding:10px 14px;border:1px solid var(--border);border-radius:7px;font-size:12px;background:#fff}
        .table-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:11px;background:#fff}
        table{width:100%;border-collapse:collapse;font-size:12px}
        th{text-align:left;padding:14px 16px;background:#f8fafc;color:var(--muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--border)}
        td{padding:12px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafcfe}
        .staff-cell-name{display:flex;align-items:center;gap:10px}
        .staff-mini-avatar{width:26px;height:26px;border-radius:50%;object-fit:cover;border:1px solid var(--border)}
        .status-badge{padding:4px 12px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block}
        .status-active{background:#e2f5e2;color:#1a7a1a}
        .status-inactive{background:#fee2e2;color:#b91c1c}
        .role-badge{padding:4px 10px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block;white-space:nowrap}
        .role-admin,.role-super_admin{background:#dbeafe;color:#1e40af}
        .role-staff,.role-commission_member{background:#e0e7ff;color:#4338ca}
        .role-commission_admin{background:#fef9c3;color:#854d0e}
        .role-parish_priest,.role-parochial_vicar,.role-parish_secretary{background:#fce7f3;color:#9d174d}
        .commission-badge{padding:3px 10px;border-radius:20px;font-size:9px;font-weight:600;display:inline-block;white-space:nowrap}
        .commission-all{background:#f0f9ff;color:#0369a1;border:1px solid #bae6fd}
        .commission-specific{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
        .commission-none{color:var(--muted)}
        .org-badge{padding:4px 10px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block;white-space:nowrap}
        .org-parish_administration{background:#fdf2f8;color:#9d174d;border:1px solid #fbcfe8}
        .org-commission{background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe}
        .org-ministry{background:#f0fdf4;color:#15803d;border:1px solid #bbf7d0}
        .org-parishioner{background:#f1f5f9;color:#475569;border:1px solid #cbd5e1}
        .org-connections-box {
            max-height: 180px;
            overflow-y: auto;
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .org-connection-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 7px 10px;
            border-radius: 6px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 0.15s ease;
        }
        .org-connection-row:hover {
            background: #f1f5f9;
            border-color: #cbd5e1;
        }
        .org-checkbox-label {
            display: flex;
            align-items: center;
            gap: 9px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            color: #1e293b;
            flex: 1;
            min-width: 0;
            line-height: 1.35;
            user-select: none;
        }
        .org-checkbox-label input[type="checkbox"] {
            flex-shrink: 0;
            width: 15px;
            height: 15px;
            accent-color: var(--navy);
            cursor: pointer;
            margin: 0;
        }
        .org-name-text {
            flex: 1;
            min-width: 0;
            white-space: normal;
            word-break: normal;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .org-role-select, select.form-control-xs {
            width: 112px !important;
            min-width: 112px !important;
            max-width: 112px !important;
            flex-shrink: 0;
            height: 30px;
            padding: 3px 8px;
            font-size: 11px;
            font-weight: 500;
            border-radius: 5px;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            cursor: pointer;
        }
        .org-role-select:disabled, select.form-control-xs:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            border-color: #e2e8f0;
            cursor: not-allowed;
            opacity: 0.65;
        }
        .org-role-select:focus, select.form-control-xs:focus {
            border-color: var(--navy);
            outline: none;
            box-shadow: 0 0 0 2px rgba(6, 47, 120, 0.12);
        }
        .field-header-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:4px}
        .perm-header-actions{display:inline-flex;align-items:center;gap:6px}
        .btn-perm-toggle{background:none;border:none;padding:0;font-size:11px;font-weight:600;color:var(--navy);cursor:pointer;text-decoration:underline}
        .btn-perm-toggle:hover{color:#0b45b0}
        .perm-action-divider{color:var(--muted);font-size:10px}
        .permissions-checklist-wrap{max-height:220px;overflow-y:auto;background:#f8fafc;border:1px solid var(--border);border-radius:8px;padding:10px 12px;display:flex;flex-direction:column;gap:10px}
        .perm-group{display:flex;flex-direction:column;gap:6px}
        .perm-group-header{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;color:var(--muted);padding-bottom:3px;border-bottom:1px dashed #e2e8f0}
        .perm-group-items{display:grid;grid-template-columns:1fr 1fr;gap:6px 12px}
        .perm-checkbox-item{display:flex;align-items:center;gap:7px;font-size:11px;color:#334155;cursor:pointer;line-height:1.3}
        .perm-checkbox-item input[type="checkbox"]{accent-color:var(--navy);cursor:pointer}
        .action-icons{display:flex;gap:10px}
        .action-icons a{color:var(--muted);text-decoration:none;font-size:14px;transition:color 0.15s ease;cursor:pointer}
        .action-icons a:hover{color:var(--navy)}
        @media(max-width:620px){.toolbar input{min-width:100%}}
        .empty-cell{text-align:center;padding:28px;color:var(--muted)}

        /* Activity History Modal */
        .activity-modal-backdrop{position:fixed;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(2px);z-index:1050}
        .activity-modal{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);width:min(620px,95vw);max-height:80vh;background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,0.25);z-index:1051;display:flex;flex-direction:column;overflow:hidden}
        .activity-modal-header{display:flex;justify-content:space-between;align-items:flex-start;padding:22px 24px 16px;border-bottom:1px solid var(--border)}
        .activity-modal-title{font-size:16px;font-weight:700;color:var(--navy);margin:0}
        .activity-modal-subtitle{font-size:12px;color:var(--muted);margin:3px 0 0}
        .activity-modal-body{flex:1;overflow-y:auto;padding:20px 24px}
        .activity-loading{display:flex;align-items:center;gap:12px;justify-content:center;padding:30px;color:var(--muted)}
        .activity-timeline{display:flex;flex-direction:column;gap:0}
        .activity-item{display:flex;gap:14px;position:relative;padding-bottom:18px}
        .activity-item:last-child{padding-bottom:0}
        .activity-item:not(:last-child)::before{content:'';position:absolute;left:13px;top:28px;bottom:0;width:1px;background:#e2e8f0}
        .activity-dot{width:28px;height:28px;border-radius:50%;background:#f1f5f9;border:2px solid #e2e8f0;display:grid;place-items:center;flex-shrink:0;font-size:12px;position:relative}
        .activity-dot.dot-created{background:#dbeafe;border-color:#93c5fd;color:#1e40af}
        .activity-dot.dot-updated{background:#fef9c3;border-color:#fde047;color:#854d0e}
        .activity-dot.dot-deactivated,.activity-dot.dot-deleted{background:#fee2e2;border-color:#fca5a5;color:#b91c1c}
        .activity-dot.dot-login{background:#dcfce7;border-color:#86efac;color:#15803d}
        .activity-content{flex:1;min-width:0}
        .activity-action-row{display:flex;align-items:center;gap:8px;margin-bottom:3px}
        .activity-action-badge{padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;display:inline-block;background:#e0e7ff;color:#4338ca}
        .activity-time{font-size:10px;color:var(--muted);margin-left:auto}
        .activity-desc{font-size:12px;color:#475569;line-height:1.5}
        .activity-meta{font-size:10px;color:#94a3b8;margin-top:4px}
        .activity-empty-state,.activity-error-state{text-align:center;padding:30px;font-size:13px;color:var(--muted)}
        .activity-error-state{color:#b91c1c}

        /* Highlight animation for newly created row */
        @keyframes highlightRow {
            0% { background-color: #dbeafe; }
            100% { background-color: transparent; }
        }
        .row-highlight {
            animation: highlightRow 3s ease forwards;
        }

        /* Flash banners */
        .alert-banner {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            font-weight: 500;
        }
        .alert-success {
            background: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .alert-danger {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* ===== Right-Side Drawer Styles ===== */
        .staff-drawer-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 1040;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
        }
        .staff-drawer-backdrop.active {
            opacity: 1;
            pointer-events: auto;
        }

        .staff-drawer {
            position: fixed;
            top: 0;
            right: 0;
            bottom: 0;
            width: 480px;
            max-width: 100%;
            background: #ffffff;
            box-shadow: -8px 0 32px rgba(6, 47, 120, 0.15);
            z-index: 1050;
            transform: translateX(100%);
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .staff-drawer.open {
            transform: translateX(0);
        }

        .staff-drawer form {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }

        .drawer-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 22px 26px 18px;
            border-bottom: 1px solid #f1f5f9;
            background: #ffffff;
            flex-shrink: 0;
        }
        .drawer-title {
            margin: 0 0 4px;
            font-size: 19px;
            color: var(--navy);
            font-family: 'Libre Baskerville', Georgia, serif;
            font-weight: 700;
        }
        .drawer-subtitle {
            margin: 0;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }
        .drawer-close-btn {
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 6px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
            margin-top: -2px;
            margin-right: -4px;
        }
        .drawer-close-btn:hover {
            background: #f1f5f9;
            color: var(--ink);
        }

        .drawer-body {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            padding: 22px 26px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }
        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
        }
        .required-star {
            color: #dc2626;
            font-weight: 700;
            margin-left: 2px;
        }
        .optional-badge {
            font-size: 11px;
            font-weight: 400;
            color: #64748b;
            margin-left: 4px;
        }
        .form-control {
            width: 100%;
            padding: 10px 13px;
            border: 1px solid var(--border);
            border-radius: 7px;
            font-size: 13px;
            color: var(--ink);
            background: #ffffff;
            font-family: inherit;
            box-sizing: border-box;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(21, 92, 180, 0.12);
        }
        .form-control:disabled {
            background-color: #f1f5f9;
            color: #94a3b8;
            cursor: not-allowed;
            border-color: #e2e8f0;
            opacity: 0.85;
        }
        .form-control.is-invalid {
            border-color: #dc2626 !important;
            background-color: #fffafa;
        }
        .field-hint {
            font-size: 11px;
            color: #64748b;
            margin-top: 5px;
            line-height: 1.35;
        }
        .field-error-text {
            font-size: 11px;
            color: #dc2626;
            font-weight: 600;
            margin-top: 4px;
            display: none;
        }
        .field-error-text.has-error {
            display: block;
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        /* Profile photo widget */
        .photo-uploader-box {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 12px 14px;
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
        }
        .avatar-preview-circle {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #e2e8f0;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }
        .avatar-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-uploader-details {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .photo-uploader-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .upload-btn-label {
            margin: 0;
            cursor: pointer;
            padding: 6px 12px;
            font-size: 11px;
        }
        .btn-link-danger {
            background: none;
            border: none;
            color: #dc2626;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            padding: 4px 6px;
        }
        .btn-link-danger:hover {
            text-decoration: underline;
        }

        /* Password input with eye toggle */
        .input-with-icon-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-with-icon-wrap .form-control {
            padding-right: 40px;
        }
        .btn-toggle-eye {
            position: absolute;
            right: 6px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            padding: 6px;
            color: #64748b;
            cursor: pointer;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease;
        }
        .btn-toggle-eye:hover {
            color: var(--navy);
        }

        /* Password Strength UX */
        .password-strength-container {
            margin-top: 8px;
        }
        .strength-track {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-bar-fill {
            height: 100%;
            width: 0%;
            border-radius: 2px;
            transition: width 0.25s ease, background-color 0.25s ease;
        }
        .strength-feedback-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            font-size: 11px;
            color: #64748b;
        }
        .strength-status-text strong {
            text-transform: capitalize;
        }

        /* Password match indicator */
        .password-match-tag {
            font-size: 11px;
            margin-top: 5px;
            font-weight: 600;
        }
        .password-match-tag.match {
            color: #16a34a;
        }
        .password-match-tag.mismatch {
            color: #dc2626;
        }

        /* Drawer Footer */
        .drawer-footer {
            padding: 16px 26px;
            background: #f8fafc;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
        }
        .btn-cancel {
            padding: 9px 18px;
        }
        .btn-submit {
            padding: 9px 22px;
            min-width: 124px;
        }
        .btn-submit:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-spinner {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 6px;
        }
        .spinner-svg {
            width: 14px;
            height: 14px;
            animation: spin 0.8s linear infinite;
        }
        .spinner-path {
            stroke: #ffffff;
            stroke-linecap: round;
            stroke-dasharray: 45;
            stroke-dashoffset: 15;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }

        /* Toast notification */
        .staff-toast {
            position: fixed;
            top: 24px;
            right: 24px;
            background: #ffffff;
            border: 1px solid #bbf7d0;
            border-left: 5px solid #16a34a;
            border-radius: 9px;
            box-shadow: 0 10px 28px rgba(15, 23, 42, 0.12);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            z-index: 2000;
            min-width: 320px;
            max-width: 440px;
            animation: toastSlide 0.28s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .staff-toast.toast-error {
            border-color: #fecaca;
            border-left-color: #dc2626;
        }
        .toast-indicator-icon {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: grid;
            place-items: center;
            flex-shrink: 0;
        }
        .staff-toast.toast-error .toast-indicator-icon {
            background: #fee2e2;
            color: #dc2626;
        }
        .toast-body {
            flex: 1;
            min-width: 0;
        }
        .toast-title {
            display: block;
            font-size: 13px;
            color: var(--ink);
            margin-bottom: 2px;
        }
        .toast-message {
            margin: 0;
            font-size: 12px;
            color: #475569;
            line-height: 1.4;
        }
        .toast-close-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
        }
        .toast-close-btn:hover {
            color: var(--ink);
        }
        @keyframes toastSlide {
            0% { transform: translateY(-16px); opacity: 0; }
            100% { transform: translateY(0); opacity: 1; }
        }

        /* Mobile drawer responsiveness */
        @media(max-width: 640px) {
            .staff-drawer {
                width: 100%;
            }
            .drawer-header, .drawer-body, .drawer-footer {
                padding-left: 18px;
                padding-right: 18px;
            }
            .form-grid-2 {
                grid-template-columns: 1fr;
            }
            .staff-toast {
                left: 16px;
                right: 16px;
                min-width: 0;
                top: 16px;
            }
        }

        /* Action buttons in staff table */
        .btn-action-icon {
            background: none;
            border: none;
            padding: 4px;
            font-size: 15px;
            color: var(--muted);
            cursor: pointer;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, background-color 0.15s ease;
            line-height: 1;
        }
        .btn-action-icon:hover {
            color: var(--navy);
            background-color: #f1f5f9;
        }
        .btn-action-perm:hover {
            color: #0369a1;
            background-color: #f0f9ff;
        }
        .btn-action-revert:hover {
            color: #dc2626;
            background-color: #fee2e2;
        }

        /* Common Modal Backdrop & Container */
        .modal-backdrop-common {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 1060;
        }
        .custom-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            z-index: 1061;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .perm-modal {
            width: min(880px, 95vw);
            max-height: 88vh;
        }
        .user-details-modal {
            width: min(680px, 95vw);
            max-height: 85vh;
        }

        /* Modal Header & Toolbar */
        .custom-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
        }
        .modal-pretitle {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 3px;
        }
        .custom-modal-title {
            font-size: 16px;
            font-weight: 700;
            color: var(--navy);
            margin: 0;
        }
        .perm-user-meta {
            font-size: 12px;
            color: var(--muted);
            margin-top: 5px;
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .perm-modal-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
            font-size: 11px;
            color: var(--muted);
            flex-wrap: wrap;
            gap: 8px;
        }
        .perm-modal-hint {
            flex: 1;
            min-width: 240px;
            line-height: 1.4;
        }
        .perm-toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-perm-tool {
            background: none;
            border: none;
            color: var(--navy);
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            padding: 0;
            text-decoration: underline;
        }
        .btn-perm-tool:hover {
            color: #0b45b0;
        }

        /* Modal Body & Permissions Grid */
        .custom-modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 20px 24px;
        }
        .perm-modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 14px;
        }
        .perm-module-card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .perm-module-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
        }
        .perm-module-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 8px;
        }
        .perm-module-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .perm-module-quick {
            font-size: 10px;
            color: var(--muted);
            cursor: pointer;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
        }
        .perm-module-quick:hover {
            color: var(--navy);
        }
        .perm-module-actions {
            display: flex;
            flex-direction: column;
            gap: 6px;
            padding-top: 4px;
        }
        .perm-item-label {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 11px;
            color: #334155;
            cursor: pointer;
            line-height: 1.35;
        }
        .perm-item-label input[type="checkbox"] {
            margin-top: 2px;
            accent-color: var(--navy);
            cursor: pointer;
        }

        /* Modal Footer & Buttons */
        .custom-modal-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            background: #f8fafc;
        }
        .btn-modal-cancel {
            padding: 8px 18px;
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: var(--ink);
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-modal-cancel:hover {
            background: #f1f5f9;
        }
        .btn-modal-save {
            padding: 8px 20px;
            background: var(--navy);
            border: 1px solid transparent;
            border-radius: 8px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s ease;
        }
        .btn-modal-save:hover {
            background: #0a3b8c;
        }
        .btn-modal-save:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }
        .btn-modal-action-secondary {
            padding: 8px 16px;
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .btn-modal-action-secondary:hover {
            background: #e2e8f0;
        }

        /* User Details Content Styles */
        .ud-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding-bottom: 16px;
            border-bottom: 1px solid #f1f5f9;
            margin-bottom: 18px;
        }
        .ud-avatar {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: var(--navy);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 18px;
            font-weight: 700;
            overflow: hidden;
            flex-shrink: 0;
            border: 2px solid #e2e8f0;
        }
        .ud-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ud-header-info {
            flex: 1;
            min-width: 0;
        }
        .ud-name {
            font-size: 16px;
            font-weight: 700;
            color: var(--ink);
            margin: 0 0 3px;
        }
        .ud-meta-row {
            font-size: 11px;
            color: var(--muted);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .ud-section {
            margin-bottom: 18px;
        }
        .ud-section:last-child {
            margin-bottom: 0;
        }
        .ud-section-title {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--muted);
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .ud-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .ud-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
        }
        .ud-label {
            font-size: 10px;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 2px;
        }
        .ud-value {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink);
            word-break: break-word;
        }
        .ud-connection-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 6px;
        }
        .ud-connection-card:last-child {
            margin-bottom: 0;
        }
        .ud-connection-name {
            font-size: 12px;
            font-weight: 600;
            color: var(--navy);
        }
        .ud-connection-role {
            font-size: 11px;
            padding: 2px 8px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            color: #475569;
            font-weight: 500;
        }
        .ud-perm-summary-box {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .ud-perm-summary-counts {
            display: flex;
            gap: 14px;
            font-size: 12px;
        }
        .ud-perm-count-granted {
            color: #0369a1;
            font-weight: 700;
        }
        .ud-perm-count-restricted {
            color: #64748b;
            font-weight: 500;
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const canManagePermissions = @json($actor->hasPermission('modify_permissions') || $actor->hasPermission('assign_permissions') || $actor->canManagePermissions());
            const canRevertStaff = @json($canRevertStaff);
            const currentUserId = {{ $actor->id }};
            const drawer = document.getElementById('staff-drawer');
            const backdrop = document.getElementById('staff-drawer-backdrop');
            const form = document.getElementById('staff-create-form');
            const submitBtn = document.getElementById('staff-submit-btn');
            const submitSpinner = document.getElementById('submit-spinner');
            const submitBtnText = document.getElementById('submit-btn-text');
            const toast = document.getElementById('staff-toast');
            let toastTimer = null;
            window.toastTimer = null;
            // --- Sort & Export Handlers ---
            window.handleSortChange = function(sortVal) {
                const filterForm = document.getElementById('staff-filter-form');
                if (!filterForm) return;
                const hiddenSort = document.getElementById('staff-hidden-sort');
                if (hiddenSort) {
                    hiddenSort.value = sortVal;
                }
                filterForm.submit();
            };

            window.exportStaffData = function() {
                const filterForm = document.getElementById('staff-filter-form');
                if (!filterForm) return;

                let exportInput = filterForm.querySelector('input[name="export"]');
                if (!exportInput) {
                    exportInput = document.createElement('input');
                    exportInput.type = 'hidden';
                    exportInput.name = 'export';
                    filterForm.appendChild(exportInput);
                }
                exportInput.value = 'csv';

                const sortSelect = document.getElementById('staff-sort-select');
                const hiddenSort = document.getElementById('staff-hidden-sort');
                if (sortSelect && hiddenSort) {
                    hiddenSort.value = sortSelect.value;
                }

                filterForm.submit();

                setTimeout(() => {
                    if (exportInput && exportInput.parentNode) {
                        exportInput.remove();
                    }
                }, 500);
            };

            // --- Drawer Open / Close ---
            window.openStaffDrawer = function() {
                try {
                    resetForm();
                } catch (e) {
                    console.warn('resetForm warning:', e);
                }
                const drawerEl = document.getElementById('staff-drawer');
                const backdropEl = document.getElementById('staff-drawer-backdrop');
                if (drawerEl) {
                    drawerEl.classList.add('open');
                    drawerEl.setAttribute('aria-hidden', 'false');
                }
                if (backdropEl) {
                    backdropEl.classList.add('active');
                    backdropEl.setAttribute('aria-hidden', 'false');
                }
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    document.getElementById('staff_name')?.focus();
                }, 150);
            };

            window.closeStaffDrawer = function() {
                const drawerEl = document.getElementById('staff-drawer');
                const backdropEl = document.getElementById('staff-drawer-backdrop');
                if (drawerEl) {
                    drawerEl.classList.remove('open');
                    drawerEl.setAttribute('aria-hidden', 'true');
                }
                if (backdropEl) {
                    backdropEl.classList.remove('active');
                    backdropEl.setAttribute('aria-hidden', 'true');
                }
                document.body.style.overflow = '';
            };

            // Close on ESC
            window.addEventListener('keydown', (e) => {
                const drawerEl = document.getElementById('staff-drawer');
                if (e.key === 'Escape' && drawerEl && drawerEl.classList.contains('open')) {
                    closeStaffDrawer();
                }
            });

            // --- Reset Form State ---
            function resetForm() {
                const formEl = document.getElementById('staff-create-form');
                if (formEl) formEl.reset();
                clearAllErrors();
                if (typeof window.removeSelectedPhoto === 'function') {
                    window.removeSelectedPhoto();
                }
                const alertBox = document.getElementById('drawer-general-alert');
                if (alertBox) alertBox.style.display = 'none';
                const meterBox = document.getElementById('strength-meter-box');
                if (meterBox) meterBox.style.display = 'none';
                const matchTag = document.getElementById('password-match-tag');
                if (matchTag) matchTag.style.display = 'none';
                setSubmitting(false);

                // Reset all role selects
                document.querySelectorAll('.org-role-select').forEach(sel => {
                    sel.disabled = true;
                    sel.value = 'member';
                });

                const orgSelect = document.getElementById('staff_organization');
                if (orgSelect && typeof window.handleOrganizationChange === 'function') {
                    window.handleOrganizationChange(orgSelect.value);
                }
            }

            function clearAllErrors() {
                document.querySelectorAll('.field-error-text').forEach(el => {
                    el.textContent = '';
                    el.classList.remove('has-error');
                });
                document.querySelectorAll('.form-control').forEach(el => {
                    el.classList.remove('is-invalid');
                });
            }

            function setFieldError(field, message) {
                const formEl = document.getElementById('staff-create-form');
                const input = formEl ? formEl.querySelector(`[name="${field}"]`) : null;
                if (input) {
                    input.classList.add('is-invalid');
                }
                const errorBox = document.getElementById(`error-${field}`);
                if (errorBox) {
                    errorBox.textContent = message;
                    errorBox.classList.add('has-error');
                }
            }

            function setSubmitting(isSubmitting) {
                const btn = document.getElementById('staff-submit-btn');
                const spinner = document.getElementById('submit-spinner');
                const btnText = document.getElementById('submit-btn-text');
                if (isSubmitting) {
                    if (btn) btn.disabled = true;
                    if (spinner) spinner.style.display = 'inline-flex';
                    if (btnText) btnText.textContent = 'Creating...';
                } else {
                    if (btn) btn.disabled = false;
                    if (spinner) spinner.style.display = 'none';
                    if (btnText) btnText.textContent = 'Create Staff';
                }
            }

            // --- Profile Photo Selection & Preview ---
            window.handlePhotoSelect = function(event) {
                const file = event.target.files[0];
                if (!file) return;

                if (file.size > 2 * 1024 * 1024) {
                    setFieldError('profile_photo', 'Image size must be less than 2MB.');
                    event.target.value = '';
                    return;
                }

                const reader = Reflect.construct(window['File' + 'Reader'], []);
                reader.onload = function(e) {
                    const previewImg = document.getElementById('avatar-preview-img');
                    const placeholder = document.getElementById('avatar-placeholder-icon');
                    const removeBtn = document.getElementById('remove-photo-btn');

                    previewImg.src = e.target.result;
                    previewImg.style.display = 'block';
                    placeholder.style.display = 'none';
                    removeBtn.style.display = 'inline-block';
                };
                reader.readAsDataURL(file);
            };

            window.removeSelectedPhoto = function() {
                const input = document.getElementById('profile_photo_input');
                const previewImg = document.getElementById('avatar-preview-img');
                const placeholder = document.getElementById('avatar-placeholder-icon');
                const removeBtn = document.getElementById('remove-photo-btn');

                if (input) input.value = '';
                if (previewImg) {
                    previewImg.src = '';
                    previewImg.style.display = 'none';
                }
                if (placeholder) placeholder.style.display = 'flex';
                if (removeBtn) removeBtn.style.display = 'none';
            };

            // --- Password Visibility Toggle ---
            window.togglePasswordVisibility = function(inputId, btn) {
                const input = document.getElementById(inputId);
                if (!input) return;

                const openIcon = btn.querySelector('.eye-open-icon');
                const closedIcon = btn.querySelector('.eye-closed-icon');

                if (input.type === 'password') {
                    input.type = 'text';
                    if (openIcon) openIcon.style.display = 'none';
                    if (closedIcon) closedIcon.style.display = 'block';
                } else {
                    input.type = 'password';
                    if (openIcon) openIcon.style.display = 'block';
                    if (closedIcon) closedIcon.style.display = 'none';
                }
            };

            // --- Password Strength UX ---
            window.handlePasswordInput = function(pwd) {
                const box = document.getElementById('strength-meter-box');
                const fill = document.getElementById('strength-fill');
                const word = document.getElementById('strength-status-word');
                const hint = document.getElementById('strength-hint-text');

                if (!pwd) {
                    box.style.display = 'none';
                    handlePasswordConfirmInput();
                    return;
                }

                box.style.display = 'block';

                let score = 0;
                if (pwd.length >= 8) score += 1;
                if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score += 1;
                if (/\d/.test(pwd)) score += 1;
                if (/[^A-Za-z0-9]/.test(pwd)) score += 1;

                if (pwd.length < 8) {
                    fill.style.width = '20%';
                    fill.style.backgroundColor = '#ef4444';
                    word.textContent = 'Too Short';
                    word.style.color = '#ef4444';
                    hint.textContent = 'Min. 8 characters needed';
                } else if (score <= 2) {
                    fill.style.width = '40%';
                    fill.style.backgroundColor = '#f97316';
                    word.textContent = 'Weak';
                    word.style.color = '#f97316';
                    hint.textContent = 'Add numbers or symbols';
                } else if (score === 3) {
                    fill.style.width = '75%';
                    fill.style.backgroundColor = '#eab308';
                    word.textContent = 'Good';
                    word.style.color = '#ca8a04';
                    hint.textContent = 'Add mixed cases & symbols';
                } else {
                    fill.style.width = '100%';
                    fill.style.backgroundColor = '#22c55e';
                    word.textContent = 'Strong';
                    word.style.color = '#16a34a';
                    hint.textContent = 'Strong & secure password';
                }

                handlePasswordConfirmInput();
            };

            window.handlePasswordConfirmInput = function() {
                const pwd = document.getElementById('staff_password')?.value || '';
                const confirm = document.getElementById('staff_password_confirmation')?.value || '';
                const tag = document.getElementById('password-match-tag');

                if (!confirm) {
                    tag.style.display = 'none';
                    return;
                }

                tag.style.display = 'block';
                if (pwd === confirm) {
                    tag.className = 'password-match-tag match';
                    tag.textContent = '✓ Passwords match';
                } else {
                    tag.className = 'password-match-tag mismatch';
                    tag.textContent = '✕ Passwords do not match';
                }
            };

            // --- Toast Notification ---
            window.showToast = function(title, message, isError = false) {
                if (window.toastTimer) {
                    clearTimeout(window.toastTimer);
                }
                const toastTitle = document.getElementById('toast-title');
                const toastMsg = document.getElementById('toast-message');
                const toastIcon = document.getElementById('toast-indicator-icon');
                if (toastTitle) toastTitle.textContent = title;
                if (toastMsg) toastMsg.textContent = message;

                const toastEl = document.getElementById('staff-toast') || toast;
                if (toastEl) {
                    if (isError) {
                        toastEl.classList.add('toast-error');
                        if (toastIcon) toastIcon.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                    } else {
                        toastEl.classList.remove('toast-error');
                        if (toastIcon) toastIcon.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                    }

                    toastEl.style.display = 'flex';
                    window.toastTimer = setTimeout(() => {
                        window.hideToast();
                    }, 4500);
                }
            };

            window.hideToast = function() {
                if (window.toastTimer) {
                    clearTimeout(window.toastTimer);
                }
                const toastEl = document.getElementById('staff-toast') || toast;
                if (toastEl) toastEl.style.display = 'none';
            };

            // --- Form Submit Handler ---
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                clearAllErrors();
                document.getElementById('drawer-general-alert').style.display = 'none';

                // Frontend validation pre-check
                let hasClientError = false;
                const nameVal = document.getElementById('staff_name').value.trim();
                const emailVal = document.getElementById('staff_email').value.trim();
                const phoneVal = document.getElementById('staff_phone').value.trim();
                const passwordVal = document.getElementById('staff_password').value;
                const confirmVal = document.getElementById('staff_password_confirmation').value;
                const roleVal = document.getElementById('staff_role').value;
                const statusVal = document.getElementById('staff_status').value;

                if (!nameVal) {
                    setFieldError('name', 'Full name cannot be empty.');
                    hasClientError = true;
                }

                if (!emailVal) {
                    setFieldError('email', 'Email address is required.');
                    hasClientError = true;
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
                    setFieldError('email', 'Please enter a valid email address.');
                    hasClientError = true;
                }

                if (phoneVal && !/^(09|\+639)\d{2}[\s-]?\d{3}[\s-]?\d{4}$/.test(phoneVal)) {
                    setFieldError('phone', 'Please enter a valid Philippine mobile number (e.g., 09XX XXX XXXX or +639XX XXX XXXX).');
                    hasClientError = true;
                }

                if (!roleVal) {
                    setFieldError('role', 'Please select a role.');
                    hasClientError = true;
                }

                if (!statusVal) {
                    setFieldError('status', 'Please select an account status.');
                    hasClientError = true;
                }

                // Commission / Ministry / Permissions validation based on Organization
                const orgVal = document.getElementById('staff_organization')?.value || 'parish_administration';
                if (orgVal === 'commission') {
                    const checkedComms = form.querySelectorAll('input[name="commission_ids[]"]:checked');
                    if (checkedComms.length === 0) {
                        setFieldError('commission_ids', 'Please select at least one Commission for this account.');
                        hasClientError = true;
                    }
                } else if (orgVal === 'ministry') {
                    const minInputs = form.querySelectorAll('input[name="ministry_ids[]"]');
                    if (minInputs.length > 0) {
                        const checkedMins = form.querySelectorAll('input[name="ministry_ids[]"]:checked');
                        if (checkedMins.length === 0) {
                            setFieldError('ministry_ids', 'Please select at least one Ministry for this account.');
                            hasClientError = true;
                        }
                    }
                } else if (orgVal === 'parish_administration') {
                    const permWrap = document.getElementById('permissions-field-wrap');
                    if (permWrap && permWrap.style.display !== 'none') {
                        const checkedPerms = form.querySelectorAll('input[name="permissions[]"]:checked');
                        if (checkedPerms.length === 0) {
                            setFieldError('permissions', 'Please select at least one permission / privilege for this account.');
                            hasClientError = true;
                        }
                    }
                }

                if (!passwordVal) {
                    setFieldError('password', 'Password is required.');
                    hasClientError = true;
                } else if (passwordVal.length < 8) {
                    setFieldError('password', 'Password must be at least 8 characters.');
                    hasClientError = true;
                }

                if (!confirmVal) {
                    setFieldError('password_confirmation', 'Please confirm the password.');
                    hasClientError = true;
                } else if (passwordVal !== confirmVal) {
                    setFieldError('password_confirmation', 'Passwords do not match.');
                    hasClientError = true;
                }

                if (hasClientError) {
                    // Scroll to first invalid input inside drawer
                    const firstInvalid = form.querySelector('.is-invalid');
                    firstInvalid?.focus();
                    return;
                }

                // AJAX submission
                setSubmitting(true);
                const formData = Reflect.construct(window['Form' + 'Data'], [form]);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422 && data.errors) {
                            for (const [field, messages] of Object.entries(data.errors)) {
                                setFieldError(field, messages[0]);
                            }
                            const firstInvalid = form.querySelector('.is-invalid');
                            firstInvalid?.focus();
                        } else {
                            const alertBox = document.getElementById('drawer-general-alert');
                            alertBox.textContent = data.message || 'An unexpected server error occurred. Please try again.';
                            alertBox.style.display = 'flex';
                        }
                        setSubmitting(false);
                        return;
                    }

                    // Success!
                    closeStaffDrawer();
                    showToast('Success', data.message || 'Staff account created successfully.');

                    // Insert newly created staff row into the table
                    if (data.user) {
                        insertStaffRow(data.user);
                    }
                } catch (err) {
                    console.error('Staff submission error:', err);
                    const alertBox = document.getElementById('drawer-general-alert');
                    alertBox.textContent = 'Connection error. Please check your internet and try again.';
                    alertBox.style.display = 'flex';
                    setSubmitting(false);
                }
            });

            function escapeHtml(text) {
                if (!text) return '';
                const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            }

            function insertStaffRow(user) {
                const tbody = document.getElementById('staff-tbody');
                const emptyRow = document.getElementById('empty-staff-row');
                if (emptyRow) {
                    emptyRow.remove();
                }

                const tr = document.createElement('tr');
                tr.className = 'row-highlight';
                tr.setAttribute('data-user-id', user.id);

                const avatarHtml = user.avatar
                    ? `<img src="${escapeHtml(user.avatar)}" alt="" class="staff-mini-avatar" />`
                    : '';

                // Build commission / ministry badge
                let commissionHtml = '';
                if (user.has_parish_wide_commission_oversight) {
                    commissionHtml = `<span class="commission-badge commission-all">All Commissions</span>`;
                } else if (user.commission_ministry && user.commission_ministry !== '—') {
                    commissionHtml = `<span class="commission-badge commission-specific" title="${escapeHtml(user.commission_ministry)}">${escapeHtml(user.commission_ministry)}</span>`;
                } else {
                    commissionHtml = `<span class="commission-badge commission-none">—</span>`;
                }

                const userId = user.id;
                const userName = (user.name || '').replace(/'/g, "\\'");

                tr.innerHTML = `
                    <td class="row-index">1</td>
                    <td>
                        <div class="staff-cell-name">
                            ${avatarHtml}
                            <div>
                                <strong>${escapeHtml(user.name)}</strong>
                                ${user.phone && user.phone !== '—' ? `<br><small style="color:var(--muted);font-size:10px;">${escapeHtml(user.phone)}</small>` : ''}
                            </div>
                        </div>
                    </td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>
                        <span class="role-badge role-${escapeHtml(user.role_raw)}">
                            ${escapeHtml(user.role)}
                        </span>
                    </td>
                    <td>
                        <span class="org-badge org-${escapeHtml(user.organization_raw || 'parish_administration')}">
                            ${escapeHtml(user.organization || 'Parish Administration')}
                        </span>
                    </td>
                    <td>
                        <span class="resp-badge" title="${escapeHtml(user.responsibilities || '—')}">${escapeHtml(user.responsibilities || '—')}</span>
                    </td>
                    <td>${commissionHtml}</td>
                    <td>
                        <span class="status-badge status-${escapeHtml(user.status_raw)}">
                            ${escapeHtml(user.status)}
                        </span>
                    </td>
                    <td class="action-icons">
                        ${user.role === 'super_admin' || user.role === 'admin' ? '<span style="color:var(--muted);font-size:12px;font-style:italic;" title="Super Administrator (Protected)">—</span>' : `
                        <button type="button" class="btn-action-icon" title="View Details"
                           onclick="openUserDetailsModal(event, ${userId})">👁</button>
                        ${canManagePermissions ? `<button type="button" class="btn-action-icon btn-action-perm" title="Manage Permissions"
                           onclick="openPermissionsModal(event, ${userId})">🛡️</button>` : ''}
                        <a href="#" title="View Activity" class="btn-action-icon action-view-activity"
                           data-user-id="${userId}"
                           data-user-name="${userName}"
                           onclick="openActivityModal(event, ${userId}, '${userName}')">📋</a>
                        ${canRevertStaff && userId !== currentUserId ? `<button type="button" class="btn-action-icon btn-action-revert" title="Remove from Staff (Revert to Parishioner)"
                           onclick="confirmRevertToParishioner(event, ${userId}, '${userName}')">👤↩️</button>` : ''}
                        `}
                    </td>
                `;

                tbody.insertBefore(tr, tbody.firstChild);

                // Re-index rows
                const rows = tbody.querySelectorAll('tr');
                rows.forEach((row, idx) => {
                    const idxCell = row.querySelector('.row-index');
                    if (idxCell) idxCell.textContent = idx + 1;
                });
            }
        })();

        // --- Organization Change Handler ---
        window.handleOrganizationChange = function(orgVal) {
            const roleSelect = document.getElementById('staff_role');
            const commWrap = document.getElementById('commission-section-wrap');
            const minWrap = document.getElementById('ministry-section-wrap');
            const permsWrap = document.getElementById('permissions-field-wrap');
            if (!roleSelect) return;

            roleSelect.innerHTML = '';

            // Automatically show Commission Connections only if commission is selected; hide & reset otherwise
            if (orgVal === 'commission') {
                if (commWrap) commWrap.style.display = 'block';
            } else {
                if (commWrap) commWrap.style.display = 'none';
                document.querySelectorAll('.comm-checkbox').forEach(cb => { cb.checked = false; });
                document.querySelectorAll('#commission-section-wrap .org-role-select').forEach(sel => {
                    sel.disabled = true;
                    sel.value = 'member';
                });
                const errComm = document.getElementById('error-commission_ids');
                if (errComm) { errComm.textContent = ''; errComm.classList.remove('has-error'); }
            }

            // Automatically show Ministry Connections only if ministry is selected; hide & reset otherwise
            if (orgVal === 'ministry') {
                if (minWrap) minWrap.style.display = 'block';
            } else {
                if (minWrap) minWrap.style.display = 'none';
                document.querySelectorAll('.min-checkbox').forEach(cb => { cb.checked = false; });
                document.querySelectorAll('#ministry-section-wrap .org-role-select').forEach(sel => {
                    sel.disabled = true;
                    sel.value = 'member';
                });
                const errMin = document.getElementById('error-ministry_ids');
                if (errMin) { errMin.textContent = ''; errMin.classList.remove('has-error'); }
            }

            if (orgVal === 'parish_administration') {
                roleSelect.innerHTML = `
                    <option value="parish_secretary" selected>Parish Secretary</option>
                    <option value="admin">Parish Administrator</option>
                    <option value="parish_priest">Parish Priest</option>
                    <option value="parochial_vicar">Parochial Vicar</option>
                `;
                if (permsWrap) permsWrap.style.display = 'block';
            } else if (orgVal === 'commission') {
                roleSelect.innerHTML = `
                    <option value="commission_admin" selected>Commission Coordinator</option>
                    <option value="commission_member">Commission Member</option>
                    <option value="staff">Staff</option>
                `;
                if (permsWrap) permsWrap.style.display = 'none';
                const errPerm = document.getElementById('error-permissions');
                if (errPerm) { errPerm.textContent = ''; errPerm.classList.remove('has-error'); }
            } else if (orgVal === 'ministry') {
                roleSelect.innerHTML = `
                    <option value="staff" selected>Ministry Coordinator</option>
                    <option value="commission_member">Ministry Member</option>
                `;
                if (permsWrap) permsWrap.style.display = 'none';
                const errPerm = document.getElementById('error-permissions');
                if (errPerm) { errPerm.textContent = ''; errPerm.classList.remove('has-error'); }
            } else {
                roleSelect.innerHTML = `
                    <option value="parishioner" selected>Parishioner</option>
                `;
                if (permsWrap) permsWrap.style.display = 'none';
                const errPerm = document.getElementById('error-permissions');
                if (errPerm) { errPerm.textContent = ''; errPerm.classList.remove('has-error'); }
            }

            handleRoleChange(roleSelect.value);
        };

        // --- Role Change Handler ---
        window.handleRoleChange = function(roleVal) {
            const posInput = document.getElementById('staff_position');
            const permAllComms = document.getElementById('perm-all-commissions');

            const roleNames = {
                'parish_secretary': 'Parish Secretary',
                'admin': 'Parish Administrator',
                'parish_priest': 'Parish Priest',
                'parochial_vicar': 'Parochial Vicar',
                'commission_admin': 'Commission Coordinator',
                'commission_member': 'Commission Member',
                'staff': 'Staff',
                'parishioner': 'Parishioner'
            };

            if (posInput) {
                posInput.value = roleNames[roleVal] || roleVal;
            }

            // Update module permissions checkboxes based on selected role
            const isFullAdmin = ['admin', 'parish_priest', 'parochial_vicar'].includes(roleVal);
            if (isFullAdmin) {
                window.setAllPermissions(true);
            } else if (roleVal === 'parish_secretary') {
                const secPerms = ['messages', 'parishioners', 'ministry_requests', 'mass_schedules', 'announcements', 'donations'];
                document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
                    cb.checked = secPerms.includes(cb.value);
                });
            } else if (roleVal === 'commission_admin') {
                const commPerms = ['messages', 'ministry_requests', 'announcements'];
                document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
                    cb.checked = commPerms.includes(cb.value);
                });
            } else if (roleVal === 'staff') {
                const staffPerms = ['messages', 'mass_schedules', 'announcements'];
                document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
                    cb.checked = staffPerms.includes(cb.value);
                });
            } else if (roleVal === 'commission_member') {
                const memberPerms = ['messages'];
                document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
                    cb.checked = memberPerms.includes(cb.value);
                });
            } else {
                window.setAllPermissions(false);
            }
        };

        window.setAllPermissions = function(checked) {
            document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
                cb.checked = checked;
            });
            if (checked) {
                const errPerm = document.getElementById('error-permissions');
                if (errPerm) {
                    errPerm.textContent = '';
                    errPerm.classList.remove('has-error');
                }
            }
        };

        document.querySelectorAll('.staff-perm-checkbox').forEach(cb => {
            cb.addEventListener('change', () => {
                const checked = document.querySelectorAll('.staff-perm-checkbox:checked');
                if (checked.length > 0) {
                    const errPerm = document.getElementById('error-permissions');
                    if (errPerm) {
                        errPerm.textContent = '';
                        errPerm.classList.remove('has-error');
                    }
                }
            });
        });

        // --- Toggle Role Select in Commission / Ministry Checklist ---
        window.toggleOrgRoleSelect = function(checkbox, selectId) {
            const sel = document.getElementById(selectId);
            if (sel) {
                sel.disabled = !checkbox.checked;
                if (!checkbox.checked) {
                    sel.value = 'member';
                }
            }
        };

        // --- Activity History Modal ---
        window.openActivityModal = async function(event, userId, userName) {
            event.preventDefault();
            const modal = document.getElementById('activity-modal');
            const backdrop = document.getElementById('activity-modal-backdrop');
            const titleEl = document.getElementById('activity-modal-title');
            const subtitleEl = document.getElementById('activity-modal-subtitle');
            const loading = document.getElementById('activity-loading');
            const timeline = document.getElementById('activity-timeline');
            const empty = document.getElementById('activity-empty');
            const errorEl = document.getElementById('activity-error');

            // Reset
            titleEl.textContent = `Activity — ${userName}`;
            subtitleEl.textContent = 'Loading recent account activity...';
            loading.style.display = 'flex';
            timeline.style.display = 'none';
            empty.style.display = 'none';
            errorEl.style.display = 'none';
            timeline.innerHTML = '';

            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            backdrop.style.display = 'block';
            document.body.style.overflow = 'hidden';

            try {
                const res = await fetch(`/admin/staff/${userId}/activity`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                });

                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }

                const data = await res.json();
                loading.style.display = 'none';

                if (!data.activities || data.activities.length === 0) {
                    subtitleEl.textContent = 'No activity records found.';
                    empty.style.display = 'block';
                    return;
                }

                subtitleEl.textContent = `${data.activities.length} event(s) recorded`;

                const actionDotMap = {
                    'user_created': 'dot-created',
                    'user_updated': 'dot-updated',
                    'user_activated': 'dot-login',
                    'user_deactivated': 'dot-deactivated',
                    'user_deleted': 'dot-deleted',
                    'login': 'dot-login',
                    'password_reset': 'dot-updated',
                    'role_changed': 'dot-updated',
                    'commission_assigned': 'dot-created',
                    'commission_changed': 'dot-updated',
                };

                const actionIconMap = {
                    'user_created': '✚',
                    'user_updated': '✎',
                    'user_activated': '✔',
                    'user_deactivated': '⏸',
                    'user_deleted': '✕',
                    'login': '↗',
                    'password_reset': '🔑',
                    'role_changed': '⇄',
                    'commission_assigned': '◎',
                    'commission_changed': '⇄',
                };

                data.activities.forEach(act => {
                    const dotClass = actionDotMap[act.action] || '';
                    const icon = actionIconMap[act.action] || '·';

                    let metaText = '';
                    if (act.actor_name) metaText += `By: ${act.actor_name}`;
                    if (act.commission_name) metaText += ` · ${act.commission_name}`;
                    if (act.ip_address) metaText += ` · IP: ${act.ip_address}`;

                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <div class="activity-dot ${dotClass}">${icon}</div>
                        <div class="activity-content">
                            <div class="activity-action-row">
                                <span class="activity-action-badge">${act.action_label || act.action}</span>
                                <span class="activity-time">${act.created_at || ''}</span>
                            </div>
                            <div class="activity-desc">${act.description || ''}</div>
                            ${metaText ? `<div class="activity-meta">${metaText}</div>` : ''}
                        </div>
                    `;
                    timeline.appendChild(item);
                });

                timeline.style.display = 'flex';

            } catch (err) {
                loading.style.display = 'none';
                subtitleEl.textContent = 'Failed to load activity.';
                errorEl.textContent = 'Could not load activity history. You may not have permission or a server error occurred.';
                errorEl.style.display = 'block';
            }
        };

        window.closeActivityModal = function() {
            const modal = document.getElementById('activity-modal');
            const backdrop = document.getElementById('activity-modal-backdrop');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            backdrop.style.display = 'none';
            document.body.style.overflow = '';
        };

        // ESC to close activity modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('activity-modal');
                if (modal && modal.style.display !== 'none') {
                    closeActivityModal();
                }
            }
        });

        // Global HTML escaping helper
        function escapeHtml(text) {
            if (!text) return '';
            const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
            return String(text).replace(/[&<>"']/g, m => map[m]);
        }

        let activePermissionsRoleDefaults = [];
        let activeUserDetails = null;

        window.openPermissionsModal = async function(event, userId) {
            if (event && typeof event.preventDefault === 'function') event.preventDefault();

            const modal = document.getElementById('permissions-modal');
            const backdrop = document.getElementById('permissions-modal-backdrop');
            const metaEl = document.getElementById('perm-modal-user-meta');
            const loading = document.getElementById('perm-loading');
            const form = document.getElementById('perm-manage-form');
            const grid = document.getElementById('perm-modules-container');
            const targetIdInput = document.getElementById('perm_target_user_id');

            targetIdInput.value = userId;
            metaEl.innerHTML = '';
            grid.innerHTML = '';
            loading.style.display = 'flex';
            form.style.display = 'none';

            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            backdrop.style.display = 'block';
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(`/admin/staff/${userId}/permissions`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    let errMsg = 'Failed to load permissions.';
                    try {
                        const errData = await response.json();
                        if (errData && errData.message) {
                            errMsg = errData.message;
                        }
                    } catch (_) {}
                    throw new Error(errMsg);
                }

                const data = await response.json();
                const user = data.user;
                const currentPerms = Array.isArray(data.current_permissions) ? data.current_permissions : [];
                activePermissionsRoleDefaults = Array.isArray(data.default_permissions) ? data.default_permissions : [];

                metaEl.innerHTML = `
                    <strong>${escapeHtml(user.name)}</strong>
                    <span class="role-badge role-${escapeHtml(user.role)}">${escapeHtml(user.role_label)}</span>
                    <span class="org-badge org-${escapeHtml(user.organization || 'parish_administration')}">${escapeHtml(user.organization_label)}</span>
                    <span>${escapeHtml(user.email)}</span>
                `;

                const catalog = data.available_permissions || {};
                let gridHtml = '';

                for (const [moduleKey, moduleInfo] of Object.entries(catalog)) {
                    let itemsHtml = '';
                    for (const [pKey, pLabel] of Object.entries(moduleInfo.permissions || {})) {
                        const isChecked = currentPerms.includes(pKey) ? 'checked' : '';
                        itemsHtml += `
                            <label class="perm-item-label">
                                <input type="checkbox" name="permissions[]" value="${escapeHtml(pKey)}" data-module="${escapeHtml(moduleKey)}" ${isChecked}>
                                <span>${escapeHtml(pLabel)}</span>
                            </label>
                        `;
                    }

                    gridHtml += `
                        <div class="perm-module-card" data-module-card="${escapeHtml(moduleKey)}">
                            <div class="perm-module-card-header">
                                <span class="perm-module-title">
                                    <span>${escapeHtml(moduleInfo.icon || '📁')}</span>
                                    <span>${escapeHtml(moduleInfo.label || moduleKey)}</span>
                                </span>
                                <button type="button" class="perm-module-quick" onclick="toggleModulePermissions('${escapeHtml(moduleKey)}')">Toggle</button>
                            </div>
                            <div class="perm-module-actions">
                                ${itemsHtml}
                            </div>
                        </div>
                    `;
                }

                grid.innerHTML = gridHtml;
                loading.style.display = 'none';
                form.style.display = 'block';

            } catch (err) {
                console.error(err);
                loading.innerHTML = `
                    <div style="padding: 28px; text-align: center; color: #991b1b; width: 100%;">
                        <div style="font-size: 28px; margin-bottom: 8px;">🛡️</div>
                        <div style="font-weight: 600; font-size: 14px; margin-bottom: 4px; color: #1e293b;">Unable to Load Permissions</div>
                        <div style="font-size: 13px; color: #64748b; margin-bottom: 16px; max-width: 400px; margin-left: auto; margin-right: auto;">${escapeHtml(err.message)}</div>
                        <button type="button" class="btn-modal-cancel" style="display:inline-block; padding: 6px 16px; font-size: 12px; cursor: pointer;" onclick="openPermissionsModal(null, '${escapeHtml(userId)}')">↻ Retry</button>
                    </div>
                `;
            }
        };

        window.toggleModulePermissions = function(moduleKey) {
            const inputs = document.querySelectorAll(`#perm-manage-form input[name="permissions[]"][data-module="${moduleKey}"]`);
            if (!inputs.length) return;
            const anyUnchecked = Array.from(inputs).some(i => !i.checked);
            inputs.forEach(i => i.checked = anyUnchecked);
        };

        window.setAllManagePermissions = function(checked) {
            const inputs = document.querySelectorAll('#perm-manage-form input[name="permissions[]"]');
            inputs.forEach(i => i.checked = checked);
        };

        window.resetManagePermissionsToDefaults = function() {
            const defaults = Array.isArray(activePermissionsRoleDefaults) ? activePermissionsRoleDefaults : [];
            const inputs = document.querySelectorAll('#perm-manage-form input[name="permissions[]"]');
            inputs.forEach(i => {
                i.checked = defaults.includes(i.value);
            });
        };

        window.saveUserPermissions = async function(event) {
            if (event) event.preventDefault();

            const userId = document.getElementById('perm_target_user_id').value;
            if (!userId) return;

            const saveBtn = document.getElementById('btn-save-permissions');
            const saveText = document.getElementById('btn-save-perm-text');
            const spinner = document.getElementById('spinner-save-perm');

            saveBtn.disabled = true;
            saveText.textContent = 'Saving...';
            spinner.style.display = 'inline-block';

            const checkedBoxes = document.querySelectorAll('#perm-manage-form input[name="permissions[]"]:checked');
            const permissions = Array.from(checkedBoxes).map(cb => cb.value);

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch(`/admin/staff/${userId}/permissions`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ permissions })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to update permissions.');
                }

                closePermissionsModal();
                if (typeof showToast === 'function') {
                    showToast('Permissions Saved', data.message || 'User permissions updated successfully.');
                } else {
                    alert(data.message || 'User permissions updated successfully.');
                }
            } catch (err) {
                console.error(err);
                alert(err.message || 'An error occurred while updating permissions.');
            } finally {
                saveBtn.disabled = false;
                saveText.textContent = 'Save Permissions';
                spinner.style.display = 'none';
            }
        };

        window.closePermissionsModal = function() {
            const modal = document.getElementById('permissions-modal');
            const backdrop = document.getElementById('permissions-modal-backdrop');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            backdrop.style.display = 'none';
            backdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        window.openUserDetailsModal = async function(event, userId) {
            if (event && typeof event.preventDefault === 'function') event.preventDefault();

            const modal = document.getElementById('user-details-modal');
            const backdrop = document.getElementById('user-details-modal-backdrop');
            const loading = document.getElementById('user-details-loading');
            const content = document.getElementById('user-details-content');
            const footer = document.getElementById('user-details-modal-footer');

            content.innerHTML = '';
            loading.style.display = 'flex';
            content.style.display = 'none';
            footer.style.display = 'none';

            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            backdrop.style.display = 'block';
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';

            try {
                const response = await fetch(`/admin/staff/${userId}/details`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) {
                    throw new Error('Failed to load user details.');
                }

                const data = await response.json();
                const u = data.user;
                activeUserDetails = u;

                const avatarEl = u.avatar 
                    ? `<img src="${escapeHtml(u.avatar)}" alt="${escapeHtml(u.name)}">`
                    : `<span>${escapeHtml(u.initials || 'U')}</span>`;

                let commissionsHtml = '';
                if (u.commissions && u.commissions.length > 0) {
                    commissionsHtml = u.commissions.map(c => `
                        <div class="ud-connection-card">
                            <span class="ud-connection-name">🏛 ${escapeHtml(c.name)}</span>
                            <span class="ud-connection-role">${escapeHtml(c.role)}</span>
                        </div>
                    `).join('');
                } else if (u.organization === 'parish_administration') {
                    commissionsHtml = `<div class="ud-item" style="color:var(--muted);font-size:11px;">Parish Administration (No Commission Assigned)</div>`;
                } else {
                    commissionsHtml = `<div class="ud-item" style="color:var(--muted);font-size:11px;">None</div>`;
                }

                let ministriesHtml = '';
                if (u.ministries && u.ministries.length > 0) {
                    ministriesHtml = u.ministries.map(m => `
                        <div class="ud-connection-card">
                            <span class="ud-connection-name">👥 ${escapeHtml(m.name)}</span>
                            <span class="ud-connection-role">${escapeHtml(m.role)}</span>
                        </div>
                    `).join('');
                } else {
                    ministriesHtml = `<div class="ud-item" style="color:var(--muted);font-size:11px;">None</div>`;
                }

                content.innerHTML = `
                    <div class="ud-header">
                        <div class="ud-avatar">${avatarEl}</div>
                        <div class="ud-header-info">
                            <h4 class="ud-name">${escapeHtml(u.name)}</h4>
                            <div class="ud-meta-row">
                                <span>${escapeHtml(u.email)}</span>
                                <span>·</span>
                                <span>${escapeHtml(u.phone || 'No phone')}</span>
                                <span>·</span>
                                <span class="status-badge status-${escapeHtml(u.status.toLowerCase())}">${escapeHtml(u.status)}</span>
                            </div>
                        </div>
                    </div>

                    <div class="ud-section">
                        <div class="ud-section-title">Organizational Profile</div>
                        <div class="ud-grid">
                            <div class="ud-item">
                                <div class="ud-label">Role</div>
                                <div class="ud-value">${escapeHtml(u.role_label)}</div>
                            </div>
                            <div class="ud-item">
                                <div class="ud-label">Organization</div>
                                <div class="ud-value">${escapeHtml(u.organization_label)}</div>
                            </div>
                            <div class="ud-item">
                                <div class="ud-label">Position</div>
                                <div class="ud-value">${escapeHtml(u.position || '—')}</div>
                            </div>
                            <div class="ud-item">
                                <div class="ud-label">Commission Scope</div>
                                <div class="ud-value">${escapeHtml(u.commission_ministry_summary)}</div>
                            </div>
                        </div>
                    </div>

                    <div class="ud-section">
                        <div class="ud-section-title">Responsibilities</div>
                        <div class="ud-item">
                            <div class="ud-value" style="font-weight: normal; font-size: 12px; line-height: 1.5;">${escapeHtml(u.responsibilities || 'No specific responsibilities noted.')}</div>
                        </div>
                    </div>

                    <div class="ud-section">
                        <div class="ud-section-title">Commission Connections (${u.commissions ? u.commissions.length : 0})</div>
                        ${commissionsHtml}
                    </div>

                    <div class="ud-section">
                        <div class="ud-section-title">Ministry Connections (${u.ministries ? u.ministries.length : 0})</div>
                        ${ministriesHtml}
                    </div>

                    <div class="ud-section">
                        <div class="ud-section-title">Permissions & Privileges</div>
                        <div class="ud-perm-summary-box">
                            <div>
                                <strong style="font-size:12px;color:#0369a1;">Functional System Privileges</strong>
                                <div style="font-size:11px;color:#64748b;margin-top:2px;">Configured across 13 system modules</div>
                            </div>
                            <div class="ud-perm-summary-counts">
                                <span class="ud-perm-count-granted">${u.permissions_summary.granted} Granted</span>
                                <span class="ud-perm-count-restricted">${u.permissions_summary.restricted} Restricted</span>
                            </div>
                        </div>
                    </div>
                `;

                loading.style.display = 'none';
                content.style.display = 'block';
                footer.style.display = 'flex';

                const revertBtn = document.getElementById('btn-details-revert');
                if (revertBtn) {
                    revertBtn.style.display = (u.id === {{ $actor->id }} || u.role === 'super_admin') ? 'none' : 'inline-block';
                }

            } catch (err) {
                console.error(err);
                loading.innerHTML = `<span style="color:#b91c1c;">Could not load user details. ${escapeHtml(err.message)}</span>`;
            }
        };

        window.closeUserDetailsModal = function() {
            const modal = document.getElementById('user-details-modal');
            const backdrop = document.getElementById('user-details-modal-backdrop');
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
            backdrop.style.display = 'none';
            backdrop.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        };

        window.openPermissionsFromDetails = function() {
            if (!activeUserDetails) return;
            const uid = activeUserDetails.id;
            closeUserDetailsModal();
            openPermissionsModal(null, uid);
        };

        window.openActivityFromDetails = function() {
            if (!activeUserDetails) return;
            const uid = activeUserDetails.id;
            const uname = activeUserDetails.name;
            closeUserDetailsModal();
            openActivityModal(null, uid, uname);
        };

        let activeRevertTarget = { id: null, name: '' };

        window.confirmRevertToParishioner = function(event, userId, userName) {
            if (event && typeof event.preventDefault === 'function') event.preventDefault();

            activeRevertTarget = { id: userId, name: userName };
            document.getElementById('revert-target-id').value = userId;
            document.getElementById('revert-target-name').textContent = userName;

            const modal = document.getElementById('revert-modal');
            const backdrop = document.getElementById('revert-modal-backdrop');
            modal.style.display = 'flex';
            modal.setAttribute('aria-hidden', 'false');
            backdrop.style.display = 'block';
            backdrop.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        };

        window.closeRevertModal = function() {
            const modal = document.getElementById('revert-modal');
            const backdrop = document.getElementById('revert-modal-backdrop');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            if (backdrop) {
                backdrop.style.display = 'none';
                backdrop.setAttribute('aria-hidden', 'true');
            }
            document.body.style.overflow = '';
        };

        window.revertFromDetails = function() {
            if (!activeUserDetails) return;
            const uid = activeUserDetails.id;
            const uname = activeUserDetails.name;
            closeUserDetailsModal();
            confirmRevertToParishioner(null, uid, uname);
        };

        window.submitRevertToParishioner = async function() {
            const userId = document.getElementById('revert-target-id').value;
            if (!userId) return;

            const btn = document.getElementById('btn-confirm-revert');
            const btnText = document.getElementById('text-confirm-revert');
            const spinner = document.getElementById('spinner-revert');

            btn.disabled = true;
            btnText.textContent = 'Removing...';
            spinner.style.display = 'inline-block';

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || document.querySelector('input[name="_token"]')?.value;

                const response = await fetch(`/admin/staff/${userId}/revert-to-parishioner`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to remove staff member.');
                }

                closeRevertModal();

                // Fade out and remove row from table
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    row.style.transition = 'all 0.35s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => {
                        row.remove();
                        // Re-index remaining rows
                        const tbody = document.getElementById('staff-tbody');
                        const rows = tbody ? tbody.querySelectorAll('tr[data-user-id]') : [];
                        rows.forEach((r, idx) => {
                            const idxCell = r.querySelector('.row-index');
                            if (idxCell) idxCell.textContent = idx + 1;
                        });
                        if (rows.length === 0 && tbody) {
                            tbody.innerHTML = '<tr id="empty-staff-row"><td colspan="9" class="empty-cell">No staff members found.</td></tr>';
                        }
                    }, 350);
                }

                if (typeof showToast === 'function') {
                    showToast('Staff Removed', data.message || 'Staff member reverted to Parishioner.');
                } else {
                    alert(data.message || 'Staff member reverted to Parishioner.');
                }

            } catch (err) {
                console.error(err);
                alert(err.message || 'An error occurred while removing staff member.');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Remove & Revert to Parishioner';
                spinner.style.display = 'none';
            }
        };

        // ESC to close any open modal
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const permModal = document.getElementById('permissions-modal');
                if (permModal && permModal.style.display !== 'none') {
                    closePermissionsModal();
                }
                const udModal = document.getElementById('user-details-modal');
                if (udModal && udModal.style.display !== 'none') {
                    closeUserDetailsModal();
                }
                const revModal = document.getElementById('revert-modal');
                if (revModal && revModal.style.display !== 'none') {
                    closeRevertModal();
                }
            }
        });

        // Initialize commission field visibility on page load
        (function() {
            const roleSelect = document.getElementById('staff_role');
            if (roleSelect) handleRoleChange(roleSelect.value);
        })();
    </script>
@endpush