@extends('layouts.admin')

@section('title', 'Parishioners')

@section('content')

    <div class="page-header">
        <h2>All Parishioners</h2>
        <div class="actions">
            <button class="btn btn-outline">📥 Export</button>
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
                <tr data-user-id="{{ $p->id }}">
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
                        @if(in_array($actor->role, ['super_admin', 'admin'], true))
                        <a href="#" title="Edit">✎</a>
                        <a href="#" title="Delete" style="color:#c0392b">✕</a>
                        <button type="button" class="btn-promote" title="Promote to Staff Roster (Parish Admin Only)"
                            onclick="openPromoteModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->email) }}')">
                            ⭐ Promote
                        </button>
                        @endif
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

    <!-- ============================================== -->
    <!-- PROMOTE PARISHIONER TO STAFF MODAL (SUPER ADMIN ONLY) -->
    <!-- ============================================== -->
    @if(in_array($actor->role, ['super_admin', 'admin'], true))
    <div id="promote-modal-backdrop" class="modal-backdrop-common" onclick="closePromoteModal()" style="display:none;" aria-hidden="true"></div>
    <div id="promote-modal" class="custom-modal" role="dialog" aria-modal="true" aria-labelledby="promote-modal-title" style="display:none; max-width: 520px;" aria-hidden="true">
        <div class="custom-modal-header">
            <div>
                <div class="modal-pretitle">Super Admin Authority</div>
                <h3 id="promote-modal-title" class="custom-modal-title">Promote to Staff Roster</h3>
            </div>
            <button type="button" class="drawer-close-btn" onclick="closePromoteModal()" aria-label="Close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="promote-form" onsubmit="submitPromoteParishioner(event)">
            <div class="custom-modal-body" style="padding: 20px 24px; max-height: 70vh; overflow-y: auto;">
                <input type="hidden" id="promote-user-id" name="user_id" value="">

                <!-- Candidate Overview Card -->
                <div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border:1px solid #e2e8f0;padding:12px 14px;border-radius:8px;margin-bottom:16px;">
                    <div style="width:38px;height:38px;border-radius:50%;background:#e0f2fe;color:#0369a1;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;" id="promote-avatar-initials">
                        P
                    </div>
                    <div>
                        <div style="font-weight:700;font-size:13px;color:#1e293b;" id="promote-user-name">—</div>
                        <div style="font-size:11px;color:#64748b;" id="promote-user-email">—</div>
                    </div>
                    <span style="margin-left:auto;background:#e2f5e2;color:#1a7a1a;font-size:10px;font-weight:700;padding:3px 8px;border-radius:12px;">Parishioner</span>
                </div>

                <!-- Role Selection -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="field-label" for="promote-role">Assigned Staff Role <span style="color:#dc2626;">*</span></label>
                    <select id="promote-role" name="role" class="form-control" required onchange="handlePromoteRoleChange(this.value)">
                        <option value="staff">Parish Staff (General)</option>
                        <option value="parish_secretary">Parish Secretary</option>
                        <option value="commission_admin">Commission Coordinator / Admin</option>
                        <option value="commission_member">Commission Member</option>
                        <option value="admin">Parish Administrator (Full Admin)</option>
                    </select>
                </div>

                <!-- Organization Selection -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="field-label" for="promote-org">Organization Unit <span style="color:#dc2626;">*</span></label>
                    <select id="promote-org" name="organization" class="form-control" required onchange="handlePromoteOrgChange(this.value)">
                        <option value="parish_administration">Parish Administration</option>
                        <option value="commission">Commission</option>
                        <option value="ministry">Ministry</option>
                    </select>
                </div>

                <!-- Position / Title -->
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="field-label" for="promote-position">Position / Official Title</label>
                    <input type="text" id="promote-position" name="position" class="form-control" placeholder="e.g., Parish Secretary, Youth Coordinator, Media Staff">
                </div>

                <!-- Primary Commission -->
                <div class="form-group" id="promote-commission-group" style="margin-bottom:14px; display:none;">
                    <label class="field-label" for="promote-commission">Primary Commission</label>
                    <select id="promote-commission" name="commission_id" class="form-control">
                        <option value="">-- Select Commission --</option>
                        @foreach($commissions as $comm)
                            <option value="{{ $comm->id }}">{{ $comm->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Assigned Ministries (Optional) -->
                @if($ministries->isNotEmpty())
                <div class="form-group" style="margin-bottom:14px;">
                    <label class="field-label">Assign to Ministries <span style="font-size:10px;color:#94a3b8;font-weight:normal;">(Optional)</span></label>
                    <div style="max-height:110px;overflow-y:auto;border:1px solid #e2e8f0;border-radius:6px;padding:8px 10px;display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                        @foreach($ministries as $min)
                        <label style="display:flex;align-items:center;gap:6px;font-size:11px;cursor:pointer;color:#334155;">
                            <input type="checkbox" name="ministry_ids[]" value="{{ $min->id }}">
                            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="{{ $min->name }}">{{ $min->name }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Permissions Notice -->
                <div style="background:#eff6ff;border:1px solid #bfdbfe;border-left:4px solid #2563eb;padding:10px 12px;border-radius:6px;font-size:11px;color:#1e40af;line-height:1.4;">
                    <strong>🛡️ Permission Baseline:</strong> Standard default permissions for the selected role will be automatically provisioned. You can fine-tune individual module privileges anytime in <strong>Staff Management → Manage Permissions</strong>.
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn-modal-cancel" onclick="closePromoteModal()">Cancel</button>
                <button type="submit" class="btn-modal-save" id="btn-submit-promote" style="background:#0284c7;border-color:#0284c7;">
                    <span id="text-submit-promote">Confirm Promotion to Staff</span>
                    <svg id="spinner-promote" class="spinner-svg" viewBox="0 0 24 24" width="16" height="16" style="display:none;"><circle class="spinner-path" cx="12" cy="12" r="10" fill="none" stroke-width="3"></circle></svg>
                </button>
            </div>
        </form>
    </div>
    @endif
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
        .action-icons{display:flex;gap:8px;align-items:center;}
        .action-icons a{color:var(--muted);text-decoration:none;font-size:14px}
        .action-icons a:hover{color:var(--navy)}
        @media(max-width:620px){.toolbar input{min-width:100%}}
        .empty-cell{text-align:center;padding:28px;color:var(--muted)}

        .btn-promote {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 9px;
            font-size: 11px;
            font-weight: 600;
            color: #0369a1;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s ease;
        }
        .btn-promote:hover {
            background: #0284c7;
            color: #ffffff;
            border-color: #0284c7;
            box-shadow: 0 2px 4px rgba(2, 132, 199, 0.2);
        }

        /* Modal Styles */
        .modal-backdrop-common {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(2px);
            z-index: 1060;
        }
        .custom-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 90%;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            z-index: 1070;
            display: flex;
            flex-direction: column;
        }
        .custom-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        .modal-pretitle {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
        }
        .custom-modal-title {
            font-size: 16px;
            font-weight: 700;
            color: #0f172a;
            margin: 2px 0 0 0;
        }
        .custom-modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 14px 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .field-label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 5px;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12px;
            color: #1e293b;
            background-color: #fff;
            box-sizing: border-box;
        }
        .btn-modal-cancel {
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
        }
        .btn-modal-save {
            padding: 8px 16px;
            background: #0284c7;
            border: 1px solid #0284c7;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .drawer-close-btn {
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            border-radius: 4px;
        }
        .drawer-close-btn:hover {
            color: #0f172a;
            background: #f1f5f9;
        }
        .spinner-svg {
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            100% { transform: rotate(360deg); }
        }
        .spinner-path {
            stroke: #ffffff;
            stroke-linecap: round;
        }
    </style>
@endpush

@push('scripts')
    <script>
        window.openPromoteModal = function(userId, userName, userEmail) {
            document.getElementById('promote-user-id').value = userId;
            document.getElementById('promote-user-name').textContent = userName;
            document.getElementById('promote-user-email').textContent = userEmail;

            // Avatar initial
            const initials = userName.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase() || 'P';
            document.getElementById('promote-avatar-initials').textContent = initials;

            // Reset form defaults
            const roleSelect = document.getElementById('promote-role');
            roleSelect.value = 'staff';
            handlePromoteRoleChange('staff');

            const modal = document.getElementById('promote-modal');
            const backdrop = document.getElementById('promote-modal-backdrop');
            if (modal) {
                modal.style.display = 'flex';
                modal.setAttribute('aria-hidden', 'false');
            }
            if (backdrop) {
                backdrop.style.display = 'block';
                backdrop.setAttribute('aria-hidden', 'false');
            }
            document.body.style.overflow = 'hidden';
        };

        window.closePromoteModal = function() {
            const modal = document.getElementById('promote-modal');
            const backdrop = document.getElementById('promote-modal-backdrop');
            if (modal) {
                modal.style.display = 'none';
                modal.setAttribute('aria-hidden', 'true');
            }
            if (backdrop) {
                backdrop.style.display = 'none';
                backdrop.setAttribute('aria-hidden', 'true');
            }
            document.body.style.overflow = '';
        };

        window.handlePromoteRoleChange = function(roleVal) {
            const orgSelect = document.getElementById('promote-org');
            const posInput = document.getElementById('promote-position');
            const commGroup = document.getElementById('promote-commission-group');

            if (roleVal === 'admin') {
                orgSelect.value = 'parish_administration';
                posInput.value = 'Parish Administrator';
                commGroup.style.display = 'none';
            } else if (roleVal === 'parish_secretary') {
                orgSelect.value = 'parish_administration';
                posInput.value = 'Parish Secretary';
                commGroup.style.display = 'none';
            } else if (roleVal === 'commission_admin') {
                orgSelect.value = 'commission';
                posInput.value = 'Commission Coordinator';
                commGroup.style.display = 'block';
            } else if (roleVal === 'commission_member') {
                orgSelect.value = 'commission';
                posInput.value = 'Commission Member';
                commGroup.style.display = 'block';
            } else {
                orgSelect.value = 'parish_administration';
                posInput.value = 'Parish Staff';
                commGroup.style.display = 'none';
            }
        };

        window.handlePromoteOrgChange = function(orgVal) {
            const commGroup = document.getElementById('promote-commission-group');
            if (orgVal === 'commission') {
                commGroup.style.display = 'block';
            } else {
                commGroup.style.display = 'none';
            }
        };

        window.submitPromoteParishioner = async function(event) {
            event.preventDefault();
            const form = event.target;
            const userId = document.getElementById('promote-user-id').value;
            if (!userId) return;

            const btn = document.getElementById('btn-submit-promote');
            const btnText = document.getElementById('text-submit-promote');
            const spinner = document.getElementById('spinner-promote');

            btn.disabled = true;
            btnText.textContent = 'Promoting...';
            spinner.style.display = 'inline-block';

            try {
                const formData = new FormData(form);
                const payload = {
                    role: formData.get('role'),
                    organization: formData.get('organization'),
                    position: formData.get('position'),
                    commission_id: formData.get('commission_id') || null,
                    ministry_ids: formData.getAll('ministry_ids[]'),
                };

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                    || '{{ csrf_token() }}';

                const response = await fetch(`/admin/parishioners/${userId}/promote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to promote parishioner.');
                }

                closePromoteModal();

                // Animate and remove from table
                const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                if (row) {
                    row.style.transition = 'all 0.4s ease';
                    row.style.background = '#e0f2fe';
                    setTimeout(() => {
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(() => {
                            row.remove();
                            // Re-index remaining rows
                            const rows = document.querySelectorAll('tbody tr[data-user-id]');
                            rows.forEach((r, idx) => {
                                const firstCell = r.querySelector('td:first-child');
                                if (firstCell) firstCell.textContent = idx + 1;
                            });
                        }, 300);
                    }, 400);
                }

                alert(`Success! ${data.message}\n\nThey have been added to Staff Management and can now access their authorized staff modules.`);

            } catch (err) {
                console.error(err);
                alert(err.message || 'An error occurred while promoting parishioner.');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Confirm Promotion to Staff';
                spinner.style.display = 'none';
            }
        };

        // ESC key close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('promote-modal');
                if (modal && modal.style.display !== 'none') {
                    closePromoteModal();
                }
            }
        });
    </script>
@endpush