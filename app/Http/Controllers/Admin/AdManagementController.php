<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class AdManagementController extends Controller
{
    /**
     * Display ads list
     */
    public function index(Request $request)
    {
        $query = Ad::query()->withCount(['impressions', 'clicks']);

        // Search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Filter by placement
        if ($request->filled('placement')) {
            $query->where('placement', $request->input('placement'));
        }

        $ads = $query->orderByDesc('priority')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => Ad::count(),
            'active' => Ad::where('status', 'active')->count(),
            'total_impressions' => Ad::sum('impressions'),
            'total_clicks' => Ad::sum('clicks'),
            'avg_ctr' => Ad::where('impressions', '>', 0)->avg('ctr'),
        ];

        return Inertia::render('Admin/Ads/Index', [
            'ads' => $ads,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'placement']),
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        return Inertia::render('Admin/Ads/Create');
    }

    /**
     * Store new ad
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'image_url' => 'nullable|image|max:2048',
            'cta_url' => 'nullable|url',
            'cta_text' => 'nullable|string|max:50',
            'placement' => 'required|in:sidebar,inline,empty_state,banner,sticky_bottom,modal',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'priority' => 'required|integer|min:0|max:100',
            'status' => 'required|in:draft,active,paused,expired',
            'meta' => 'nullable|array',
        ]);

        // Set default CTA text if not provided
        $validated['cta_text'] = $validated['cta_text'] ?? 'Learn More';

        // Handle image upload
        if ($request->hasFile('image_url')) {
            $validated['image_url'] = $request->file('image_url')->store('ads', 'public');
        } else {
            unset($validated['image_url']);
        }

        Ad::create($validated);

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad created successfully');
    }

    /**
     * Show edit form
     */
    public function edit(Ad $ad)
    {
        return Inertia::render('Admin/Ads/Edit', [
            'ad' => $ad,
        ]);
    }

    /**
     * Update ad
     */
    public function update(Request $request, Ad $ad)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'image_url' => 'nullable|image|max:2048',
            'cta_url' => 'nullable|url',
            'cta_text' => 'nullable|string|max:50',
            'placement' => 'required|in:sidebar,inline,empty_state,banner,sticky_bottom,modal',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after:start_at',
            'priority' => 'required|integer|min:0|max:100',
            'status' => 'required|in:draft,active,paused,expired',
            'meta' => 'nullable|array',
        ]);

        // Set default CTA text if not provided
        $validated['cta_text'] = $validated['cta_text'] ?? 'Learn More';

        // Handle image upload
        if ($request->hasFile('image_url')) {
            // Delete old image
            if ($ad->image_url) {
                Storage::disk('public')->delete($ad->image_url);
            }
            $validated['image_url'] = $request->file('image_url')->store('ads', 'public');
        } else {
            unset($validated['image_url']);
        }

        $ad->update($validated);

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad updated successfully');
    }

    /**
     * Delete ad
     */
    public function destroy(Ad $ad)
    {
        // Delete image
        if ($ad->image_url) {
            Storage::disk('public')->delete($ad->image_url);
        }

        $ad->delete();

        return redirect()->route('admin.ads.index')
            ->with('success', 'Ad deleted successfully');
    }

    /**
     * Toggle ad status
     */
    public function toggleStatus(Ad $ad)
    {
        $ad->update([
            'status' => $ad->status === 'active' ? 'paused' : 'active',
        ]);

        return back()->with('success', 'Ad status updated');
    }

    /**
     * Show analytics
     */
    public function analytics(Ad $ad)
    {
        $impressionsByDay = $ad->impressions()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereNotNull('created_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        $clicksByDay = $ad->clicks()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereNotNull('created_at')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(30)
            ->get();

        // Get top pages by impressions
        $topPages = $ad->impressions()
            ->selectRaw('page, COUNT(*) as count')
            ->whereNotNull('page')
            ->groupBy('page')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Get device breakdown
        $deviceBreakdown = $ad->impressions()
            ->selectRaw('device_type, COUNT(*) as count')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        return Inertia::render('Admin/Ads/Analytics', [
            'ad' => $ad,
            'impressionsByDay' => $impressionsByDay,
            'clicksByDay' => $clicksByDay,
            'topPages' => $topPages,
            'deviceBreakdown' => $deviceBreakdown,
        ]);
    }
}
