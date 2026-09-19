<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LivestreamSetting;
use App\Models\Notification;
use App\Models\SiteSetting;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SettingsController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeSettings($request);
        $user = $request->user();
        $canEdit = $user->hasPermission('edit_settings');
        $site = SiteSetting::current();
        $livestream = LivestreamSetting::query()->orderBy('id')->first() ?? new LivestreamSetting([
            'is_live' => false, 'title' => 'Pilar Shrine is live', 'url' => $site->facebook_url,
        ]);
        $notifications = $this->notificationQuery($request)->latest('id')->paginate(10)->withQueryString();
        $notificationCounts = Notification::query()->where('user_id', $user->id)
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.notifications', compact('user', 'canEdit', 'site', 'livestream', 'notifications', 'notificationCounts'));
    }

    public function updateAccount(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request, true);
        $user = $request->user();
        $emailChanged = $request->input('email') !== $user->email;
        $data = $request->validateWithBag('account', [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id),
                ...($user->google_id ? [Rule::in([$user->email])] : []),
            ],
            'current_password' => $emailChanged ? ['required', 'current_password:web'] : ['nullable'],
        ], [
            'email.in' => 'The email address linked to Google cannot be changed here.',
        ]);

        DB::transaction(function () use ($user, $data, $emailChanged) {
            $before = $user->only(['name', 'email', 'phone']);
            $user->fill(collect($data)->only(['name', 'email', 'phone'])->all());
            if ($emailChanged) {
                $user->email_verified_at = null;
            }
            $user->save();
            AuditLogger::log('account_updated', 'Updated own account details.', $user,
                oldValues: $before, newValues: $user->only(['name', 'email', 'phone']), category: 'settings');
        });

        return redirect()->to(route('admin.settings').'#account')
            ->with('success', 'Your account details have been saved.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request, true);
        abort_if($request->user()->google_id, 403, 'Manage your Google password through your Google Account.');
        $data = $request->validateWithBag('password', [
            'current_password' => ['required', 'current_password:web'],
            'password' => ['required', 'string', 'min:8', 'max:255', 'confirmed', 'different:current_password'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $user = $request->user();
            $user->password_hash = $data['password'];
            $user->remember_token = Str::random(60);
            $user->save();
            AuditLogger::log('password_changed', 'Changed own account password.', $user, category: 'settings');
        });
        $request->session()->regenerate();

        return redirect()->to(route('admin.settings').'#security')
            ->with('success', 'Your password has been updated.');
    }

    public function updateWebsite(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request, true);
        $data = $request->validateWithBag('website', [
            'address' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[+0-9() .-]+$/'],
            'email' => ['required', 'email', 'max:255'],
            'office_hours' => ['required', 'string', 'max:2000'],
            'facebook_url' => ['required', 'url:https', 'max:255'],
            'youtube_url' => ['required', 'url:https', 'max:255'],
            'tiktok_url' => ['required', 'url:https', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $site = SiteSetting::current();
            $before = $site->only(array_keys(SiteSetting::defaults()));
            $site = SiteSetting::query()->updateOrCreate(['id' => 1], [...$data, 'updated_by' => $request->user()->id]);
            AuditLogger::log('website_settings_updated', 'Updated public website contact details and social links.', $site,
                oldValues: $before, newValues: $data, category: 'settings');
        });

        return redirect()->to(route('admin.settings').'#website')
            ->with('success', 'Website settings saved. Your public contact information is now updated.');
    }

    public function updateLivestream(Request $request): RedirectResponse
    {
        $this->authorizeSettings($request, true);
        $data = $request->validateWithBag('livestream', [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url:https', 'max:255'],
        ]);

        DB::transaction(function () use ($request, $data) {
            $livestream = LivestreamSetting::query()->orderBy('id')->first() ?? new LivestreamSetting;
            $before = $livestream->only(['title', 'url']);
            $livestream->fill([...$data, 'is_live' => false, 'updated_by' => $request->user()->id])->save();
            AuditLogger::log('livestream_settings_updated', 'Updated the website livestream banner.', $livestream,
                oldValues: $before, newValues: $livestream->only(['title', 'url']), category: 'settings');
        });

        return redirect()->to(route('admin.settings').'#livestream')
            ->with('success', 'Broadcast details saved. The Live button follows the Mass schedule automatically.');
    }

    public function exportNotifications(Request $request): StreamedResponse
    {
        $this->authorizeSettings($request);
        $query = $this->notificationQuery($request);

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Subject', 'Channel', 'Status', 'Message', 'Recorded at', 'Sent at'], escape: '');
            foreach ($query->lazyById(200) as $notification) {
                $cells = [$notification->subject, $notification->type, $notification->status, $notification->message,
                    $notification->created_at?->toIso8601String(), $notification->sent_at?->toIso8601String()];
                $cells = array_map(fn ($value) => preg_match('/^[\s]*[=+@-]/u', (string) $value) ? "'".$value : $value, $cells);
                fputcsv($output, $cells, escape: '');
            }
            fclose($output);
        }, 'my-notifications-'.now()->format('Y-m-d').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function notificationQuery(Request $request): Builder
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['all', 'pending', 'sent', 'failed'])],
        ]);
        $query = Notification::query()->where('user_id', $request->user()->id);
        if (! empty($filters['search'])) {
            $query->where(function (Builder $query) use ($filters) {
                $query->where('subject', 'like', '%'.$filters['search'].'%')
                    ->orWhere('message', 'like', '%'.$filters['search'].'%');
            });
        }
        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    private function authorizeSettings(Request $request, bool $editing = false): void
    {
        $user = $request->user();
        $allowed = $editing
            ? $user?->hasPermission('edit_settings')
            : ($user?->hasPermission('view_settings') || $user?->hasPermission('edit_settings'));
        abort_unless($allowed, 403, 'You do not have permission to access these settings.');
    }
}
