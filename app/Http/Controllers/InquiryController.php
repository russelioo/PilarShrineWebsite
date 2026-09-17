<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\InquiryAttachment;
use App\Models\InquiryMessage;
use App\Models\Ministry;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InquiryController extends Controller
{
    /**
     * Main page: Renders the Messenger-style UI while maintaining backwards compatibility
     * with server-rendered test assertions.
     */
    public function index(Request $request)
    {
        $me = $request->user();
        abort_unless(
            $me && ($me->role === 'super_admin' || $me->hasPermission('view_messages') || $me->hasPermission('messages')),
            403,
            'You do not have permission to view messages.'
        );
        $data = $request->validate([
            'with' => ['nullable', 'integer', 'exists:users,id'],
            'q' => ['nullable', 'string', 'max:100'],
            'conversation' => ['nullable', 'integer', 'exists:conversations,id'],
        ]);

        $peer = ! empty($data['with']) ? User::findOrFail($data['with']) : null;
        abort_if($peer && $peer->id === $me->id, 422);

        $activeConversation = null;
        $messages = null;

        if (! empty($data['conversation'])) {
            $activeConversation = Conversation::with(['participants', 'commission', 'ministry'])->findOrFail($data['conversation']);
            abort_unless($request->user()->can('view', $activeConversation), 403);
            $this->markConversationRead($activeConversation, $me->id);
            $messages = $activeConversation->messages()->with('attachments')->latest('id')->paginate(30)->withQueryString();
        } elseif ($peer) {
            $activeConversation = $this->getOrCreateDirectConversation($me->id, $peer->id);
            $this->markConversationRead($activeConversation, $me->id);
            $messages = InquiryMessage::with('attachments')->where(function ($query) use ($me, $peer) {
                $query->where(fn ($q) => $q->where('sender_id', $me->id)->where('recipient_id', $peer->id))
                    ->orWhere(fn ($q) => $q->where('sender_id', $peer->id)->where('recipient_id', $me->id));
            })->latest('id')->paginate(30)->withQueryString();
        }

        $contacts = User::where('id', '!=', $me->id)->where(function ($query) use ($me, $data) {
            if (! empty($data['q'])) {
                $query->where('name', 'like', '%'.$data['q'].'%');
            } else {
                $query->whereIn('id', InquiryMessage::where('sender_id', $me->id)->select('recipient_id'))
                    ->orWhereIn('id', InquiryMessage::where('recipient_id', $me->id)->select('sender_id'));
            }
        })->orderBy('name')->limit(50)->get();

        $unread = InquiryMessage::where('recipient_id', $me->id)->whereNull('read_at')
            ->selectRaw('sender_id, count(*) as total')
            ->groupBy('sender_id')
            ->pluck('total', 'sender_id');

        $usage = InquiryAttachment::where('user_id', $me->id)
            ->where('upload_day', now('Asia/Manila')->toDateString())
            ->selectRaw('kind, count(*) as total')
            ->groupBy('kind')
            ->pluck('total', 'kind');

        $role = $me->role;
        $isAdminOrStaff = in_array($role, [
            'admin', 'super_admin', 'parish_priest', 'parochial_vicar',
            'parish_secretary', 'commission_admin', 'commission_member', 'staff'
        ], true) || $me->isParishAdministration() || $me->isCommissionMember();
        $layout = $isAdminOrStaff ? 'layouts.admin' : 'layouts.parishioner';

        // Initial JSON state for Vue Messenger App
        $initialState = [
            'currentUser' => [
                'id' => $me->id,
                'name' => $me->display_name,
                'email' => $me->email,
                'role' => $me->role,
                'role_label' => $me->role_badge_label,
                'avatar' => $me->avatar,
                'commission_id' => $me->commission_id,
                'has_parish_wide_access' => $me->hasParishWideAccess(),
            ],
            'activeConversationId' => $activeConversation?->id,
            'activePeerId' => $peer?->id,
            'dailyUsage' => [
                'images' => (int) ($usage['image'] ?? 0),
                'files' => (int) ($usage['file'] ?? 0),
                'max_images' => 3,
                'max_files' => 3,
            ],
        ];

        return view('inquiries.index', compact(
            'peer', 'messages', 'contacts', 'unread', 'usage', 'layout', 'activeConversation', 'initialState'
        ));
    }

    /**
     * Backwards-compatible message store (supports traditional form posts & AJAX).
     */
    public function store(Request $request)
    {
        $sender = $request->user();
        abort_unless(
            $sender && ($sender->role === 'super_admin' || $sender->hasPermission('send_messages') || $sender->hasPermission('messages')),
            403,
            'You do not have permission to send messages.'
        );
        $data = $request->validate([
            'recipient_id' => ['nullable', 'integer', 'exists:users,id', 'not_in:'.$request->user()->id],
            'conversation_id' => ['nullable', 'integer', 'exists:conversations,id'],
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:6'],
            'attachments.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx,ppt,pptx,csv', 'extensions:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx,ppt,pptx,csv'],
        ]);

        if (empty($data['recipient_id']) && empty($data['conversation_id'])) {
            throw ValidationException::withMessages(['recipient_id' => 'Please select a recipient or conversation.']);
        }

        $files = $request->file('attachments', []);
        if (trim($data['body'] ?? '') === '' && ! $files) {
            throw ValidationException::withMessages(['body' => 'Write a message or attach a file.']);
        }

        $conversation = null;
        if (! empty($data['conversation_id'])) {
            $conversation = Conversation::findOrFail($data['conversation_id']);
            abort_unless($request->user()->can('sendMessage', $conversation), 403);
        } else {
            $conversation = $this->getOrCreateDirectConversation($request->user()->id, $data['recipient_id']);
        }

        $recipientId = $data['recipient_id'] ?? null;
        if (! $recipientId && $conversation->isDirect()) {
            $recipientId = $conversation->participants()->where('user_id', '!=', $request->user()->id)->value('user_id');
        }

        $paths = [];
        $message = null;

        try {
            DB::transaction(function () use ($request, $data, $files, $conversation, $recipientId, &$paths, &$message) {
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $day = now('Asia/Manila')->toDateString();
                $counts = InquiryAttachment::where('user_id', $request->user()->id)->where('upload_day', $day)
                    ->selectRaw('kind, count(*) as total')
                    ->groupBy('kind')
                    ->pluck('total', 'kind')
                    ->all();

                foreach ($files as $file) {
                    $kind = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';
                    $counts[$kind] = ($counts[$kind] ?? 0) + 1;
                    if ($counts[$kind] > 3) {
                        throw ValidationException::withMessages([
                            'attachments' => 'Daily limit reached: 3 images and 3 documents/files per user. Resets at midnight (Philippine time).',
                        ]);
                    }
                }

                $message = InquiryMessage::create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $request->user()->id,
                    'recipient_id' => $recipientId,
                    'body' => trim($data['body'] ?? '') ?: null,
                ]);

                foreach ($files as $file) {
                    $path = $file->store('inquiries', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to save attachment.');
                    }
                    $paths[] = $path;
                    $message->attachments()->create([
                        'user_id' => $request->user()->id,
                        'path' => $path,
                        'name' => mb_substr(basename($file->getClientOriginalName()), 0, 240),
                        'kind' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file',
                        'size' => $file->getSize(),
                        'upload_day' => $day,
                    ]);
                }

                $conversation->update(['last_message_at' => now()]);

                // Update sender's read pointer
                $conversation->participants()->updateExistingPivot($request->user()->id, ['last_read_at' => now()]);
            });

            // Audit log
            AuditLogger::log(
                action: 'message_sent',
                description: 'Sent message in conversation #'.$conversation->id,
                target: $message,
                commissionId: $conversation->commission_id,
                category: 'messaging'
            );
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        if ($request->wantsJson()) {
            $message->load(['attachments', 'sender']);

            return response()->json([
                'success' => true,
                'message' => $this->formatMessage($message, $request->user()->id),
            ], 201);
        }

        $redirectWith = $recipientId ?: $data['recipient_id'] ?? null;

        return redirect()->route('inquiries.index', ['with' => $redirectWith])->with('success', 'Message sent.');
    }

    /**
     * Download or stream private attachments.
     */
    public function download(Request $request, InquiryAttachment $attachment)
    {
        $message = InquiryMessage::findOrFail($attachment->message_id);
        $user = $request->user();

        $authorized = false;
        if (in_array($user->id, [$message->sender_id, $message->recipient_id], true)) {
            $authorized = true;
        } elseif ($message->conversation_id) {
            $conversation = Conversation::find($message->conversation_id);
            if ($conversation && $user->can('view', $conversation)) {
                $authorized = true;
            }
        }

        abort_unless($authorized, 403, 'Unauthorized access to attachment.');
        abort_unless(Storage::disk('local')->exists($attachment->path), 404, 'Attachment not found.');

        // Inline display for images in chat / lightbox
        if ($request->has('preview') && $attachment->kind === 'image') {
            return response()->file(Storage::disk('local')->path($attachment->path), [
                'Content-Type' => Storage::disk('local')->mimeType($attachment->path) ?: 'image/jpeg',
                'Cache-Control' => 'private, max-age=3600',
            ]);
        }

        return Storage::disk('local')->download($attachment->path, $attachment->name, [
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    // =========================================================================
    // REAL-TIME MESSAGING API ENDPOINTS (No page refresh / Instant sync)
    // =========================================================================

    /**
     * GET /api/messages/conversations
     * Returns conversations list for the current user.
     */
    public function apiConversations(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasPermission('view_messages') || $user->hasPermission('messages')),
            403,
            'You do not have permission to view messages.'
        );
        $filter = $request->query('filter', 'all'); // all, unread, archived
        $search = trim($request->query('q', ''));

        $query = Conversation::query()
            ->whereHas('participants', function ($q) use ($user, $filter) {
                $q->where('user_id', $user->id);
                if ($filter === 'archived') {
                    $q->where('is_archived', true);
                } else {
                    $q->where('is_archived', false);
                }
            })
            ->with(['participants', 'commission', 'ministry', 'latestMessage.sender'])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id');

        $conversations = $query->get()->map(function (Conversation $conv) use ($user, $search) {
            return $this->formatConversationSummary($conv, $user);
        });

        if ($filter === 'unread') {
            $conversations = $conversations->filter(fn ($c) => $c['unread_count'] > 0)->values();
        }

        if ($search !== '') {
            $needle = mb_strtolower($search);
            $conversations = $conversations->filter(function ($c) use ($needle) {
                return str_contains(mb_strtolower($c['title']), $needle)
                    || str_contains(mb_strtolower($c['subtitle'] ?? ''), $needle)
                    || str_contains(mb_strtolower($c['latest_message']['body'] ?? ''), $needle);
            })->values();
        }

        return response()->json([
            'conversations' => $conversations->values(),
            'total_unread' => $conversations->sum('unread_count'),
        ]);
    }

    /**
     * GET /api/messages/conversations/{conversation}
     * Returns active conversation details + recent messages.
     */
    public function apiConversationDetails(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($request->user()->can('view', $conversation), 403);

        $user = $request->user();
        $this->markConversationRead($conversation, $user->id);

        $conversation->load(['participants', 'commission', 'ministry']);

        $messages = $conversation->messages()
            ->with(['attachments', 'sender'])
            ->latest('id')
            ->limit(40)
            ->get()
            ->reverse()
            ->values()
            ->map(fn ($m) => $this->formatMessage($m, $user->id));

        return response()->json([
            'conversation' => $this->formatConversationSummary($conversation, $user),
            'participants' => $conversation->participants->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->display_name,
                'role' => $u->role,
                'role_label' => $u->role_badge_label,
                'avatar' => $u->avatar,
                'is_online' => $u->isOnline(),
                'last_seen_label' => $u->last_seen_label,
            ]),
            'messages' => $messages,
        ]);
    }

    /**
     * GET /api/messages/conversations/{conversation}/messages
     * Paginated older messages for infinite upward scroll.
     */
    public function apiMessages(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($request->user()->can('view', $conversation), 403);

        $beforeId = $request->query('before_id');
        $limit = min((int) $request->query('limit', 30), 50);

        $query = $conversation->messages()->with(['attachments', 'sender'])->latest('id');

        if ($beforeId) {
            $query->where('id', '<', $beforeId);
        }

        $messages = $query->limit($limit)->get()->reverse()->values()->map(
            fn ($m) => $this->formatMessage($m, $request->user()->id)
        );

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() >= $limit,
        ]);
    }

    /**
     * POST /api/messages/conversations/{conversation}/messages
     * Asynchronous message sending.
     */
    public function apiSendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        return $this->store($request->merge(['conversation_id' => $conversation->id]));
    }

    /**
     * POST /api/messages/conversations
     * Start a new conversation (Direct or Commission/Ministry).
     */
    public function apiStartConversation(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasPermission('send_messages') || $user->hasPermission('messages')),
            403,
            'You do not have permission to send messages.'
        );
        $validated = $request->validate([
            'recipient_id' => ['nullable', 'integer', 'exists:users,id', 'not_in:'.$user->id],
            'commission_id' => ['nullable', 'integer', 'exists:commissions,id'],
            'ministry_id' => ['nullable', 'integer', 'exists:ministries,id'],
            'body' => ['nullable', 'string', 'max:5000'],
        ]);

        $conversation = null;

        if (! empty($validated['recipient_id'])) {
            $conversation = $this->getOrCreateDirectConversation($user->id, $validated['recipient_id']);
        } elseif (! empty($validated['commission_id'])) {
            $commission = Commission::findOrFail($validated['commission_id']);
            $conversation = Conversation::firstOrCreate(
                ['type' => 'commission', 'commission_id' => $commission->id],
                ['title' => $commission->name, 'created_by' => $user->id, 'last_message_at' => now()]
            );

            // Add user as participant if not present
            if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
                $conversation->participants()->attach($user->id, ['role' => 'member', 'last_read_at' => now()]);
            }
        } elseif (! empty($validated['ministry_id'])) {
            $ministry = Ministry::findOrFail($validated['ministry_id']);
            $conversation = Conversation::firstOrCreate(
                ['type' => 'ministry', 'ministry_id' => $ministry->id],
                ['title' => $ministry->name, 'created_by' => $user->id, 'last_message_at' => now()]
            );

            if (! $conversation->participants()->where('user_id', $user->id)->exists()) {
                $conversation->participants()->attach($user->id, ['role' => 'member', 'last_read_at' => now()]);
            }
        } else {
            return response()->json(['message' => 'Invalid conversation target.'], 422);
        }

        // If an initial body was provided, send it
        if (! empty($validated['body'])) {
            $this->store($request->merge(['conversation_id' => $conversation->id]));
        }

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'conversation' => $this->formatConversationSummary($conversation, $user),
        ], 201);
    }

    /**
     * POST /api/messages/conversations/{conversation}/read
     * Mark conversation as read.
     */
    public function apiMarkAsRead(Request $request, Conversation $conversation): JsonResponse
    {
        abort_unless($request->user()->can('view', $conversation), 403);
        $this->markConversationRead($conversation, $request->user()->id);

        return response()->json(['success' => true]);
    }

    /**
     * POST /api/messages/conversations/{conversation}/archive
     * Toggle archive status.
     */
    public function apiToggleArchive(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasPermission('archive_messages') || $user->hasPermission('view_messages') || $user->hasPermission('messages')),
            403,
            'You do not have permission to archive messages.'
        );
        abort_unless($user->can('view', $conversation), 403);

        $participant = ConversationParticipant::where('conversation_id', $conversation->id)
            ->where('user_id', $user->id)
            ->first();

        if ($participant) {
            $participant->update(['is_archived' => ! $participant->is_archived]);
        }

        return response()->json(['success' => true, 'is_archived' => (bool) $participant?->is_archived]);
    }

    /**
     * GET /api/messages/sync
     * Lightweight background polling sync endpoint.
     */
    public function apiSync(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && ($user->hasPermission('view_messages') || $user->hasPermission('messages')),
            403,
            'You do not have permission to view messages.'
        );
        $activeId = $request->query('active_conversation_id');
        $lastMsgId = $request->query('last_message_id');

        $newMessages = [];
        $readUpdates = [];

        if ($activeId) {
            $conversation = Conversation::find($activeId);
            if ($conversation && $user->can('view', $conversation)) {
                $query = $conversation->messages()->with(['attachments', 'sender']);
                if ($lastMsgId) {
                    $query->where('id', '>', $lastMsgId);
                }
                $newMsgs = $query->oldest('id')->limit(50)->get();

                if ($newMsgs->isNotEmpty()) {
                    $this->markConversationRead($conversation, $user->id);
                    $newMessages = $newMsgs->map(fn ($m) => $this->formatMessage($m, $user->id));
                }

                // Check for read status updates on outgoing messages
                $readUpdates = InquiryMessage::where('conversation_id', $conversation->id)
                    ->where('sender_id', $user->id)
                    ->whereNotNull('read_at')
                    ->latest('id')
                    ->limit(20)
                    ->pluck('id');
            }
        }

        // Summary unread count
        $totalUnread = InquiryMessage::where('recipient_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'new_messages' => $newMessages,
            'read_updates' => $readUpdates,
            'read_status_updates' => $readUpdates,
            'total_unread' => $totalUnread,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * GET /api/messages/directory
     * Directory search for "+ New Message" modal.
     */
    public function apiDirectory(Request $request): JsonResponse
    {
        $user = $request->user();
        $q = trim($request->query('q', ''));
        $hasParishWide = $user->hasParishWideAccess();

        // Connected commission & ministry IDs for scoped user
        $userCommIds = $user->commissions()->pluck('commissions.id')->all();
        if ($user->commission_id && ! in_array($user->commission_id, $userCommIds, true)) {
            $userCommIds[] = $user->commission_id;
        }
        $userMinIds = $user->ministries()->pluck('ministries.id')->all();

        // Query all portal users reachable by the user
        $userSearchCallback = function ($query) use ($q) {
            if (! empty($q)) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%");
                });
            }
        };

        // All users in directory (sorted by name)
        $allUsersQuery = User::where('id', '!=', $user->id)->with('commission');
        $userSearchCallback($allUsersQuery);
        $allUsers = $allUsersQuery->orderBy('name')->limit(500)->get()->map(function ($u) {
            $cat = match ($u->role) {
                'super_admin', 'admin', 'parish_secretary' => 'Administrator',
                'parish_priest' => 'Parish Priest',
                'parochial_vicar' => 'Parochial Vicar',
                'staff', 'commission_admin', 'commission_member' => 'Staff',
                default => 'Parishioner',
            };
            return $this->formatDirectoryUser($u, $cat);
        });

        // Specific category slices
        $admins = $allUsers->filter(fn ($u) => in_array($u['role'], ['super_admin', 'admin', 'parish_secretary'], true))->values();
        $priests = $allUsers->filter(fn ($u) => $u['role'] === 'parish_priest')->values();
        $vicars = $allUsers->filter(fn ($u) => $u['role'] === 'parochial_vicar')->values();
        $staff = $allUsers->filter(fn ($u) => in_array($u['role'], ['staff', 'commission_admin', 'commission_member', 'parish_secretary'], true))->values();
        $parishioners = $allUsers->filter(fn ($u) => in_array($u['role'], ['parishioner', 'user'], true))->values();

        // Commissions (All active commissions for parish-wide oversight, or user's connected commissions)
        $commQuery = Commission::where('is_active', true);
        if (! $hasParishWide && ! empty($userCommIds)) {
            $commQuery->whereIn('id', $userCommIds);
        }
        if ($q) {
            $commQuery->where('name', 'like', "%{$q}%");
        }
        $commissions = $commQuery->orderBy('name')->get()->map(fn ($c) => [
            'id' => $c->id,
            'type' => 'commission',
            'name' => $c->name,
            'category' => 'Commission',
            'subtitle' => 'Commission · '.($c->activeMembersCount() ?: 1).' members',
            'icon' => '🏛️',
            'initials' => 'COM',
        ]);

        // Ministries (All active ministries for parish-wide oversight, or user's connected ministries)
        $minQuery = Ministry::where('is_accepting_members', true);
        if (! $hasParishWide && ! empty($userMinIds)) {
            $minQuery->whereIn('id', $userMinIds);
        }
        if ($q) {
            $minQuery->where('name', 'like', "%{$q}%");
        }
        $ministries = $minQuery->orderBy('name')->limit(50)->get()->map(fn ($m) => [
            'id' => $m->id,
            'type' => 'ministry',
            'name' => $m->name,
            'category' => 'Ministry',
            'subtitle' => 'Ministry · '.($m->category ?? 'Parish Group'),
            'icon' => $m->icon ?: '✝',
            'initials' => 'MIN',
        ]);

        return response()->json([
            'users' => $allUsers->values(),
            'commissions' => $commissions->values(),
            'ministries' => $ministries->values(),
            'categories' => [
                'all' => $allUsers->concat($commissions)->concat($ministries)->values(),
                'parishioner' => $parishioners,
                'commission' => $commissions->values(),
                'ministry' => $ministries->values(),
                'staff' => $staff,
                'parish_priest' => $priests,
                'parochial_vicar' => $vicars,
                'administrator' => $admins,
                // Backwards-compatible aliases
                'administration' => $admins,
                'clergy' => $priests->concat($vicars)->values(),
                'commissions' => $commissions->values(),
                'ministries' => $ministries->values(),
                'parishioners' => $parishioners,
            ],
        ]);
    }

    // =========================================================================
    // HELPER METHODS
    // =========================================================================

    private function getOrCreateDirectConversation(int $user1Id, int $user2Id): Conversation
    {
        $conversation = Conversation::where('type', 'direct')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user1Id))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user2Id))
            ->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'type' => 'direct',
                'title' => null,
                'created_by' => $user1Id,
                'last_message_at' => now(),
            ]);

            $conversation->participants()->attach([
                $user1Id => ['last_read_at' => now(), 'is_archived' => false, 'role' => 'member'],
                $user2Id => ['last_read_at' => null, 'is_archived' => false, 'role' => 'member'],
            ]);
        }

        return $conversation;
    }

    private function markConversationRead(Conversation $conversation, int $userId): void
    {
        InquiryMessage::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        // Also update direct messages legacy read status
        InquiryMessage::where('recipient_id', $userId)
            ->whereNull('read_at')
            ->whereIn('sender_id', $conversation->participants()->pluck('user_id'))
            ->update(['read_at' => now()]);

        $conversation->participants()->updateExistingPivot($userId, ['last_read_at' => now()]);
    }

    private function formatConversationSummary(Conversation $conv, User $currentUser): array
    {
        $otherUser = null;
        if ($conv->isDirect()) {
            if ($conv->relationLoaded('participants')) {
                $otherUser = $conv->participants->firstWhere('id', '!=', $currentUser->id);
            } else {
                $otherUser = $conv->participants()->where('user_id', '!=', $currentUser->id)->first();
            }

            // Fallback: check messages if participants pivot was incomplete
            if (! $otherUser) {
                $peerId = $conv->messages()->where('sender_id', '!=', $currentUser->id)->value('sender_id')
                    ?? $conv->messages()->where('recipient_id', '!=', $currentUser->id)->value('recipient_id');
                if ($peerId) {
                    $otherUser = User::find($peerId);
                    if ($otherUser) {
                        $conv->participants()->syncWithoutDetaching([$otherUser->id => ['role' => 'member']]);
                    }
                }
            }
        }

        $displayName = $conv->getDisplayNameFor($currentUser);
        if (($displayName === 'Direct Message' || empty($displayName)) && $otherUser) {
            $displayName = $otherUser->display_name;
        } elseif (($displayName === 'Direct Message' || empty($displayName)) && ! $otherUser) {
            $displayName = $currentUser->display_name.' (You)';
        }

        $subtitle = 'Direct Conversation';
        $avatar = $otherUser?->avatar_url ?? $otherUser?->avatar;
        $initials = $otherUser ? $otherUser->initials : ($conv->isDirect() ? $currentUser->initials : 'OL');

        if ($conv->isCommission() && $conv->commission) {
            $subtitle = 'Commission · '.$conv->commission->activeMembersCount().' members';
            $initials = 'COM';
            $displayName = $conv->commission->name;
        } elseif ($conv->isMinistry() && $conv->ministry) {
            $subtitle = 'Ministry · '.($conv->ministry->category ?? 'Parish');
            $initials = 'MIN';
            $displayName = $conv->ministry->name;
        } elseif ($otherUser) {
            $subtitle = $otherUser->role_badge_label;
        }

        $latest = $conv->latestMessage;
        $unreadCount = $conv->unreadCountFor($currentUser->id);

        $participantPivot = $conv->relationLoaded('participants')
            ? $conv->participants->firstWhere('id', $currentUser->id)?->pivot
            : $conv->participants()->where('user_id', $currentUser->id)->first()?->pivot;
        $isArchived = (bool) ($participantPivot?->is_archived ?? false);

        $participantsList = $conv->relationLoaded('participants')
            ? $conv->participants->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->display_name,
                'email' => $u->email,
                'role' => $u->role,
                'role_label' => $u->role_badge_label,
                'avatar' => $u->avatar_url ?? $u->avatar,
                'initials' => $u->initials,
                'is_online' => $u->isOnline(),
                'last_seen_label' => $u->last_seen_label,
            ])->values()->all()
            : [];

        return [
            'id' => $conv->id,
            'name' => $displayName,
            'title' => $displayName,
            'type' => $conv->type,
            'is_direct' => $conv->isDirect(),
            'is_commission' => $conv->isCommission(),
            'is_ministry' => $conv->isMinistry(),
            'commission_name' => $conv->commission?->name,
            'ministry_name' => $conv->ministry?->name,
            'subtitle' => $subtitle,
            'avatar' => $avatar,
            'initials' => $initials,
            'unread_count' => $unreadCount,
            'is_archived' => $isArchived,
            'peer_id' => $otherUser?->id,
            'peer' => $otherUser ? [
                'id' => $otherUser->id,
                'name' => $otherUser->display_name,
                'display_name' => $otherUser->display_name,
                'role' => $otherUser->role,
                'role_label' => $otherUser->role_badge_label,
                'avatar' => $otherUser->avatar_url ?? $otherUser->avatar,
                'initials' => $otherUser->initials,
                'is_online' => $otherUser->isOnline(),
                'last_seen_label' => $otherUser->last_seen_label,
            ] : null,
            'participants' => $participantsList,
            'commission_id' => $conv->commission_id,
            'ministry_id' => $conv->ministry_id,
            'last_activity_time' => $conv->last_message_at?->diffForHumans() ?? 'No messages yet',
            'last_message_at' => $conv->last_message_at?->toIso8601String(),
            'latest_message' => $latest ? [
                'id' => $latest->id,
                'body' => $latest->body ? mb_substr($latest->body, 0, 60) : ($latest->attachments->first()?->name ?? 'Attachment'),
                'sender_id' => $latest->sender_id,
                'is_mine' => $latest->sender_id === $currentUser->id,
                'time' => $latest->created_at?->timezone('Asia/Manila')->format('g:i A'),
                'created_at' => $latest->created_at?->toIso8601String(),
                'read' => (bool) $latest->read_at,
            ] : null,
        ];
    }

    private function formatMessage(InquiryMessage $message, int $currentUserId): array
    {
        $manilaTime = $message->created_at ? $message->created_at->timezone('Asia/Manila') : now('Asia/Manila');
        $isMine = ($message->sender_id === $currentUserId);

        $status = 'sent';
        if ($isMine) {
            if ($message->read_at) {
                $status = 'read';
            } else {
                $status = 'delivered';
            }
        }

        $senderAvatar = $message->sender?->avatar_url ?? $message->sender?->avatar;

        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'sender_name' => $message->sender?->display_name ?? 'User',
            'sender_role' => $message->sender?->role_badge_label ?? 'Member',
            'sender_avatar' => $senderAvatar,
            'sender' => [
                'id' => $message->sender_id,
                'name' => $message->sender?->display_name ?? 'User',
                'role_label' => $message->sender?->role_badge_label ?? 'Member',
                'avatar' => $senderAvatar,
                'initials' => $message->sender?->initials ?? 'U',
            ],
            'body' => $message->body,
            'created_at' => $manilaTime->toIso8601String(),
            'time_formatted' => $manilaTime->format('g:i A'),
            'date_group' => $this->formatDateGroup($manilaTime),
            'is_mine' => $isMine,
            'status' => $status,
            'read_at' => $message->read_at?->toIso8601String(),
            'attachments' => $message->attachments->map(fn ($att) => [
                'id' => $att->id,
                'name' => $att->name,
                'kind' => $att->kind,
                'size_formatted' => number_format($att->size / 1024, 1).' KB',
                'is_image' => ($att->kind === 'image'),
                'download_url' => route('inquiries.download', $att),
                'preview_url' => route('inquiries.download', ['attachment' => $att, 'preview' => 1]),
            ]),
        ];
    }

    private function formatDateGroup(\Carbon\CarbonInterface $date): string
    {
        $today = now('Asia/Manila')->startOfDay();
        $dateDay = $date->copy()->startOfDay();

        if ($dateDay->equalTo($today)) {
            return 'Today, '.$date->format('F j, Y');
        }

        if ($dateDay->equalTo($today->copy()->subDay())) {
            return 'Yesterday';
        }

        return $date->format('F j, Y');
    }

    private function formatDirectoryUser(User $u, string $category): array
    {
        $desc = $u->position ?: $u->role_badge_label;
        if ($u->commission_ministry_summary !== '—') {
            $desc .= ' · '.$u->commission_ministry_summary;
        } elseif ($u->organization_label && $u->organization_label !== 'Parishioner') {
            $desc .= ' · '.$u->organization_label;
        }

        return [
            'id' => $u->id,
            'type' => 'user',
            'name' => $u->display_name,
            'display_name' => $u->display_name,
            'email' => $u->email,
            'category' => $category,
            'subtitle' => $desc,
            'avatar' => $u->avatar_url ?? $u->avatar,
            'initials' => $u->initials,
            'organization' => $u->organization_label,
            'role' => $u->role,
            'role_label' => $u->role_badge_label,
            'role_badge_label' => $u->role_badge_label,
            'commission_name' => $u->commission?->name,
            'is_online' => $u->isOnline(),
            'last_seen_label' => $u->last_seen_label,
        ];
    }
}
