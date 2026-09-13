@extends($layout)
@section('title', 'Inquiries')
@section('content')
<style>
.inq{max-width:1200px;margin:auto}.inq h2{color:#062f78}.inq-grid{display:grid;grid-template-columns:290px minmax(0,1fr);gap:20px}.inq-panel{background:#fff;border:1px solid #dce5ee;border-radius:14px;padding:22px;min-width:0}.inq input:not([type=file]),.inq textarea{width:100%;padding:12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}.inq button,.inq-action{display:inline-block;background:#062f78;color:white;border:0;border-radius:8px;padding:11px 16px;cursor:pointer;text-decoration:none}.inq label{display:block;margin:14px 0 8px;font-weight:600}.inq-contact{display:block;padding:14px 0;border-bottom:1px solid #e2e8f0;text-decoration:none;color:#062f78}.inq small,.inq-note{color:#64748b}.inq-thread{display:flex;flex-direction:column;gap:14px;max-height:520px;overflow:auto;padding:12px 0}.inq-msg{padding:14px;border-radius:12px;background:#f1f5f9;max-width:85%;align-self:flex-start;overflow-wrap:anywhere}.inq-msg.mine{align-self:flex-end;background:#eaf2fb}.inq-msg p{white-space:pre-wrap;margin:8px 0}.inq-msg a{display:block;margin-top:8px}.inq-alert{padding:12px;background:#fff3df;border-radius:8px;margin:12px 0}.inq-compose{border-top:1px solid #e2e8f0;margin-top:20px}.inq-compose button{margin-top:15px}.inq-pagination{margin:12px 0;display:flex;gap:20px}@media(max-width:800px){.inq-grid{grid-template-columns:1fr}.inq-panel{padding:16px}}
</style>
<div class="inq">
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
            @forelse($contacts as $contact)
                <a class="inq-contact" href="{{ route('inquiries.index', ['with' => $contact->id]) }}" @if($peer?->id === $contact->id) aria-current="page" @endif>
                    <strong>{{ $contact->display_name }}</strong><br><small>{{ ucfirst($contact->role ?? 'parishioner') }}</small>
                    @if($unread[$contact->id] ?? 0)<span> · {{ $unread[$contact->id] }} unread</span>@endif
                </a>
            @empty
                <p class="inq-note">{{ request('q') ? 'No matching users found.' : 'No conversations yet. Search for a registered user to start messaging.' }}</p>
            @endforelse
        </aside>
        <section class="inq-panel">
        @if($peer)
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
<script>const thread = document.getElementById('inquiry-thread'); if (thread) thread.scrollTop = thread.scrollHeight;</script>
@endsection
