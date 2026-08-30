@extends('layouts.parishioner')
@section('title', 'My Mass Intentions')

@section('content')
<div class="page-heading">
    <div><p class="eyebrow">My requests</p><h2>Mass Intentions</h2><p>Follow the progress of every Mass intention you have submitted.</p></div>
    <a class="btn btn-primary" href="{{ route('parishioner.request-mass-intention') }}">+ New Mass intention</a>
</div>

@if(session('success'))<div class="success-message" role="status"><span?</span>{{ session('success') }}</div>@endif

<div class="summary-grid">
    <article><span class="summary-dot pending"></span><div><strong>{{ $intentions->where('status', 'pending')->count() }}</strong><small>Pending on this page</small></div></article>
    <article><span class="summary-dot offered"></span><div><strong>{{ $intentions->where('status', 'offered')->count() }}</strong><small>Offered on this page</small></div></article>
    <article><span class="summary-dot completed"></span><div><strong>{{ $intentions->where('status', 'completed')->count() }}</strong><small>Completed on this page</small></div></article>
</div>

<div class="requests-card">
    @forelse($intentions as $intention)
        <article class="request-row">
            <div class="request-icon">&</div>
            <div class="request-main">
                <div class="request-top">
                    <div><span class="type">{{ str($intention->intention_type)->headline() }}</span><h3>{{ $intention->names }}</h3></div>
                    <span class="status status-{{ $intention->status }}">{{ ucfirst($intention->status) }}</span>
                </div>
                <div class="request-meta">
                    <span><b>Mass</b>{{ $intention->massSchedule->day_of_week }} at {{ \Carbon\Carbon::parse($intention->massSchedule->start_time)->format('g:i A') }}</span>
                    <span><b>Location</b>{{ $intention->massSchedule->location }}</span>
                    <span><b>Submitted</b>{{ $intention->requested_date->format('M j, Y') }}</span>
                    @if((float) $intention->offering_amount > 0)<span><b>Offering</b>±{{ number_format((float) $intention->offering_amount, 2) }}</span>@endif
                </div>
                @if($intention->offered_date)<p class="offered-date">Offered on {{ $intention->offered_date->format('F j, Y') }}</p>@endif
            </div>
        </article>
    @empty
        <div class="empty-state"><div?&</div><h3>No Mass intentions yet</h3><p>Your submitted intentions and their status will appear here.</p><a class="btn btn-primary" href="{{ route('parishioner.request-mass-intention') }}">Submit your first request</a></div>
    @endforelse
</div>

@if($intentions->hasPages())
<div class="pagination">
    @if($intentions->onFirstPage())<span>Previous</span>@else<a href="{{ $intentions->previousPageUrl() }}">Previous</a>@endif
    <small>Page {{ $intentions->currentPage() }} of {{ $intentions->lastPage() }}</small>
    @if($intentions->hasMorePages())<a href="{{ $intentions->nextPageUrl() }}">Next</a>@else<span>Next</span>@endif
</div>
@endif
@endsection

@push('styles')
<style>
.page-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:20px;margin-bottom:22px}.eyebrow{margin:0 0 6px!important;color:var(--gold)!important;font-size:9px!important;font-weight:800;text-transform:uppercase;letter-spacing:.12em}.page-heading h2{margin:0;color:var(--navy);font:700 27px Georgia,serif}.page-heading p{margin:7px 0 0;color:var(--muted);font-size:11px}.page-heading .btn{text-decoration:none}.success-message{display:flex;align-items:center;gap:9px;margin-bottom:18px;padding:13px 16px;border:1px solid #b7e4c7;border-radius:8px;background:#effaf3;color:#176b3a;font-size:11px}.success-message span{font-weight:900}.summary-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:18px}.summary-grid article{display:flex;align-items:center;gap:12px;padding:16px 18px;border:1px solid var(--line);border-radius:10px;background:#fff}.summary-dot{width:10px;height:10px;border-radius:50%}.summary-dot.pending{background:#d59b20}.summary-dot.offered{background:#2672c9}.summary-dot.completed{background:#32945b}.summary-grid strong,.summary-grid small{display:block}.summary-grid strong{color:var(--navy);font:700 20px Georgia,serif}.summary-grid small{margin-top:3px;color:var(--muted);font-size:9px}.requests-card{overflow:hidden;border:1px solid var(--line);border-radius:12px;background:#fff;box-shadow:0 5px 18px #173a680c}.request-row{display:flex;gap:15px;padding:20px;border-bottom:1px solid #edf2f7}.request-row:last-child{border-bottom:0}.request-icon{width:38px;height:38px;flex:none;display:grid;place-items:center;border-radius:50%;background:#fff6dc;color:#a77b13}.request-main{min-width:0;flex:1}.request-top{display:flex;justify-content:space-between;gap:20px}.type{color:var(--blue);font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.09em}.request-top h3{margin:5px 0 0;color:var(--ink);font:700 14px Georgia,serif;white-space:normal}.status{height:max-content;padding:6px 10px;border-radius:20px;font-size:8px;font-weight:800;text-transform:uppercase;letter-spacing:.05em}.status-pending{background:#fff5d9;color:#86620e}.status-offered{background:#e8f2ff;color:#1558a3}.status-completed{background:#e8f7ee;color:#176b3a}.request-meta{display:flex;flex-wrap:wrap;gap:12px 28px;margin-top:14px;color:var(--muted);font-size:10px}.request-meta span{display:flex;flex-direction:column;gap:3px}.request-meta b{color:#7a8795;font-size:8px;text-transform:uppercase}.offered-date{margin:12px 0 0;color:#176b3a;font-size:9px}.empty-state{padding:55px 20px;text-align:center}.empty-state>div{color:var(--gold);font-size:28px}.empty-state h3{margin:10px 0 6px;color:var(--navy);font:700 18px Georgia,serif}.empty-state p{margin:0 0 18px;color:var(--muted);font-size:10px}.empty-state .btn{text-decoration:none}.pagination{display:flex;align-items:center;justify-content:center;gap:18px;margin-top:18px;font-size:10px}.pagination a,.pagination span{padding:7px 11px;border:1px solid var(--line);border-radius:6px;text-decoration:none;color:var(--navy);background:#fff}.pagination span{opacity:.45}.pagination small{color:var(--muted)}@media(max-width:720px){.summary-grid{grid-template-columns:1fr}.page-heading{display:block}.page-heading .btn{display:inline-block;margin-top:14px}.request-meta{display:grid;grid-template-columns:1fr 1fr}.request-row{padding:16px}.request-icon{display:none}}
</style>
@endpush
