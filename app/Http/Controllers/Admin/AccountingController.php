<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountingController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Accounting Dashboard
     */
    public function dashboard(Request $request): Response
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        // Update overdue invoices
        $this->invoiceService->updateOverdueInvoices();

        // Get stats
        $stats = $this->invoiceService->getDashboardStats($year, $month);

        // Revenue by month (for chart)
        $revenueByMonth = $this->invoiceService->getRevenueByMonth($year);

        // Format for chart
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[] = [
                'month' => Carbon::create($year, $m, 1)->format('M'),
                'total' => (float) ($revenueByMonth[$m]['total'] ?? 0),
                'paid' => (float) ($revenueByMonth[$m]['paid'] ?? 0),
            ];
        }

        // Top clients
        $topClients = $this->invoiceService->getTopClients(5);

        // Payment method breakdown
        $paymentMethods = $this->invoiceService->getPaymentMethodStats();

        // Aging report
        $agingReport = $this->invoiceService->getAgingReport();

        // Recent invoices
        $recentInvoices = Invoice::with('client:id,name')
            ->latest()
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = Payment::with([
            'invoice:id,invoice_number',
            'receiver:id,name',
        ])
            ->where('status', Payment::STATUS_COMPLETED)
            ->latest('payment_date')
            ->take(5)
            ->get();

        return Inertia::render('Admin/Accounting/Dashboard', [
            'stats' => $stats,
            'monthlyRevenue' => $monthlyRevenue,
            'topClients' => $topClients,
            'paymentMethods' => $paymentMethods,
            'agingReport' => $agingReport,
            'recentInvoices' => $recentInvoices,
            'recentPayments' => $recentPayments,
            'year' => (int) $year,
            'month' => (int) $month,
            'availableYears' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    /**
     * Reports page
     */
    public function reports(Request $request): Response
    {
        $period = $request->get('period', 'month');

        // Determine date range based on period
        [$startDate, $endDate] = $this->getPeriodDates($period);

        // Calculate metrics
        $totalRevenue = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->sum('total_amount');

        $totalCollected = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount');

        $totalOutstanding = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
            ->sum(\DB::raw('total_amount - paid_amount'));

        $totalOverdue = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->where('status', Invoice::STATUS_OVERDUE)
            ->sum(\DB::raw('total_amount - paid_amount'));

        $outstandingInvoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED, Invoice::STATUS_PAID])
            ->count();

        $overdueInvoices = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->where('status', Invoice::STATUS_OVERDUE)
            ->count();

        // Calculate growth
        [$prevStart, $prevEnd] = $this->getPreviousPeriodDates($period);
        $prevRevenue = Invoice::whereBetween('issue_date', [$prevStart, $prevEnd])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->sum('total_amount');
        $revenueGrowth = $prevRevenue > 0 ? round((($totalRevenue - $prevRevenue) / $prevRevenue) * 100, 1) : 0;

        $collectionRate = $totalRevenue > 0 ? round(($totalCollected / $totalRevenue) * 100, 1) : 0;

        $metrics = [
            'totalRevenue' => $totalRevenue,
            'totalCollected' => $totalCollected,
            'totalOutstanding' => $totalOutstanding,
            'totalOverdue' => $totalOverdue,
            'outstandingInvoices' => $outstandingInvoices,
            'overdueInvoices' => $overdueInvoices,
            'revenueGrowth' => $revenueGrowth,
            'collectionRate' => $collectionRate,
        ];

        // Revenue data for chart
        $revenueData = $this->invoiceService->getRevenueByMonth(now()->year);

        // Payment methods breakdown
        $totalPayments = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->sum('amount');

        $paymentMethods = Payment::whereBetween('payment_date', [$startDate, $endDate])
            ->where('status', Payment::STATUS_COMPLETED)
            ->selectRaw('payment_method as method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get()
            ->map(function ($item) use ($totalPayments) {
                $item->percentage = $totalPayments > 0 ? round(($item->total / $totalPayments) * 100, 1) : 0;

                return $item;
            });

        // Top clients
        $topClients = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->selectRaw('
                COALESCE(client_id, 0) as id,
                COALESCE(MAX(client_name), "Direct Client") as name,
                COUNT(*) as invoiceCount,
                SUM(total_amount) as total
            ')
            ->groupBy('client_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Invoice status breakdown
        $invoiceStatusBreakdown = Invoice::whereBetween('issue_date', [$startDate, $endDate])
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('status')
            ->get();

        // Aging report
        $agingReport = $this->invoiceService->getAgingReport();

        // Monthly summary
        $driver = config('database.default');
        $monthExpr = $driver === 'sqlite'
            ? "CAST(strftime('%m', issue_date) AS INTEGER)"
            : 'MONTH(issue_date)';

        $monthlySummary = Invoice::whereYear('issue_date', now()->year)
            ->whereNotIn('status', [Invoice::STATUS_CANCELLED])
            ->selectRaw("
                {$monthExpr} as month,
                COUNT(*) as invoiceCount,
                SUM(total_amount) as invoiced,
                SUM(paid_amount) as collected,
                SUM(total_amount - paid_amount) as outstanding
            ")
            ->groupByRaw($monthExpr)
            ->orderByRaw($monthExpr)
            ->get()
            ->map(function ($item) {
                $item->monthName = Carbon::create(now()->year, $item->month, 1)->format('F Y');
                $item->collectionRate = $item->invoiced > 0 ? round(($item->collected / $item->invoiced) * 100, 0) : 0;

                return $item;
            });

        return Inertia::render('Admin/Accounting/Reports/Index', [
            'metrics' => $metrics,
            'revenueData' => $revenueData,
            'paymentMethods' => $paymentMethods,
            'topClients' => $topClients,
            'invoiceStatusBreakdown' => $invoiceStatusBreakdown,
            'agingReport' => $agingReport,
            'monthlySummary' => $monthlySummary,
            'period' => $period,
        ]);
    }

    /**
     * Get date range for period
     */
    protected function getPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'week' => [now()->startOfWeek(), now()->endOfWeek()],
            'month' => [now()->startOfMonth(), now()->endOfMonth()],
            'quarter' => [now()->startOfQuarter(), now()->endOfQuarter()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            'all' => [Carbon::parse('2020-01-01'), now()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    /**
     * Get previous period dates for comparison
     */
    protected function getPreviousPeriodDates(string $period): array
    {
        return match ($period) {
            'today' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week' => [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()],
            'month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'quarter' => [now()->subQuarter()->startOfQuarter(), now()->subQuarter()->endOfQuarter()],
            'year' => [now()->subYear()->startOfYear(), now()->subYear()->endOfYear()],
            'all' => [Carbon::parse('2019-01-01'), Carbon::parse('2019-12-31')],
            default => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
        };
    }

    /**
     * Export report data
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'invoices');
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        // This would typically use Laravel Excel or similar
        // For now, return JSON that can be processed client-side

        if ($type === 'invoices') {
            $data = Invoice::with(['client:id,name', 'items'])
                ->whereBetween('issue_date', [$startDate, $endDate])
                ->get();
        } else {
            $data = Payment::with(['invoice:id,invoice_number', 'receiver:id,name'])
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->get();
        }

        return response()->json([
            'data' => $data,
            'type' => $type,
            'period' => "{$startDate} to {$endDate}",
        ]);
    }
}
