@extends('layouts.admin')

@section('title', 'Parishioners')

@section('content')

    <div class="page-header">
        <h2>All Parishioners</h2>
        <div class="actions">
            <button class="btn btn-outline">📥 Export</button>
            <button class="btn btn-primary">＋ Add New</button>
        </div>
    </div>

    <form class="toolbar" method="GET" action="{{ route('admin.parishioners') }}">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search by name, email, or phone...">
        <select name="status">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        </select>
        <select name="sort">
            <option value="">Sort by</option>
            <option value="name" @selected(request('sort') === 'name')>Name</option>
            <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
        </select>
        <button class="btn btn-primary" type="submit">Apply</button>
        @if(request()->hasAny(['search', 'status', 'sort']))<a class="btn btn-outline" href="{{ route('admin.parishioners') }}">Clear</a>@endif
    </form>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($parishioners as $index => $p)
                @php($status = $p->is_verified ? 'Active' : 'Pending')
                <tr>
                    <td>{{ $parishioners->firstItem() + $index }}</td>
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->email }}</td>
                    <td>{{ $p->phone ?: '—' }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($status) }}">
                            {{ $status }}
                        </span>
                    </td>
                    <td>{{ $p->last_login?->format('M d, Y h:i A') ?? 'Never' }}</td>
                    <td class="action-icons">
                        <a href="#" title="View">👁</a>
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="empty-cell">No parishioners found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span>Showing {{ $parishioners->firstItem() ?? 0 }}-{{ $parishioners->lastItem() ?? 0 }} of {{ $parishioners->total() }} parishioners</span>
        {{ $parishioners->onEachSide(1)->links() }}
    </div>
@endsection

@push('styles')
    <style>
        .toolbar{display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap}
        .toolbar input{flex:1;min-width:200px;padding:10px 14px;border:1px solid var(--line);border-radius:7px;font-size:12px}
        .toolbar select{padding:10px 14px;border:1px solid var(--line);border-radius:7px;font-size:12px;background:#fff}
        .table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:11px;background:#fff}
        table{width:100%;border-collapse:collapse;font-size:12px}
        th{text-align:left;padding:14px 16px;background:#f7fafc;color:var(--muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--line)}
        td{padding:12px 16px;border-bottom:1px solid #f0f4f8}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafcfe}
        .status-badge{padding:4px 12px;border-radius:20px;font-size:9px;font-weight:700}
        .status-active{background:#e2f5e2;color:#1a7a1a}
        .status-inactive{background:#fde8e8;color:#a12222}
        .status-pending{background:#fff3d7;color:#8a6714}
        .action-icons{display:flex;gap:8px}
        .action-icons a{color:var(--muted);text-decoration:none;font-size:14px}
        .action-icons a:hover{color:var(--navy)}
        @media(max-width:620px){.toolbar input{min-width:100%}}
    </style>
        .empty-cell{text-align:center;padding:28px;color:var(--muted)}
@endpush