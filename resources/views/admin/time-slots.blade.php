{{-- @php
$title = 'Time Slots';
$singular = 'Time Slot';
$description = 'Review capacity and availability for bookable schedule slots.';
$columns = ['Mass Schedule','Slot Time','Capacity','Bookings','Available'];
$rows = [['Sunday Morning Mass','6:00 AM','50','32','Yes'],['Sunday Morning Mass','7:30 AM','50','50','No']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))--}}
@extends('layouts.admin')
@section('title', 'Time Slots')
@section('content')
<div class="page-header"><div><h2>Sunday Time Slots</h2><p>Manage the morning and evening slots available for parishioner sacrament requests.</p></div></div>
@if(session('success'))<div class="notice success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="notice error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

<section class="editor">
 <h3>{{ $editing ? 'Edit Time Slot' : 'Create Time Slot' }}</h3>
 <form method="POST" action="{{ $editing ? route('admin.time-slots.update', $editing) : route('admin.time-slots.store') }}">
  @csrf
  @if($editing)@method('PUT')@endif
  <label>Sunday Mass
   <select name="mass_schedule_id" required>
    @foreach($schedules as $schedule)<option value="{{ $schedule->id }}" @selected((string) old('mass_schedule_id', $editing?->mass_schedule_id) === (string) $schedule->id)>{{ $schedule->title }} — {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}</option>@endforeach
   </select>
  </label>
  <label>Slot Time<input type="time" name="slot_time" value="{{ old('slot_time', $editing?->slot_time ? \Carbon\Carbon::parse($editing->slot_time)->format('H:i') : '') }}" required></label>
  <label>Capacity<input type="number" name="max_capacity" min="1" max="1000" value="{{ old('max_capacity', $editing?->max_capacity ?? 50) }}" required></label>
  <label class="check"><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $editing?->is_available ?? true))> Available for booking</label>
  <div class="actions"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save Changes' : 'Create Slot' }}</button>@if($editing)<a class="btn btn-outline" href="{{ route('admin.time-slots') }}">Cancel</a>@endif</div>
 </form>
</section>

<div class="table-wrap"><table>
 <thead><tr><th>Sunday Mass</th><th>Time</th><th>Capacity</th><th>Bookings</th><th>Remaining</th><th>Available</th><th>Actions</th></tr></thead>
 <tbody>
 @forelse($slots as $slot)
 <tr>
  <td><strong>{{ $slot->massSchedule?->title ?? 'Deleted schedule' }}</strong><small>{{ $slot->massSchedule?->location }}</small></td>
  <td>{{ \Carbon\Carbon::parse($slot->slot_time)->format('g:i A') }}</td>
  <td>{{ $slot->max_capacity }}</td><td>{{ $slot->current_bookings }}</td><td>{{ max(0, $slot->max_capacity - $slot->current_bookings) }}</td>
  <td><span class="badge {{ $slot->is_available ? 'on' : 'off' }}">{{ $slot->is_available ? 'Yes' : 'No' }}</span></td>
  <td class="row-actions"><a href="{{ route('admin.time-slots', ['edit'=>$slot->id]) }}">Edit</a>
   <form method="POST" action="{{ route('admin.time-slots.destroy', $slot) }}" onsubmit="return confirm('Delete this time slot?')">@csrf @method('DELETE')<button type="submit">Delete</button></form>
  </td>
 </tr>
 @empty
 <tr><td colspan="7" class="empty">No time slots found.</td></tr>
 @endforelse
 </tbody>
</table></div>
@endsection
@push('styles')
<style>
.page-header p{margin:6px 0 0;color:var(--muted);font-size:11px}.notice{margin-bottom:14px;padding:12px 15px;border-radius:8px;font-size:11px}.notice ul{margin:0;padding-left:18px}.success{border:1px solid #b7e4c7;background:#effaf3;color:#176b3a}.error{border:1px solid #f1b8b8;background:#fff1f1;color:#922}.editor{margin-bottom:20px;padding:18px;border:1px solid var(--line);border-radius:11px;background:#fff}.editor h3{margin:0 0 14px;color:var(--navy);font:700 17px Georgia}.editor form{display:grid;grid-template-columns:2fr 1fr 1fr;gap:12px;align-items:end}.editor label{display:flex;flex-direction:column;gap:6px;color:var(--navy);font-size:10px;font-weight:700}.editor input,.editor select{padding:10px;border:1px solid var(--line);border-radius:7px;background:#fff}.editor .check{flex-direction:row;align-items:center}.editor .check input{width:auto}.actions{display:flex;gap:8px}.actions a{text-decoration:none}.table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:11px;background:#fff}table{width:100%;border-collapse:collapse;font-size:11px}th,td{padding:13px 15px;text-align:left;border-bottom:1px solid #edf2f7}th{background:#f7fafc;color:var(--muted);font-size:9px;text-transform:uppercase}td small{display:block;margin-top:4px;color:var(--muted)}.badge{padding:4px 9px;border-radius:14px;font-size:9px;font-weight:700}.badge.on{background:#e8f7ee;color:#176b3a}.badge.off{background:#fde8e8;color:#922}.row-actions{display:flex;gap:10px}.row-actions a,.row-actions button{border:0;background:none;color:var(--blue);font-size:10px;font-weight:700;cursor:pointer;text-decoration:none}.row-actions form{margin:0}.row-actions button{color:#a12222}.empty{text-align:center;padding:30px}@media(max-width:760px){.editor form{grid-template-columns:1fr}}
</style>
@endpush
