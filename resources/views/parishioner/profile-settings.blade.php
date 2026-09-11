@extends('layouts.parishioner')
@section('title', 'Parishioner Profile & Settings')

@push('styles')
<style>
    .profile-page {
        max-width: 1200px;
        margin: 0 auto;
    }

    /* Alerts */
    .alert-banner {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 18px;
        border-radius: 9px;
        font-size: 13px;
        margin-bottom: 24px;
        font-weight: 500;
    }
    .alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    /* Header Profile Card */
    .profile-hero-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 12px;
        padding: 28px 32px;
        margin-bottom: 28px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        box-shadow: 0 2px 8px rgba(6, 47, 120, 0.04);
        position: relative;
        overflow: hidden;
    }
    .profile-hero-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, var(--navy), var(--gold));
    }
    .profile-hero-main {
        display: flex;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }
    .profile-avatar-wrap {
        width: 84px;
        height: 84px;
        min-width: 84px;
        min-height: 84px;
        max-width: 84px;
        max-height: 84px;
        border-radius: 50%;
        overflow: hidden;
        border: 3px solid var(--gold);
        box-shadow: 0 4px 12px rgba(6, 47, 120, 0.12);
        flex-shrink: 0;
        display: grid;
        place-items: center;
        background: #eaf2fb;
        box-sizing: border-box;
    }
    .profile-avatar-img {
        width: 100%;
        height: 100%;
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
        display: block;
    }
    .profile-avatar-initials {
        font-size: 30px;
        font-weight: 800;
        color: var(--navy);
        font-family: Georgia, serif;
    }
    .profile-hero-info {
        min-width: 0;
    }
    .profile-hero-title {
        margin: 0 0 6px;
        font-size: 24px;
        color: var(--navy);
        font-family: Georgia, serif;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .profile-hero-subtitle {
        margin: 0 0 10px;
        color: var(--muted);
        font-size: 13px;
    }
    .badges-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        align-items: center;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.02em;
    }
    .badge-google {
        background: #fff;
        border: 1px solid #dadce0;
        color: #3c4043;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
    .badge-password {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        color: #475569;
    }
    .badge-verified {
        background: #dcfce7;
        border: 1px solid #86efac;
        color: #15803d;
    }
    .badge-unverified {
        background: #fef3c7;
        border: 1px solid #fde68a;
        color: #b45309;
    }
    .badge-complete {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
    }
    .badge-incomplete {
        background: #fffbeb;
        border: 1px solid #fcd34d;
        color: #d97706;
    }

    /* Grid Layout */
    .profile-grid {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 28px;
        align-items: start;
    }

    /* Cards */
    .section-card {
        background: #fff;
        border: 1px solid var(--line);
        border-radius: 10px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 1px 4px rgba(6, 47, 120, 0.03);
    }
    .section-card:last-child {
        margin-bottom: 0;
    }
    .card-title {
        font-size: 16px;
        font-family: Georgia, serif;
        color: var(--navy);
        margin: 0 0 6px;
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .card-desc {
        margin: 0 0 18px;
        font-size: 12px;
        color: var(--muted);
    }

    /* Key-Value View Rows */
    .detail-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .detail-item {
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f1f5f9;
    }
    .detail-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }
    .detail-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: var(--muted);
    }
    .detail-val {
        font-size: 13px;
        color: var(--ink);
        font-weight: 500;
    }
    .detail-val.empty {
        color: #94a3b8;
        font-style: italic;
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 18px;
    }
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }
    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 6px;
    }
    .form-label .req {
        color: #dc2626;
    }
    .form-control {
        width: 100%;
        padding: 10px 14px;
        font-size: 13px;
        border: 1px solid var(--line);
        border-radius: 7px;
        background: #fff;
        color: var(--ink);
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--navy);
        box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.1);
    }
    .form-control.is-invalid {
        border-color: #ef4444;
        background: #fffafb;
    }
    .form-control[readonly] {
        background: #f8fafc;
        color: #64748b;
        cursor: not-allowed;
        border-color: #e2e8f0;
    }
    .field-hint {
        display: block;
        margin-top: 5px;
        font-size: 11px;
        color: var(--muted);
    }
    .field-hint.google-note {
        color: #2563eb;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .field-error {
        margin-top: 4px;
        font-size: 11px;
        color: #dc2626;
        font-weight: 500;
    }

    /* Form Actions */
    .form-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 26px;
        padding-top: 20px;
        border-top: 1px solid var(--line);
    }
    .btn-submit {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: var(--navy);
        color: #fff;
        border: 1px solid var(--navy);
        border-radius: 7px;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.15s, transform 0.15s;
    }
    .btn-submit:hover {
        background: #052660;
    }
    .btn-secondary {
        display: inline-flex;
        align-items: center;
        padding: 12px 20px;
        background: #fff;
        color: var(--ink);
        border: 1px solid var(--line);
        border-radius: 7px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s;
    }
    .btn-secondary:hover {
        background: #f8fafc;
    }

    /* Security callout */
    .oauth-info-box {
        background: #f0f7ff;
        border: 1px solid #bfdbfe;
        border-radius: 8px;
        padding: 14px 16px;
        margin-top: 16px;
        font-size: 12px;
        color: #1e40af;
        line-height: 1.5;
        display: flex;
        gap: 10px;
        align-items: flex-start;
    }
    .oauth-info-box svg {
        flex-shrink: 0;
        margin-top: 2px;
    }

    @media (max-width: 900px) {
        .profile-grid {
            grid-template-columns: 1fr;
        }
        .profile-hero-card {
            flex-direction: column;
            align-items: flex-start;
        }
        .form-row {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
<div class="profile-page">
    <div class="page-header">
        <div>
            <h2>Parishioner Profile & Settings</h2>
            <p class="date">Manage your account information, linked Google identity, and verified parish residence.</p>
        </div>
        <div class="actions">
            <a href="{{ route('parishioner.dashboard') }}" class="btn btn-outline">
                &larr; Back to Dashboard
            </a>
        </div>
    </div>

    {{-- Session Flash Alerts --}}
    @if(session('success'))
        <div class="alert-banner alert-success" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-banner alert-error" role="alert">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>Please review and correct the errors below before saving.</span>
        </div>
    @endif

    {{-- Hero Profile Summary Card --}}
    <div class="profile-hero-card">
        <div class="profile-hero-main">
            <div class="profile-avatar-wrap">
                @if($user->avatar)
                    <img src="{{ $user->avatar }}" alt="{{ $user->displayName }}" class="profile-avatar-img" referrerpolicy="no-referrer">
                @else
                    <span class="profile-avatar-initials">{{ $user->initials }}</span>
                @endif
            </div>
            <div class="profile-hero-info">
                <h3 class="profile-hero-title">{{ $user->displayName }}</h3>
                <p class="profile-hero-subtitle">{{ $user->email }}</p>
                <div class="badges-row">
                    @if($user->authProvider === 'google')
                        <span class="badge badge-google" title="Authenticated with Google OAuth 2.0">
                            <svg width="14" height="14" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                            </svg>
                            Google Account
                        </span>
                    @else
                        <span class="badge badge-password" title="Standard Password Credentials">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Email & Password
                        </span>
                    @endif

                    @if($user->email_verified_at || $user->is_verified)
                        <span class="badge badge-verified" title="Email address has been verified">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            Verified Email
                        </span>
                    @else
                        <span class="badge badge-unverified">Unverified Email</span>
                    @endif

                    @if($user->isProfileComplete())
                        <span class="badge badge-complete">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M8 12l2.5 2.5L16 9"></path>
                            </svg>
                            Profile Complete
                        </span>
                    @else
                        <span class="badge badge-incomplete">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                            </svg>
                            Incomplete Details
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="profile-grid">
        {{-- Left Column: Account & Profile Overview --}}
        <div>
            {{-- Google Identity Card --}}
            <div class="section-card">
                <h4 class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    Authentication & Identity
                </h4>
                <p class="card-desc">Details regarding your portal sign-in credentials and security.</p>

                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-label">Sign-In Method</span>
                        <span class="detail-val">
                            {{ $user->authProvider === 'google' ? 'Google OAuth 2.0 (Single Sign-On)' : 'Standard Parishioner Password' }}
                        </span>
                    </div>

                    @if($user->google_id)
                        <div class="detail-item">
                            <span class="detail-label">Google Account ID</span>
                            <span class="detail-val" style="font-family: monospace; font-size: 11px; color: #475569;">
                                {{ substr($user->google_id, 0, 10) }}••••••••
                            </span>
                        </div>
                    @endif

                    <div class="detail-item">
                        <span class="detail-label">Email Address</span>
                        <span class="detail-val">{{ $user->email }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Account Role</span>
                        <span class="detail-val">{{ ucfirst($user->role ?? 'Parishioner') }}</span>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">Registered Since</span>
                        <span class="detail-val">{{ $user->created_at?->format('F d, Y') ?? 'N/A' }}</span>
                    </div>
                </div>

                @if($user->authProvider === 'google')
                    <div class="oauth-info-box">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <div>
                            <strong>Google Account Protected</strong><br>
                            Your credentials are handled directly through Google's official identity service. Passwords are never collected or stored on parish servers.
                        </div>
                    </div>
                @endif
            </div>

            {{-- Parish Residence Summary Card --}}
            <div class="section-card">
                <h4 class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                    </svg>
                    Parish Residence on Record
                </h4>
                <p class="card-desc">Your physical location within the ecclesiastical jurisdiction.</p>

                <div class="detail-list">
                    <div class="detail-item">
                        <span class="detail-label">Country</span>
                        <span class="detail-val">{{ $user->country ?? 'Philippines' }}</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Region</span>
                        <span class="detail-val {{ empty($user->region) ? 'empty' : '' }}">
                            {{ $user->region ?? 'Not yet provided' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Province</span>
                        <span class="detail-val {{ empty($user->province) ? 'empty' : '' }}">
                            {{ $user->province ?? ($user->region === 'National Capital Region (NCR)' ? 'None (NCR)' : 'Not yet provided') }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Municipality / City</span>
                        <span class="detail-val {{ empty($user->municipality_city) ? 'empty' : '' }}">
                            {{ $user->municipality_city ?? 'Not yet provided' }}
                        </span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Barangay</span>
                        <span class="detail-val {{ empty($user->barangay) ? 'empty' : '' }}">
                            {{ $user->barangay ?? 'Not yet provided' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Edit Profile Form --}}
        <div>
            <div class="section-card">
                <h4 class="card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Update Parishioner Information
                </h4>
                <p class="card-desc">Keep your official information up-to-date for sacrament records and certificates.</p>

                <form method="POST" action="{{ route('parishioner.profile-settings.update') }}" id="profile-form">
                    @csrf
                    @method('PUT')

                    {{-- Personal Information --}}
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="first_name">
                                First Name <span class="req">*</span>
                            </label>
                            <input type="text"
                                   id="first_name"
                                   name="first_name"
                                   class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name', $user->first_name) }}"
                                   required>
                            @error('first_name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="last_name">
                                Last Name <span class="req">*</span>
                            </label>
                            <input type="text"
                                   id="last_name"
                                   name="last_name"
                                   class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name', $user->last_name) }}"
                                   required>
                            @error('last_name')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="date_of_birth">
                                Date of Birth <span class="req">*</span>
                            </label>
                            <input type="date"
                                   id="date_of_birth"
                                   name="date_of_birth"
                                   max="{{ now()->format('Y-m-d') }}"
                                   class="form-control @error('date_of_birth') is-invalid @enderror"
                                   value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}"
                                   required>
                            @error('date_of_birth')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <span class="field-hint">Required for sacrament verification and age eligibility.</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">
                                Contact / Mobile Phone <span class="req">*</span>
                            </label>
                            <input type="tel"
                                   id="phone"
                                   name="phone"
                                   placeholder="e.g. 09171234567"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   value="{{ old('phone', $user->phone) }}"
                                   required>
                            @error('phone')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                            <span class="field-hint">Used for appointment and sacramental schedule alerts.</span>
                        </div>
                    </div>

                    {{-- Contact & Account Security --}}
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Account Email Address
                        </label>
                        <input type="email"
                               id="email"
                               class="form-control"
                               value="{{ $user->email }}"
                               readonly>
                        @if($user->authProvider === 'google')
                            <span class="field-hint google-note">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14h2v2h-2v-2zm0-10h2v8h-2V6z"/>
                                </svg>
                                Managed by Google OAuth. This email is linked to your Google Account and cannot be edited manually.
                            </span>
                        @else
                            <span class="field-hint">Your primary registered email address.</span>
                        @endif
                    </div>

                    <hr style="margin: 24px 0; border: none; border-top: 1px solid var(--line);">

                    <h5 style="margin: 0 0 6px; font-size: 14px; font-family: Georgia, serif; color: var(--navy);">
                        Philippine Residence Address
                    </h5>
                    <p class="card-desc">Standard geographic location (PSGC) within the Philippines.</p>

                    <input type="hidden" name="country" value="Philippines">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="region_select">
                                Region <span class="req">*</span>
                            </label>
                            <select id="region_select"
                                    name="region"
                                    class="form-control @error('region') is-invalid @enderror"
                                    required>
                                <option value="">Loading regions...</option>
                            </select>
                            @error('region')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="province_select">
                                Province <span id="province-req-star" class="req">*</span>
                            </label>
                            <select id="province_select"
                                    name="province"
                                    class="form-control @error('province') is-invalid @enderror">
                                <option value="">Select Region first</option>
                            </select>
                            @error('province')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="municipality_select">
                                Municipality / City <span class="req">*</span>
                            </label>
                            <select id="municipality_select"
                                    name="municipality_city"
                                    class="form-control @error('municipality_city') is-invalid @enderror"
                                    required>
                                <option value="">Select Province first</option>
                            </select>
                            @error('municipality_city')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="barangay_select">
                                Barangay <span class="req">*</span>
                            </label>
                            <select id="barangay_select"
                                    name="barangay"
                                    class="form-control @error('barangay') is-invalid @enderror"
                                    required>
                                <option value="">Select City / Municipality first</option>
                            </select>
                            @error('barangay')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="{{ route('parishioner.dashboard') }}" class="btn-secondary">Cancel</a>
                        <button type="submit" class="btn-submit" id="submit-btn">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    // Current user location from backend
    const savedLocation = {
        region: @json(old('region', $user->region ?? '')),
        province: @json(old('province', $user->province ?? '')),
        municipality: @json(old('municipality_city', $user->municipality_city ?? '')),
        barangay: @json(old('barangay', $user->barangay ?? ''))
    };

    const BASE_URL = 'https://psgc.gitlab.io/api';
    const cache = new Map();

    const FALLBACK_REGIONS = [
        { code: '010000000', name: 'Ilocos Region (Region I)' },
        { code: '020000000', name: 'Cagayan Valley (Region II)' },
        { code: '030000000', name: 'Central Luzon (Region III)' },
        { code: '040000000', name: 'CALABARZON (Region IV-A)' },
        { code: '170000000', name: 'MIMAROPA Region' },
        { code: '050000000', name: 'Bicol Region (Region V)' },
        { code: '060000000', name: 'Western Visayas (Region VI)' },
        { code: '070000000', name: 'Central Visayas (Region VII)' },
        { code: '080000000', name: 'Eastern Visayas (Region VIII)' },
        { code: '090000000', name: 'Zamboanga Peninsula (Region IX)' },
        { code: '100000000', name: 'Northern Mindanao (Region X)' },
        { code: '110000000', name: 'Davao Region (Region XI)' },
        { code: '120000000', name: 'SOCCSKSARGEN (Region XII)' },
        { code: '130000000', name: 'National Capital Region (NCR)' },
        { code: '140000000', name: 'Cordillera Administrative Region (CAR)' },
        { code: '160000000', name: 'Caraga (Region XIII)' },
        { code: '150000000', name: 'Bangsamoro Autonomous Region in Muslim Mindanao (BARMM)' }
    ];

    const FALLBACK_BICOL_PROVINCES = [
        { code: '050500000', name: 'Albay' },
        { code: '051600000', name: 'Camarines Norte' },
        { code: '051700000', name: 'Camarines Sur' },
        { code: '052000000', name: 'Catanduanes' },
        { code: '054100000', name: 'Masbate' },
        { code: '056200000', name: 'Sorsogon' }
    ];

    const FALLBACK_SORSOGON_MUNS = [
        { code: '056202000', name: 'Barcelona' },
        { code: '056203000', name: 'Bulan' },
        { code: '056204000', name: 'Bulusan' },
        { code: '056205000', name: 'Casiguran' },
        { code: '056206000', name: 'Castilla' },
        { code: '056207000', name: 'Donsol' },
        { code: '056208000', name: 'Gubat' },
        { code: '056209000', name: 'Irosin' },
        { code: '056210000', name: 'Juban' },
        { code: '056211000', name: 'Magallanes' },
        { code: '056212000', name: 'Matnog' },
        { code: '056213000', name: 'Pilar' },
        { code: '056214000', name: 'Prieto Diaz' },
        { code: '056215000', name: 'Santa Magdalena' },
        { code: '056216000', name: 'City of Sorsogon' }
    ];

    const FALLBACK_PILAR_BRGYS = [
        'Abucay', 'Agas', 'Antipolo', 'Bagacay', 'Bayas', 'Bayawas', 'Binanuahan', 'Cabiguan',
        'Cagdongon', 'Calateo', 'Comapo-capo', 'Danlog', 'Dao', 'Dapdap', 'Del Rosario',
        'Esmeralda', 'Esperanza', 'Ginablan', 'Guiron', 'Inang', 'Inapugan', 'Leona', 'Lipason',
        'Lourdes', 'Lubiano', 'Lumbang', 'Lungib', 'Mabanate', 'Malbog', 'Marifosque (Pob.)',
        'Mercedes', 'Migabod', 'Naspi', 'Palanas', 'Pangpang', 'Pinagsalog', 'Pineda', 'Poctol',
        'Pudo', 'Putiao', 'Sacnangan', 'Salvacion', 'San Antonio (Millabas)', 'San Antonio (Sapa)',
        'San Jose', 'San Rafael', 'Santa Fe'
    ].map((name, i) => ({ code: '056213' + String(i + 1).padStart(3, '0'), name }));

    const regionEl = document.getElementById('region_select');
    const provinceEl = document.getElementById('province_select');
    const munEl = document.getElementById('municipality_select');
    const brgyEl = document.getElementById('barangay_select');
    const provReqStar = document.getElementById('province-req-star');

    async function fetchApi(endpoint) {
        if (cache.has(endpoint)) return cache.get(endpoint);
        try {
            const ctrl = new AbortController();
            const tm = setTimeout(() => ctrl.abort(), 4000);
            const res = await fetch(BASE_URL + endpoint, { signal: ctrl.signal });
            clearTimeout(tm);
            if (!res.ok) throw new Error('HTTP ' + res.status);
            const data = await res.json();
            cache.set(endpoint, data);
            return data;
        } catch (e) {
            console.warn('[PSGC]', endpoint, e.message);
            return null;
        }
    }

    async function loadRegions() {
        let regions = await fetchApi('/regions.json');
        if (!Array.isArray(regions) || regions.length === 0) {
            regions = FALLBACK_REGIONS;
        } else {
            regions = regions.map(r => ({
                code: r.code,
                name: r.regionName ? `${r.name} (${r.regionName})` : r.name
            }));
        }

        regionEl.innerHTML = '<option value="">-- Select Region --</option>';
        regions.forEach(r => {
            const opt = document.createElement('option');
            opt.value = r.name;
            opt.dataset.code = r.code;
            opt.textContent = r.name;
            regionEl.appendChild(opt);
        });

        // Match saved region
        if (savedLocation.region) {
            for (let opt of regionEl.options) {
                if (opt.value.toLowerCase().includes(savedLocation.region.toLowerCase()) ||
                    savedLocation.region.toLowerCase().includes(opt.value.toLowerCase())) {
                    opt.selected = true;
                    break;
                }
            }
            if (!regionEl.value) {
                // If not found in select, add custom option
                const customOpt = new Option(savedLocation.region, savedLocation.region, true, true);
                regionEl.add(customOpt);
            }
        }

        await onRegionChange();
    }

    async function onRegionChange() {
        const selectedOpt = regionEl.selectedOptions[0];
        const regionCode = selectedOpt ? selectedOpt.dataset.code : null;
        const regionName = regionEl.value;

        provinceEl.innerHTML = '<option value="">Loading provinces...</option>';
        munEl.innerHTML = '<option value="">Select Province first</option>';
        brgyEl.innerHTML = '<option value="">Select City / Municipality first</option>';

        const isNCR = regionCode === '130000000' || (regionName && regionName.includes('NCR'));

        if (isNCR) {
            provinceEl.innerHTML = '<option value="">Not Applicable (NCR has no provinces)</option>';
            provinceEl.disabled = true;
            provReqStar.style.display = 'none';
            await loadMunicipalities(regionCode, null);
            return;
        }

        provinceEl.disabled = false;
        provReqStar.style.display = 'inline';

        if (!regionCode) {
            provinceEl.innerHTML = '<option value="">Select Region first</option>';
            return;
        }

        let provinces = await fetchApi(`/regions/${regionCode}/provinces.json`);
        if (!Array.isArray(provinces) || provinces.length === 0) {
            if (regionCode === '050000000' || (regionName && regionName.includes('Bicol'))) {
                provinces = FALLBACK_BICOL_PROVINCES;
            } else {
                provinces = [];
            }
        }

        provinceEl.innerHTML = '<option value="">-- Select Province --</option>';
        provinces.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.name;
            opt.dataset.code = p.code;
            opt.textContent = p.name;
            provinceEl.appendChild(opt);
        });

        if (savedLocation.province) {
            for (let opt of provinceEl.options) {
                if (opt.value.toLowerCase() === savedLocation.province.toLowerCase()) {
                    opt.selected = true;
                    break;
                }
            }
            if (!provinceEl.value && savedLocation.province) {
                const opt = new Option(savedLocation.province, savedLocation.province, true, true);
                provinceEl.add(opt);
            }
        }

        await onProvinceChange();
    }

    async function onProvinceChange() {
        const selectedRegion = regionEl.selectedOptions[0];
        const regionCode = selectedRegion ? selectedRegion.dataset.code : null;
        const selectedProv = provinceEl.selectedOptions[0];
        const provinceCode = selectedProv ? selectedProv.dataset.code : null;

        await loadMunicipalities(regionCode, provinceCode);
    }

    async function loadMunicipalities(regionCode, provinceCode) {
        munEl.innerHTML = '<option value="">Loading cities/municipalities...</option>';
        brgyEl.innerHTML = '<option value="">Select City / Municipality first</option>';

        let endpoint = null;
        if (provinceCode) {
            endpoint = `/provinces/${provinceCode}/cities-municipalities.json`;
        } else if (regionCode) {
            endpoint = `/regions/${regionCode}/cities-municipalities.json`;
        }

        let muns = endpoint ? await fetchApi(endpoint) : null;
        if (!Array.isArray(muns) || muns.length === 0) {
            if (provinceCode === '056200000' || (provinceEl.value && provinceEl.value.includes('Sorsogon'))) {
                muns = FALLBACK_SORSOGON_MUNS;
            } else {
                muns = [];
            }
        }

        munEl.innerHTML = '<option value="">-- Select Municipality / City --</option>';
        muns.forEach(m => {
            const opt = document.createElement('option');
            opt.value = m.name;
            opt.dataset.code = m.code;
            opt.textContent = m.name;
            munEl.appendChild(opt);
        });

        if (savedLocation.municipality) {
            for (let opt of munEl.options) {
                if (opt.value.toLowerCase() === savedLocation.municipality.toLowerCase()) {
                    opt.selected = true;
                    break;
                }
            }
            if (!munEl.value && savedLocation.municipality) {
                const opt = new Option(savedLocation.municipality, savedLocation.municipality, true, true);
                munEl.add(opt);
            }
        }

        await onMunicipalityChange();
    }

    async function onMunicipalityChange() {
        const selectedMun = munEl.selectedOptions[0];
        const munCode = selectedMun ? selectedMun.dataset.code : null;
        const munName = munEl.value;

        if (!munCode && !munName) {
            brgyEl.innerHTML = '<option value="">Select City / Municipality first</option>';
            return;
        }

        brgyEl.innerHTML = '<option value="">Loading barangays...</option>';

        let brgys = munCode ? await fetchApi(`/cities-municipalities/${munCode}/barangays.json`) : null;
        if (!Array.isArray(brgys) || brgys.length === 0) {
            if (munCode === '056213000' || (munName && munName.toLowerCase().includes('pilar'))) {
                brgys = FALLBACK_PILAR_BRGYS;
            } else {
                brgys = [];
            }
        }

        brgyEl.innerHTML = '<option value="">-- Select Barangay --</option>';
        brgys.forEach(b => {
            const opt = document.createElement('option');
            opt.value = b.name;
            opt.dataset.code = b.code;
            opt.textContent = b.name;
            brgyEl.appendChild(opt);
        });

        if (savedLocation.barangay) {
            for (let opt of brgyEl.options) {
                if (opt.value.toLowerCase() === savedLocation.barangay.toLowerCase()) {
                    opt.selected = true;
                    break;
                }
            }
            if (!brgyEl.value && savedLocation.barangay) {
                const opt = new Option(savedLocation.barangay, savedLocation.barangay, true, true);
                brgyEl.add(opt);
            }
        }
    }

    regionEl.addEventListener('change', () => {
        savedLocation.region = regionEl.value;
        savedLocation.province = '';
        savedLocation.municipality = '';
        savedLocation.barangay = '';
        onRegionChange();
    });

    provinceEl.addEventListener('change', () => {
        savedLocation.province = provinceEl.value;
        savedLocation.municipality = '';
        savedLocation.barangay = '';
        onProvinceChange();
    });

    munEl.addEventListener('change', () => {
        savedLocation.municipality = munEl.value;
        savedLocation.barangay = '';
        onMunicipalityChange();
    });

    // Initialize
    loadRegions();
})();
</script>
@endpush

