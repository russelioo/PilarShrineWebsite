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
<div class="heading"><div><h2>Request a Sacrament</h2><p>Choose a service, preferred date, and an available parish time slot.</p></div><a class="btn btn-outline" href="{{ route('parishioner.sacrament-requests') }}">My Requests</a></div>
@if($errors->any())<div class="errors"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form class="request-form" method="POST" action="{{ route('parishioner.sacrament-requests.store') }}">
 @csrf
 <label>Sacrament / Service<select name="service_type" required>
  <option value="">Select a service</option>
  @foreach(['baptism'=>'Baptism','marriage'=>'Marriage / Wedding','funeral'=>'Funeral','confirmation'=>'Confirmation'] as $value=>$label)
   <option value="{{ $value }}" @selected(old('service_type') === $value)>{{ $label }}</option>
  @endforeach
 </select></label>
 <label>Preferred Date<input type="date" name="preferred_date" min="{{ now()->toDateString() }}" value="{{ old('preferred_date') }}" required></label>
 <label class="wide">Available Time Slot<select name="slot_id" required>
  <option value="">Select an available slot</option>
  @foreach($slots as $slot)
   <option value="{{ $slot->id }}" @selected((string) old('slot_id') === (string) $slot->id)>
    {{ \Carbon\Carbon::parse($slot->slot_time)->format('g:i A') }} — {{ $slot->massSchedule?->location ?? 'Parish office' }} ({{ $slot->max_capacity - $slot->current_bookings }} remaining)
   </option>
  @endforeach
 </select></label>
 <label class="wide">Notes<textarea name="notes" rows="5" maxlength="2000" placeholder="Names, contact details, and other information for parish review">{{ old('notes') }}</textarea></label>
 @if($slots->isEmpty())<p class="no-slots">No time slots are currently available. Please contact the parish office.</p>@endif
 <div class="actions wide"><a class="btn btn-outline" href="{{ route('parishioner.sacrament-requests') }}">Cancel</a><button class="btn btn-primary" type="submit" @disabled($slots->isEmpty())>Submit Request</button></div>
</form>
@endsection
@push('styles')
<style>
.heading{display:flex;justify-content:space-between;gap:18px;margin-bottom:20px}.heading h2{margin:0;color:var(--navy);font:700 26px Georgia}.heading p{margin:6px 0 0;color:var(--muted);font-size:11px}.heading a,.actions a{text-decoration:none}.errors{margin-bottom:16px;padding:13px 16px;border:1px solid #f1b8b8;border-radius:8px;background:#fff1f1;color:#922;font-size:11px}.errors ul{margin:7px 0 0;padding-left:18px}.request-form{display:grid;grid-template-columns:1fr 1fr;gap:16px;padding:22px;border:1px solid var(--line);border-radius:11px;background:#fff}.request-form label{display:flex;flex-direction:column;gap:7px;color:var(--navy);font-size:10px;font-weight:700}.request-form input,.request-form select,.request-form textarea{width:100%;padding:11px 12px;border:1px solid var(--line);border-radius:7px;background:#fff;font:inherit;color:var(--ink);box-sizing:border-box}.wide{grid-column:1/-1}.actions{display:flex;justify-content:flex-end;gap:10px}.no-slots{grid-column:1/-1;margin:0;padding:11px;border-radius:7px;background:#fff5d9;color:#86620e;font-size:10px}@media(max-width:620px){.request-form{grid-template-columns:1fr}.heading{display:block}.heading>a{display:inline-block;margin-top:12px}}
</style>
@endpush
