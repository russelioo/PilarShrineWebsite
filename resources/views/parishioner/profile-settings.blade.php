@extends('layouts.parishioner')
@section('title', 'Account Settings')

@push('styles')
<style>
.settings-page{max-width:1120px;margin:0 auto}
.settings-header{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;margin-bottom:24px}
.settings-header h2{margin:0;color:var(--navy);font-family:var(--font-heading);font-size:27px;font-weight:700}
.settings-header p{margin:6px 0 0;color:var(--muted);font-size:11px}
.header-actions{display:flex;align-items:center;gap:10px}
.btn-outline-website,.btn-outline-back{display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border:1px solid var(--line);border-radius:7px;background:#fff;color:var(--navy);font-size:11px;font-weight:700;text-decoration:none;transition:all .15s ease}
.btn-outline-website:hover{border-color:var(--gold);color:var(--gold)}
.btn-outline-back:hover{background:#f8fafc;border-color:var(--navy)}

.alert-success,.alert-danger{display:flex;align-items:center;gap:10px;padding:14px 18px;border-radius:8px;font-size:12px;margin-bottom:22px;line-height:1.4}
.alert-success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.alert-danger{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}

/* 8. PROFILE HEADER */
.account-hero-card{background:#fff;border:1px solid var(--line);border-radius:12px;padding:24px 28px;margin-bottom:26px;display:flex;align-items:center;gap:22px;box-shadow:0 3px 12px rgba(6,47,120,0.04);position:relative;overflow:hidden}
.account-hero-card::before{content:"";position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--navy),var(--gold))}
.hero-avatar-wrap{width:76px;height:76px;min-width:76px;min-height:76px;max-width:76px;max-height:76px;border-radius:50%;overflow:hidden;border:2.5px solid var(--gold);box-shadow:0 4px 12px rgba(6,47,120,0.12);flex-shrink:0;display:grid;place-items:center;background:#eaf2fb;box-sizing:border-box}
.hero-avatar-img{width:100%;height:100%;object-fit:cover;display:block}
.hero-avatar-initials{font-size:26px;font-weight:800;color:var(--navy);font-family:var(--font-body)}
.hero-details{min-width:0}
.hero-name{margin:0 0 4px;font-size:22px;color:var(--navy);font-family:var(--font-heading);font-weight:700}
.hero-email{margin:0 0 10px;color:var(--muted);font-size:12px}
.hero-badges{display:flex;flex-wrap:wrap;gap:8px;align-items:center}
.badge{display:inline-flex;align-items:center;gap:5px;padding:4px 9px;border-radius:16px;font-size:10px;font-weight:700;letter-spacing:0.02em}
.badge-google{background:#fff;border:1px solid #dadce0;color:#3c4043;box-shadow:0 1px 2px rgba(0,0,0,0.04)}
.badge-password{background:#f1f5f9;border:1px solid #cbd5e1;color:#475569}
.badge-verified{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.badge-unverified{background:#fef3c7;border:1px solid #fde68a;color:#b45309}
.badge-complete{background:#eff6ff;border:1px solid #bfdbfe;color:#1d4ed8}
.badge-incomplete{background:#fffbeb;border:1px solid #fcd34d;color:#d97706}

/* Two-column layout */
.settings-layout{display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start}
.settings-column-main,.settings-column-side{display:flex;flex-direction:column;gap:22px}

/* Clean Cards */
.card{background:#fff;border:1px solid var(--line);border-radius:10px;box-shadow:0 2px 8px rgba(6,47,120,0.03);overflow:hidden}
.card-head{display:flex;align-items:flex-start;gap:12px;padding:18px 22px;border-bottom:1px solid #edf2f7;background:#fafcff}
.head-icon{width:32px;height:32px;border-radius:8px;background:#eaf2fb;color:var(--navy);display:grid;place-items:center;flex-shrink:0}
.card-head h4{margin:0;color:var(--navy);font-family:var(--font-heading);font-size:15px;font-weight:700}
.card-head p{margin:3px 0 0;color:var(--muted);font-size:10px}
.card-body{padding:22px}

/* Form Styles */
.form-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:16px;display:flex;flex-direction:column;gap:6px}
.form-group:last-child{margin-bottom:0}
.form-group label{color:var(--navy);font-size:11px;font-weight:700}
.req{color:#dc2626}
.form-input{width:100%;padding:10px 12px;border:1px solid #ccd8e4;border-radius:7px;background:#fff;color:var(--ink);font:400 11.5px Arial,sans-serif;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s}
.form-input:focus{border-color:var(--navy);box-shadow:0 0 0 3px rgba(6,47,120,0.1)}
.form-input.is-invalid{border-color:#ef4444;background:#fffafb}
.hint{color:var(--muted);font-size:9.5px}
.err{color:#dc2626;font-size:9.5px;font-weight:600}

/* Spec Rows for Side Cards */
.spec-row{padding:12px 0;border-bottom:1px solid #f1f5f9;display:flex;flex-direction:column;gap:4px}
.spec-row:first-child{padding-top:0}
.spec-row:last-child{border-bottom:none;padding-bottom:0}
.spec-label{font-size:10px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.04em}
.spec-val-wrap{display:flex;align-items:center;justify-content:space-between;gap:8px}
.spec-val{font-size:12px;color:var(--navy);font-weight:600}
.spec-code{font-family:monospace;font-size:11px;color:#64748b}
.spec-note{font-size:9.5px;color:#64748b;line-height:1.4;margin-top:2px}
.pill{font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;white-space:nowrap}
.pill-green{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.pill-slate{background:#f1f5f9;border:1px solid #cbd5e1;color:#475569}
.pill-amber{background:#fffbeb;border:1px solid #fcd34d;color:#b45309}

/* Save Actions */
.form-actions{display:flex;justify-content:flex-end;margin-top:6px}
.btn-save-changes{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:var(--navy);color:#fff;border:1px solid var(--navy);border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s ease}
.btn-save-changes:hover{background:#052660;transform:translateY(-1px);box-shadow:0 4px 12px rgba(6,47,120,0.2)}

@media(max-width:920px){
  .settings-layout{grid-template-columns:1fr}
  .settings-header{flex-direction:column;align-items:stretch}
  .header-actions{justify-content:flex-start;flex-wrap:wrap}
  .form-grid-2{grid-template-columns:1fr}
}
</style>
@endpush

@section('content')
<div class="settings-page">
    <div class="settings-header">
        <div>
            <h2>Parishioner Profile &amp; Settings</h2>
            <p>Manage your parish account identity, personal details, and residence records.</p>
        </div>
        <div class="header-actions">
            <a href="/" class="btn-outline-website" title="Return to Official Public Parish Website">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 12H5M12 19l-7-7 7-7"/>
                </svg>
                <span>Official Website</span>
            </a>
            <a href="{{ route('parishioner.dashboard') }}" class="btn-outline-back">
                &larr; Portal Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert-danger" role="alert">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
            <span>Please review and correct the errors below before saving your changes.</span>
        </div>
    @endif

    <!-- 8. PROFILE HEADER: Dynamic Authenticated User Info -->
    <div class="account-hero-card">
        <div class="hero-avatar-wrap">
            @if($user->avatar)
                <img src="{{ $user->avatar }}" alt="{{ $user->displayName }}" class="hero-avatar-img" referrerpolicy="no-referrer">
            @else
                <span class="hero-avatar-initials">{{ $user->initials }}</span>
            @endif
        </div>
        <div class="hero-details">
            <h3 class="hero-name">{{ $user->displayName }}</h3>
            <p class="hero-email">{{ $user->email }}</p>
            <div class="hero-badges">
                @if($user->authProvider === 'google')
                    <span class="badge badge-google">
                        <svg width="12" height="12" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.665-5.17 3.665-9.17z"/>
                            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.32v3.15C3.33 21.37 7.37 24 12 24z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        Google Account
                    </span>
                @else
                    <span class="badge badge-password">Email &amp; Password</span>
                @endif

                @if($user->email_verified_at || $user->is_verified)
                    <span class="badge badge-verified">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        Verified Email
                    </span>
                @else
                    <span class="badge badge-unverified">Unverified Email</span>
                @endif

                @if($user->isProfileComplete())
                    <span class="badge badge-complete">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                        Profile Complete
                    </span>
                @else
                    <span class="badge badge-incomplete">Incomplete Details</span>
                @endif
            </div>
        </div>
    </div>

    <!-- MAIN SETTINGS FORM -->
    <form method="POST" action="{{ route('parishioner.profile-settings.update') }}">
        @csrf
        @method('PUT')

        <div class="settings-layout">
            <!-- Left Column: Personal Information & Residence -->
            <div class="settings-column-main">

                <!-- 1. PERSONAL INFORMATION (PARISH COLLECTED DATA) -->
                <div class="card">
                    <div class="card-head">
                        <div class="head-icon">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </div>
                        <div>
                            <h4>Personal Information</h4>
                            <p>Parish pastoral demographic and contact information.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="req">*</span></label>
                                <input type="text" id="first_name" name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="form-input @error('first_name') is-invalid @enderror">
                                @error('first_name')<span class="err">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="last_name">Last Name <span class="req">*</span></label>
                                <input type="text" id="last_name" name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="form-input @error('last_name') is-invalid @enderror">
                                @error('last_name')<span class="err">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth <span class="req">*</span></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" max="{{ now()->toDateString() }}" value="{{ old('date_of_birth', $user->date_of_birth?->format('Y-m-d')) }}" required class="form-input @error('date_of_birth') is-invalid @enderror">
                                <small class="hint">Required for sacramental certificates and verification.</small>
                                @error('date_of_birth')<span class="err">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="phone">Mobile Number <span class="req">*</span></label>
                                <input type="tel" id="phone" name="phone" placeholder="0917 123 4567" value="{{ old('phone', $user->phone) }}" required class="form-input @error('phone') is-invalid @enderror">
                                <small class="hint">Used for schedule reminders and pastoral notices.</small>
                                @error('phone')<span class="err">{{ $message }}</span>@enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. RESIDENCE (PARISH JURISDICTION) -->
                <div class="card">
                    <div class="card-head">
                        <div class="head-icon">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        </div>
                        <div>
                            <h4>Residence</h4>
                            <p>Parish ecclesiastical jurisdiction and registered domicile.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="country">Country <span class="req">*</span></label>
                                <input type="text" id="country" name="country" value="{{ old('country', $user->country ?? 'Philippines') }}" required class="form-input">
                            </div>

                            <div class="form-group">
                                <label for="region">Region <span class="req">*</span></label>
                                <input type="text" id="region" name="region" value="{{ old('region', $user->region ?? 'Bicol Region') }}" required class="form-input @error('region') is-invalid @enderror">
                                @error('region')<span class="err">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="form-grid-2">
                            <div class="form-group">
                                <label for="province">Province</label>
                                <input type="text" id="province" name="province" value="{{ old('province', $user->province ?? 'Sorsogon') }}" placeholder="Sorsogon (or leave blank for NCR)" class="form-input @error('province') is-invalid @enderror">
                                @error('province')<span class="err">{{ $message }}</span>@enderror
                            </div>

                            <div class="form-group">
                                <label for="municipality_city">Municipality / City <span class="req">*</span></label>
                                <input type="text" id="municipality_city" name="municipality_city" value="{{ old('municipality_city', $user->municipality_city ?? 'Pilar') }}" required class="form-input @error('municipality_city') is-invalid @enderror">
                                @error('municipality_city')<span class="err">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="barangay">Barangay / Complete Address <span class="req">*</span></label>
                            <input type="text" id="barangay" name="barangay" value="{{ old('barangay', $user->barangay) }}" placeholder="e.g. Poblacion, Pilar or Street / Subdivision" required class="form-input @error('barangay') is-invalid @enderror">
                            @error('barangay')<span class="err">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save-changes">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </div>

            <!-- Right Column: Connected Google Account & Account Status -->
            <div class="settings-column-side">

                <!-- 3. CONNECTED ACCOUNT (READ-ONLY GOOGLE DATA) -->
                <div class="card">
                    <div class="card-head">
                        <div class="head-icon">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        </div>
                        <div>
                            <h4>Connected Account</h4>
                            <p>Authentication and Google identity.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="spec-row">
                            <span class="spec-label">Google Account Email</span>
                            <div class="spec-val-wrap">
                                <span class="spec-val">{{ $user->email }}</span>
                                <span class="pill pill-green">Verified &check;</span>
                            </div>
                            <small class="spec-note">Managed by Google OAuth. To protect your security, email cannot be overwritten as parish data.</small>
                        </div>

                        <div class="spec-row">
                            <span class="spec-label">Google Account</span>
                            <div class="spec-val-wrap">
                                @if($user->google_id)
                                    <span class="spec-val">Connected</span>
                                    <span class="pill pill-green">&check; Active</span>
                                @else
                                    <span class="spec-val">Email &amp; Password</span>
                                    <span class="pill pill-slate">Standard</span>
                                @endif
                            </div>
                        </div>

                        @if($user->google_id)
                            <div class="spec-row">
                                <span class="spec-label">Google Account ID</span>
                                <span class="spec-code">{{ substr($user->google_id, 0, 10) }}••••••••</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 4. ACCOUNT STATUS -->
                <div class="card">
                    <div class="card-head">
                        <div class="head-icon">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        </div>
                        <div>
                            <h4>Account Status</h4>
                            <p>Parishioner record details.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="spec-row">
                            <span class="spec-label">Profile Completion</span>
                            <div class="spec-val-wrap">
                                @if($user->isProfileComplete())
                                    <span class="spec-val" style="color: #065f46; font-weight:700;">Complete</span>
                                    <span class="pill pill-green">&check;</span>
                                @else
                                    <span class="spec-val" style="color: #b45309; font-weight:700;">Incomplete</span>
                                    <span class="pill pill-amber">Action Needed</span>
                                @endif
                            </div>
                        </div>

                        <div class="spec-row">
                            <span class="spec-label">Email Verification</span>
                            <div class="spec-val-wrap">
                                <span class="spec-val">Verified</span>
                                <span class="pill pill-green">&check;</span>
                            </div>
                        </div>

                        <div class="spec-row">
                            <span class="spec-label">Account Created</span>
                            <span class="spec-val">{{ $user->created_at?->format('F d, Y') ?? 'N/A' }}</span>
                        </div>

                        <div class="spec-row">
                            <span class="spec-label">Last Login</span>
                            <span class="spec-val">{{ $user->last_login?->format('M d, Y · g:i A') ?? 'Active session' }}</span>
                        </div>
                    </div>
                </div>

                <!-- 5. PARISH MINISTRIES -->
                <div class="card">
                    <div class="card-head">
                        <div class="head-icon">
                            <svg viewBox="0 0 24 24" width="17" height="17" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <div>
                            <h4>Parish Ministries</h4>
                            <p>Your apostolates and volunteer service.</p>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($user->ministryMemberships && $user->ministryMemberships->isNotEmpty())
                            @foreach($user->ministryMemberships as $membership)
                                <div class="spec-row">
                                    <div class="spec-val-wrap">
                                        <span class="spec-val" style="display:flex; align-items:center; gap:6px;">
                                            <span>{{ $membership->ministry->icon ?: '✝' }}</span>
                                            <span>{{ $membership->ministry->name }}</span>
                                        </span>
                                        @if($membership->status === 'approved')
                                            <span class="pill pill-green">&check; Member</span>
                                        @elseif($membership->status === 'pending')
                                            <span class="pill pill-amber">Pending</span>
                                        @elseif($membership->status === 'rejected')
                                            <span class="pill pill-slate">Not Approved</span>
                                        @else
                                            <span class="pill pill-slate">{{ ucfirst($membership->status) }}</span>
                                        @endif
                                    </div>
                                    <small class="spec-note">
                                        @if($membership->status === 'approved' && $membership->reviewed_at)
                                            Approved on {{ $membership->reviewed_at->format('M d, Y') }}
                                        @elseif($membership->status === 'pending')
                                            Application submitted on {{ $membership->created_at->format('M d, Y') }}
                                        @else
                                            Updated {{ $membership->updated_at->format('M d, Y') }}
                                        @endif
                                    </small>
                                </div>
                            @endforeach
                        @else
                            <div style="text-align: center; padding: 12px 0; color: #64748b;">
                                <p style="margin: 0 0 6px; font-size: 11.5px; font-weight: 600; color: #334155;">No active ministry memberships</p>
                                <small style="display: block; font-size: 10px; color: #94a3b8; margin-bottom: 6px;">Serve our Lord and community through parish apostolates.</small>
                            </div>
                        @endif

                        <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; text-align: center;">
                            <a href="{{ route('parishioner.ministries') }}" style="font-size: 11px; font-weight: 700; color: var(--navy); text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <span>Browse Ministry Directory</span> &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>
@endsection
