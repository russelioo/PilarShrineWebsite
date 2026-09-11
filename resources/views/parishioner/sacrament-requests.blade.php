@extends('layouts.parishioner')
@section('title', 'My Sacrament Requests')
@section('content')
<div class="heading">
    <div>
        <h2>Sacrament Requests</h2>
        <p>Track your baptism, marriage, funeral, and confirmation requests.</p>
    </div>
    <a class="btn btn-primary nav-item-disabled" href="javascript:void(0)" data-disabled-feature="true" data-feature-name="Sacrament Requests" role="button" aria-disabled="true" title="This feature will be available soon">+ New Request <span class="badge-soon" style="margin-left:6px;font-size:8px;">Soon</span></a>
</div>

<div class="coming-soon-banner" role="alert">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="8" x2="12" y2="12"></line>
        <line x1="12" y1="16" x2="12.01" y2="16"></line>
    </svg>
    <div>
        <strong>Notice: Online Sacrament Requests &amp; Tracking will be available soon</strong>
        <p>Online sacrament booking and submission tracking are currently being prepared. To schedule baptism, wedding, or funeral services, please contact or visit the parish office directly.</p>
    </div>
</div>

@if(session('success'))<div class="notice">{{ session('success') }}</div>@endif
<div class="request-list">
@forelse($requests as $item)
<article>
 <div><span class="service">{{ str($item->service_type)->headline() }}</span><h3>{{ $item->preferred_date->format('F j, Y') }} at {{ \Carbon\Carbon::parse($item->preferred_time)->format('g:i A') }}</h3>
 <p>{{ $item->timeSlot?->massSchedule?->location ?? 'Parish office' }}@if($item->notes) · {{ $item->notes }}@endif</p></div>
 <span class="badge badge-{{ $item->status }}">{{ ucfirst($item->status) }}</span>
</article>
@empty
<div class="empty">
    <h3>No sacrament requests yet</h3>
    <p>Online sacrament submissions will be available soon. Please consult the parish office for immediate sacramental scheduling.</p>
    <a class="btn btn-primary nav-item-disabled" href="javascript:void(0)" data-disabled-feature="true" data-feature-name="Sacrament Requests" role="button" aria-disabled="true" title="This feature will be available soon">Submit a request <span class="badge-soon" style="margin-left:6px;font-size:8px;">Soon</span></a>
</div>
@endforelse
</div>
@if($requests->hasPages())<div class="pagination">{{ $requests->links() }}</div>@endif
@endsection
@push('styles')
<style>
.coming-soon-banner{display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border-radius:10px;background:#fff8e6;border:1px solid #fde68a;color:#b45309;margin-bottom:20px}.coming-soon-banner svg{flex-shrink:0;margin-top:2px;color:#d97706}.coming-soon-banner strong{display:block;font-size:13px;font-family:Georgia,serif;margin-bottom:3px;color:#92400e}.coming-soon-banner p{margin:0;font-size:11px;line-height:1.5;color:#b45309}
.heading{display:flex;justify-content:space-between;align-items:flex-start;gap:18px;margin-bottom:20px}.heading h2{margin:0;color:var(--navy);font:700 26px Georgia}.heading p{margin:6px 0 0;color:var(--muted);font-size:11px}.heading a,.empty a{text-decoration:none}.notice{margin-bottom:16px;padding:12px;border:1px solid #b7e4c7;border-radius:8px;background:#effaf3;color:#176b3a}.request-list{border:1px solid var(--line);border-radius:11px;background:#fff;overflow:hidden}.request-list article{display:flex;justify-content:space-between;gap:18px;padding:18px;border-bottom:1px solid var(--line)}.request-list article:last-child{border:0}.service{color:var(--blue);font-size:9px;font-weight:800;text-transform:uppercase}.request-list h3{margin:5px 0;color:var(--navy);font:700 15px Georgia}.request-list p{margin:0;color:var(--muted);font-size:10px}.badge{height:max-content;padding:6px 10px;border-radius:20px;font-size:9px;font-weight:800;text-transform:uppercase}.badge-pending{background:#fff5d9;color:#86620e}.badge-approved{background:#e8f2ff;color:#1558a3}.badge-rejected{background:#fde8e8;color:#a12222}.badge-completed{background:#e8f7ee;color:#176b3a}.empty{padding:48px;text-align:center}.empty p{margin-bottom:18px}.pagination{margin-top:18px}@media(max-width:620px){.heading{display:block}.heading a{display:inline-block;margin-top:12px}}
</style>
@endpush
