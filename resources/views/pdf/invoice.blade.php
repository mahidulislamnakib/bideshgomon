<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #333;
            padding: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            font-size: 28px;
            color: #111;
            margin-bottom: 5px;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .meta-section {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }
        .meta-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }
        .meta-value {
            font-size: 12px;
            color: #111;
        }
        .bill-to {
            background: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .bill-to-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
        }
        .client-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .client-details {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        th {
            background: #f3f4f6;
            padding: 10px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            border-bottom: 1px solid #e5e7eb;
        }
        th:last-child {
            text-align: right;
        }
        td {
            padding: 12px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 12px;
        }
        td:last-child {
            text-align: right;
        }
        .totals {
            width: 300px;
            margin-left: auto;
        }
        .totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-row.total {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 2px solid #111;
            padding: 12px 0;
        }
        .totals-row.paid {
            color: #059669;
        }
        .totals-row.balance {
            color: #dc2626;
        }
        .notes {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
        }
        .notes-title {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
        }
        .notes-content {
            font-size: 11px;
            color: #666;
            line-height: 1.6;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1d4ed8; }
        .status-paid { background: #d1fae5; color: #059669; }
        .status-partial { background: #fef3c7; color: #d97706; }
        .status-overdue { background: #fee2e2; color: #dc2626; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }
        .paid-stamp {
            color: #059669;
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
            border: 3px solid #059669;
            padding: 10px 20px;
            display: inline-block;
            transform: rotate(-5deg);
            margin-top: 20px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">BideshGomon</div>
        <div class="invoice-title">
            <h1>INVOICE</h1>
            <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            <div style="margin-top: 10px;">
                <span class="status-badge status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
            </div>
        </div>
    </div>

    <div class="meta-section">
        <div class="meta-column">
            <div class="meta-label">Issue Date</div>
            <div class="meta-value">{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</div>
        </div>
        <div class="meta-column" style="text-align: right;">
            <div class="meta-label">Due Date</div>
            <div class="meta-value">{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="bill-to">
        <div class="bill-to-title">Bill To</div>
        <div class="client-name">{{ $invoice->client_name ?: ($invoice->client->name ?? 'N/A') }}</div>
        <div class="client-details">
            @if($invoice->client_email || $invoice->client?->email)
                {{ $invoice->client_email ?: $invoice->client->email }}<br>
            @endif
            @if($invoice->client_phone || $invoice->client?->phone)
                {{ $invoice->client_phone ?: $invoice->client->phone }}<br>
            @endif
            @if($invoice->client_address || $invoice->client?->address)
                {{ $invoice->client_address ?: $invoice->client->address }}
            @endif
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 50%;">Description</th>
                <th style="width: 15%; text-align: center;">Qty</th>
                <th style="width: 15%; text-align: right;">Rate</th>
                <th style="width: 20%; text-align: right;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td style="text-align: center;">{{ $item->quantity }}</td>
                <td style="text-align: right;">৳{{ number_format($item->unit_price, 2) }}</td>
                <td style="text-align: right;">৳{{ number_format($item->amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="totals-row">
            <span>Subtotal</span>
            <span>৳{{ number_format($invoice->subtotal, 2) }}</span>
        </div>
        @if($invoice->tax_amount > 0)
        <div class="totals-row">
            <span>VAT ({{ $invoice->tax_rate }}%)</span>
            <span>৳{{ number_format($invoice->tax_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->discount_amount > 0)
        <div class="totals-row">
            <span>Discount</span>
            <span>-৳{{ number_format($invoice->discount_amount, 2) }}</span>
        </div>
        @endif
        <div class="totals-row total">
            <span>Total</span>
            <span>৳{{ number_format($invoice->total_amount, 2) }}</span>
        </div>
        @if($invoice->paid_amount > 0)
        <div class="totals-row paid">
            <span>Paid</span>
            <span>-৳{{ number_format($invoice->paid_amount, 2) }}</span>
        </div>
        @endif
        @if($invoice->total_amount - $invoice->paid_amount > 0)
        <div class="totals-row balance">
            <span>Balance Due</span>
            <span>৳{{ number_format($invoice->total_amount - $invoice->paid_amount, 2) }}</span>
        </div>
        @endif
    </div>

    @if($invoice->status === 'paid')
    <div style="text-align: center; margin-top: 30px;">
        <div class="paid-stamp">✓ Paid in Full</div>
    </div>
    @endif

    @if($invoice->notes)
    <div class="notes">
        <div class="notes-title">Notes</div>
        <div class="notes-content">{{ $invoice->notes }}</div>
    </div>
    @endif

    @if($invoice->terms)
    <div class="notes" style="margin-top: 20px;">
        <div class="notes-title">Terms & Conditions</div>
        <div class="notes-content">{{ $invoice->terms }}</div>
    </div>
    @endif

    <div class="footer">
        <p>Thank you for your business!</p>
        <p style="margin-top: 5px;">BideshGomon - Your Trusted Migration Partner</p>
    </div>
</body>
</html>
