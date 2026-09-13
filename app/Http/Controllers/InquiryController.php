<?php

namespace App\Http\Controllers;

use App\Models\InquiryAttachment;
use App\Models\InquiryMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InquiryController extends Controller
{
    public function index(Request $request)
    {
        $me = $request->user()->id;
        $data = $request->validate(['with' => ['nullable', 'integer', 'exists:users,id'], 'q' => ['nullable', 'string', 'max:100']]);
        $peer = ! empty($data['with']) ? User::findOrFail($data['with']) : null;
        abort_if($peer && $peer->id === $me, 422);
        $messages = null;
        if ($peer) {
            InquiryMessage::where('sender_id', $peer->id)->where('recipient_id', $me)->whereNull('read_at')->update(['read_at' => now()]);
            $messages = InquiryMessage::with('attachments')->where(function ($query) use ($me, $peer) {
                $query->where(fn ($q) => $q->where('sender_id', $me)->where('recipient_id', $peer->id))
                    ->orWhere(fn ($q) => $q->where('sender_id', $peer->id)->where('recipient_id', $me));
            })->latest('id')->paginate(30)->withQueryString();
        }
        $contacts = User::where('id', '!=', $me)->where(function ($query) use ($me, $data) {
            if (! empty($data['q'])) {
                $query->where('name', 'like', '%'.$data['q'].'%');
            } else {
                $query->whereIn('id', InquiryMessage::where('sender_id', $me)->select('recipient_id'))
                    ->orWhereIn('id', InquiryMessage::where('recipient_id', $me)->select('sender_id'));
            }
        })->orderBy('name')->limit(50)->get();
        $unread = InquiryMessage::where('recipient_id', $me)->whereNull('read_at')->selectRaw('sender_id, count(*) as total')->groupBy('sender_id')->pluck('total', 'sender_id');
        $usage = InquiryAttachment::where('user_id', $me)->where('upload_day', now('Asia/Manila')->toDateString())->selectRaw('kind, count(*) as total')->groupBy('kind')->pluck('total', 'kind');
        $role = $request->user()->role;
        $layout = in_array($role, ['admin', 'staff']) ? 'layouts.'.$role : 'layouts.parishioner';

        return view('inquiries.index', compact('peer', 'messages', 'contacts', 'unread', 'usage', 'layout'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient_id' => ['required', 'integer', 'exists:users,id', 'not_in:'.$request->user()->id],
            'body' => ['nullable', 'string', 'max:5000'],
            'attachments' => ['nullable', 'array', 'max:6'],
            'attachments.*' => ['required', 'file', 'max:10240', 'mimes:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx,ppt,pptx,csv', 'extensions:jpg,jpeg,png,gif,webp,pdf,txt,doc,docx,xls,xlsx,ppt,pptx,csv'],
        ]);
        $files = $request->file('attachments', []);
        if (trim($data['body'] ?? '') === '' && ! $files) {
            throw ValidationException::withMessages(['body' => 'Write a message or attach a file.']);
        }
        $paths = [];
        try {
            DB::transaction(function () use ($request, $data, $files, &$paths) {
                // Serialize all uploads by this sender, including across conversations.
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $day = now('Asia/Manila')->toDateString();
                $counts = InquiryAttachment::where('user_id', $request->user()->id)->where('upload_day', $day)->selectRaw('kind, count(*) as total')->groupBy('kind')->pluck('total', 'kind')->all();
                foreach ($files as $file) {
                    $kind = str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file';
                    $counts[$kind] = ($counts[$kind] ?? 0) + 1;
                    if ($counts[$kind] > 3) {
                        throw ValidationException::withMessages(['attachments' => 'Daily limit reached: 3 images and 3 documents/files per user. Resets at midnight (Philippine time).']);
                    }
                }
                $message = InquiryMessage::create(['sender_id' => $request->user()->id, 'recipient_id' => $data['recipient_id'], 'body' => trim($data['body'] ?? '') ?: null]);
                foreach ($files as $file) {
                    $path = $file->store('inquiries', 'local');
                    if (! $path) {
                        throw new \RuntimeException('Unable to save attachment.');
                    }
                    $paths[] = $path;
                    $message->attachments()->create(['user_id' => $request->user()->id, 'path' => $path, 'name' => mb_substr(basename($file->getClientOriginalName()), 0, 240), 'kind' => str_starts_with($file->getMimeType(), 'image/') ? 'image' : 'file', 'size' => $file->getSize(), 'upload_day' => $day]);
                }
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($paths);
            throw $exception;
        }

        return redirect()->route('inquiries.index', ['with' => $data['recipient_id']])->with('success', 'Message sent.');
    }

    public function download(Request $request, InquiryAttachment $attachment)
    {
        $message = InquiryMessage::findOrFail($attachment->message_id);
        abort_unless(in_array($request->user()->id, [$message->sender_id, $message->recipient_id]), 403);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return Storage::disk('local')->download($attachment->path, $attachment->name, ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, no-store']);
    }
}
