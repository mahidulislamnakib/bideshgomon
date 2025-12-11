<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Application {{ $application->application_number }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e4282b;
        }
        .header h1 {
            color: #e4282b;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .section {
            margin-bottom: 25px;
        }
        .section-title {
            background-color: #f8f9fa;
            padding: 10px;
            font-weight: bold;
            font-size: 14px;
            color: #e4282b;
            border-left: 4px solid #e4282b;
            margin-bottom: 15px;
        }
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }
        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            padding: 5px 0;
        }
        .info-value {
            display: table-cell;
            width: 60%;
            padding: 5px 0;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #d1ecf1; color: #0c5460; }
        .status-approved { background-color: #d4edda; color: #155724; }
        .status-rejected { background-color: #f8d7da; color: #721c24; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #999;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table th {
            background-color: #f8f9fa;
            padding: 8px;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ config('app.name') }}</h1>
        <p>Application Form</p>
        <p><strong>Application #{{ $application->application_number }}</strong></p>
    </div>

    <!-- Basic Information -->
    <div class="section">
        <div class="section-title">Application Details</div>
        <div class="info-row">
            <div class="info-label">Service:</div>
            <div class="info-value">{{ $application->serviceModule->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Country:</div>
            <div class="info-value">{{ $application->country->name ?? 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Status:</div>
            <div class="info-value">
                <span class="status-badge status-{{ $application->status }}">
                    {{ ucfirst($application->status) }}
                </span>
            </div>
        </div>
        <div class="info-row">
            <div class="info-label">Applied Date:</div>
            <div class="info-value">{{ $application->created_at->format('d/m/Y H:i') }}</div>
        </div>
        @if($application->service_start_date)
        <div class="info-row">
            <div class="info-label">Service Start Date:</div>
            <div class="info-value">{{ $application->service_start_date->format('d/m/Y') }}</div>
        </div>
        @endif
    </div>

    <!-- Applicant Information -->
    <div class="section">
        <div class="section-title">Applicant Information</div>
        <div class="info-row">
            <div class="info-label">Name:</div>
            <div class="info-value">{{ $application->user->name }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Email:</div>
            <div class="info-value">{{ $application->user->email }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Phone:</div>
            <div class="info-value">{{ $application->user->phone ?? 'N/A' }}</div>
        </div>
        @if($application->user->profile)
        <div class="info-row">
            <div class="info-label">Date of Birth:</div>
            <div class="info-value">{{ $application->user->profile->dob ? $application->user->profile->dob->format('d/m/Y') : 'N/A' }}</div>
        </div>
        <div class="info-row">
            <div class="info-label">Gender:</div>
            <div class="info-value">{{ ucfirst($application->user->profile->gender ?? 'N/A') }}</div>
        </div>
        @endif
    </div>

    <!-- Form Data -->
    @if($application->form_data && count($application->form_data) > 0)
    <div class="section">
        <div class="section-title">Application Form Data</div>
        @foreach($application->form_data as $key => $value)
            @if(!empty($value))
            <div class="info-row">
                <div class="info-label">{{ ucfirst(str_replace('_', ' ', $key)) }}:</div>
                <div class="info-value">
                    @if(is_array($value))
                        {{ implode(', ', $value) }}
                    @else
                        {{ $value }}
                    @endif
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif

    <!-- Documents -->
    @if($application->documents && $application->documents->count() > 0)
    <div class="section">
        <div class="section-title">Submitted Documents</div>
        <table>
            <thead>
                <tr>
                    <th>Document Type</th>
                    <th>Original Filename</th>
                    <th>Status</th>
                    <th>Upload Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach($application->documents as $document)
                <tr>
                    <td>{{ $document->document_type }}</td>
                    <td>{{ $document->original_filename }}</td>
                    <td>
                        <span class="status-badge status-{{ $document->status }}">
                            {{ ucfirst($document->status) }}
                        </span>
                    </td>
                    <td>{{ $document->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Notes -->
    @if($application->notes)
    <div class="section">
        <div class="section-title">Additional Notes</div>
        <p>{{ $application->notes }}</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>This is an automatically generated document from {{ config('app.name') }}</p>
        <p>Generated on {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
