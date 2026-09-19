@extends('layouts.admin')
@section('title', 'Settings')
@section('content')
@php
    $formValue = fn ($section, $key, $default) => old('_section') === $section ? old($key, $default) : $default;
@endphp
<div class="settings-page">
    <div class="settings-intro">
        <div><span class="settings-eyebrow">YOUR PARISH PORTAL</span><h2>Account &amp; website settings</h2><p>Manage your account and the information visitors see on the parish website.</p></div>
        <a class="btn btn-outline" href="/" target="_blank" rel="noopener noreferrer">Open website ↗</a>
    </div>
    @if(session('success'))<div class="settings-alert settings-success" role="status">{{ session('success') }}</div>@endif
    @if(!$canEdit)<div class="settings-alert" role="note">You have viewing access. Contact an administrator to request permission to edit settings.</div>@endif
    @if($errors->getBag('default')->any())<div class="settings-alert settings-error" role="alert">{{ $errors->getBag('default')->first() }}</div>@endif
    <nav class="settings-nav" aria-label="Settings sections">
        <a href="#account">Account</a><a href="#security">Security</a><a href="#website">Website</a><a href="#livestream">Livestream</a><a href="#notifications">Notifications</a>
    </nav>
    <div class="settings-grid">
        <section class="settings-card" id="account" aria-labelledby="account-heading">
            <div class="settings-card-heading"><span class="settings-icon" aria-hidden="true">◎</span><div><h3 id="account-heading">Your account</h3><p>Your name, email address, and contact number.</p></div></div>
            @if($errors->account->any())<div class="settings-alert settings-error" role="alert">@foreach($errors->account->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <form method="post" action="{{ route('admin.settings.account') }}">
                @csrf @method('PUT')
                <input type="hidden" name="_section" value="account">
                <fieldset @disabled(!$canEdit)>
                    <label for="account-name">Display name</label>
                    <input id="account-name" name="name" value="{{ $formValue('account', 'name', $user->name) }}" required maxlength="255" autocomplete="name">
                    <label for="account-email">Email address</label>
                    <input id="account-email" type="email" name="email" value="{{ $formValue('account', 'email', $user->email) }}" required maxlength="255" autocomplete="email" @readonly($user->google_id)>
                    @if($user->google_id)<p class="settings-help">This address is linked to your Google sign-in.</p>@endif
                    <label for="account-phone">Phone number</label>
                    <input id="account-phone" type="tel" name="phone" value="{{ $formValue('account', 'phone', $user->phone) }}" maxlength="30" autocomplete="tel">
                    @unless($user->google_id)
                        <label for="account-current-password">Current password <span class="settings-optional">Only when changing email</span></label>
                        <input id="account-current-password" type="password" name="current_password" autocomplete="current-password">
                    @endunless
                    <div class="settings-form-footer"><span class="settings-help">{{ $user->role_badge_label }}</span>@if($canEdit)<button class="btn btn-primary" type="submit">Save account</button>@endif</div>
                </fieldset>
            </form>
        </section>
        <section class="settings-card" id="security" aria-labelledby="security-heading">
            <div class="settings-card-heading"><span class="settings-icon" aria-hidden="true">◇</span><div><h3 id="security-heading">Password &amp; security</h3><p>Keep your parish account secure.</p></div></div>
            @if($user->google_id)
                <div class="settings-note"><strong>Signed in with Google</strong><p>Your Google Account manages your sign-in password.</p><a href="https://myaccount.google.com/security" target="_blank" rel="noopener noreferrer">Manage Google security ↗</a></div>
            @else
                @if($errors->password->any())<div class="settings-alert settings-error" role="alert">@foreach($errors->password->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
                <form method="post" action="{{ route('admin.settings.password') }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="_section" value="password">
                    <fieldset @disabled(!$canEdit)>
                        <label for="security-current-password">Current password</label>
                        <input id="security-current-password" type="password" name="current_password" required autocomplete="current-password">
                        <label for="security-password">New password</label>
                        <input id="security-password" type="password" name="password" required minlength="8" maxlength="255" autocomplete="new-password" aria-describedby="password-help">
                        <p id="password-help" class="settings-help">Use at least 8 characters and choose a password you do not use elsewhere.</p>
                        <label for="security-password-confirmation">Confirm new password</label>
                        <input id="security-password-confirmation" type="password" name="password_confirmation" required minlength="8" maxlength="255" autocomplete="new-password">
                        @if($canEdit)<div class="settings-form-footer"><button class="btn btn-primary" type="submit">Update password</button></div>@endif
                    </fieldset>
                </form>
            @endif
        </section>
        <section class="settings-card settings-wide" id="website" aria-labelledby="website-heading">
            <div class="settings-card-heading"><span class="settings-icon settings-icon-gold" aria-hidden="true">⌂</span><div><h3 id="website-heading">Public website</h3><p>Contact details, office hours, and official social media links.</p></div></div>
            @if($errors->website->any())<div class="settings-alert settings-error" role="alert">@foreach($errors->website->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <form method="post" action="{{ route('admin.settings.website') }}">
                @csrf @method('PUT')
                <input type="hidden" name="_section" value="website">
                <fieldset @disabled(!$canEdit)>
                    <div class="settings-fields">
                        @foreach(['address' => ['Parish address', 'text', 255], 'phone' => ['Parish phone number', 'tel', 40], 'email' => ['Parish email address', 'email', 255], 'facebook_url' => ['Facebook page', 'url', 255], 'youtube_url' => ['YouTube channel', 'url', 255], 'tiktok_url' => ['TikTok account', 'url', 255]] as $field => [$label, $type, $max])
                            <div><label for="website-{{ $field }}">{{ $label }}</label><input id="website-{{ $field }}" name="{{ $field }}" type="{{ $type }}" value="{{ $formValue('website', $field, $site->$field) }}" required maxlength="{{ $max }}"></div>
                        @endforeach
                        <div class="settings-wide"><label for="website-hours">Office hours</label><textarea id="website-hours" name="office_hours" rows="4" required maxlength="2000" aria-describedby="hours-help">{{ $formValue('website', 'office_hours', $site->office_hours) }}</textarea><p class="settings-help" id="hours-help">Put each schedule or closure on a new line. These hours appear on the Home and Contact pages and in the footer.</p></div>
                    </div>
                    <div class="settings-form-footer"><span class="settings-help">@if($site->updated_at)Last saved {{ $site->updated_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}@else Shown throughout the public website.@endif</span>@if($canEdit)<button class="btn btn-primary" type="submit">Save website settings</button>@endif</div>
                </fieldset>
            </form>
        </section>
        <section class="settings-card settings-wide" id="livestream" aria-labelledby="livestream-heading">
            <div class="settings-card-heading"><span class="settings-icon settings-icon-gold" aria-hidden="true">▷</span><div><h3 id="livestream-heading">Automatic livestream</h3><p>The Live button follows the Masses marked FB Live in your schedule.</p></div></div>
            @if($errors->livestream->any())<div class="settings-alert settings-error" role="alert">@foreach($errors->livestream->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
            <form method="post" action="{{ route('admin.settings.livestream') }}">
                @csrf @method('PUT')
                <input type="hidden" name="_section" value="livestream">
                <fieldset @disabled(!$canEdit)>
                    <div class="settings-note"><strong>Automatic &middot; Philippine time</strong><p>The Live button appears <strong>5 minutes before Mass</strong> and closes <strong>1 hour 20 minutes after the scheduled start</strong>. No manual switch is needed.</p><p>For a 7:30 AM Mass, the button is visible from 7:25 AM to 8:50 AM.</p>@if($user->hasPermission('mass_schedules'))<a href="{{ route('admin.mass-schedules') }}">Manage Mass schedule &rarr;</a>@endif</div>
                    <div class="settings-fields">
                        <div><label for="livestream-title">Broadcast title</label><input id="livestream-title" name="title" value="{{ $formValue('livestream', 'title', $livestream->title) }}" required maxlength="255"></div>
                        <div><label for="livestream-url">Broadcast link</label><input id="livestream-url" name="url" type="url" value="{{ $formValue('livestream', 'url', $livestream->url) }}" required maxlength="255" placeholder="https://www.facebook.com/..."></div>
                    </div>
                    <div class="settings-form-footer"><span class="settings-help">Save only when changing the broadcast title or link.</span>@if($canEdit)<button class="btn btn-primary" type="submit">Save broadcast details</button>@endif</div>
                </fieldset>
            </form>
        </section>
        <section class="settings-card settings-wide" id="notifications" aria-labelledby="notifications-heading">
            <div class="settings-card-heading"><span class="settings-icon" aria-hidden="true">♧</span><div><h3 id="notifications-heading">Your notifications</h3><p>Notification records for your account, including updates from parish activities.</p></div></div>
            <div class="settings-stats"><div><strong>{{ $notificationCounts->sum() }}</strong><span>Total records</span></div><div><strong>{{ $notificationCounts->get('pending', 0) }}</strong><span>Pending</span></div><div><strong>{{ $notificationCounts->get('failed', 0) }}</strong><span>Failed</span></div></div>
            <form class="settings-filter" method="get" action="{{ route('admin.settings') }}#notifications">
                <div><label for="notification-search">Search notifications</label><input id="notification-search" name="search" type="search" value="{{ request('search') }}" maxlength="120" placeholder="Search subjects or messages"></div>
                <div><label for="notification-status">Status</label><select id="notification-status" name="status">@foreach(['all' => 'All statuses', 'pending' => 'Pending', 'sent' => 'Sent', 'failed' => 'Failed'] as $value => $label)<option value="{{ $value }}" @selected(request('status', 'all') === $value)>{{ $label }}</option>@endforeach</select></div>
                <button class="btn btn-primary" type="submit">Filter</button><a class="btn btn-outline" href="{{ route('admin.settings') }}#notifications">Reset</a><a class="btn btn-outline" href="{{ route('admin.settings.notifications.export', request()->only('search', 'status')) }}">Export CSV</a>
            </form>
            <div class="settings-table-wrap"><table class="settings-table"><thead><tr><th scope="col">Notification</th><th scope="col">Channel</th><th scope="col">Status</th><th scope="col">Recorded</th></tr></thead><tbody>
                @forelse($notifications as $notification)
                    <tr><td><details><summary>{{ $notification->subject }}</summary><p class="settings-message">{{ $notification->message }}</p>@if($notification->sent_at)<p class="settings-help">Sent at {{ $notification->sent_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</p>@endif</details></td><td>{{ strtoupper($notification->type) }}</td><td><span class="settings-status settings-status-{{ $notification->status }}">{{ ucfirst($notification->status) }}</span></td><td>{{ $notification->created_at->timezone('Asia/Manila')->format('M j, Y · g:i A') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="settings-empty">{{ request()->filled('search') || request('status', 'all') !== 'all' ? 'No notifications match your filters.' : 'No notifications yet. Updates for your account will appear here.' }}</td></tr>
                @endforelse
            </tbody></table></div>
            @if($notifications->hasPages())<nav class="settings-pagination" aria-label="Notification pages">@if($notifications->previousPageUrl())<a class="btn btn-outline" href="{{ $notifications->previousPageUrl() }}#notifications">Previous</a>@endif<span>Page {{ $notifications->currentPage() }} of {{ $notifications->lastPage() }}</span>@if($notifications->nextPageUrl())<a class="btn btn-outline" href="{{ $notifications->nextPageUrl() }}#notifications">Next</a>@endif</nav>@endif
        </section>
    </div>
</div>
@endsection
@push('styles')
<style>
.settings-page { max-width:1180px; margin:0 auto; }
.settings-intro { display:flex; align-items:center; justify-content:space-between; gap:20px; margin-bottom:24px; }
.settings-eyebrow { font-size:10px; letter-spacing:.12em; font-weight:700; color:#947016; }
.settings-intro h2 { font-size:25px; line-height:1.3; color:var(--navy); margin:8px 0; }
.settings-intro p, .settings-card-heading p { color:var(--muted); font-size:12px; margin:6px 0 0; line-height:1.6; }
.settings-intro .btn { white-space:nowrap; }
.settings-nav { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 24px; padding:6px; background:#edf2f8; border-radius:12px; }
.settings-nav a { padding:9px 18px; border-radius:8px; color:var(--navy); font-weight:600; font-size:12px; text-decoration:none; }
.settings-nav a:hover, .settings-nav a:focus-visible { background:white; box-shadow:var(--shadow-xs); }
.settings-grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:24px; }
.settings-wide { grid-column:1/-1; }
.settings-card { min-width:0; padding:28px; background:#fff; border:1px solid var(--border); border-radius:16px; box-shadow:0 4px 18px #062f7805; scroll-margin-top:90px; }
.settings-card-heading { display:flex; align-items:flex-start; gap:12px; margin-bottom:24px; }
.settings-card h3 { font-size:17px; line-height:1.35; color:var(--navy); margin:0; }
.settings-icon { display:grid; place-items:center; width:40px; height:40px; flex-shrink:0; background:#edf4fb; color:var(--navy); border-radius:11px; font-size:23px; }
.settings-icon-gold { background:#fff7e5; color:#987326; }
.settings-page fieldset { border:0; margin:0; padding:0; min-width:0; }
.settings-page label { display:block; font-size:12px; font-weight:600; color:#334155; margin:16px 0 7px; }
.settings-page input:not([type=hidden]):not([type=checkbox]), .settings-page textarea, .settings-page select { width:100%; border:1px solid #d6e0eb; border-radius:8px; padding:11px 12px; font:inherit; font-size:13px; color:#172c47; background:#fff; min-width:0; }
.settings-page textarea { resize:vertical; line-height:1.7; }
.settings-page input:focus, .settings-page textarea:focus, .settings-page select:focus { outline:2px solid #a6c5ee; outline-offset:2px; border-color:#477cb8; }
.settings-page input:read-only, .settings-page :disabled { background:#f8fafc; color:#64748b; }
.settings-help { font-size:11px; color:#64748b; line-height:1.6; margin:7px 0 0; }
.settings-optional { font-weight:400; color:#64748b; font-size:10px; margin-left:5px; }
.settings-form-footer { display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap; margin-top:24px; padding-top:18px; border-top:1px solid #edf2f7; }
.settings-form-footer .btn { margin-left:auto; }
.settings-page .btn { text-decoration:none; display:inline-flex; align-items:center; justify-content:center; font-size:12px; min-height:40px; }
.settings-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); column-gap:24px; row-gap:4px; }
.settings-note { padding:18px; border:1px solid #e0e8f2; border-radius:10px; background:#f7fafd; font-size:12px; line-height:1.7; }
.settings-note strong { color:var(--navy); }
.settings-note p { margin:6px 0 0; color:#64748b; }
.settings-note a { display:inline-block; margin-top:12px; color:var(--blue); font-weight:600; }
.settings-alert { padding:14px 18px; border:1px solid #d6e3f2; background:#eff6ff; border-radius:10px; margin:0 0 20px; color:#254f7a; font-size:12px; line-height:1.6; }
.settings-alert p { margin:3px 0; }
.settings-success { background:#eef8f2; border-color:#c4e5d2; color:#286044; }
.settings-error { background:#fff1f1; border-color:#efcccc; color:#9c3030; }
.settings-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:14px; margin-bottom:24px; }
.settings-stats>div { padding:16px; background:#f7f9fc; border-radius:10px; }
.settings-stats strong { display:block; font-size:24px; color:var(--navy); }
.settings-stats span { display:block; color:#64748b; font-size:11px; margin-top:4px; }
.settings-filter { display:flex; align-items:flex-end; gap:10px; margin-bottom:22px; flex-wrap:wrap; }
.settings-filter>div:first-child { flex:1; min-width:200px; }
.settings-filter>div:nth-child(2) { min-width:130px; }
.settings-filter label { margin-top:0; }
.settings-table-wrap { overflow-x:auto; }
.settings-table { border-collapse:collapse; width:100%; font-size:12px; }
.settings-table th { text-align:left; font-size:10px; letter-spacing:.05em; text-transform:uppercase; background:#f7f9fc; color:#64748b; padding:13px 14px; }
.settings-table td { padding:16px 14px; border-bottom:1px solid #edf2f7; vertical-align:top; }
.settings-table td:first-child { width:55%; min-width:230px; }
.settings-table td:last-child { white-space:nowrap; color:#64748b; }
.settings-table summary { cursor:pointer; color:var(--navy); font-weight:600; line-height:1.6; }
.settings-message { white-space:pre-wrap; overflow-wrap:anywhere; line-height:1.7; margin:10px 0; color:#475569; }
.settings-status { padding:4px 8px; border-radius:5px; background:#edf2f7; color:#475569; font-size:10px; font-weight:600; }
.settings-status-sent { background:#eaf6ed; color:#2f6c45; }
.settings-status-pending { background:#fff6e4; color:#8b691f; }
.settings-status-failed { background:#fff0f0; color:#a33d3d; }
.settings-table .settings-empty { text-align:center; color:#64748b; padding:32px; }
.settings-pagination { display:flex; align-items:center; justify-content:flex-end; gap:14px; margin-top:18px; font-size:12px; color:#64748b; }
@media(max-width:1050px) { .settings-grid { grid-template-columns:1fr; } }
@media(max-width:640px) {
    .settings-intro { align-items:flex-start; flex-direction:column; }
    .settings-intro h2 { font-size:22px; }
    .settings-card { padding:20px; }
    .settings-fields { grid-template-columns:1fr; }
    .settings-nav a { padding:8px 12px; }
    .settings-stats { gap:8px; }
    .settings-stats>div { padding:12px; }
    .settings-filter>div { width:100%; }
    .settings-form-footer { align-items:flex-start; }
    .settings-form-footer .btn { width:100%; }
}
</style>
@endpush
