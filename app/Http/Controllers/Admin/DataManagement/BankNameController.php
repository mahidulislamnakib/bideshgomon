<?php

namespace App\Http\Controllers\Admin\DataManagement;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\Traits\BulkUploadable;
use App\Models\BankName;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class BankNameController extends Controller
{
    use BulkUploadable;

    protected $entityName = 'Bank Name';
    protected $entityNamePlural = 'Bank Names';
    protected $indexRoute = 'admin.data.bank-names.index';
    protected $bulkUploadView = 'Admin/DataManagement/BankNames/BulkUpload';

    public function index(Request $request)
    {
        $query = BankName::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%")
                  ->orWhere('short_name', 'like', "%{$search}%")
                  ->orWhere('swift_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $sortField = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortField, $sortDirection);

        $bankNames = $query->paginate(15)->withQueryString();

        $types = BankName::select('type')
            ->whereNotNull('type')
            ->distinct()
            ->pluck('type')
            ->sort()
            ->values();

        return Inertia::render('Admin/DataManagement/BankNames/Index', [
            'bankNames' => $bankNames,
            'types' => $types,
            'filters' => $request->only(['search', 'type', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Admin/DataManagement/BankNames/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bank_names,name',
            'name_bn' => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:20',
            'routing_number' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            BankName::create($validated);
            return redirect()->route('admin.data.bank-names.index')
                ->with('success', 'Bank name created successfully.');
        } catch (\Exception $e) {
            Log::error('Bank name creation failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to create bank name.');
        }
    }

    public function edit(BankName $bankName)
    {
        return Inertia::render('Admin/DataManagement/BankNames/Edit', [
            'bankName' => $bankName,
        ]);
    }

    public function update(Request $request, BankName $bankName)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('bank_names')->ignore($bankName->id)],
            'name_bn' => 'nullable|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:20',
            'routing_number' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        try {
            $bankName->update($validated);
            return redirect()->route('admin.data.bank-names.index')
                ->with('success', 'Bank name updated successfully.');
        } catch (\Exception $e) {
            Log::error('Bank name update failed', ['error' => $e->getMessage()]);
            return back()->withInput()->with('error', 'Failed to update bank name.');
        }
    }

    public function destroy(BankName $bankName)
    {
        try {
            $bankName->delete();
            return redirect()->route('admin.data.bank-names.index')
                ->with('success', 'Bank name deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Bank name deletion failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to delete bank name.');
        }
    }

    public function toggleStatus(BankName $bankName)
    {
        try {
            $bankName->update(['is_active' => !$bankName->is_active]);
            return back()->with('success', 'Bank name status updated successfully.');
        } catch (\Exception $e) {
            Log::error('Bank name status toggle failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to update status.');
        }
    }

    protected function getTemplateColumns(): array
    {
        return ['name', 'name_bn', 'short_name', 'swift_code', 'routing_number', 'type', 'website', 'description', 'is_active', 'sort_order'];
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
            'short_name' => 'nullable|string|max:100',
            'swift_code' => 'nullable|string|max:20',
            'routing_number' => 'nullable|string|max:50',
            'type' => 'nullable|string|max:50',
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|in:true,false,1,0,yes,no',
            'sort_order' => 'nullable|integer',
        ];
    }

    protected function getSampleData(): array
    {
        return [
            ['Sonali Bank', 'সোনালী ব্যাংক', 'SONALI', 'SONIBD2D', '012345678', 'Commercial', 'https://sonalibank.com.bd', 'Largest public sector bank', 'true', '1'],
            ['Islami Bank Bangladesh', 'ইসলামী ব্যাংক বাংলাদেশ', 'IBBL', 'IBBLBDDH', '987654321', 'Islamic', 'https://islamibankbd.com', 'Leading Islamic bank', 'true', '2'],
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
        BankName::updateOrCreate(['name' => $data['name']], $data);
    }

    protected function getExportQuery(Request $request)
    {
        $query = BankName::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_bn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
            $record->short_name,
            $record->swift_code,
            $record->routing_number,
            $record->type,
            $record->website,
            $record->description,
            $record->is_active ? 'true' : 'false',
            $record->sort_order,
        ];
    }
}
