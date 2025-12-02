<?php

namespace App\Http\Controllers\Admin\DataManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\BulkUploadable;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class DocumentTypeController extends Controller
{
    use BulkUploadable;

    protected $entityName = 'Document Type';
    protected $entityNamePlural = 'Document Types';
    protected $indexRoute = 'admin.data.document-types.index';
    protected $bulkUploadView = 'Admin/DataManagement/DocumentTypes/BulkUpload';

    public function index(Request $request)
    {
        $query = DocumentType::query();

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

        $documentTypes = $query->paginate(15)->withQueryString();

        $categories = DocumentType::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();

        return Inertia::render('Admin/DataManagement/DocumentTypes/Index', [
            'documentTypes' => $documentTypes,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DataManagement/DocumentTypes/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'name_bn' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:document_types,slug',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            DocumentType::create($validated);
            return redirect()->route('admin.data.document-types.index')
                ->with('success', 'Document type created successfully.');
        } catch (\Exception $e) {
            Log::error('Document type creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create document type.');
        }
    }

    public function edit(DocumentType $documentType)
    {
        return Inertia::render('Admin/DataManagement/DocumentTypes/Edit', [
            'documentType' => $documentType,
        ]);
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('document_types')->ignore($documentType->id)],
            'name_bn' => 'nullable|string|max:255',
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('document_types')->ignore($documentType->id)],
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $documentType->update($validated);
            return redirect()->route('admin.data.document-types.index')
                ->with('success', 'Document type updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document type update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update document type.');
        }
    }

    public function destroy(DocumentType $documentType)
    {
        try {
            $documentType->delete();
            return redirect()->route('admin.data.document-types.index')
                ->with('success', 'Document type deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Document type deletion failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete document type.');
        }
    }

    public function toggleStatus(DocumentType $documentType)
    {
        try {
            $documentType->update(['is_active' => !$documentType->is_active]);
            return back()->with('success', 'Document type status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document type status toggle failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update status.');
        }
    }

    protected function getTemplateColumns(): array
    {
        return ['name', 'name_bn', 'slug', 'description', 'category', 'is_required', 'is_active', 'sort_order'];
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
            'is_required' => 'nullable|in:true,false,1,0,yes,no',
            'is_active' => 'nullable|in:true,false,1,0,yes,no',
            'sort_order' => 'nullable|integer',
        ];
    }

    protected function getSampleData(): array
    {
        return [
            ['Passport', 'পাসপোর্ট', 'passport', 'Valid passport for international travel', 'Identity', 'true', 'true', '1'],
            ['NID Card', 'এনআইডি কার্ড', 'nid-card', 'National Identity Card', 'Identity', 'true', 'true', '2'],
        ];
    }

    protected function transformRowData(array $data): array
    {
        if (isset($data['is_required'])) {
            $data['is_required'] = in_array(strtolower($data['is_required']), ['true', '1', 'yes']);
        }
        if (isset($data['is_active'])) {
            $data['is_active'] = in_array(strtolower($data['is_active']), ['true', '1', 'yes']);
        }
        return $data;
    }

    protected function saveRecord(array $data)
    {
        DocumentType::updateOrCreate(['name' => $data['name']], $data);
    }

    protected function getExportQuery(Request $request)
    {
        $query = DocumentType::query();

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
            $record->is_required ? 'true' : 'false',
            $record->is_active ? 'true' : 'false',
            $record->sort_order,
        ];
    }
}
