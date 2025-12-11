<?php

namespace App\Http\Controllers\Admin\DataManagement;

use App\Http\Controllers\Admin\Traits\BulkUploadable;
use App\Http\Controllers\Controller;
use App\Models\InstitutionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class InstitutionTypeController extends Controller
{
    use BulkUploadable;

    protected $entityName = 'Institution Type';

    protected $entityNamePlural = 'Institution Types';

    protected $indexRoute = 'admin.data.institution-types.index';

    protected $bulkUploadView = 'Admin/DataManagement/InstitutionTypes/BulkUpload';

    public function index(Request $request)
    {
        $query = InstitutionType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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

        $institutionTypes = $query->paginate(15)->withQueryString();

        $categories = InstitutionType::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Admin/DataManagement/InstitutionTypes/Index', [
            'institutionTypes' => $institutionTypes,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DataManagement/InstitutionTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:institution_types,name',
            'name_bn' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:institution_types,slug',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            InstitutionType::create($validated);

            return redirect()->route('admin.data.institution-types.index')
                ->with('success', 'Institution type created successfully.');
        } catch (\Exception $e) {
            Log::error('Institution type creation failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to create institution type.');
        }
    }

    public function edit(InstitutionType $institutionType)
    {
        return Inertia::render('Admin/DataManagement/InstitutionTypes/Edit', [
            'institutionType' => $institutionType,
        ]);
    }

    public function update(Request $request, InstitutionType $institutionType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('institution_types')->ignore($institutionType->id)],
            'name_bn' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('institution_types')->ignore($institutionType->id)],
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $institutionType->update($validated);

            return redirect()->route('admin.data.institution-types.index')
                ->with('success', 'Institution type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Institution type update failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to update institution type.');
        }
    }

    public function destroy(InstitutionType $institutionType)
    {
        try {
            $institutionType->delete();

            return redirect()->route('admin.data.institution-types.index')
                ->with('success', 'Institution type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Institution type deletion failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to delete institution type.');
        }
    }

    public function toggleStatus(InstitutionType $institutionType)
    {
        try {
            $institutionType->update(['is_active' => ! $institutionType->is_active]);

            return back()->with('success', 'Institution type status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Institution type status toggle failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to update status.');
        }
    }

    protected function getTemplateColumns(): array
    {
        return ['name', 'name_bn', 'slug', 'description', 'category', 'level', 'is_active', 'sort_order'];
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
            'level' => 'nullable|string|max:50',
            'is_active' => 'nullable|in:true,false,1,0,yes,no',
            'sort_order' => 'nullable|integer',
        ];
    }

    protected function getSampleData(): array
    {
        return [
            ['University', 'বিশ্ববিদ্যালয়', 'university', 'Higher education institution', 'Education', 'Higher', 'true', '1'],
            ['College', 'কলেজ', 'college', 'Secondary and higher secondary institution', 'Education', 'Secondary', 'true', '2'],
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
        InstitutionType::updateOrCreate(['name' => $data['name']], $data);
    }

    protected function getExportQuery(Request $request)
    {
        $query = InstitutionType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
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
            $record->level,
            $record->is_active ? 'true' : 'false',
            $record->sort_order,
        ];
    }
}
