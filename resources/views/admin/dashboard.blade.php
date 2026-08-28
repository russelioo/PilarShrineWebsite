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
        @media(max-width:620px){.stats,.grid{grid-template-columns:1fr}}
    </style>
@endpush