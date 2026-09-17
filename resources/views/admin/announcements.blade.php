@extends('layouts.admin')
@section('title', 'Manage Announcements & News')

@section('content')
<div class="page-header">
    <div>
        <h2>Parish Announcements &amp; News</h2>
        <p class="page-description">Publish, schedule, and pin liturgical notices, feast day advisories, and parish community news shown on the website.</p>
    </div>
    @if(auth()->user() && auth()->user()->hasPermission('create_announcements'))
    <div class="actions">
        <button type="button" class="btn btn-primary" onclick="openAddModal()">
            <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>+ New Announcement</span>
        </button>
    </div>
    @endif
</div>

@if(session('success'))
    <div class="notice-success">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="notice-error">
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <span>{{ session('error') }}</span>
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

<!-- Summary Counters -->
<div class="summary-grid">
    <article class="stat-box">
        <strong>{{ $totalCount }}</strong>
        <span>Total Published</span>
    </article>
    <article class="stat-box highlight-pinned">
        <strong>{{ $pinnedCount }}</strong>
        <span>Pinned Notices</span>
    </article>
    <article class="stat-box highlight-high">
        <strong>{{ $highPriorityCount }}</strong>
        <span>High Priority</span>
    </article>
    <article class="stat-box highlight-images">
        <strong>{{ $withImagesCount }}</strong>
        <span>Featured Stories (Images)</span>
    </article>
</div>

<!-- Filter & Search Toolbar -->
<div class="filter-card">
    <form method="GET" action="{{ route('admin.announcements') }}" class="filter-form">
        <div class="filter-input-wrap">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" value="{{ $search }}" placeholder="Search announcements by title or keywords..." class="search-input">
        </div>

        <div class="filter-select-wrap">
            <select name="category" onchange="this.form.submit()" class="filter-select">
                <option value="all">All Categories</option>
                @foreach($categoriesList as $cat)
                    <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
        </div>

        <div class="filter-select-wrap">
            <select name="priority" onchange="this.form.submit()" class="filter-select">
                <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>All Priorities</option>
                <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>High Priority</option>
                <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Low Priority</option>
            </select>
        </div>

        <button type="submit" class="btn btn-outline btn-filter">Filter</button>

        @if($search || ($category && $category !== 'all') || ($priority && $priority !== 'all'))
            <a href="{{ route('admin.announcements') }}" class="btn-clear-filter">Reset</a>
        @endif
    </form>
</div>

