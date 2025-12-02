<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\ServiceModule;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PackageController extends Controller
{
    /**
     * Display package comparison table
     */
    public function index(Request $request)
    {
        $query = Package::with('serviceModule')
            ->active()
            ->available()
            ->ordered();

        // Filter by service
        if ($request->service) {
            $query->byService($request->service);
        }

        // Filter by price range
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by features
        if ($request->features) {
            $features = is_array($request->features) ? $request->features : explode(',', $request->features);
            foreach ($features as $feature) {
                $query->whereJsonContains('features', $feature);
            }
        }

        // Show popular first if requested
        if ($request->popular_first) {
            $query->orderByDesc('is_popular');
        }

        $packages = $query->get();

        // Get all services for filter
        $services = ServiceModule::active()->ordered()->get(['id', 'name', 'slug']);

        // Extract unique features from all packages for filter options
        $allFeatures = $packages->pluck('features')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
            'services' => $services,
            'allFeatures' => $allFeatures,
            'filters' => [
                'service' => $request->service,
                'min_price' => $request->min_price,
                'max_price' => $request->max_price,
                'features' => $request->features,
                'popular_first' => $request->boolean('popular_first'),
            ],
        ]);
    }

    /**
     * Display single package details
     */
    public function show(string $slug)
    {
        $package = Package::with('serviceModule')
            ->where('slug', $slug)
            ->firstOrFail();

        // Get related packages from same service
        $relatedPackages = Package::with('serviceModule')
            ->where('service_module_id', $package->service_module_id)
            ->where('id', '!=', $package->id)
            ->active()
            ->available()
            ->ordered()
            ->limit(3)
            ->get();

        return Inertia::render('Packages/Show', [
            'package' => $package,
            'relatedPackages' => $relatedPackages,
        ]);
    }

    /**
     * Compare specific packages side-by-side
     */
    public function compare(Request $request)
    {
        $slugs = $request->input('packages', []);
        
        if (count($slugs) < 2) {
            return redirect()->route('packages.index')
                ->with('error', 'Please select at least 2 packages to compare');
        }

        $packages = Package::with('serviceModule')
            ->whereIn('slug', $slugs)
            ->active()
            ->get();

        if ($packages->count() < 2) {
            return redirect()->route('packages.index')
                ->with('error', 'One or more packages not found');
        }

        // Extract all unique features across all compared packages
        $allFeatures = $packages->pluck('features')
            ->flatten()
            ->unique()
            ->sort()
            ->values();

        return Inertia::render('Packages/Compare', [
            'packages' => $packages,
            'allFeatures' => $allFeatures,
        ]);
    }
}
