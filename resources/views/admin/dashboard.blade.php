@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        // Static data initialization
        $stats = [
            ['icon' => 'people', 'value' => '1,234', 'label' => 'Parishioners', 'change' => '↑ 12 this month'],
            ['icon' => 'requests', 'value' => '47', 'label' => 'Pending Requests', 'change' => '↑ 3 since yesterday'],
            ['icon' => 'calendar', 'value' => '28', 'label' => 'Upcoming Events', 'change' => '↓ 4 next week'],
            ['icon' => 'donations', 'value' => '₱45,230', 'label' => 'Donations', 'change' => '↑ ₱2,800 this week'],
        ];
    @endphp

    <section class="welcome">
        <div>
            <h2>Good day, Admin</h2>
            <p>Here is what is happening in the parish today.</p>
        </div>
        <div class="date">{{ now()->format('l, F j, Y') }}</div>
    </section>

    @if(session('status'))
        <div class="success-message" role="status">{{ session('status') }}</div>
    @endif

    <section class="livestream-control {{ $livestream->is_live ? 'is-live' : '' }}">
        <div class="live-state">
            <span class="live-dot" aria-hidden="true"></span>
            <div>
                <small>FACEBOOK LIVESTREAM</small>
                <h3>{{ $livestream->is_live ? 'LIVE banner is ON' : 'LIVE banner is OFF' }}</h3>
                <p>{{ $livestream->is_live ? 'Visitors can see the pulsing LIVE NOW banner.' : 'Turn it on when the Facebook broadcast begins.' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.livestream.update') }}">
            @csrf
            <input type="hidden" name="is_live" value="{{ $livestream->is_live ? 0 : 1 }}">
            <input type="hidden" name="title" value="{{ $livestream->title }}">
            <input type="hidden" name="url" value="{{ $livestream->url }}">
            <button class="live-toggle" type="submit">
                {{ $livestream->is_live ? 'Turn livestream OFF' : 'Turn livestream ON' }}
            </button>
        </form>
    </section>

    <section class="stats">
        @foreach($stats as $stat)
            <article class="stat">
                <div class="icon">
                    @switch($stat['icon'])
                        @case('people')♙@break
                        @case('requests')▤@break
                        @case('calendar')▣@break
                        @default✣
                    @endswitch
                </div>
                <strong>{{ $stat['value'] }}</strong>
                <b>{{ $stat['label'] }}</b>
                <small>{{ $stat['change'] }}</small>
            </article>
        @endforeach
    </section>

    <div class="grid">
        <section class="panel">
            <div class="panel-head">
                <h3>Recent requests</h3>
                <a href="#">VIEW ALL →</a>
            </div>
            @foreach([['♙','Baptism Request — Maria Santos','Today at 9:30 AM'],['♡','Mass Intention — Roberto Cruz','Yesterday'],['♢','Wedding Request — Ana & Miguel','August 20']] as $item)
                <div class="request">
                    <i>{{ $item[0] }}</i>
                    <div>
                        <b>{{ $item[1] }}</b>
                        <small>{{ $item[2] }}</small>
                    </div>
                    <span class="badge">PENDING</span>
                </div>
            @endforeach
        </section>
        
        <section class="panel">
            <div class="panel-head">
                <h3>Quick actions</h3>
            </div>
            <div class="quick">
                <button><span>＋</span>Add event</button>
                <button><span>✦</span>Post notice</button>
                <button><span>▣</span>Mass schedule</button>
                <button><span>♙</span>Add member</button>
            </div>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .stats{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}
        .success-message{margin-bottom:18px;padding:12px 16px;border:1px solid #a7d7b4;border-radius:8px;background:#edf9f0;color:#246637;font-size:12px}
        .livestream-control{display:flex;align-items:center;justify-content:space-between;gap:24px;margin-bottom:22px;padding:20px 22px;border:1px solid #d7e0e9;border-left:5px solid #718096;border-radius:11px;background:#fff;box-shadow:0 7px 20px #12345b0b}
        .livestream-control.is-live{border-color:#efc3c7;border-left-color:#b4232c;background:#fff8f8}
        .live-state{display:flex;align-items:center;gap:15px}
        .live-state small{color:var(--muted);font-size:9px;font-weight:800;letter-spacing:.08em}
        .live-state h3{margin:4px 0;color:var(--navy);font:700 18px Georgia,serif}
        .live-state p{margin:0;color:var(--muted);font-size:11px}
        .live-dot{width:15px;height:15px;flex:none;border-radius:50%;background:#718096}
        .is-live .live-dot{background:#d3212d;box-shadow:0 0 0 6px #d3212d1f}
        .live-toggle{min-width:180px;padding:12px 16px;border:1px solid #b4232c;border-radius:7px;background:#b4232c;color:#fff;font-size:11px;font-weight:800;cursor:pointer}
        .is-live .live-toggle{background:#fff;color:#9a2028}
        .stat,.panel{padding:21px;border:1px solid var(--line);border-radius:11px;background:#fff;box-shadow:0 7px 20px #12345b0b}
        .icon{width:39px;height:39px;display:grid;place-items:center;border-radius:9px;background:#eaf2fb;color:var(--navy)}
        .stat strong{display:block;margin:18px 0 5px;color:var(--navy);font:700 27px Georgia}
        .stat b{font-size:11px}
        .stat small{display:block;margin-top:7px;color:var(--muted);font-size:9px}
        .grid{display:grid;grid-template-columns:1.5fr 1fr;gap:20px;margin-top:22px}
        .panel-head{display:flex;justify-content:space-between;margin-bottom:18px}
        .panel h3{margin:0;color:var(--navy);font-size:16px;font-family:Georgia,serif}
        .panel-head a{color:var(--blue);font-size:9px;font-weight:700;text-decoration:none}
        .request{display:grid;grid-template-columns:38px 1fr auto;gap:12px;align-items:center;padding:13px 0;border-top:1px solid #edf1f5}
        .request i{width:36px;height:36px;display:grid;place-items:center;border-radius:50%;background:#f0f5fa;font-style:normal}
        .request b{display:block;font-size:11px}
        .request small{color:var(--muted);font-size:9px}
        .badge{padding:5px 8px;border-radius:20px;background:#fff3d7;color:#8a6714;font-size:8px;font-weight:700}
        .quick{display:grid;grid-template-columns:1fr 1fr;gap:10px}
        .quick button{min-height:86px;border:1px solid var(--line);border-radius:8px;background:#f9fbfd;color:var(--navy);font-size:10px;font-weight:700}
        .quick span{display:block;margin-bottom:8px;font-size:22px}
        @media(max-width:620px){.stats,.grid{grid-template-columns:1fr}.livestream-control{align-items:stretch;flex-direction:column}.live-toggle{width:100%}}
    </style>
@endpush
