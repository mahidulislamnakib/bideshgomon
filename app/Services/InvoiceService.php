<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create a new invoice with items
     */
    public function createInvoice(array $data, array $items = []): Invoice
    {
        return DB::transaction(function () use ($data, $items) {
            // Generate invoice number if not provided
            if (empty($data['invoice_number'])) {
                $data['invoice_number'] = Invoice::generateInvoiceNumber();
            }

            // Set defaults
            $data['tax_rate'] = $data['tax_rate'] ?? 15.00;
            $data['status'] = $data['status'] ?? Invoice::STATUS_DRAFT;
            $data['issue_date'] = $data['issue_date'] ?? now();
            $data['due_date'] = $data['due_date'] ?? now()->addDays(30);

            $invoice = Invoice::create($data);

            // Add items
            foreach ($items as $index => $item) {
                $item['sort_order'] = $index;
                $invoice->items()->create($item);
            }

            // Calculate totals
            $invoice->calculateTotals();

            return $invoice->fresh(['items', 'client', 'creator']);
        });
    }

    /**
     * Update an existing invoice
     */
    public function updateInvoice(Invoice $invoice, array $data, ?array $items = null): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $items) {
            $invoice->update($data);

            // If items are provided, sync them
            if ($items !== null) {
                // Remove existing items
                $invoice->items()->delete();

                // Add new items
                foreach ($items as $index => $item) {
                    $item['sort_order'] = $index;
                    $invoice->items()->create($item);
                }

                // Recalculate totals
                $invoice->calculateTotals();
            }

            return $invoice->fresh(['items', 'client', 'creator']);
        });
    }

    /**
     * Record a payment for an invoice
     */
    public function recordPayment(Invoice $invoice, array $paymentData): Payment
    {
        return DB::transaction(function () use ($invoice, $paymentData) {
            $paymentData['invoice_id'] = $invoice->id;
            $paymentData['payment_date'] = $paymentData['payment_date'] ?? now();
            $paymentData['status'] = $paymentData['status'] ?? Payment::STATUS_COMPLETED;

            $payment = Payment::create($paymentData);

            // Invoice status will be automatically updated via Payment model boot method

            return $payment;
        });
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(Invoice $invoice): Invoice
    {
        if ($invoice->status === Invoice::STATUS_DRAFT) {
            $invoice->update(['status' => Invoice::STATUS_SENT]);
        }

        return $invoice;
    }

    /**
     * Cancel an invoice
     */
    public function cancelInvoice(Invoice $invoice): Invoice
    {
        $invoice->update(['status' => Invoice::STATUS_CANCELLED]);

        return $invoice;
    }

    /**
     * Duplicate an invoice
     */
    public function duplicateInvoice(Invoice $invoice): Invoice
    {
        return DB::transaction(function () use ($invoice) {
            $newInvoice = $invoice->replicate();
            $newInvoice->invoice_number = Invoice::generateInvoiceNumber();
            $newInvoice->status = Invoice::STATUS_DRAFT;
            $newInvoice->issue_date = now();
            $newInvoice->due_date = now()->addDays(30);
            $newInvoice->paid_amount = 0;
            $newInvoice->save();

            // Duplicate items
            foreach ($invoice->items as $item) {
                $newItem = $item->replicate();
                $newItem->invoice_id = $newInvoice->id;
                $newItem->save();
            }

            return $newInvoice->fresh(['items', 'client', 'creator']);
        });
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats(?int $year = null, ?int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();
        $startOfYear = Carbon::create($year, 1, 1)->startOfYear();
        $endOfYear = $startOfYear->copy()->endOfYear();

        return [
            'monthly' => [
                'total_invoices' => Invoice::dateRange($startOfMonth, $endOfMonth)->count(),
                'total_amount' => Invoice::dateRange($startOfMonth, $endOfMonth)->sum('total_amount'),
                'paid_amount' => Invoice::dateRange($startOfMonth, $endOfMonth)->sum('paid_amount'),
                'pending_amount' => Invoice::dateRange($startOfMonth, $endOfMonth)
                    ->whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PARTIAL])
                    ->selectRaw('SUM(total_amount - paid_amount) as pending')
                    ->value('pending') ?? 0,
            ],
            'yearly' => [
                'total_invoices' => Invoice::dateRange($startOfYear, $endOfYear)->count(),
                'total_amount' => Invoice::dateRange($startOfYear, $endOfYear)->sum('total_amount'),
                'paid_amount' => Invoice::dateRange($startOfYear, $endOfYear)->sum('paid_amount'),
            ],
            'outstanding' => [
                'count' => Invoice::unpaid()->count(),
                'amount' => Invoice::unpaid()
                    ->selectRaw('SUM(total_amount - paid_amount) as outstanding')
                    ->value('outstanding') ?? 0,
            ],
            'overdue' => [
                'count' => Invoice::overdue()->count(),
                'amount' => Invoice::overdue()
                    ->selectRaw('SUM(total_amount - paid_amount) as overdue')
                    ->value('overdue') ?? 0,
            ],
            'by_status' => Invoice::selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->toArray(),
        ];
    }

    /**
     * Get revenue by month for a year
     */
    public function getRevenueByMonth(int $year): Collection
    {
        $driver = config('database.default');

        if ($driver === 'sqlite') {
            $monthExpr = "CAST(strftime('%m', issue_date) AS INTEGER)";
        } else {
            $monthExpr = 'MONTH(issue_date)';
        }

        return Invoice::selectRaw("{$monthExpr} as month, SUM(total_amount) as total, SUM(paid_amount) as paid")
            ->whereYear('issue_date', $year)
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->groupByRaw($monthExpr)
            ->orderByRaw($monthExpr)
            ->get()
            ->keyBy('month');
    }

    /**
     * Get top clients by revenue
     */
    public function getTopClients(int $limit = 10): Collection
    {
        return Invoice::with('client:id,name,email')
            ->selectRaw('client_id, COUNT(*) as invoice_count, SUM(total_amount) as total_revenue, SUM(paid_amount) as paid_revenue')
            ->whereNotNull('client_id')
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->groupBy('client_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment method statistics
     */
    public function getPaymentMethodStats(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $query = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->where('status', Payment::STATUS_COMPLETED)
            ->groupBy('payment_method')
            ->orderByDesc('total');

        if ($startDate && $endDate) {
            $query->whereBetween('payment_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    /**
     * Get aging report (overdue analysis)
     */
    public function getAgingReport(): array
    {
        $today = now();

        return [
            'current' => Invoice::unpaid()->where('due_date', '>=', $today)->count(),
            '1_30_days' => Invoice::unpaid()
                ->whereBetween('due_date', [$today->copy()->subDays(30), $today->copy()->subDay()])
                ->count(),
            '31_60_days' => Invoice::unpaid()
                ->whereBetween('due_date', [$today->copy()->subDays(60), $today->copy()->subDays(31)])
                ->count(),
            '61_90_days' => Invoice::unpaid()
                ->whereBetween('due_date', [$today->copy()->subDays(90), $today->copy()->subDays(61)])
                ->count(),
            'over_90_days' => Invoice::unpaid()
                ->where('due_date', '<', $today->copy()->subDays(90))
                ->count(),
        ];
    }

    /**
     * Check and update overdue invoices
     */
    public function updateOverdueInvoices(): int
    {
        return Invoice::whereIn('status', [Invoice::STATUS_SENT, Invoice::STATUS_PARTIAL])
            ->where('due_date', '<', now())
            ->update(['status' => Invoice::STATUS_OVERDUE]);
    }
}
