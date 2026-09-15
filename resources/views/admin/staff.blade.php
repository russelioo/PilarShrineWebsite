@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')

    <div class="page-header">
        <h2>Staff Management</h2>
        <div class="actions">
            <button class="btn btn-outline" type="button">📥 Export</button>
            <button class="btn btn-primary" type="button" onclick="openStaffDrawer()">＋ Add New Staff</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-banner alert-success">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form class="toolbar" method="GET" action="{{ route('admin.staff') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone...">
        <select name="role">
            <option value="">All Roles</option>
            <option value="admin" @selected(request('role') === 'admin')>Admin</option>
            <option value="staff" @selected(request('role') === 'staff')>Staff</option>
        </select>
        <select name="status">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <select name="sort">
            <option value="">Sort by</option>
            <option value="name" @selected(request('sort') === 'name')>Name</option>
            <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
        </select>
        <button class="btn btn-primary" type="submit">Apply</button>
        @if(request()->hasAny(['search', 'role', 'status', 'sort']))<a class="btn btn-outline" href="{{ route('admin.staff') }}">Clear</a>@endif
    </form>

    <div class="table-wrap">
        <table id="staff-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
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
                            <strong>{{ $s->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $s->email }}</td>
                    <td>{{ $s->phone ?: '—' }}</td>
                    <td>
                        <span class="role-badge role-{{ $s->role }}">
                            {{ ucfirst($s->role) }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($status) }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td>{{ $s->last_login?->format('M d, Y h:i A') ?? 'Never' }}</td>
                    <td class="action-icons">
                        <a href="#" title="View">👁</a>
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Reset Password">🔑</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
                    </td>
                </tr>
                @empty
                <tr id="empty-staff-row"><td colspan="8" class="empty-cell">No staff members found.</td></tr>
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

                <!-- Role & Status (2 columns) -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="staff_role" class="field-label">Role <span class="required-star">*</span></label>
                        <select id="staff_role" name="role" class="form-control" required>
                            <option value="staff" selected>Staff</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div class="field-error-text" id="error-role"></div>
                    </div>

                    <div class="form-group">
                        <label for="staff_status" class="field-label">Status <span class="required-star">*</span></label>
                        <select id="staff_status" name="status" class="form-control" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                        <div class="field-error-text" id="error-status"></div>
                    </div>
                </div>

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

@endsection

@push('styles')
    <style>
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
        .role-badge{padding:4px 12px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block}
        .role-admin{background:#dbeafe;color:#1e40af}
        .role-staff{background:#e0e7ff;color:#4338ca}
        .action-icons{display:flex;gap:10px}
        .action-icons a{color:var(--muted);text-decoration:none;font-size:14px;transition:color 0.15s ease}
        .action-icons a:hover{color:var(--navy)}
        @media(max-width:620px){.toolbar input{min-width:100%}}
        .empty-cell{text-align:center;padding:28px;color:var(--muted)}

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
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const drawer = document.getElementById('staff-drawer');
            const backdrop = document.getElementById('staff-drawer-backdrop');
            const form = document.getElementById('staff-create-form');
            const submitBtn = document.getElementById('staff-submit-btn');
            const submitSpinner = document.getElementById('submit-spinner');
            const submitBtnText = document.getElementById('submit-btn-text');
            const toast = document.getElementById('staff-toast');
            let toastTimer = null;

            // --- Drawer Open / Close ---
            window.openStaffDrawer = function() {
                resetForm();
                drawer.classList.add('open');
                drawer.setAttribute('aria-hidden', 'false');
                backdrop.classList.add('active');
                backdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                setTimeout(() => {
                    document.getElementById('staff_name')?.focus();
                }, 150);
            };

            window.closeStaffDrawer = function() {
                drawer.classList.remove('open');
                drawer.setAttribute('aria-hidden', 'true');
                backdrop.classList.remove('active');
                backdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
            };

            // Close on ESC
            window.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && drawer.classList.contains('open')) {
                    closeStaffDrawer();
                }
            });

            // --- Reset Form State ---
            function resetForm() {
                form.reset();
                clearAllErrors();
                removeSelectedPhoto();
                document.getElementById('drawer-general-alert').style.display = 'none';
                document.getElementById('strength-meter-box').style.display = 'none';
                document.getElementById('password-match-tag').style.display = 'none';
                setSubmitting(false);
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
                const input = form.querySelector(`[name="${field}"]`);
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
                if (isSubmitting) {
                    submitBtn.disabled = true;
                    submitSpinner.style.display = 'inline-flex';
                    submitBtnText.textContent = 'Creating...';
                } else {
                    submitBtn.disabled = false;
                    submitSpinner.style.display = 'none';
                    submitBtnText.textContent = 'Create Staff';
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

                const reader = new FileReader();
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
                clearTimeout(toastTimer);
                document.getElementById('toast-title').textContent = title;
                document.getElementById('toast-message').textContent = message;

                if (isError) {
                    toast.classList.add('toast-error');
                    document.getElementById('toast-indicator-icon').innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
                } else {
                    toast.classList.remove('toast-error');
                    document.getElementById('toast-indicator-icon').innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                }

                toast.style.display = 'flex';
                toastTimer = setTimeout(() => {
                    hideToast();
                }, 4500);
            };

            window.hideToast = function() {
                clearTimeout(toastTimer);
                toast.style.display = 'none';
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
                const formData = new FormData(form);

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

                tr.innerHTML = `
                    <td class="row-index">1</td>
                    <td>
                        <div class="staff-cell-name">
                            ${avatarHtml}
                            <strong>${escapeHtml(user.name)}</strong>
                        </div>
                    </td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.phone)}</td>
                    <td>
                        <span class="role-badge role-${escapeHtml(user.role_raw)}">
                            ${escapeHtml(user.role)}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-${escapeHtml(user.status_raw)}">
                            ${escapeHtml(user.status)}
                        </span>
                    </td>
                    <td>${escapeHtml(user.last_login)}</td>
                    <td class="action-icons">
                        <a href="#" title="View">👁</a>
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Reset Password">🔑</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
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
    </script>
@endpush