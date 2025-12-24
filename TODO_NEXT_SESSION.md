# Next Session TODO: Accounting & Invoice System

**Date Created:** December 24, 2025  
**Priority:** High  
**Estimated Time:** 2-3 sessions

---

## 🎯 Objective
Build a comprehensive Accounting Section with Invoice Generator for the BideshGomon platform.

---

## 📋 Planned Features

### 1. Invoice Generator Component
- [ ] `InvoiceBuilder.vue` - Visual invoice creator
- [ ] Service line items with quantity, rate, tax
- [ ] Bangladesh-specific formatting (৳ currency, DD/MM/YYYY dates)
- [ ] Auto-calculate subtotal, tax (VAT 15%), grand total
- [ ] Invoice numbering system (e.g., BG-2025-0001)
- [ ] Client/customer selection from database
- [ ] PDF export functionality
- [ ] Print-friendly layout

### 2. Invoice Management
- [ ] `InvoiceList.vue` - List all invoices with filters
- [ ] Status tracking (Draft, Sent, Paid, Overdue, Cancelled)
- [ ] Search by invoice #, client, date range
- [ ] Bulk actions (mark paid, send reminders)
- [ ] Invoice preview modal

### 3. Accounting Dashboard
- [ ] Revenue overview (daily, weekly, monthly, yearly)
- [ ] Outstanding invoices widget
- [ ] Payment collection stats
- [ ] Top clients by revenue
- [ ] Expense tracking summary

### 4. Payment Recording
- [ ] Record payments against invoices
- [ ] Partial payment support
- [ ] Payment methods (bKash, Nagad, Bank Transfer, Cash)
- [ ] Payment receipt generation
- [ ] Transaction history

### 5. Financial Reports
- [ ] Profit & Loss statement
- [ ] Revenue by service type
- [ ] Client payment history
- [ ] Aging report (overdue invoices)
- [ ] Export to Excel/CSV

---

## 🗂️ Database Schema (Proposed)

```sql
-- invoices table
invoices:
  - id
  - invoice_number (unique, e.g., BG-2025-0001)
  - client_id (foreign key to users)
  - issue_date
  - due_date
  - subtotal
  - tax_amount
  - tax_rate (default 15%)
  - discount_amount
  - total_amount
  - paid_amount
  - status (draft, sent, paid, partial, overdue, cancelled)
  - notes
  - terms
  - created_by
  - timestamps

-- invoice_items table
invoice_items:
  - id
  - invoice_id
  - service_id (optional, link to services)
  - description
  - quantity
  - unit_price
  - tax_rate
  - amount
  - timestamps

-- payments table
payments:
  - id
  - invoice_id
  - amount
  - payment_date
  - payment_method (bkash, nagad, bank, cash, card)
  - transaction_id
  - notes
  - received_by
  - timestamps
```

---

## 🎨 UI Components to Create

1. **InvoiceBuilder.vue** - Main invoice creation form
2. **InvoicePreview.vue** - Print/PDF preview
3. **InvoiceTable.vue** - List with advanced filters
4. **PaymentModal.vue** - Record payment dialog
5. **AccountingDashboard.vue** - Overview with charts
6. **RevenueChart.vue** - Revenue visualization
7. **InvoiceStatusBadge.vue** - Status indicators
8. **PaymentMethodSelector.vue** - Bangladesh payment methods

---

## 📁 File Structure

```
resources/js/
├── Pages/
│   └── Admin/
│       └── Accounting/
│           ├── Dashboard.vue
│           ├── Invoices/
│           │   ├── Index.vue
│           │   ├── Create.vue
│           │   ├── Edit.vue
│           │   └── Show.vue
│           ├── Payments/
│           │   └── Index.vue
│           └── Reports/
│               └── Index.vue
└── Components/
    └── Accounting/
        ├── InvoiceBuilder.vue
        ├── InvoicePreview.vue
        ├── InvoiceLineItem.vue
        ├── PaymentForm.vue
        └── RevenueChart.vue

app/
├── Http/Controllers/Admin/
│   ├── InvoiceController.php
│   ├── PaymentController.php
│   └── AccountingController.php
├── Models/
│   ├── Invoice.php
│   ├── InvoiceItem.php
│   └── Payment.php
└── Services/
    ├── InvoiceService.php
    └── AccountingService.php
```

---

## 🔗 Routes (Proposed)

```php
Route::prefix('admin/accounting')->name('admin.accounting.')->group(function () {
    Route::get('/', [AccountingController::class, 'dashboard'])->name('dashboard');
    
    Route::resource('invoices', InvoiceController::class);
    Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::post('invoices/{invoice}/duplicate', [InvoiceController::class, 'duplicate'])->name('invoices.duplicate');
    
    Route::resource('payments', PaymentController::class);
    
    Route::get('reports', [AccountingController::class, 'reports'])->name('reports');
    Route::get('reports/export', [AccountingController::class, 'export'])->name('reports.export');
});
```

---

## ✅ Session 60 Summary (Today)

Completed **60 UI/UX improvements** across 12 batches:
- Batch 11: ToggleSwitch, Banner, MetricCard, CommandPalette, Tooltip
- Batch 12: Popover, ToastContainer, DataTable, Countdown, Stepper

All components in `resources/js/Components/ui/` directory.

---

## 📝 Notes

- Use existing `useBangladeshFormat` composable for currency/dates
- Follow existing service layer pattern (like WalletService)
- Invoice PDF can use DomPDF or Browsershot
- Consider SMS/Email notifications for invoice reminders
- bKash/Nagad integration for online payments (future)

---

**Ready to start next session! 🚀**
