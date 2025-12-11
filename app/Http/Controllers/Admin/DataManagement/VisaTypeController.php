<?php

namespace App\Http\Controllers\Admin\DataManagement;

use App\Http\Controllers\Admin\Traits\BulkUploadable;
use App\Http\Controllers\Controller;
use App\Models\VisaType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VisaTypeController extends Controller
{
    use BulkUploadable;

    protected $entityName = 'Visa Type';

    protected $entityNamePlural = 'Visa Types';

    protected $indexRoute = 'admin.data.visa-types.index';

    protected $bulkUploadView = 'Admin/DataManagement/VisaTypes/BulkUpload';

    public function index(Request $request)
    {
        $query = VisaType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortField = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $visaTypes = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/DataManagement/VisaTypes/Index', [
            'visaTypes' => $visaTypes,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DataManagement/VisaTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:visa_types,name',
            'slug' => 'nullable|string|max:255|unique:visa_types,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            VisaType::create($validated);

            return redirect()->route('admin.data.visa-types.index')
                ->with('success', 'Visa type created successfully.');
        } catch (\Exception $e) {
            Log::error('Visa type creation failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to create visa type.');
        }
    }

    public function edit(VisaType $visaType)
    {
        return Inertia::render('Admin/DataManagement/VisaTypes/Edit', [
            'visaType' => $visaType,
        ]);
    }

    public function update(Request $request, VisaType $visaType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('visa_types')->ignore($visaType->id)],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('visa_types')->ignore($visaType->id)],
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            $visaType->update($validated);

            return redirect()->route('admin.data.visa-types.index')
                ->with('success', 'Visa type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Visa type update failed', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', 'Failed to update visa type.');
        }
    }

    public function destroy(VisaType $visaType)
    {
        try {
            $visaType->delete();

            return redirect()->route('admin.data.visa-types.index')
                ->with('success', 'Visa type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Visa type deletion failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to delete visa type.');
        }
    }

    public function toggleStatus(VisaType $visaType)
    {
        try {
            $visaType->update(['is_active' => ! $visaType->is_active]);

            return back()->with('success', 'Visa type status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Visa type status toggle failed', ['error' => $e->getMessage()]);

            return back()->with('error', 'Failed to update status.');
        }
    }

    protected function getTemplateColumns(): array
    {
        return ['name', 'slug', 'description', 'is_active'];
    }

    protected function getRequiredColumns(): array
    {
        return ['name'];
    }

    protected function getValidationRules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:true,false,1,0,yes,no',
        ];
    }

    protected function getSampleData(): array
    {
        return [
            ['Tourist Visa', 'tourist-visa', 'For tourism and leisure travel', 'true'],
            ['Student Visa', 'student-visa', 'For educational purposes', 'true'],
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
        VisaType::updateOrCreate(['name' => $data['name']], $data);
    }

    protected function getExportQuery(Request $request)
    {
        $query = VisaType::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        return $query->orderBy('name');
    }

    protected function getExportColumns(): array
    {
        return $this->getTemplateColumns();
    }

    protected function prepareExportRow($record): array
    {
        return [
            $record->name,
            $record->slug,
            $record->description,
            $record->is_active ? 'true' : 'false',
        ];
    }
}
