<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ClientController extends Controller
{
    /**
     * Display a listing of clients
     */
    public function index(Request $request): Response
    {
        $query = Client::query()
            ->withCount(['invoices', 'quotations'])
            ->withSum('invoices as total_invoiced', 'total_amount')
            ->withSum('invoices as total_paid', 'paid_amount');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filter by type
        if ($type = $request->get('type')) {
            $query->where('client_type', $type);
        }

        // Filter by status
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

        $clients = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('Admin/Accounting/Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'type', 'active']),
            'stats' => [
                'total' => Client::count(),
                'active' => Client::where('is_active', true)->count(),
                'businesses' => Client::where('client_type', 'business')->count(),
                'total_outstanding' => Client::sum('outstanding_balance'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new client
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Accounting/Clients/Create', [
            'clientTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'business', 'label' => 'Business'],
                ['value' => 'agency', 'label' => 'Agency'],
            ],
            'paymentTerms' => Client::PAYMENT_TERMS,
        ]);
    }

    /**
     * Store a newly created client
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:individual,business,agency',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:20',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = true;
        $validated['currency'] = 'BDT';

        $client = Client::create($validated);

        return redirect()
            ->route('admin.accounting.clients.show', $client)
            ->with('success', 'Client created successfully.');
    }

    /**
     * Display the specified client
     */
    public function show(Client $client): Response
    {
        $client->load(['invoices' => function ($q) {
            $q->latest()->take(10);
        }, 'quotations' => function ($q) {
            $q->latest()->take(10);
        }]);

        return Inertia::render('Admin/Accounting/Clients/Show', [
            'client' => $client,
            'stats' => [
                'total_invoiced' => $client->invoices()->sum('total_amount'),
                'total_paid' => $client->invoices()->sum('paid_amount'),
                'total_outstanding' => $client->outstanding_balance,
                'invoice_count' => $client->invoices()->count(),
                'quotation_count' => $client->quotations()->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the client
     */
    public function edit(Client $client): Response
    {
        return Inertia::render('Admin/Accounting/Clients/Edit', [
            'client' => $client,
            'clientTypes' => [
                ['value' => 'individual', 'label' => 'Individual'],
                ['value' => 'business', 'label' => 'Business'],
                ['value' => 'agency', 'label' => 'Agency'],
            ],
            'paymentTerms' => Client::PAYMENT_TERMS,
        ]);
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'client_type' => 'required|in:individual,business,agency',
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'alternate_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'nid' => 'nullable|string|max:20',
            'passport_number' => 'nullable|string|max:20',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|string',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $client->update($validated);

        return redirect()
            ->route('admin.accounting.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client
     */
    public function destroy(Client $client)
    {
        // Check for related records
        if ($client->invoices()->exists()) {
            return back()->with('error', 'Cannot delete client with existing invoices.');
        }

        $client->delete();

        return redirect()
            ->route('admin.accounting.clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}
