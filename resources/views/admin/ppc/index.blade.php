@extends('layouts.admin')
@section('title', 'Parish Pastoral Council (PPC)')

@section('content')
<div class="page-header">
    <div>
        <div class="breadcrumbs">
            <span>Parish Administration</span>
            <span class="crumb-separator">/</span>
            <span class="crumb-current">Parish Pastoral Council</span>
        </div>
        <h2>Parish Pastoral Council (PPC)</h2>
        <p class="page-description">The supreme consultative and collaborative pastoral governing body of Our Lady of the Pillar Parish Shrine, uniting executive clergy, lay leadership, and all 8 pastoral commissions.</p>
    </div>
    @if(auth()->user() && auth()->user()->hasParishWideAccess())
    <div class="actions">
        <button type="button" class="btn btn-primary" onclick="openAddPpcModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>+ Appoint Member to PPC</span>
        </button>
    </div>
    @endif
</div>

@if(session('status'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if($errors->any())
    <div class="notice-error">
        <strong>Please correct the following errors:</strong>
        <ul style="margin: 6px 0 0 16px; padding: 0;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Real Dynamic KPI Counters -->
<div class="summary-grid">
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(6, 47, 120, 0.08); color: #062f78;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['total_active'] }}</strong>
            <span>Active Council Members</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(216, 170, 60, 0.12); color: #b48520;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['executive_officers'] }}</strong>
            <span>Executive Officers</span>
        </div>
    </article>
    <article class="stat-box">
        <div class="stat-icon-wrap" style="background: rgba(34, 197, 94, 0.1); color: #16a34a;">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        </div>
        <div>
            <strong>{{ $kpi['represented_commissions'] }} of {{ $kpi['commissions_count'] }}</strong>
            <span>Commissions Represented</span>
        </div>
    </article>
</div>

<!-- SECTION 1: EXECUTIVE LEADERSHIP -->
<div class="council-section-card">
    <div class="section-title-wrap">
        <div class="section-badge-icon" style="background:#062f78;color:#f7d27e;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        </div>
        <div>
            <h3 class="section-heading">PPC Executive Committee</h3>
            <p class="section-subtext">The presiding clergy and executive officers responsible for council administration, agendas, and resolutions.</p>
        </div>
    </div>

    <div class="council-members-grid">
        @forelse($executiveOfficers as $officer)
        <div class="ppc-member-card">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="member-avatar">
                    @if($officer->user->avatar_url)
                        <img src="{{ $officer->user->avatar_url }}" alt="{{ $officer->user->name }}">
                    @else
                        <span>{{ $officer->user->initials }}</span>
                    @endif
                </div>
                <div style="min-width:0;flex:1;">
                    <span class="role-title-badge {{ str_contains(strtolower($officer->role_title), 'priest') ? 'badge-clergy' : 'badge-exec' }}">
                        {{ $officer->role_title }}
                    </span>
                    <h4 class="member-name">{{ $officer->user->name }}</h4>
                    <span class="member-email">{{ $officer->user->email }}</span>
                </div>
            </div>
            @if($officer->notes)
                <p class="member-notes">"{{ $officer->notes }}"</p>
            @endif
            <div class="card-footer-meta">
                <span>Term: {{ $officer->term_start ? $officer->term_start->format('Y') : '2025' }} – {{ $officer->term_end ? $officer->term_end->format('Y') : '2027' }}</span>
                @if(auth()->user() && auth()->user()->hasParishWideAccess())
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn-icon" title="Edit Member" onclick="openEditPpcModal({{ json_encode($officer) }})">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.ppc.members.destroy', $officer) }}" onsubmit="return confirm('Remove {{ $officer->user->name }} from the PPC?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon" title="Remove" style="color:#b91c1c;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <p style="grid-column:1/-1;text-align:center;padding:24px;color:#94a3b8;">No executive officers appointed yet.</p>
        @endforelse
    </div>
</div>

<!-- SECTION 2: COMMISSION CHAIRPERSONS -->
<div class="council-section-card">
    <div class="section-title-wrap">
        <div class="section-badge-icon" style="background:#0284c7;color:#ffffff;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
        </div>
        <div>
            <h3 class="section-heading">Pastoral Commission Chairpersons (Ex-Officio Members)</h3>
            <p class="section-subtext">The 8 official pastoral commission heads representing their respective commissions on the Parish Pastoral Council.</p>
        </div>
    </div>

    <div class="council-members-grid">
        @forelse($commissionChairs as $chair)
        <div class="ppc-member-card">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="member-avatar">
                    @if($chair->user->avatar_url)
                        <img src="{{ $chair->user->avatar_url }}" alt="{{ $chair->user->name }}">
                    @else
                        <span>{{ $chair->user->initials }}</span>
                    @endif
                </div>
                <div style="min-width:0;flex:1;">
                    <span class="role-title-badge badge-commission">
                        {{ $chair->commission->name ?? 'Pastoral Commission' }}
                    </span>
                    <h4 class="member-name">{{ $chair->user->name }}</h4>
                    <span class="member-email">{{ $chair->user->email }}</span>
                </div>
            </div>
            <div style="margin:10px 0 0;padding:8px 10px;background:#f8fafc;border-radius:8px;font-size:11.5px;color:#475569;">
                <div><strong>Representing:</strong> {{ $chair->commission->name ?? 'Unassigned' }}</div>
                @if($chair->notes)
                    <div style="margin-top:2px;font-style:italic;">"{{ $chair->notes }}"</div>
                @endif
            </div>
            <div class="card-footer-meta">
                <a href="{{ route('admin.commissions.show', $chair->commission_id) }}" style="font-size:11px;color:#062f78;font-weight:600;text-decoration:none;">
                    View Commission Workspace →
                </a>
                @if(auth()->user() && auth()->user()->hasParishWideAccess())
                <div style="display:flex;gap:4px;">
                    <button type="button" class="btn-icon" title="Edit Member" onclick="openEditPpcModal({{ json_encode($chair) }})">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    </button>
                    <form method="POST" action="{{ route('admin.ppc.members.destroy', $chair) }}" onsubmit="return confirm('Remove this chairperson from PPC?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-icon" title="Remove" style="color:#b91c1c;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <p style="grid-column:1/-1;text-align:center;padding:24px;color:#94a3b8;">No commission chairpersons enrolled in the PPC.</p>
        @endforelse
    </div>
</div>

@if($otherMembers->count() > 0)
<!-- SECTION 3: OTHER REPRESENTATIVES -->
<div class="council-section-card">
    <div class="section-title-wrap">
        <div class="section-badge-icon" style="background:#64748b;color:#ffffff;">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
        </div>
        <div>
            <h3 class="section-heading">Representative &amp; Sectoral Members</h3>
            <p class="section-subtext">Sectoral leaders, religious community representatives, and youth deputies.</p>
        </div>
    </div>
    <div class="council-members-grid">
        @foreach($otherMembers as $other)
        <div class="ppc-member-card">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="member-avatar">
                    @if($other->user->avatar_url)
                        <img src="{{ $other->user->avatar_url }}" alt="{{ $other->user->name }}">
                    @else
                        <span>{{ $other->user->initials }}</span>
                    @endif
                </div>
                <div style="min-width:0;flex:1;">
                    <span class="role-title-badge" style="background:#f1f5f9;color:#475569;">{{ $other->role_title }}</span>
                    <h4 class="member-name">{{ $other->user->name }}</h4>
                    <span class="member-email">{{ $other->user->email }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- MODAL: APPOINT PPC MEMBER -->
<div class="modal-backdrop" id="addPpcModal" style="display:none;" onclick="if(event.target===this) closeAddPpcModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Appoint Member to Parish Pastoral Council</h3>
            <button type="button" class="modal-close-btn" onclick="closeAddPpcModal()">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.ppc.members.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Registered User <span class="req">*</span></label>
                    <select name="user_id" required class="form-select">
                        <option value="">-- Choose User --</option>
                        @foreach($allUsers as $u)
                            <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Council Role / Title <span class="req">*</span></label>
                    <input type="text" name="role_title" list="ppc_role_suggestions" required placeholder="e.g. Lay Co-Chair, Commission Chairperson" class="form-input">
                    <datalist id="ppc_role_suggestions">
                        <option value="Parish Priest">
                        <option value="Parochial Vicar">
                        <option value="Lay Co-Chair">
                        <option value="PPC Secretary">
                        <option value="PPC Treasurer">
                        <option value="PPC Auditor">
                        <option value="Commission Chairperson">
                        <option value="Representative">
                    </datalist>
                </div>
                <div class="form-group">
                    <label class="form-label">Representing Commission (If applicable)</label>
                    <select name="commission_id" class="form-select">
                        <option value="">-- None (Executive / General Officer) --</option>
                        @foreach($commissions as $comm)
                            <option value="{{ $comm->id }}">{{ $comm->name }} ({{ $comm->code }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Term Start</label>
                        <input type="date" name="term_start" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Term End</label>
                        <input type="date" name="term_end" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Pastoral Notes / Appointment Citation</label>
                    <textarea name="notes" rows="2" placeholder="Responsibilities, committee representation, or decree notes..." class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeAddPpcModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Appoint Member</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: EDIT PPC MEMBER -->
<div class="modal-backdrop" id="editPpcModal" style="display:none;" onclick="if(event.target===this) closeEditPpcModal()">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Update PPC Member Record</h3>
            <button type="button" class="modal-close-btn" onclick="closeEditPpcModal()">&times;</button>
        </div>
        <form method="POST" id="editPpcForm" action="">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Council Role / Title <span class="req">*</span></label>
                    <input type="text" name="role_title" id="edit_ppc_role_title" required class="form-input">
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Status <span class="req">*</span></label>
                        <select name="status" id="edit_ppc_status" required class="form-select">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Commission</label>
                        <select name="commission_id" id="edit_ppc_commission_id" class="form-select">
                            <option value="">-- None (Executive) --</option>
                            @foreach($commissions as $comm)
                                <option value="{{ $comm->id }}">{{ $comm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Term Start</label>
                        <input type="date" name="term_start" id="edit_ppc_term_start" class="form-input">
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label class="form-label">Term End</label>
                        <input type="date" name="term_end" id="edit_ppc_term_end" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Pastoral Notes</label>
                    <textarea name="notes" id="edit_ppc_notes" rows="2" class="form-textarea"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeEditPpcModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.breadcrumbs { display: flex; align-items: center; gap: 6px; font-size: 11.5px; color: #64748b; margin-bottom: 6px; }
.crumb-separator { opacity: 0.5; }
.crumb-current { color: #062f78; font-weight: 600; }

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    margin: 18px 0 22px;
}
.stat-box {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.stat-box strong { display: block; font-size: 22px; font-weight: 700; color: #0f172a; }
.stat-box span { font-size: 11px; color: #64748b; font-weight: 600; }

.council-section-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 22px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.02);
}
.section-title-wrap {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 18px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.section-badge-icon {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}
.section-heading { margin: 0; font-size: 16px; font-weight: 700; color: #0f172a; }
.section-subtext { margin: 3px 0 0; font-size: 12px; color: #64748b; }

.council-members-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
}
.ppc-member-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.ppc-member-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(6, 47, 120, 0.06);
}
.member-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: #e2e8f0;
    display: grid;
    place-items: center;
    overflow: hidden;
    flex-shrink: 0;
}
.member-avatar img { width: 100%; height: 100%; object-fit: cover; }
.member-avatar span { font-size: 13px; font-weight: 700; color: #475569; }

.role-title-badge {
    font-size: 9.5px;
    font-weight: 800;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 2.5px 7px;
    border-radius: 6px;
    display: inline-block;
    margin-bottom: 2px;
}
.badge-clergy { background: #fee2e2; color: #991b1b; }
.badge-exec { background: #dbeafe; color: #1e40af; }
.badge-commission { background: #fef3c7; color: #92400e; }

.member-name { font-size: 14px; font-weight: 700; color: #0f172a; margin: 2px 0 1px; }
.member-email { font-size: 11px; color: #64748b; display: block; }
.member-notes { font-size: 11.5px; color: #475569; font-style: italic; margin: 10px 0 0; line-height: 1.4; }

.card-footer-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 14px;
    padding-top: 10px;
    border-top: 1px solid #e2e8f0;
    font-size: 11px;
    color: #94a3b8;
}

.btn-icon {
    width: 28px;
    height: 28px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    display: grid;
    place-items: center;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
}
.btn-icon:hover { background: #f8fafc; color: #0f172a; border-color: #cbd5e1; }

/* Modals */
.modal-backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 16px;
}
.modal-box {
    background: #ffffff;
    border-radius: 14px;
    max-width: 520px;
    width: 100%;
    box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    overflow: hidden;
    animation: modalSlide 0.2s ease-out;
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}
.modal-header h3 { margin: 0; font-size: 15px; font-weight: 700; color: #0f172a; }
.modal-close-btn { background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1; }
.modal-body { padding: 18px 20px; }
.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 14px 20px;
    border-top: 1px solid #e2e8f0;
    background: #f8fafc;
}
.form-group { margin-bottom: 14px; }
.form-row { display: flex; gap: 12px; }
.form-label { display: block; font-size: 11.5px; font-weight: 600; color: #334155; margin-bottom: 5px; }
.form-label .req { color: #dc2626; }
.form-input, .form-select, .form-textarea {
    width: 100%;
    box-sizing: border-box;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 12.5px;
    color: #1e293b;
    outline: none;
    transition: border-color 0.15s ease;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
    border-color: #062f78;
    box-shadow: 0 0 0 3px rgba(6, 47, 120, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
function openAddPpcModal() {
    document.getElementById('addPpcModal').style.display = 'flex';
}
function closeAddPpcModal() {
    document.getElementById('addPpcModal').style.display = 'none';
}

function openEditPpcModal(member) {
    const form = document.getElementById('editPpcForm');
    form.action = `/admin/ppc/members/${member.id}`;

    document.getElementById('edit_ppc_role_title').value = member.role_title || '';
    document.getElementById('edit_ppc_status').value = member.status || 'active';
    document.getElementById('edit_ppc_commission_id').value = member.commission_id || '';
    document.getElementById('edit_ppc_term_start').value = member.term_start ? member.term_start.substring(0, 10) : '';
    document.getElementById('edit_ppc_term_end').value = member.term_end ? member.term_end.substring(0, 10) : '';
    document.getElementById('edit_ppc_notes').value = member.notes || '';

    document.getElementById('editPpcModal').style.display = 'flex';
}
function closeEditPpcModal() {
    document.getElementById('editPpcModal').style.display = 'none';
}
</script>
@endpush

