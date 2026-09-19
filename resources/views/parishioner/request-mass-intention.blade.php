@extends('layouts.parishioner')
@section('title', 'Request Mass Intention')

@section('content')
<div class="page-heading">
    <div>
        <p class="eyebrow">Prayer request</p>
        <h2>Request a Mass Intention</h2>
        <p>Submit the intention you would like remembered during one of our scheduled Masses.</p>
    </div>
    <a class="btn btn-outline" href="{{ route('parishioner.mass-intentions') }}">View my requests</a>
</div>

<div class="coming-soon-banner" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <div>
        <strong>Notice: Online Mass Intention Requests will be available soon</strong>
        <p>Online mass intention requests are currently being prepared. Online submissions are temporarily paused. For immediate Mass intentions and offerings, please visit or contact the parish office.</p>
    </div>
</div>

<div class="form-layout">
    <form class="intention-form" method="POST" action="{{ route('parishioner.mass-intentions.store') }}">
        @csrf
        <div class="form-section">
            <span class="step">1</span>
            <div>
                <h3>Intention details</h3>
                <p>Tell us whom or what you would like the Mass offered for.</p>
            </div>
        </div>

        <div class="field-grid">
            <label class="field">
                <span>Intention type <b>*</b></span>
                <select name="intention_type" disabled required>
                    <option value="">Select an intention type</option>
                    @foreach(['living' => 'For the Living', 'deceased' => 'For the Deceased', 'thanksgiving' => 'Thanksgiving', 'special' => 'Special Intention'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('intention_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('intention_type')<small class="error">{{ $message }}</small>@enderror
            </label>

            <label class="field">
                <span>Preferred Mass schedule <b>*</b></span>
                <select name="mass_schedule_id" disabled required>
                    <option value="">Select an available Mass</option>
                    @foreach($schedules as $schedule)
                        <option value="{{ $schedule->id }}" @selected((string) old('mass_schedule_id') === (string) $schedule->id)>
                            {{ $schedule->day_of_week }} · {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }} · {{ $schedule->location }}
                        </option>
                    @endforeach
                </select>
                @error('mass_schedule_id')<small class="error">{{ $message }}</small>@enderror
                @if($schedules->isEmpty())<small class="error">There are currently no active Mass schedules. Please contact the parish office.</small>@endif
            </label>

            <label class="field field-wide">
                <span>Name(s) or intention <b>*</b></span>
                <textarea name="names" rows="5" maxlength="1000" disabled required placeholder="Example: For the eternal repose of Juan Dela Cruz">{{ old('names') }}</textarea>
                <small class="hint">Please write the complete names or a short description of the intention.</small>
                @error('names')<small class="error">{{ $message }}</small>@enderror
            </label>

            <label class="field">
                <span>Offering amount <em>(optional)</em></span>
                <div class="money"><span>PHP</span><input name="offering_amount" type="number" min="0" max="99999999.99" step="0.01" value="{{ old('offering_amount') }}" disabled placeholder="0.00"></div>
                @error('offering_amount')<small class="error">{{ $message }}</small>@enderror
            </label>
        </div>

        <div class="notice"><strong>What happens next?</strong><span>The parish office will review your request. You can follow its progress from the My Mass Intentions page.</span></div>
        <div class="form-actions">
            <a class="btn btn-outline" href="{{ route('parishioner.mass-intentions') }}">Cancel</a>
            <button class="btn btn-primary" type="button" disabled style="opacity: 0.55; cursor: not-allowed;" title="This feature will be available soon">Submit Request (Available Soon)</button>
        </div>
    </form>

    <aside class="help-card">
        <div class="help-icon">&</div>
        <h3>About Mass Intentions</h3>
        <p>A Mass may be offered for the living, the deceased, thanksgiving, or another special intention.</p>
        <hr>
        <strong>Your request status</strong>
        <ul><li><b>Pending</b> — under parish review</li><li><b>Offered</b> — scheduled or offered at Mass</li><li><b>Completed</b> — request fulfilled</li></ul>
    </aside>
</div>
@endsection

@push('styles')
<style>
.coming-soon-banner{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-radius:10px;background:#fff8e6;border:1px solid #fde68a;color:#b45309;margin-bottom:20px}.coming-soon-banner svg{flex-shrink:0;margin-top:2px;color:#d97706}.coming-soon-banner strong{display:block;font-size:13px;font-family:var(--font-heading);font-weight:700;margin-bottom:3px;color:#92400e}.coming-soon-banner p{margin:0;font-size:11px;line-height:1.5;color:#b45309}
.page-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}.eyebrow{margin:0 0 6px!important;color:var(--gold)!important;font-size:9px!important;font-weight:800;text-transform:uppercase;letter-spacing:.12em}.page-heading h2{margin:0;color:var(--navy);font-family:var(--font-heading);font-size:27px;font-weight:700}.page-heading p{margin:7px 0 0;color:var(--muted);font-size:11px}.form-layout{display:grid;grid-template-columns:minmax(0,1fr) 280px;gap:20px;align-items:start}.intention-form,.help-card{border:1px solid var(--line);border-radius:12px;background:#fff;box-shadow:0 5px 18px #173a6810}.intention-form{padding:24px}.form-section{display:flex;gap:12px;align-items:center;padding-bottom:18px;margin-bottom:20px;border-bottom:1px solid #edf2f7}.step{width:32px;height:32px;display:grid;place-items:center;border-radius:50%;background:var(--navy);color:#fff;font-size:12px;font-weight:800}.form-section h3{margin:0;color:var(--navy);font-family:var(--font-heading);font-size:16px;font-weight:700}.form-section p{margin:4px 0 0;color:var(--muted);font-size:10px}.field-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}.field{display:flex;flex-direction:column;gap:7px;color:var(--ink);font-size:10px;font-weight:700}.field-wide{grid-column:1/-1}.field b{color:#b42318}.field em{color:var(--muted);font-weight:400}.field input,.field select,.field textarea{width:100%;padding:11px 12px;border:1px solid #ccd8e4;border-radius:7px;background:#f8fafc;color:var(--muted);font:400 11px Arial,sans-serif;outline:none;cursor:not-allowed}.field textarea{resize:vertical;line-height:1.55}.money{display:flex}.money span{display:grid;place-items:center;padding:0 11px;border:1px solid #ccd8e4;border-right:0;border-radius:7px 0 0 7px;background:#f6f8fb;color:var(--muted);font-size:9px}.money input{border-radius:0 7px 7px 0}.notice{display:flex;gap:10px;margin-top:22px;padding:13px;border-radius:8px;background:#f0f6fc;color:#49647f;font-size:10px;line-height:1.45}.notice strong{white-space:nowrap;color:var(--navy)}.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}.btn{text-decoration:none}.btn:disabled{opacity:.55;cursor:not-allowed}.help-card{padding:24px}.help-icon{width:38px;height:38px;display:grid;place-items:center;border-radius:50%;background:#fff6dc;color:#a77b13;font-size:18px}.help-card h3{margin:14px 0 8px;color:var(--navy);font-family:var(--font-heading);font-size:17px;font-weight:700}.help-card p,.help-card li{color:var(--muted);font-size:10px;line-height:1.6}.help-card hr{margin:18px 0;border:0;border-top:1px solid #edf2f7}.help-card>strong{color:var(--ink);font-size:10px}.help-card ul{padding-left:17px;margin-bottom:0}.help-card li b{color:var(--ink)}@media(max-width:900px){.form-layout{grid-template-columns:1fr}.help-card{order:-1}}@media(max-width:620px){.page-heading{display:block}.page-heading .btn{display:inline-block;margin-top:14px}.field-grid{grid-template-columns:1fr}.field-wide{grid-column:auto}.intention-form{padding:18px}.notice{display:block}.notice strong{display:block;margin-bottom:4px}}
</style>
@endpush
