<?php

namespace App\Http\Controllers\Admin\DataManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\BulkUploadable;
use App\Models\RelationshipType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class RelationshipTypeController extends Controller
{
    use BulkUploadable;

    protected $entityName = 'Relationship Type';
    protected $entityNamePlural = 'Relationship Types';
    protected $indexRoute = 'admin.data.relationship-types.index';
    protected $bulkUploadView = 'Admin/DataManagement/RelationshipTypes/BulkUpload';

    public function index(Request $request)
    {
        $query = RelationshipType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $relationshipTypes = $query->paginate(15)->withQueryString();

        $categories = RelationshipType::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Admin/DataManagement/RelationshipTypes/Index', [
            'relationshipTypes' => $relationshipTypes,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DataManagement/RelationshipTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:relationship_types,name',
            'name_bn' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:relationship_types,slug',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            RelationshipType::create($validated);
            return redirect()->route('admin.data.relationship-types.index')
                ->with('success', 'Relationship type created successfully.');
        } catch (\Exception $e) {
            Log::error('Relationship type creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create relationship type.');
        }
    }

    public function edit(RelationshipType $relationshipType)
    {
        return Inertia::render('Admin/DataManagement/RelationshipTypes/Edit', [
            'relationshipType' => $relationshipType,
        ]);
    }

    public function update(Request $request, RelationshipType $relationshipType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('relationship_types')->ignore($relationshipType->id)],
            'name_bn' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('relationship_types')->ignore($relationshipType->id)],
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $relationshipType->update($validated);
            return redirect()->route('admin.data.relationship-types.index')
                ->with('success', 'Relationship type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Relationship type update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update relationship type.');
        }
    }

    public function destroy(RelationshipType $relationshipType)
    {
        try {
            $relationshipType->delete();
            return redirect()->route('admin.data.relationship-types.index')
                ->with('success', 'Relationship type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Relationship type deletion failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete relationship type.');
        }
    }

    public function toggleStatus(RelationshipType $relationshipType)
    {
        try {
            $relationshipType->update(['is_active' => !$relationshipType->is_active]);
            return back()->with('success', 'Relationship type status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Relationship type status toggle failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update status.');
        }
    }

    protected function getTemplateColumns(): array
    {
        return ['name', 'name_bn', 'slug', 'description', 'category', 'is_active', 'sort_order'];
    }

    protected function getRequiredColumns(): array
    {
        return ['name'];
    }

    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|in:true,false,1,0,yes,no',
            'sort_order' => 'nullable|integer',
        ];
    }

    protected function getSampleData(): array
    {
        return [
            ['Spouse', 'স্বামী/স্ত্রী', 'spouse', 'Husband or Wife', 'Family', 'true', '1'],
            ['Child', 'সন্তান', 'child', 'Son or Daughter', 'Family', 'true', '2'],
        ];
    }

    protected function transformRowData(array $data): array
    {
        if (isset($data['is_active'])) {
            $data['is_active'] = in_array(strtolower($data['is_active']), ['true', '1', 'yes']);
        }
        return $data;
    }

    protected function saveRecord(array $data)
    {
        RelationshipType::updateOrCreate(['name' => $data['name']], $data);
    }

    protected function getExportQuery(Request $request)
    {
        $query = RelationshipType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        return $query->orderBy('sort_order');
    }

    protected function getExportColumns(): array
    {
        return $this->getTemplateColumns();
    }

    protected function prepareExportRow($record): array
    {
        return [
            $record->name,
            $record->name_bn,
            $record->slug,
            $record->description,
            $record->category,
            $record->is_active ? 'true' : 'false',
            $record->sort_order,
        ];
    }
}
