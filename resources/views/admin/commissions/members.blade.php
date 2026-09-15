@extends('layouts.admin')

@section('title', $commission->name . ' — Members')

@section('content')

    <div class="page-header">
        <div>
            <h2>{{ $commission->name }}</h2>
            <p style="font-size:12px;color:var(--muted);margin:4px 0 0;">Commission Members</p>
        </div>
        @if($actor->hasParishWideAccess())
        <div class="actions">
            <a href="{{ route('admin.staff') }}" class="btn btn-outline">← Back to Staff</a>
        </div>
        @endif
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
                    <th>Joined</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $index => $member)
                @php($status = $member->is_verified ? 'Active' : 'Inactive')
                <tr>
                    <td>{{ $members->firstItem() + $index }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            @if($member->avatar)
                                <img src="{{ $member->avatar }}" alt="" style="width:26px;height:26px;border-radius:50%;object-fit:cover;border:1px solid var(--border);">
                            @endif
                            <strong>{{ $member->name }}</strong>
                        </div>
                    </td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->phone ?: '—' }}</td>
                    <td>
                        <span class="role-badge role-{{ $member->role }}">{{ $member->role_badge_label }}</span>
                    </td>
                    <td>
                        <span class="status-badge status-{{ strtolower($status) }}">{{ $status }}</span>
                    </td>
                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center;padding:28px;color:var(--muted);">No members in this commission.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:18px;font-size:11px;color:var(--muted)">
        <span>Showing {{ $members->firstItem() ?? 0 }}–{{ $members->lastItem() ?? 0 }} of {{ $members->total() }} members</span>
        {{ $members->onEachSide(1)->links() }}
    </div>

@endsection

@push('styles')
<style>
    .table-wrap{overflow-x:auto;border:1px solid var(--border);border-radius:11px;background:#fff}
    table{width:100%;border-collapse:collapse;font-size:12px}
    th{text-align:left;padding:14px 16px;background:#f8fafc;color:var(--muted);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--border)}
    td{padding:12px 16px;border-bottom:1px solid #f1f5f9;vertical-align:middle}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:#fafcfe}
    .role-badge{padding:4px 10px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block;white-space:nowrap}
    .role-admin,.role-super_admin{background:#dbeafe;color:#1e40af}
    .role-staff,.role-commission_member{background:#e0e7ff;color:#4338ca}
    .role-commission_admin{background:#fef9c3;color:#854d0e}
    .role-parish_priest,.role-parochial_vicar,.role-parish_secretary{background:#fce7f3;color:#9d174d}
    .status-badge{padding:4px 12px;border-radius:20px;font-size:9px;font-weight:700;display:inline-block}
    .status-active{background:#e2f5e2;color:#1a7a1a}
    .status-inactive{background:#fee2e2;color:#b91c1c}
</style>
@endpush

