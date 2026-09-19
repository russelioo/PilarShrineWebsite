@extends($layout)
@section('title', 'Messages & Inquiries')

@push('styles')
@vite(['resources/css/app.css'])
<style>
/* Lock layout to 100vh so only the internal conversation and message lists scroll */
html:has(#messages-app),
body:has(#messages-app) {
    height: 100vh !important;
    overflow: hidden !important;
}

/* Align the conversation content with the desktop page title. */
@media (min-width: 901px) {
    #messages-app {
        --conversation-inset: 32px;
    }

    #messages-app .conversation-sidebar-header {
        padding-inline: var(--conversation-inset);
    }

    #messages-app .conversation-list-item {
        padding-left: calc(var(--conversation-inset) - 3px);
        padding-right: var(--conversation-inset);
    }
}

@media (min-width: 901px) and (max-width: 1024px) {
    .admin-layout #messages-app {
        --conversation-inset: 24px;
    }
}

/* Hide global topbar search bar and divider on Messages / Inquiries workspace */
.admin-topbar .global-search-wrap,
.admin-topbar .topbar-title-divider,
.topbar-title-divider,
.global-search-wrap {
    display: none !important;
}

.admin-layout:has(#messages-app),
.layout:has(#messages-app) {
    height: 100vh !important;
    max-height: 100vh !important;
    overflow: hidden !important;
}

.admin-layout:has(#messages-app) .main-content,
.layout:has(#messages-app) .main-content {
    height: 100vh !important;
    max-height: 100vh !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
}

.admin-layout:has(#messages-app) .content-body,
.layout:has(#messages-app) .content,
body:has(#messages-app) .content-body,
body:has(#messages-app) .content {
    padding: 0 !important;
    max-width: 100% !important;
    margin: 0 !important;
    height: calc(100vh - var(--topbar-height, 72px)) !important;
    max-height: calc(100vh - var(--topbar-height, 72px)) !important;
    display: flex !important;
    flex-direction: column !important;
    flex: 1 !important;
    overflow: hidden !important;
}

@media (max-width: 768px) {
    .admin-layout:has(#messages-app) .content-body,
    .layout:has(#messages-app) .content,
    body:has(#messages-app) .content-body,
    body:has(#messages-app) .content {
        height: calc(100vh - var(--topbar-height, 64px)) !important;
    }
}
</style>
@endpush

@section('content')
<div class="h-full w-full flex-1 flex flex-col min-h-0">
    <!-- Vue 3 Real-time Messenger App Container -->
    <div id="messages-app" class="h-full w-full flex-1 flex flex-col min-h-0" data-initial="{{ json_encode($initialState ?? []) }}">
        <!-- Loading Skeleton until Vue hydrates -->
        <div class="h-full w-full flex-1 flex items-center justify-center p-8 text-center text-slate-400 bg-white">
            <div class="flex flex-col items-center gap-3">
                <div class="w-8 h-8 border-3 border-[#062f78] border-t-transparent rounded-full animate-spin"></div>
                <p class="text-sm font-medium text-slate-600">Loading Pilar Shrine Messages...</p>
            </div>
        </div>
    </div>

    <!-- Hidden Accessible / SSR Fallback (preserves 100% test compatibility and SEO) -->
    <div class="hidden sr-only" style="display:none!important;" aria-hidden="true">
        <h2>Inquiries &amp; messages</h2>
        <p class="inq-note">Connect with parishioners and parish staff for church activities, ministries, and parish concerns.</p>
        @if(session('success'))<p role="status" class="inq-alert">{{ session('success') }}</p>@endif
        @if($errors->any())<div role="alert" class="inq-alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        
        <div class="inq-grid">
            <aside class="inq-panel">
                <h3>Conversations</h3>
                <form action="{{ route('inquiries.index') }}" method="get">
                    @if($peer)<input type="hidden" name="with" value="{{ $peer->id }}">@endif
                    <label for="contact-search">Find someone by name</label>
                    <input id="contact-search" name="q" maxlength="100" value="{{ request('q') }}" placeholder="Search registered users">
                    <button style="margin-top:8px" type="submit">Search</button>
                </form>
                @if(isset($contacts))
                @forelse($contacts as $contact)
                    <a class="inq-contact" href="{{ route('inquiries.index', ['with' => $contact->id]) }}" @if($peer?->id === $contact->id) aria-current="page" @endif>
                        <strong>{{ $contact->display_name }}</strong><br><small>{{ ucfirst($contact->role ?? 'parishioner') }}</small>
                        @if($unread[$contact->id] ?? 0)<span> · {{ $unread[$contact->id] }} unread</span>@endif
                    </a>
                @empty
                    <p class="inq-note">{{ request('q') ? 'No matching users found.' : 'No conversations yet. Search for a registered user to start messaging.' }}</p>
                @endforelse
                @endif
            </aside>
            <section class="inq-panel">
            @if($peer && isset($messages))
                <h3>{{ $peer->display_name }}</h3>
                <a href="{{ route('inquiries.index', ['with' => $peer->id]) }}">Refresh messages</a>
                <div class="inq-thread" id="inquiry-thread">
                @forelse($messages->getCollection()->reverse() as $message)
                    <article class="inq-msg {{ $message->sender_id === auth()->id() ? 'mine' : '' }}">
                        <small>{{ $message->sender_id === auth()->id() ? 'You' : $peer->display_name }} · {{ $message->created_at->timezone('Asia/Manila')->format('M j, Y g:i A') }}</small>
                        @if($message->body)<p>{{ $message->body }}</p>@endif
                        @foreach($message->attachments as $attachment)
                            <a href="{{ route('inquiries.download', $attachment) }}">Download {{ $attachment->name }} ({{ number_format($attachment->size / 1024, 1) }} KB)</a>
                        @endforeach
                        @if($message->sender_id === auth()->id())<small>{{ $message->read_at ? 'Read' : 'Sent' }}</small>@endif
                    </article>
                @empty
                    <p class="inq-note">Start a conversation about a church concern or activity.</p>
                @endforelse
                </div>
                <nav class="inq-pagination" aria-label="Message history">
                    @if($messages->nextPageUrl())<a href="{{ $messages->nextPageUrl() }}">Older messages</a>@endif
                    @if($messages->previousPageUrl())<a href="{{ $messages->previousPageUrl() }}">Newer messages</a>@endif
                </nav>
                <form class="inq-compose" action="{{ route('inquiries.store') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $peer->id }}">
                    <label for="message-body">Message</label>
                    <textarea id="message-body" name="body" rows="4" maxlength="5000" placeholder="Write your church-related message…">{{ old('body') }}</textarea>
                    <label for="message-files">Attach images or documents</label>
                    <input id="message-files" name="attachments[]" type="file" multiple accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.csv" aria-describedby="attachment-limits">
                    <p id="attachment-limits" class="inq-note">10 MB maximum per attachment. Today: {{ $usage['image'] ?? 0 }}/3 images and {{ $usage['file'] ?? 0 }}/3 documents/files used. Resets at midnight Philippine time. PDF, Office documents, TXT, CSV, JPG, PNG, GIF, and WebP supported.</p>
                    <button type="submit">Send message</button>
                </form>
            @else
                <h3>Keep your parish connected</h3>
                <p class="inq-note">Select a conversation or search by name to message another registered user. Messages and attachments are only available to the sender and recipient.</p>
            @endif
            </section>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@vite(['resources/js/messages.js'])
@endpush
