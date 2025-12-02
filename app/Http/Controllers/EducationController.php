<?php

namespace App\Http\Controllers;

use App\Models\University;
use App\Models\Country;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EducationController extends Controller
{
    /**
     * Display university search page.
     */
    public function index(Request $request)
    {
        $query = University::with('country')
            ->active()
            ->orderBy('world_ranking', 'asc');

        // Apply filters
        if ($request->filled('country_id')) {
            $query->byCountry($request->country_id);
        }

        if ($request->filled('ranking_min') || $request->filled('ranking_max')) {
            $query->byRankingRange($request->ranking_min, $request->ranking_max);
        }

        if ($request->filled('tuition_max')) {
            $query->byTuitionRange($request->tuition_max);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('scholarships')) {
            $query->where('scholarships_available', true);
        }

        $universities = $query->paginate(24);

        // Get countries for filter dropdown
        $countries = Country::whereHas('universities', function ($q) {
            $q->active();
        })->orderBy('name')->get(['id', 'name']);

        // Get statistics
        $stats = [
            'total' => University::active()->count(),
            'countries' => Country::whereHas('universities', function ($q) {
                $q->active();
            })->count(),
            'with_scholarships' => University::active()->where('scholarships_available', true)->count(),
        ];

        return Inertia::render('Services/Education/Index', [
            'universities' => $universities,
            'countries' => $countries,
            'filters' => $request->only(['country_id', 'ranking_min', 'ranking_max', 'tuition_max', 'search', 'type', 'scholarships']),
            'stats' => $stats,
        ]);
    }

    /**
     * Display university details.
     */
    public function show(University $university)
    {
        $university->load('country');

        // Get similar universities (same country, similar ranking)
        $similarUniversities = University::with('country')
            ->active()
            ->where('id', '!=', $university->id)
            ->where('country_id', $university->country_id)
            ->when($university->world_ranking, function ($query) use ($university) {
                $query->whereBetween('world_ranking', [
                    max(1, $university->world_ranking - 50),
                    $university->world_ranking + 50
                ]);
            })
            ->orderBy('world_ranking', 'asc')
            ->take(6)
            ->get();

        return Inertia::render('Services/Education/Show', [
            'university' => $university,
            'similarUniversities' => $similarUniversities,
        ]);
    }

    /**
     * Compare universities.
     */
    public function compare(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:2|max:3',
            'ids.*' => 'exists:universities,id',
        ]);

        $universities = University::with('country')
            ->whereIn('id', $request->ids)
            ->get();

        return response()->json([
            'universities' => $universities,
        ]);
    }
}
