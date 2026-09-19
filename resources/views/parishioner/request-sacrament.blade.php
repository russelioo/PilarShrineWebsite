{{-- @php
$title = 'Baptism / Wedding / Funeral';
$description = 'Static request preview for sacramental and funeral services.';
$columns = ['Service','Typical Requirement','Office Review'];
$rows = [['Baptism','Birth certificate and sponsor details','Required'],['Wedding','Canonical interview documents','Required'],['Funeral','Deceased and schedule information','Required']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))--}}
@extends('layouts.parishioner')
@section('title', 'Request a Sacrament')
@section('content')
<div class="heading"><div><h2>Request a Sacrament</h2><p>Choose a service, preferred date, and an available parish time slot.</p></div><a class="btn btn-outline" href="{{ route('parishioner.dashboard') }}">Dashboard</a></div>

<div class="coming-soon-banner" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <div>
        <strong>Notice: Online Sacrament Requests will be available soon</strong>
        <p>Online scheduling and submissions for baptism, wedding, and funeral services are currently being prepared. Please visit or contact the parish office for immediate sacramental scheduling.</p>
    </div>
</div>

@if($errors->any())<div class="errors"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="request-form" method="POST" action="{{ route('parishioner.sacrament-requests.store') }}">
 @csrf
 <label>Sacrament / Service<select name="service_type" disabled required>
  <option value="">Select a service</option>
  @foreach(['baptism'=>'Baptism','marriage'=>'Marriage / Wedding','funeral'=>'Funeral','confirmation'=>'Confirmation'] as $value=>$label)
   <option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>
  @endforeach
 </select></label>
 <label>Preferred Date<input type="date" name="preferred_date" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}" disabled required></label>
 <label class="wide">Available Time Slot<select name="slot_id" disabled required>
  <option value="">Select an available slot</option>
  @foreach($slots as $slot)
   <option value="{{ $slot->id }}" @selected((string) old('slot_id') === (string) $slot->id)>
    {{ \Carbon\Carbon::parse($slot->slot_time)->format('g:i A') }} — {{ $slot->massSchedule?->location ?? 'Parish office' }} ({{ $slot->max_capacity - $slot->current_bookings }} remaining)
   </option>
  @endforeach
 </select></label>
 <label class="wide">Notes<textarea name="notes" rows="5" maxlength="2000" disabled placeholder="Names, contact details, and other information for parish review">{{ old('notes') }}</textarea></label>
 <div class="actions wide"><a class="btn btn-outline" href="{{ route('parishioner.dashboard') }}">Back to Dashboard</a><button class="btn btn-primary" type="button" disabled style="opacity: 0.55; cursor: not-allowed;" title="This feature will be available soon">Submit Request (Available Soon)</button></div>
</form>
@endsection
@push('styles')
<style>
.coming-soon-banner{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-radius:10px;background:#fff8e6;border:1px solid #fde68a;color:#b45309;margin-bottom:20px}.coming-soon-banner svg{flex-shrink:0;margin-top:2px;color:#d97706}.coming-soon-banner strong{display:block;font-size:13px;font-family:var(--font-heading);font-weight:700;margin-bottom:3px;color:#92400e}.coming-soon-banner p{margin:0;font-size:11px;line-height:1.5;color:#b45309}
.heading{display:flex;justify-content:space-between;gap:18px;margin-bottom:20px}.heading h2{margin:0;color:var(--navy);font-family:var(--font-heading);font-size:26px;font-weight:700}.heading p{margin:6px 0 0;color:var(--muted);font-size:11px}.heading a,.actions a{text-decoration:none}.errors{margin-bottom:16px;padding:13px 16px;border:1px solid #f1b8b8;border-radius:8px;background:#fff1f1;color:#922;font-size:11px}.errors ul{margin:7px 0 0;padding-left:18px}.request-form{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:22px;border:1px solid var(--line);border-radius:11px;background:#fff}.request-form label{display:flex;flex-direction:column;gap:7px;color:var(--navy);font-size:10px;font-weight:700}.request-form input,.request-form select,.request-form textarea{width:100%;padding:11px 12px;border:1px solid var(--line);border-radius:7px;background:#f8fafc;font:inherit;color:var(--muted);box-sizing:border-box;cursor:not-allowed}.wide{grid-column:1/-1}.actions{display:flex;justify-content:flex-end;gap:10px}.no-slots{grid-column:1/-1;margin:0;padding:11px;border-radius:7px;background:#fff5d9;color:#86620e;font-size:10px}@media(max-width:620px){.request-form{grid-template-columns:1fr}.heading{display:block}.heading>a{display:inline-block;margin-top:12px}}
</style>
@endpush
