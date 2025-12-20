<?php

namespace App\Exports;

use Illuminate\Support\Collection;

class ApplicationsExport
{
    protected Collection $applications;

    public function __construct(Collection $applications)
    {
        $this->applications = $applications;
    }

    /**
     * Export applications to CSV
     */
    public function toCsv(): string
    {
        $output = fopen('php://temp', 'r+');

        // CSV Headers (Bangladesh formatted)
        $headers = [
            'Application Number',
            'Applicant Name',
            'Email',
            'Phone',
            'Service',
            'Status',
            'Amount (৳)',
            'Submitted Date (DD/MM/YYYY)',
            'Processing Days',
            'Assigned To',
            'Notes',
        ];

        fputcsv($output, $headers);

        // Data rows
        foreach ($this->applications as $application) {
            $processingDays = $application->approved_at
                ? $application->created_at->diffInDays($application->approved_at)
                : $application->created_at->diffInDays(now());

            fputcsv($output, [
                $application->application_number,
                $application->user->name ?? 'N/A',
                $application->user->email ?? 'N/A',
                format_bd_phone($application->user->phone ?? 'N/A'),
                $application->serviceModule->name ?? 'N/A',
                ucfirst($application->status),
                format_bd_currency($application->amount ?? 0),
                format_bd_date($application->created_at),
                $processingDays.' days',
                $application->assignedTo->name ?? 'Unassigned',
                $application->notes ?? '',
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Export applications to Excel-compatible CSV with UTF-8 BOM
     */
    public function toExcel(): string
    {
        // Add UTF-8 BOM for Excel compatibility
        return "\xEF\xBB\xBF".$this->toCsv();
    }

    /**
     * Get download response
     */
    public function download(string $filename = 'applications.csv', bool $excel = false)
    {
        $content = $excel ? $this->toExcel() : $this->toCsv();

        return response($content)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
