<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxSettingController extends Controller
{
    /**
     * Display a listing of tax settings
     */
    public function index(): Response
    {
        $taxes = TaxSetting::orderBy('is_default', 'desc')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Accounting/TaxSettings/Index', [
            'taxes' => $taxes,
            'defaultTax' => TaxSetting::getDefault(),
        ]);
    }

    /**
     * Store a newly created tax setting
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'applies_to' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            TaxSetting::where('is_default', true)->update(['is_default' => false]);
        }

        TaxSetting::create($validated);

        return back()->with('success', 'Tax setting created successfully.');
    }

    /**
     * Update the specified tax setting
     */
    public function update(Request $request, TaxSetting $taxSetting)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'rate' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:percentage,fixed',
            'applies_to' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'effective_from' => 'nullable|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // If setting as default, unset other defaults
        if (($validated['is_default'] ?? false) && ! $taxSetting->is_default) {
            TaxSetting::where('is_default', true)->update(['is_default' => false]);
        }

        $taxSetting->update($validated);

        return back()->with('success', 'Tax setting updated successfully.');
    }

    /**
     * Set as default tax
     */
    public function setDefault(TaxSetting $taxSetting)
    {
        TaxSetting::where('is_default', true)->update(['is_default' => false]);
        $taxSetting->update(['is_default' => true]);

        return back()->with('success', 'Tax set as default.');
    }

    /**
     * Remove the specified tax setting
     */
    public function destroy(TaxSetting $taxSetting)
    {
        if ($taxSetting->is_default) {
            return back()->with('error', 'Cannot delete default tax setting.');
        }

        $taxSetting->delete();

        return back()->with('success', 'Tax setting deleted successfully.');
    }
}
