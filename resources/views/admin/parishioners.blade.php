@extends('layouts.admin')

@section('title', 'Parishioners')

@section('content')
    @php
        // Static data initialization
        $parishioners = [
            ['name' => 'Juan Dela Cruz', 'email' => 'juan@email.com', 'phone' => '0917 123 4567', 'status' => 'Active', 'last_login' => '2026-08-28 10:30'],
            ['name' => 'Maria Santos', 'email' => 'maria@email.com', 'phone' => '0928 123 4567', 'status' => 'Active', 'last_login' => '2026-08-27 15:20'],
            ['name' => 'Pedro Reyes', 'email' => 'pedro@email.com', 'phone' => '0918 123 4567', 'status' => 'Pending', 'last_login' => 'Never'],
            ['name' => 'Ana Flores', 'email' => 'ana@email.com', 'phone' => '0936 123 4567', 'status' => 'Active', 'last_login' => '2026-08-26 09:15'],
            ['name' => 'Jose Garcia', 'email' => 'jose@email.com', 'phone' => '0915 123 4567', 'status' => 'Inactive', 'last_login' => '2026-08-25 18:45'],
            ['name' => 'Rosa Dimagiba', 'email' => 'rosa@email.com', 'phone' => '0922 123 4567', 'status' => 'Active', 'last_login' => '2026-08-24 11:00'],
        ];
    @endphp

    <div class="page-header">
        <h2>All Parishioners</h2>
        <div class="actions">
            <button class="btn btn-outline">📥 Export</button>
            <button class="btn btn-primary">＋ Add New</button>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" placeholder="Search by name, email, or phone...">
        <select>
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending</option>
        </select>
        <select>
            <option value="">Sort by</option>
            <option value="name">Name</option>
            <option value="newest">Newest</option>
            <option value="oldest">Oldest</option>
        </select>
    </div>

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
                @foreach($parishioners as $index => $p)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $p['name'] }}</strong></td>
                    <td>{{ $p['email'] }}</td>
                    <td>{{ $p['phone'] }}</td>
                    <td>
                        <span class="status-badge status-{{ strtolower($p['status']) }}">
                            {{ $p['status'] }}
                        </span>
                    </td>
                    <td>{{ $p['last_login'] ?? 'Never' }}</td>
                    <td class="action-icons">
                        <a href="#" title="View">👁</a>
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span>Showing 1-6 of 234 parishioners</span>
        <div style="display:flex;gap:4px">
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">‹</button>
            <button style="padding:6px 12px;border:1px solid var(--navy);border-radius:5px;background:var(--navy);color:#fff;cursor:pointer">1</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">2</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">3</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">…</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">20</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">›</button>
        </div>
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
@endpush