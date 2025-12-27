<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories
     */
    public function index(Request $request): Response
    {
        $categories = ExpenseCategory::withCount('expenses')
            ->withSum('expenses', 'total_amount')
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/Accounting/ExpenseCategories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:expense_categories,id',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = true;

        ExpenseCategory::create($validated);

        return back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, ExpenseCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'parent_id' => 'nullable|exists:expense_categories,id',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category
     */
    public function destroy(ExpenseCategory $category)
    {
        if ($category->expenses()->exists()) {
            return back()->with('error', 'Cannot delete category with existing expenses.');
        }

        if ($category->children()->exists()) {
            return back()->with('error', 'Cannot delete category with subcategories.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
