{{-- @php
$title = 'Sacrament Requests';
$description = 'View your baptism, wedding, funeral, and other sacrament requests.';
$columns = ['Sacrament','For','Preferred Date','Status'];
$rows = [['Baptism','Sofia Dela Cruz','Sep 12, 2026','Pending'],['Marriage','Ana Flores & Miguel Reyes','Oct 8, 2026','Approved']];
@endphp
@include('parishioner.module-index', compact('title', 'description', 'columns', 'rows'))--}}
@extends('layouts.parishioner')
@section('title', 'My Sacrament Requests')
@section('content')
<div class="heading"><div><h2>Sacrament Requests</h2><p>Track your baptism, marriage, funeral, and confirmation requests.</p></div><a class="btn btn-primary" href="{{ route('parishioner.request-sacrament') }}">+ New Request</a></div>
@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
<div class="request-list">
@forelse($requests as $item)
<article>
 <div><span class="service">{{ str($item->service_type)->headline() }}</span><h3>{{ $item->preferred_date->format('F j, Y') }} at {{ \Carbon\Carbon::parse($item->preferred_time)->format('g:i A') }}</h3>
 <p>{{ $item->timeSlot?->massSchedule?->location ?? 'Parish office' }}@if($item->notes) · {{ $item->notes }}@endif</p></div>
 <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
</article>
@empty
<div class="empty"><h3>No sacrament requests yet</h3><p>Your submitted requests will appear here.</p><a class="btn btn-primary" href="{{ route('parishioner.request-sacrament') }}">Submit a request</a></div>
@endforelse
</div>
@if($requests->hasPages())<div class="pagination">{{ $requests->links() }}</div>@endif
@endsection
@push('styles')
<style>
.heading{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:20px}.heading h2{margin:0;color:var(--navy);font:700 26px Georgia}.heading p{margin:6px 0 0;color:var(--muted);font-size:11px}.heading a,.empty a{text-decoration:none}.notice{margin-bottom:16px;padding:12px;border:1px solid #b7e4c7;border-radius:8px;background:#effaf3;color:#176b3a}.request-list{border:1px solid var(--line);border-radius:11px;background:#fff;overflow:hidden}.request-list article{display:flex;justify-content:space-between;gap:18px;padding:18px;border-bottom:1px solid var(--line)}.request-list article:last-child{border:0}.service{color:var(--blue);font-size:9px;font-weight:800;text-transform:uppercase}.request-list h3{margin:5px 0;color:var(--navy);font:700 15px Georgia}.request-list p{margin:0;color:var(--muted);font-size:10px}.badge{height:max-content;padding:6px 10px;border-radius:20px;font-size:9px;font-weight:800;text-transform:uppercase}.badge-pending{background:#fff5d9;color:#86620e}.badge-approved{background:#e8f2ff;color:#1558a3}.badge-rejected{background:#fde8e8;color:#a12222}.badge-completed{background:#e8f7ee;color:#176b3a}.empty{padding:48px;text-align:center}.empty p{margin-bottom:18px}.pagination{margin-top:18px}@media(max-width:620px){.heading{display:block}.heading a{display:inline-block;margin-top:12px}}
</style>
@endpush
