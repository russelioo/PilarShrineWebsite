{{-- @php
$title = 'Mass Intentions';
$singular = 'Mass Intention';
$description = 'Track requested intentions, offerings, and fulfillment status.';
$columns = ['Requested By','Type','Names','Offering','Requested Date','Status'];
$rows = [['Maria Santos','Thanksgiving','Santos Family','PHP 500.00','Aug 28, 2026','Pending'],['Roberto Cruz','Deceased','Elena Cruz','PHP 300.00','Aug 27, 2026','Offered']];
@endphp
@include('admin.module-index', compact('title', 'singular', 'description', 'columns', 'rows'))--}}
@extends('layouts.admin')
@section('title', 'Mass Intentions')
@section('content')
<div class="page-header"><div><h2>Mass Intentions</h2><p>Track requests, offerings, schedules, and fulfillment status.</p></div></div>
@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
<div class="summary">
 <article><strong>{{ $counts->sum() }}</strong><span>Total requests</span></article>
 <article><strong>{{ $counts->get('pending', 0) }}</strong><span>Pending</span></article>
 <article><strong>{{ $counts->get('offered', 0) + $counts->get('completed', 0) }}</strong><span>Fulfilled</span></article>
</div>
<form class="toolbar" method="GET" action="{{ route('admin.mass-intentions') }}">
 <input type="search" name="search" value="{{ request('search') }}" placeholder="Search requester, email, or names...">
 <select name="type"><option value="">All types</option>
 @foreach(['living','deceased','thanksgiving','special'] as $type)<option value="{{ $type }}" @selected(request('type') === $type)>{{ str($type)->headline() }}</option>@endforeach
 </select>
 <select name="status"><option value="">All statuses</option>
 @foreach(['pending','offered','completed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
 </select>
 <button class="btn btn-primary" type="submit">Apply</button>
 @if(request()->hasAny(['search','type','status']))<a class="btn btn-outline" href="{{ route('admin.mass-intentions') }}">Clear</a>@endif
</form>
<div class="table-wrap"><table>
<thead><tr><th>#</th><th>Requested By</th><th>Intention</th><th>Mass Schedule</th><th>Offering</th><th>Requested</th><th>Status</th></tr></thead>
<tbody>
@forelse($intentions as $index => $intention)
<tr>
 <td>{{ $intentions->firstItem() + $index }}</td>
 <td><strong>{{ $intention->requested_by }}</strong><small>{{ $intention->user?->email ?? 'No account email' }}</small></td>
 <td><b>{{ str($intention->intention_type)->headline() }}</b><div>{{ $intention->names }}</div></td>
 <td>@if($intention->massSchedule){{ $intention->massSchedule->day_of_week }} · {{ \Carbon\Carbon::parse($intention->massSchedule->start_time)->format('g:i A') }}<small>{{ $intention->massSchedule->location }}</small>@else Schedule unavailable @endif</td>
 <td>₱{{ number_format((float) $intention->offering_amount, 2) }}</td>
 <td>{{ $intention->requested_date->format('M j, Y') }}</td>
 <td><form method="POST" action="{{ route('admin.mass-intentions.status', $intention) }}">@csrf @method('PATCH')
  <select class="status status-{{ $intention->status }}" name="status" onchange="this.form.submit()">
  @foreach(['pending','offered','completed'] as $status)<option value="{{ $status }}" @selected($intention->status === $status)>{{ ucfirst($status) }}</option>@endforeach
  </select></form>
  @if($intention->offered_date)<small>Offered {{ $intention->offered_date->format('M j, Y') }}</small>@endif
 </td>
</tr>
@empty
<tr><td class="empty" colspan="7">No Mass intentions found.</td></tr>
@endforelse
</tbody></table></div>
@if($intentions->hasPages())<div class="pagination">
 @if($intentions->onFirstPage())<span>Previous</span>@else<a href="{{ $intentions->previousPageUrl() }}">Previous</a>@endif
 <small>Page {{ $intentions->currentPage() }} of {{ $intentions->lastPage() }}</small>
 @if($intentions->hasMorePages())<a href="{{ $intentions->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
</div>@endif
@endsection
@push('styles')
<style>
.page-header p{margin:6px 0 0;color:var(--muted);font-size:11px}.notice{margin-bottom:16px;padding:12px;border:1px solid #b7e4c7;border-radius:8px;background:#effaf3;color:#176b3a}.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}.summary article{padding:16px 18px;border:1px solid var(--line);border-radius:9px;background:#fff}.summary strong,.summary span{display:block}.summary strong{font:700 20px Georgia;color:var(--navy)}.summary span{font-size:9px;color:var(--muted);text-transform:uppercase}.toolbar{display:flex;gap:10px;margin-bottom:18px;flex-wrap:wrap}.toolbar input,.toolbar select{padding:10px 12px;border:1px solid var(--line);border-radius:7px;background:#fff}.toolbar input{flex:1}.toolbar a{text-decoration:none}.table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:11px;background:#fff}table{width:100%;border-collapse:collapse;font-size:11px}th,td{padding:13px 15px;text-align:left;border-bottom:1px solid #edf2f7;vertical-align:top}th{background:#f7fafc;color:var(--muted);font-size:9px;text-transform:uppercase;white-space:nowrap}td small{display:block;margin-top:4px;color:var(--muted)}td b{color:var(--blue);font-size:9px;text-transform:uppercase}.status{padding:6px;border:0;border-radius:14px;font-weight:700}.status-pending{background:#fff5d9;color:#86620e}.status-offered{background:#e8f2ff;color:#1558a3}.status-completed{background:#e8f7ee;color:#176b3a}.empty{text-align:center;padding:34px}.pagination{display:flex;justify-content:center;align-items:center;gap:18px;margin-top:18px}.pagination a,.pagination span{padding:7px 11px;border:1px solid var(--line);border-radius:6px;text-decoration:none}@media(max-width:720px){.summary{grid-template-columns:1fr}}
</style>
@endpush
