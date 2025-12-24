<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Directory;
use App\Models\DirectoryCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DirectoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Directory::with(['category', 'country'])
            ->published();

        // Filter by category
        if ($request->category) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by country
        if ($request->country) {
            $query->where('country_id', $request->country);
        }

        // Search
        if ($request->search) {
            $query->search($request->search);
        }

        // Only verified
        if ($request->verified) {
            $query->verified();
        }

        $directories = $query->orderBy('is_featured', 'desc')
            ->orderBy('is_verified', 'desc')
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = DirectoryCategory::active()
            ->withCount(['directories' => function ($q) {
                $q->published();
            }])
            ->ordered()
            ->get();

        $countries = Country::whereHas('directories', function ($q) {
            $q->published();
        })->orderBy('name')->get(['id', 'name', 'iso_code_2']);

        $featuredDirectories = Directory::with(['category', 'country'])
            ->published()
            ->featured()
            ->verified()
            ->take(6)
            ->get();

        return Inertia::render('Directories/Index', [
            'directories' => $directories,
            'categories' => $categories,
            'countries' => $countries,
            'featuredDirectories' => $featuredDirectories,
            'filters' => $request->only(['category', 'country', 'search', 'verified']),
        ]);
    }

    public function show(string $slug)
    {
        $directory = Directory::with(['category', 'country'])
            ->where('slug', $slug)
            ->published()
            ->firstOrFail();

        // Increment views
        $directory->increment('views_count');

        $relatedDirectories = Directory::with(['category'])
            ->published()
            ->where('directory_category_id', $directory->directory_category_id)
            ->where('id', '!=', $directory->id)
            ->take(4)
            ->get();

        return Inertia::render('Directories/Show', [
            'directory' => $directory,
            'relatedDirectories' => $relatedDirectories,
        ]);
    }
}
