<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Ministry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicMinistryController extends Controller
{
    /**
     * Public JSON API for the Shrine Ministries directory (MinistriesPage.vue).
     * Strictly database-driven, read-only, sanitized. Zero mock or fallback data.
     */
    public function publicIndex(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $commissionFilter = $request->string('commission', 'all')->trim()->toString();

        // 1. Fetch active commissions with real count of active, public parish ministries
        $commissions = Commission::query()
            ->where('is_active', true)
            ->with([
                'coordinator:id,name,email,phone',
                'ministries' => fn ($q) => $q->where('status', 'active')->where('is_public', true)->orderBy('name'),
                'projects' => fn ($q) => $q->whereIn('status', ['ongoing', 'planning', 'completed'])->latest(),
            ])
            ->withCount([
                'ministries' => fn ($q) => $q->where('status', 'active')->where('is_public', true),
            ])
            ->orderBy('name')
            ->get();

        // 2. Fetch real active, public parish ministries
        $query = Ministry::query()
            ->where('status', 'active')
            ->where('is_public', true)
            ->with(['commission:id,name,slug,code,icon'])
            ->orderBy('name');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('about', 'like', "%{$search}%")
                    ->orWhere('coordinator_name', 'like', "%{$search}%")
                    ->orWhere('meeting_location', 'like', "%{$search}%")
                    ->orWhereHas('commission', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%");
                    });
            });
        }

        if (! empty($commissionFilter) && $commissionFilter !== 'all') {
            $query->whereHas('commission', function ($cq) use ($commissionFilter) {
                $cq->where('slug', $commissionFilter)
                    ->orWhere('id', $commissionFilter);
            });
        }

        $allMinistries = $query->get();

        $sanitizedMinistries = $allMinistries->map(function (Ministry $m) {
            return [
                'id' => $m->id,
                'name' => $m->name,
                'slug' => $m->slug,
                'category' => $m->category,
                'icon' => $m->icon ?: null,
                'description' => $m->description,
                'about' => $m->about ?: null,
                'activities' => is_array($m->activities) ? array_values(array_filter($m->activities)) : [],
                'meeting_schedule' => $m->meeting_schedule ?: null,
                'meeting_location' => $m->meeting_location ?: null,
                'coordinator_name' => $m->coordinator_name ?: null,
                'coordinator_email' => $m->coordinator_email ?: null,
                'coordinator_phone' => $m->coordinator_phone ?: null,
                'requirements' => is_array($m->requirements) ? array_values(array_filter($m->requirements)) : [],
                'is_accepting_members' => (bool) $m->is_accepting_members,
                'status' => $m->status,
                'is_public' => (bool) $m->is_public,
                'commission' => $m->commission ? [
                    'id' => $m->commission->id,
                    'name' => $m->commission->name,
                    'slug' => $m->commission->slug,
                    'code' => $m->commission->code,
                    'icon' => $m->commission->icon,
                ] : null,
            ];
        });

        $totalActiveCount = Ministry::where('status', 'active')->where('is_public', true)->count();

        return response()->json([
            'commissions' => $commissions->map(function ($c) {
                $coord = $c->coordinator ?? $c->coordinator_user;
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'code' => $c->code,
                    'icon' => $c->icon,
                    'description' => $c->description,
                    'ministries_count' => (int) $c->ministries_count,
                    'coordinator_name' => $c->coordinator_name ?: ($coord?->name ?: null),
                    'coordinator_email' => $c->coordinator_email ?: ($coord?->email ?: null),
                    'coordinator_phone' => $c->coordinator_phone ?: ($coord?->phone ?: null),
                    'posted_ministries' => $c->ministries->map(fn ($m) => [
                        'id' => $m->id,
                        'name' => $m->name,
                        'slug' => $m->slug,
                        'description' => $m->description,
                        'coordinator_name' => $m->coordinator_name,
                        'meeting_schedule' => $m->meeting_schedule,
                        'meeting_location' => $m->meeting_location,
                        'is_accepting_members' => (bool) $m->is_accepting_members,
                    ])->values()->all(),
                    'posted_projects' => $c->projects->map(fn ($p) => [
                        'id' => $p->id,
                        'title' => $p->title,
                        'description' => $p->description,
                        'status' => $p->status,
                        'status_label' => $p->status_label,
                    ])->values()->all(),
                ];
            }),
            'ministries' => $sanitizedMinistries,
            'total_count' => $totalActiveCount,
            'filtered_count' => $sanitizedMinistries->count(),
        ]);
    }
}
