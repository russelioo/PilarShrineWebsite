@extends('layouts.admin')

@section('title', 'Staff Management')

@section('content')
    @php
        // Static data for staff
        $staff = [
            ['name' => 'Fr. John Reyes', 'email' => 'fr.john@pilarshrine.com', 'phone' => '0917 123 4567', 'role' => 'Admin', 'status' => 'Active', 'last_login' => '2026-08-28 10:30 AM'],
            ['name' => 'Maria Santos', 'email' => 'maria@pilarshrine.com', 'phone' => '0928 123 4567', 'role' => 'Staff', 'status' => 'Active', 'last_login' => '2026-08-27 03:15 PM'],
            ['name' => 'Pedro Cruz', 'email' => 'pedro@pilarshrine.com', 'phone' => '0918 123 4567', 'role' => 'Staff', 'status' => 'Pending', 'last_login' => 'Never'],
            ['name' => 'Ana Flores', 'email' => 'ana@pilarshrine.com', 'phone' => '0936 123 4567', 'role' => 'Staff', 'status' => 'Active', 'last_login' => '2026-08-26 09:00 AM'],
            ['name' => 'Ramon Rivera', 'email' => 'ramon@pilarshrine.com', 'phone' => '0922 123 4567', 'role' => 'Admin', 'status' => 'Active', 'last_login' => '2026-08-25 06:45 PM'],
            ['name' => 'Luzviminda Tan', 'email' => 'luz@pilarshrine.com', 'phone' => '0915 123 4567', 'role' => 'Staff', 'status' => 'Inactive', 'last_login' => '2026-08-20 11:00 AM'],
        ];
    @endphp

    <div class="page-header">
        <h2>Staff Management</h2>
        <div class="actions">
            <button class="btn btn-outline">📥 Export</button>
            <button class="btn btn-primary">＋ Add New Staff</button>
        </div>
    </div>

    <div class="toolbar">
        <input type="text" placeholder="Search by name, email, or phone...">
        <select>
            <option value="">All Roles</option>
            <option value="admin">Admin</option>
            <option value="staff">Staff</option>
        </select>
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
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last Login</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($staff as $index => $s)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><strong>{{ $s['name'] }}</strong></td>
                    <td>{{ $s['email'] }}</td>
                    <td>{{ $s['phone'] }}</td>
                    <td>
                        <span class="role-badge role-{{ strtolower($s['role']) }}">
                            {{ $s['role'] }}
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($s['status']) }}">
                            {{ $s['status'] }}
                        </span>
                    </td>
                    <td>{{ $s['last_login'] }}</td>
                    <td class="action-icons">
                        <a href="#" title="View">👁</a>
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Reset Password">🔑</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span>Showing 1-6 of 12 staff members</span>
        <div style="display:flex;gap:4px">
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">‹</button>
            <button style="padding:6px 12px;border:1px solid var(--navy);border-radius:5px;background:var(--navy);color:#fff;cursor:pointer">1</button>
            <button style="padding:6px 12px;border:1px solid var(--line);border-radius:5px;background:#fff;cursor:pointer">2</button>
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
        .role-badge{padding:4px 12px;border-radius:20px;font-size:9px;font-weight:700}
        .role-admin{background:#dbeafe;color:#1e40af}
        .role-staff{background:#e0e7ff;color:#4338ca}
        .action-icons{display:flex;gap:8px}
        .action-icons a{color:var(--muted);text-decoration:none;font-size:14px}
        .action-icons a:hover{color:var(--navy)}
        @media(max-width:620px){.toolbar input{min-width:100%}}
    </style>
@endpush