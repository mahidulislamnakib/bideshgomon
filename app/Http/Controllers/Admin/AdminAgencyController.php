<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdminAgencyController extends Controller
{
    /**
     * Display a listing of agencies.
     */
    public function index(Request $request): Response
    {
        $query = Agency::with(['user', 'agencyType'])
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status, function ($q, $status) {
                if ($status === 'active') {
                    $q->where('is_active', true);
                } elseif ($status === 'suspended') {
                    $q->where('is_active', false);
                } elseif ($status === 'inactive') {
                    $q->where('is_active', false);
                }
            })
            ->when($request->verification, function ($q, $verification) {
                if ($verification === 'verified') {
                    $q->where('is_verified', true);
                } elseif ($verification === 'pending') {
                    $q->where('is_verified', false)->whereNull('verified_at');
                } elseif ($verification === 'rejected') {
                    // Agencies that were reviewed but not verified
                    $q->where('is_verified', false)->whereNotNull('verification_notes');
                }
            })
            ->when($request->type, function ($q, $type) {
                $q->where('agency_type_id', $type);
            })
            ->latest();

        $agencies = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total' => Agency::count(),
            'verified' => Agency::where('is_verified', true)->count(),
            'pending' => Agency::where('is_verified', false)->whereNull('verification_notes')->count(),
            'rejected' => Agency::where('is_verified', false)->whereNotNull('verification_notes')->count(),
            'growth' => '+12%', // Calculate actual growth
            'verifiedGrowth' => '+8%',
            'newThisMonth' => Agency::whereMonth('created_at', now()->month)->count(),
        ];

        return Inertia::render('Admin/Agencies/IndexRedesigned', [
            'agencies' => $agencies,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'verification', 'type']),
        ]);
    }

    /**
     * Show the form for creating a new agency.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Agencies/Create');
    }

    /**
     * Store a newly created agency.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:agencies,email',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'agency_type_id' => 'nullable|exists:agency_types,id',
        ]);

        $agency = Agency::create($validated);

        return redirect()->route('admin.agencies.index')
            ->with('success', 'Agency created successfully.');
    }

    /**
     * Display the specified agency.
     */
    public function show(Agency $agency): Response
    {
        $agency->load(['user', 'agencyType', 'teamMembers', 'verificationRequest']);

        return Inertia::render('Admin/Agencies/Show', [
            'agency' => $agency,
        ]);
    }

    /**
     * Show the form for editing the specified agency.
     */
    public function edit(Agency $agency): Response
    {
        return Inertia::render('Admin/Agencies/Edit', [
            'agency' => $agency,
        ]);
    }

    /**
     * Update the specified agency.
     */
    public function update(Request $request, Agency $agency)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:agencies,email,'.$agency->id,
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'agency_type_id' => 'nullable|exists:agency_types,id',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $agency->update($validated);

        return redirect()->route('admin.agencies.index')
            ->with('success', 'Agency updated successfully.');
    }

    /**
     * Remove the specified agency.
     */
    public function destroy(Agency $agency)
    {
        $agency->delete();

        return redirect()->route('admin.agencies.index')
            ->with('success', 'Agency deleted successfully.');
    }

    /**
     * Verify an agency.
     */
    public function verify(Agency $agency)
    {
        $agency->update([
            'is_verified' => true,
            'verified_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Agency verified successfully.');
    }

    /**
     * Suspend an agency.
     */
    public function suspend(Request $request, Agency $agency)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $agency->update([
            'is_active' => false,
            'verification_notes' => $request->reason,
        ]);

        return redirect()->back()
            ->with('success', 'Agency suspended successfully.');
    }

    /**
     * Unsuspend an agency.
     */
    public function unsuspend(Agency $agency)
    {
        $agency->update([
            'is_active' => true,
        ]);

        return redirect()->back()
            ->with('success', 'Agency unsuspended successfully.');
    }

    /**
     * Toggle featured status.
     */
    public function toggleFeatured(Agency $agency)
    {
        $agency->update([
            'is_featured' => ! $agency->is_featured,
        ]);

        return redirect()->back()
            ->with('success', 'Agency featured status updated.');
    }

    /**
     * Export agencies to CSV.
     */
    public function export(Request $request)
    {
        $agencies = Agency::with(['user', 'agencyType'])
            ->when($request->status, function ($q, $status) {
                if ($status === 'verified') {
                    $q->where('is_verified', true);
                }
            })
            ->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="agencies-'.date('Y-m-d').'.csv"',
        ];

        $callback = function () use ($agencies) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Company', 'Email', 'Phone', 'City', 'Country', 'Verified', 'Active', 'Created']);

            foreach ($agencies as $agency) {
                fputcsv($file, [
                    $agency->id,
                    $agency->name,
                    $agency->company_name,
                    $agency->email,
                    $agency->phone,
                    $agency->city,
                    $agency->country,
                    $agency->is_verified ? 'Yes' : 'No',
                    $agency->is_active ? 'Yes' : 'No',
                    $agency->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