<!-- Table Card -->
<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 50px;">Pin</th>
                <th>Announcement / Story</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Published</th>
                <th>Author</th>
                <th style="text-align: right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($announcements as $a)
                <tr>
                    <td style="text-align: center;">
                        @if(auth()->user() && auth()->user()->hasPermission('publish_announcements'))
                        <form method="POST" action="{{ route('admin.announcements.toggle-pin', $a) }}" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn-pin {{ $a->is_pinned ? 'is-pinned' : '' }}" title="{{ $a->is_pinned ? 'Unpin from top' : 'Pin to top of website' }}">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="{{ $a->is_pinned ? '#d6aa3e' : 'none' }}" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="17" x2="12" y2="22"></line>
                                    <path d="M5 17h14v-1.76a2 2 0 0 0-1.11-1.79l-1.78-.9A2 2 0 0 1 15 10.76V6h1a1 1 0 0 0 0-2H8a1 1 0 0 0 0 2h1v4.76a2 2 0 0 1-1.11 1.79l-1.78.9A2 2 0 0 0 5 15.24Z"></path>
                                </svg>
                            </button>
                        </form>
                        @elseif($a->is_pinned)
                            <span title="Pinned notice">📌</span>
                        @endif
                    </td>
                    <td>
                        <div class="item-title-group">
                            @if($a->primary_image_url)
                                <div class="thumb-stack">
                                    <img src="{{ $a->primary_image_url }}" alt="" class="item-thumbnail" onerror="this.style.display='none'">
                                    @if(count($a->image_urls) > 1)
                                        <span class="photo-count-badge" title="2 photos uploaded">2 📷</span>
                                    @endif
                                </div>
                            @endif
                            <div>
                                <strong class="item-title">{{ $a->title }}</strong>
                                <p class="item-snippet">{{ \Illuminate\Support\Str::limit($a->content, 95) }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="category-badge">{{ $a->category }}</span>
                    </td>
                    <td>
                        <span class="priority-badge priority-{{ $a->priority }}">{{ ucfirst($a->priority) }}</span>
                    </td>
                    <td>
                        <span class="date-text">{{ ($a->published_at ?? $a->created_at)->format('M j, Y') }}</span>
                    </td>
                    <td>
                        <span class="author-text">{{ $a->creator->name ?? 'Admin' }}</span>
                    </td>
                    <td>
                        <div class="actions-cell">
                            @if(auth()->user() && auth()->user()->hasPermission('edit_announcements'))
                            <button type="button" class="btn-sm btn-edit" onclick="openEditModal({{ json_encode($a) }}, {{ json_encode($a->image_urls) }})">
                                Edit
                            </button>
                            @endif
                            @if(auth()->user() && auth()->user()->hasPermission('delete_announcements'))
                            <button type="button" class="btn-sm btn-delete" onclick="openDeleteModal({{ $a->id }}, '{{ addslashes($a->title) }}')">
                                Delete
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-cell">
                        <p>No announcements found matching your criteria.</p>
                        @if(auth()->user() && (auth()->user()->hasPermission('create_announcements') || auth()->user()->hasPermission('announcements')))
                        <button type="button" class="btn btn-primary" onclick="openAddModal()">+ Create First Announcement</button>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($announcements->hasPages())
    <div class="pagination-wrap">
        {{ $announcements->links() }}
    </div>
@endif

<!-- 1. ADD ANNOUNCEMENT MODAL -->
<div id="add-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-header">
            <h4>+ Create New Announcement / News Story</h4>
            <button type="button" class="modal-close" onclick="closeModal('add-modal')">&times;</button>
        </div>
        <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="modal-body modal-scroll">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="add-title">Announcement Title <span class="req">*</span></label>
                        <input type="text" id="add-title" name="title" required class="form-input" placeholder="e.g. Solemn Easter Vigil Schedule 2026">
                    </div>
                    <div class="form-group">
                        <label for="add-category">Category <span class="req">*</span></label>
                        <input list="category-suggestions" id="add-category" name="category" required class="form-input" placeholder="e.g. Liturgical Notice">
                        <datalist id="category-suggestions">
                            @foreach($categoriesList as $c)
                                <option value="{{ $c }}">
                            @endforeach
                        </datalist>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="add-priority">Priority Level <span class="req">*</span></label>
                        <select id="add-priority" name="priority" class="form-input" required>
                            <option value="high">High (Urgent / Highlighted)</option>
                            <option value="medium" selected>Medium (Standard Notice)</option>
                            <option value="low">Low (General Info)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="add-published">Publish date</label>
                        <input type="date" id="add-published" name="published_at" value="{{ now()->format('Y-m-d') }}" class="form-input">
                    </div>
                </div>

                <!-- Photo Upload Field (Max 10MB each, up to 2 photos) -->
                <div class="form-group">
                    <label>Upload Featured Photos (Optional &bull; Max 10MB each &bull; Up to 2 photos)</label>
                    <div class="photo-uploader-box" onclick="document.getElementById('add-photos').click()">
                        <input type="file" id="add-photos" name="photos[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;" onchange="previewPhotos(this, 'add-preview-list')">
                        <div class="uploader-prompt">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" class="upload-icon">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span class="upload-title">Click here to select photos (or drag and drop)</span>
                            <span class="upload-sub">Up to 2 photos &bull; Max 10MB per file &bull; Formats: JPG, PNG, WEBP, GIF</span>
                        </div>
                    </div>
                    <div id="add-preview-list" class="photo-preview-grid" style="display: none;"></div>
                    <small style="color: #64748b; font-size: 11px; margin-top: 4px;">Announcements with uploaded photos will be featured in the community news section on the website.</small>
                </div>

                <div class="form-group">
                    <label for="add-content">Content / Detailed Message <span class="req">*</span></label>
                    <textarea id="add-content" name="content" rows="5" required class="form-input" placeholder="Write the announcement or news article content here..."></textarea>
                </div>

                <div class="form-group-checkbox">
                    <label>
                        <input type="checkbox" name="is_pinned" value="1">
                        <span><strong>Pin this announcement to the top</strong> of the website</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('add-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Publish Announcement</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. EDIT ANNOUNCEMENT MODAL -->
<div id="edit-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog modal-dialog-lg">
        <div class="modal-header">
            <h4>Edit Announcement</h4>
            <button type="button" class="modal-close" onclick="closeModal('edit-modal')">&times;</button>
        </div>
        <form id="edit-form" method="POST" action="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="modal-body modal-scroll">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit-title">Announcement Title <span class="req">*</span></label>
                        <input type="text" id="edit-title" name="title" required class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="edit-category">Category <span class="req">*</span></label>
                        <input list="category-suggestions" id="edit-category" name="category" required class="form-input">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="edit-priority">Priority Level <span class="req">*</span></label>
                        <select id="edit-priority" name="priority" class="form-input" required>
                            <option value="high">High (Urgent / Highlighted)</option>
                            <option value="medium">Medium (Standard Notice)</option>
                            <option value="low">Low (General Info)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-published">Publish date</label>
                        <input type="date" id="edit-published" name="published_at" class="form-input">
                    </div>
                </div>

                <!-- Existing & New Photo Upload Field -->
                <div class="form-group">
                    <label>Announcement Photos (Optional &bull; Max 10MB each &bull; Up to 2 photos)</label>

                    <!-- Existing photos view -->
                    <div id="edit-existing-wrap" class="existing-photos-container" style="display: none;">
                        <span class="existing-photos-label">Current Photo(s):</span>
                        <div id="edit-existing-grid" class="photo-preview-grid"></div>
                        <label class="remove-photos-checkbox">
                            <input type="checkbox" id="edit-remove-photos" name="remove_photos" value="1" onchange="toggleRemoveNotice(this)">
                            <span>Remove current photo(s) upon saving</span>
                        </label>
                    </div>

                    <!-- Upload new photos box -->
                    <div class="photo-uploader-box" onclick="document.getElementById('edit-photos').click()">
                        <input type="file" id="edit-photos" name="photos[]" multiple accept="image/jpeg,image/png,image/jpg,image/webp,image/gif" style="display: none;" onchange="previewPhotos(this, 'edit-preview-list')">
                        <div class="uploader-prompt">
                            <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" class="upload-icon">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                <polyline points="17 8 12 3 7 8"></polyline>
                                <line x1="12" y1="3" x2="12" y2="15"></line>
                            </svg>
                            <span class="upload-title">Click to upload new photo(s) to replace</span>
                            <span class="upload-sub">Select up to 2 photos &bull; Max 10MB per file &bull; Formats: JPG, PNG, WEBP, GIF</span>
                        </div>
                    </div>
                    <div id="edit-preview-list" class="photo-preview-grid" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="edit-content">Content / Detailed Message <span class="req">*</span></label>
                    <textarea id="edit-content" name="content" rows="5" required class="form-input"></textarea>
                </div>

                <div class="form-group-checkbox">
                    <label>
                        <input type="checkbox" id="edit-pinned" name="is_pinned" value="1">
                        <span><strong>Pin this announcement to the top</strong> of the website</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('edit-modal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Announcement</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. DELETE CONFIRMATION MODAL -->
<div id="delete-modal" class="modal-backdrop" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-header">
            <h4>Delete Announcement</h4>
            <button type="button" class="modal-close" onclick="closeModal('delete-modal')">&times;</button>
        </div>
        <form id="delete-form" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="modal-body">
                <p id="delete-modal-msg" style="font-size: 13px; color: #334155; line-height: 1.5; margin: 0 0 8px;"></p>
                <p style="font-size: 11px; color: #dc2626; margin: 0;">
                    Warning: This action will unpublish and delete this announcement from the website.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('delete-modal')">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Delete</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Header & Alerts */
.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 20px; }
.page-description { margin: 4px 0 0; color: var(--muted); font-size: 12px; }
.notice-success { display: flex; align-items: center; gap: 8px; padding: 12px 16px; border: 1px solid #a7f3d0; border-radius: 8px; background: #ecfdf5; color: #065f46; font-size: 12px; margin-bottom: 18px; }
.notice-error { padding: 12px 16px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2; color: #991b1b; font-size: 12px; margin-bottom: 18px; }

/* Summary Stats */
.summary-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 20px; }
.stat-box { background: #ffffff; border: 1px solid var(--line); border-radius: 10px; padding: 16px 20px; display: flex; flex-direction: column; gap: 4px; border-left: 4px solid var(--navy); }
.stat-box strong { font-size: 24px; font-family: Georgia, serif; color: var(--navy); }
.stat-box span { font-size: 11px; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; }
.stat-box.highlight-pinned { border-left-color: var(--gold); }
.stat-box.highlight-high { border-left-color: #dc2626; }
.stat-box.highlight-images { border-left-color: #2563eb; }

/* Filter Card */
.filter-card { background: #ffffff; border: 1px solid var(--line); border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; }
.filter-form { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.filter-input-wrap { display: flex; align-items: center; gap: 8px; flex: 1; min-width: 200px; border: 1px solid #cbd5e1; border-radius: 7px; padding: 0 10px; background: #fff; }
.search-input { border: none; outline: none; padding: 9px 0; font-size: 12px; width: 100%; }
.filter-select-wrap { min-width: 140px; }
.filter-select { width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 12px; background: #fff; outline: none; }
.btn-filter { padding: 8px 16px; }
.btn-clear-filter { font-size: 11px; color: #64748b; text-decoration: none; padding: 8px 12px; }
.btn-clear-filter:hover { color: #0f172a; text-decoration: underline; }

/* Table */
.table-card { background: #ffffff; border: 1px solid var(--line); border-radius: 10px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 12px; }
.data-table th { background: #fafcff; padding: 12px 16px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--navy); border-bottom: 1px solid var(--line); }
.data-table td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
.data-table tr:hover td { background: #fafcff; }

/* Item Elements */
.item-title-group { display: flex; align-items: center; gap: 12px; }
.thumb-stack { position: relative; display: inline-flex; }
.item-thumbnail { width: 50px; height: 40px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; flex-shrink: 0; }
.photo-count-badge { position: absolute; bottom: -4px; right: -4px; background: var(--navy); color: #fff; font-size: 9px; font-weight: 700; padding: 1px 4px; border-radius: 8px; border: 1px solid #fff; }
.item-title { display: block; color: var(--navy); font-size: 13px; font-weight: 700; }
.item-snippet { margin: 3px 0 0; color: #64748b; font-size: 11px; line-height: 1.4; max-width: 380px; }
.category-badge { display: inline-block; padding: 3px 9px; border-radius: 12px; background: #eff6ff; color: #1d4ed8; font-size: 10px; font-weight: 700; }
.priority-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; text-transform: uppercase; }
.priority-high { background: #fee2e2; color: #991b1b; }
.priority-medium { background: #fef3c7; color: #92400e; }
.priority-low { background: #f1f5f9; color: #475569; }
.date-text, .author-text { color: #64748b; font-size: 11.5px; }

/* Pin Button */
.btn-pin { background: none; border: 1px solid #cbd5e1; border-radius: 6px; width: 30px; height: 30px; display: grid; place-items: center; cursor: pointer; color: #94a3b8; transition: all 0.15s; }
.btn-pin:hover { border-color: var(--gold); color: var(--gold); }
.btn-pin.is-pinned { border-color: var(--gold); color: var(--gold); background: #fffdf5; }

/* Actions */
.actions-cell { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
.btn-sm { padding: 5px 10px; border-radius: 5px; font-size: 11px; font-weight: 700; cursor: pointer; border: 1px solid var(--line); background: #fff; text-decoration: none; transition: all 0.15s ease; }
.btn-edit { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
.btn-edit:hover { background: #dbeafe; }
.btn-delete { color: #dc2626; border-color: #fecaca; }
.btn-delete:hover { background: #fef2f2; }
.btn-danger { background: #dc2626; color: #fff; border: 1px solid #b91c1c; padding: 9px 16px; border-radius: 6px; font-weight: 700; cursor: pointer; }

/* Empty State */
.empty-cell { text-align: center; padding: 48px 16px !important; color: #64748b; }
.empty-cell p { margin: 0 0 14px; font-size: 13px; }

/* Modals */
.modal-backdrop { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.65); display: flex; align-items: center; justify-content: center; z-index: 9999; padding: 20px; box-sizing: border-box; }
.modal-dialog { background: #ffffff; border-radius: 12px; max-width: 480px; width: 100%; box-shadow: 0 15px 35px rgba(0,0,0,0.25); overflow: hidden; display: flex; flex-direction: column; max-height: calc(100vh - 40px); }
.modal-dialog-lg { max-width: 620px; }
.modal-dialog form { display: flex; flex-direction: column; flex: 1; min-height: 0; overflow: hidden; }
.modal-header { display: flex; align-items: center; justify-content: space-between; padding: 16px 22px; border-bottom: 1px solid #f1f5f9; background: #fafcff; flex-shrink: 0; }
.modal-header h4 { margin: 0; font-size: 15px; color: var(--navy); font-weight: 700; font-family: Georgia, serif; }
.modal-close { background: none; border: none; font-size: 22px; cursor: pointer; color: #64748b; line-height: 1; }
.modal-close:hover { color: #0f172a; }
.modal-body { padding: 20px 22px; }
.modal-scroll { overflow-y: auto; flex: 1; min-height: 0; scrollbar-width: thin; scrollbar-color: #cbd5e1 #f1f5f9; }
.modal-scroll::-webkit-scrollbar { width: 6px; }
.modal-scroll::-webkit-scrollbar-track { background: #f1f5f9; }
.modal-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.modal-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.modal-footer { padding: 14px 22px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 8px; flex-shrink: 0; }

/* Forms */
.form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { margin-bottom: 14px; display: flex; flex-direction: column; gap: 5px; }
.form-group label { font-size: 11px; font-weight: 700; color: var(--navy); }
.req { color: #dc2626; }
.form-input { width: 100%; box-sizing: border-box; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 7px; font-size: 12px; outline: none; transition: border-color 0.15s, box-shadow 0.15s; font-family: inherit; }
.form-input:focus { border-color: var(--navy); box-shadow: 0 0 0 3px rgba(6,47,120,0.1); }
.form-group-checkbox { margin-top: 8px; padding: 10px 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 7px; }
.form-group-checkbox label { display: flex; align-items: center; gap: 8px; font-size: 12px; cursor: pointer; color: #1e293b; }

/* Photo Uploader Styles */
.photo-uploader-box { border: 2px dashed #cbd5e1; border-radius: 9px; padding: 18px 16px; text-align: center; background: #fafcff; cursor: pointer; transition: border-color 0.2s, background-color 0.2s; }
.photo-uploader-box:hover { border-color: var(--navy); background: #f1f6fc; }
.uploader-prompt { display: flex; flex-direction: column; align-items: center; gap: 6px; }
.upload-icon { color: #64748b; }
.photo-uploader-box:hover .upload-icon { color: var(--navy); }
.upload-title { font-size: 12px; font-weight: 700; color: var(--navy); }
.upload-sub { font-size: 10.5px; color: #64748b; }

.photo-preview-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 10px; }
.photo-preview-card { position: relative; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: #ffffff; display: flex; align-items: center; gap: 10px; padding: 8px; }
.photo-preview-card img { width: 52px; height: 52px; object-fit: cover; border-radius: 6px; flex-shrink: 0; }
.photo-preview-info { min-width: 0; flex: 1; }
.photo-preview-name { display: block; font-size: 11px; font-weight: 700; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.photo-preview-size { display: block; font-size: 10px; color: #64748b; margin-top: 2px; }

.existing-photos-container { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
.existing-photos-label { font-size: 11px; font-weight: 700; color: var(--navy); margin-bottom: 6px; display: block; }
.remove-photos-checkbox { display: flex; align-items: center; gap: 6px; margin-top: 8px; font-size: 11px; color: #dc2626; cursor: pointer; }

.pagination-wrap { display: flex; justify-content: center; margin-top: 20px; }

@media(max-width: 900px) {
    .summary-grid { grid-template-columns: 1fr 1fr; }
    .form-grid-2 { grid-template-columns: 1fr; }
    .photo-preview-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@push('scripts')
<script>
async function compressImageIfNeeded(file) {
    // If already small (under 1.5MB), or SVG/GIF, return as-is
    if (file.size <= 1.5 * 1024 * 1024 || file.type === 'image/gif') {
        return file;
    }

    return new Promise((resolve) => {
        const reader = new FileReader();
    return new window.Promise((resolve) => {
        const reader = new window.FileReader();
        reader.onload = (e) => {
            const img = new Image();
            const img = new window.Image();
            img.onload = () => {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxDim = 1920;

                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    if (blob && blob.size < file.size) {
                        const newName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                        const compressedFile = new File([blob], newName, {
                        const compressedFile = new window.File([blob], newName, {
                            type: 'image/jpeg',
                            lastModified: Date.now(),
                            lastModified: window.Date.now(),
                        });
                        resolve(compressedFile);
                    } else {
                        resolve(file);
                    }
                }, 'image/jpeg', 0.85);
            };
            img.onerror = () => resolve(file);
            img.src = e.target.result;
        };
        reader.onerror = () => resolve(file);
        reader.readAsDataURL(file);
    });
}

async function previewPhotos(input, containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '';

    let files = Array.from(input.files || []);
    if (files.length === 0) {
        container.style.display = 'none';
        return;
    }

    // 1. Check max 2 photos
    if (files.length > 2) {
        alert('You can select up to 2 photos only. Please choose 1 or 2 files.');
        input.value = '';
        container.style.display = 'none';
        return;
    }

    // 2. Check max 15MB limit
    const MAX_BYTES = 15 * 1024 * 1024;
    for (const file of files) {
        if (file.size > MAX_BYTES) {
            const mb = (file.size / (1024 * 1024)).toFixed(1);
            alert(`The photo "${file.name}" is ${mb}MB, which exceeds the limit. Please choose a smaller photo.`);
            input.value = '';
            container.style.display = 'none';
            return;
        }
    }

    // 3. Show loading indicator while preparing preview
    container.style.display = 'grid';
    container.innerHTML = '<div style="grid-column: 1/-1; padding: 10px; color: var(--navy); font-size: 11px; text-align: center;">Processing and optimizing photo(s)...</div>';

    // 4. Compress large images client-side for smooth uploads
    const optimizedFiles = [];
    for (const file of files) {
        const opt = await compressImageIfNeeded(file);
        optimizedFiles.push(opt);
    }

    // Update input.files with optimized files via DataTransfer if supported
    try {
        const dt = new DataTransfer();
        const dt = new window.DataTransfer();
        optimizedFiles.forEach(f => dt.items.add(f));
        input.files = dt.files;
        files = Array.from(input.files);
    } catch (e) {
        files = optimizedFiles;
    }

    container.innerHTML = '';
    files.forEach((file, index) => {
        const card = document.createElement('div');
        card.className = 'photo-preview-card';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);

        const info = document.createElement('div');
        info.className = 'photo-preview-info';

        const name = document.createElement('span');
        name.className = 'photo-preview-name';
        name.innerText = file.name;

        const size = document.createElement('span');
        size.className = 'photo-preview-size';
        const sizeKb = Math.round(file.size / 1024);
        const sizeText = sizeKb > 1024 ? (file.size / (1024 * 1024)).toFixed(2) + ' MB' : sizeKb + ' KB';
        size.innerText = `${sizeText} (Ready to upload)`;

        info.appendChild(name);
        info.appendChild(size);

        card.appendChild(img);
        card.appendChild(info);
        container.appendChild(card);
    });
}

function openAddModal() {
    document.getElementById('add-photos').value = '';
    const preview = document.getElementById('add-preview-list');
    if (preview) {
        preview.innerHTML = '';
        preview.style.display = 'none';
    }
    document.getElementById('add-modal').style.display = 'flex';
}

function openEditModal(announcement, imageUrls) {
    const form = document.getElementById('edit-form');
    form.action = `/admin/announcements/${announcement.id}`;
    document.getElementById('edit-title').value = announcement.title || '';
    document.getElementById('edit-category').value = announcement.category || '';
    document.getElementById('edit-priority').value = announcement.priority || 'medium';
    document.getElementById('edit-content').value = announcement.content || '';
    document.getElementById('edit-pinned').checked = Boolean(announcement.is_pinned);

    // Reset photo file input and new preview
    document.getElementById('edit-photos').value = '';
    const newPreview = document.getElementById('edit-preview-list');
    if (newPreview) {
        newPreview.innerHTML = '';
        newPreview.style.display = 'none';
    }

    // Existing photos display
    const existingWrap = document.getElementById('edit-existing-wrap');
    const existingGrid = document.getElementById('edit-existing-grid');
    const removeCheckbox = document.getElementById('edit-remove-photos');
    if (removeCheckbox) removeCheckbox.checked = false;

    let urls = imageUrls || [];
    if (!Array.isArray(urls) || urls.length === 0) {
        if (announcement.image_url) {
            try {
                const parsed = JSON.parse(announcement.image_url);
                urls = Array.isArray(parsed) ? parsed : [announcement.image_url];
            } catch (e) {
                urls = [announcement.image_url];
            }
        }
    }

    if (urls.length > 0) {
        existingGrid.innerHTML = '';
        urls.forEach((url, i) => {
            const card = document.createElement('div');
            card.className = 'photo-preview-card';

            const img = document.createElement('img');
            img.src = url;
            img.alt = 'Photo ' + (i + 1);

            const info = document.createElement('div');
            info.className = 'photo-preview-info';

            const name = document.createElement('span');
            name.className = 'photo-preview-name';
            name.innerText = 'Photo ' + (i + 1);

            const sub = document.createElement('span');
            sub.className = 'photo-preview-size';
            sub.innerText = 'Uploaded photo';

            info.appendChild(name);
            info.appendChild(sub);

            card.appendChild(img);
            card.appendChild(info);
            existingGrid.appendChild(card);
        });
        existingWrap.style.display = 'block';
    } else {
        existingWrap.style.display = 'none';
    }

    if (announcement.published_at) {
        const d = new Date(announcement.published_at);
        const d = new window.Date(announcement.published_at);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('edit-published').value = `${yyyy}-${mm}-${dd}`;
    } else {
        document.getElementById('edit-published').value = '';
    }

    document.getElementById('edit-modal').style.display = 'flex';
}

function toggleRemoveNotice(checkbox) {
    const grid = document.getElementById('edit-existing-grid');
    if (grid) {
        grid.style.opacity = checkbox.checked ? '0.4' : '1';
    }
}

function openDeleteModal(id, title) {
    document.getElementById('delete-modal-msg').innerText = `Are you sure you want to delete "${title}"?`;
    document.getElementById('delete-form').action = `/admin/announcements/${id}`;
    document.getElementById('delete-modal').style.display = 'flex';
}

function closeModal(modalId) {
    const el = document.getElementById(modalId);
    if (el) el.style.display = 'none';
}

window.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-backdrop')) {
        e.target.style.display = 'none';
    }
});
</script>
@endpush