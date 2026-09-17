<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    /**
     * Public JSON API for the public website NewsPage.vue component.
     */
    public function publicIndex(): JsonResponse
    {
        $all = Announcement::query()
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        // Format items with images as featured news
        $newsItems = $all->filter(fn ($item) => !empty($item->image_url))->values()->map(function ($a) {
            $dateFormatted = $a->published_at ? $a->published_at->format('F j, Y') : $a->created_at->format('F j, Y');
            return [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'date' => $dateFormatted,
                'place' => 'Diocesan Shrine & Parish',
                'image' => $a->primary_image_url,
                'images' => $a->image_urls,
                'description' => $a->content,
                'fullText' => $a->content,
                'is_pinned' => (bool) $a->is_pinned,
                'priority' => $a->priority,
            ];
        });

        // Format all published items as announcements/advisories
        $announcementItems = $all->map(function ($a) {
            $dateFormatted = $a->published_at ? $a->published_at->format('F j, Y') : $a->created_at->format('F j, Y');
            return [
                'id' => $a->id,
                'title' => $a->title,
                'category' => $a->category,
                'badge' => $a->category,
                'date' => $dateFormatted,
                'place' => 'Diocesan Shrine & Parish',
                'description' => $a->content,
                'fullText' => $a->content,
                'is_pinned' => (bool) $a->is_pinned,
                'priority' => $a->priority,
                'image' => $a->primary_image_url,
                'images' => $a->image_urls,
            ];
        });

        return response()->json([
            'success' => true,
            'announcements' => $announcementItems,
            'news' => $newsItems,
            'total' => $all->count(),
        ]);
    }

    /**
     * Display the Admin Announcements listing page.
     */
    public function index(Request $request): View
    {
        $this->authorizeAction($request->user(), 'view_announcements');

        $search = $request->string('search')->trim()->toString();
        $category = $request->string('category')->trim()->toString();
        $priority = $request->string('priority', 'all')->trim()->toString();

        $query = Announcement::query()
            ->with('creator')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if (!empty($category) && $category !== 'all') {
            $query->where('category', $category);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        $announcements = $query->paginate(10)->withQueryString();

        // Metrics
        $totalCount = Announcement::count();
        $pinnedCount = Announcement::where('is_pinned', true)->count();
        $highPriorityCount = Announcement::where('priority', 'high')->count();
        $withImagesCount = Announcement::whereNotNull('image_url')->where('image_url', '!=', '')->count();

        $categoriesList = [
            'Liturgical Notice',
            'Marian Devotion',
            'Parish Life',
            'Community',
            'Youth Ministry',
            'Liturgical Feast',
            'Advisory',
            'Schedule',
            'Faith Formation',
        ];

        return view('admin.announcements', compact(
            'announcements',
            'totalCount',
            'pinnedCount',
            'highPriorityCount',
            'withImagesCount',
            'categoriesList',
            'search',
            'category',
            'priority'
        ));
    }

    /**
     * Store a new Announcement.
     */
    /**
     * Store a new Announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'create_announcements');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'priority' => 'required|in:low,medium,high',
            'content' => 'required|string',
            'photos' => 'nullable|array|max:2',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'image_url' => 'nullable|string|max:500',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ], [
            'photos.max' => 'You can upload a maximum of 2 photos.',
            'photos.*.image' => 'Each uploaded file must be a valid image.',
            'photos.*.mimes' => 'Images must be in JPEG, PNG, JPG, WEBP, or GIF format.',
            'photos.*.max' => 'Each photo must not exceed 10MB in size.',
            'photos.*.uploaded' => 'The photo could not be uploaded. Please choose an image under 10MB.',
            'photos.uploaded' => 'The photos could not be uploaded. Please choose images under 10MB.',
        ], [
            'photos.0' => 'first photo',
            'photos.1' => 'second photo',
        ]);

        $storedUrls = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if (count($storedUrls) < 2) {
                    $path = $photo->store('announcements', 'public');
                    $storedUrls[] = Storage::url($path);
                }
            }
        }

        if (!empty($storedUrls)) {
            $validated['image_url'] = count($storedUrls) === 1 ? $storedUrls[0] : json_encode($storedUrls);
        }

        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['published_at'] = !empty($validated['published_at']) ? Carbon::parse($validated['published_at']) : now();
        $validated['expires_at'] = !empty($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null;
        $validated['created_by'] = $request->user()->id;

        Announcement::create($validated);

        return redirect()->route('admin.announcements')
            ->with('success', "Announcement '{$validated['title']}' published successfully.");
    }

    /**
     * Update an existing Announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'edit_announcements');

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'priority' => 'required|in:low,medium,high',
            'content' => 'required|string',
            'photos' => 'nullable|array|max:2',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:10240',
            'remove_photos' => 'nullable|boolean',
            'image_url' => 'nullable|string|max:500',
            'is_pinned' => 'nullable|boolean',
            'published_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:published_at',
        ], [
            'photos.max' => 'You can upload a maximum of 2 photos.',
            'photos.*.image' => 'Each uploaded file must be a valid image.',
            'photos.*.mimes' => 'Images must be in JPEG, PNG, JPG, WEBP, or GIF format.',
            'photos.*.max' => 'Each photo must not exceed 10MB in size.',
            'photos.*.uploaded' => 'The photo could not be uploaded. Please choose an image under 10MB.',
            'photos.uploaded' => 'The photos could not be uploaded. Please choose images under 10MB.',
        ], [
            'photos.0' => 'first photo',
            'photos.1' => 'second photo',
        ]);

        $storedUrls = [];
        if ($request->boolean('remove_photos')) {
            $validated['image_url'] = null;
        } elseif ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                if (count($storedUrls) < 2) {
                    $path = $photo->store('announcements', 'public');
                    $storedUrls[] = Storage::url($path);
                }
            }
            if (!empty($storedUrls)) {
                $validated['image_url'] = count($storedUrls) === 1 ? $storedUrls[0] : json_encode($storedUrls);
            }
        }

        $validated['is_pinned'] = $request->boolean('is_pinned');
        $validated['published_at'] = !empty($validated['published_at']) ? Carbon::parse($validated['published_at']) : $announcement->published_at;
        $validated['expires_at'] = !empty($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null;

        $announcement->update($validated);

        return redirect()->route('admin.announcements')
            ->with('success', "Announcement '{$announcement->title}' updated successfully.");
    }

    /**
     * Remove an Announcement.
     */
    public function destroy(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'delete_announcements');

        $title = $announcement->title;
        $announcement->delete();

        return redirect()->route('admin.announcements')
            ->with('success', "Announcement '{$title}' has been deleted.");
    }

    /**
     * Toggle the pinned status of an Announcement.
     */
    public function togglePin(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->authorizeAction($request->user(), 'publish_announcements');

        $announcement->update([
            'is_pinned' => !$announcement->is_pinned,
        ]);

        $status = $announcement->is_pinned ? 'pinned to the top' : 'unpinned';

        return redirect()->route('admin.announcements')
            ->with('success', "Announcement '{$announcement->title}' is now {$status}.");
    }

    /**
     * Enforce permission-based authorization for announcements.
     */
    private function authorizeAction($user, string $permission): void
    {
        abort_unless(
            $user && ($user->role === 'super_admin' || $user->hasPermission($permission)),
            403,
            'You do not have permission to perform this announcement action.'
        );
    }
}
