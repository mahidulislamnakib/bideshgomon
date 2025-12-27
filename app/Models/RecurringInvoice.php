<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'created_by',
        'title',
        'description',
        'frequency',
        'frequency_interval',
        'start_date',
        'end_date',
        'next_invoice_date',
        'day_of_month',
        'total_occurrences',
        'occurrences_generated',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'items',
        'status',
        'send_automatically',
        'days_before_due',
        'notes',
        'terms',
        'last_generated_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'next_invoice_date' => 'date',
        'last_generated_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'items' => 'array',
        'send_automatically' => 'boolean',
    ];

    const STATUS_ACTIVE = 'active';

    const STATUS_PAUSED = 'paused';

    const STATUS_COMPLETED = 'completed';

    const STATUS_CANCELLED = 'cancelled';

    const FREQUENCY_WEEKLY = 'weekly';

    const FREQUENCY_BIWEEKLY = 'bi-weekly';

    const FREQUENCY_MONTHLY = 'monthly';

    const FREQUENCY_QUARTERLY = 'quarterly';

    const FREQUENCY_SEMIANNUAL = 'semi-annual';

    const FREQUENCY_ANNUAL = 'annual';

    /**
     * Get frequency options
     */
    public static function getFrequencies(): array
    {
        return [
            self::FREQUENCY_WEEKLY => 'Weekly',
            self::FREQUENCY_BIWEEKLY => 'Bi-Weekly',
            self::FREQUENCY_MONTHLY => 'Monthly',
            self::FREQUENCY_QUARTERLY => 'Quarterly',
            self::FREQUENCY_SEMIANNUAL => 'Semi-Annual',
            self::FREQUENCY_ANNUAL => 'Annual',
        ];
    }

    /**
     * Calculate next invoice date
     */
    public function calculateNextInvoiceDate(): ?\Carbon\Carbon
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }

        $lastDate = $this->last_generated_at ?? $this->start_date;
        $interval = $this->frequency_interval;

        switch ($this->frequency) {
            case self::FREQUENCY_WEEKLY:
                $nextDate = $lastDate->copy()->addWeeks($interval);
                break;
            case self::FREQUENCY_BIWEEKLY:
                $nextDate = $lastDate->copy()->addWeeks($interval * 2);
                break;
            case self::FREQUENCY_MONTHLY:
                $nextDate = $lastDate->copy()->addMonths($interval);
                if ($this->day_of_month) {
                    $nextDate->day = min($this->day_of_month, $nextDate->daysInMonth);
                }
                break;
            case self::FREQUENCY_QUARTERLY:
                $nextDate = $lastDate->copy()->addMonths($interval * 3);
                break;
            case self::FREQUENCY_SEMIANNUAL:
                $nextDate = $lastDate->copy()->addMonths($interval * 6);
                break;
            case self::FREQUENCY_ANNUAL:
                $nextDate = $lastDate->copy()->addYears($interval);
                break;
            default:
                $nextDate = $lastDate->copy()->addMonths($interval);
        }

        // Check if we've reached the end
        if ($this->end_date && $nextDate > $this->end_date) {
            return null;
        }

        if ($this->total_occurrences && $this->occurrences_generated >= $this->total_occurrences) {
            return null;
        }

        return $nextDate;
    }

    /**
     * Generate invoice from recurring template
     */
    public function generateInvoice(): ?Invoice
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return null;
        }

        $client = $this->client;

        $invoice = Invoice::create([
            'client_id' => $this->client_id,
            'created_by' => $this->created_by,
            'issue_date' => now(),
            'due_date' => now()->addDays($this->days_before_due),
            'subtotal' => $this->subtotal,
            'tax_rate' => $this->tax_rate,
            'tax_amount' => $this->tax_amount,
            'total_amount' => $this->total_amount,
            'status' => $this->send_automatically ? Invoice::STATUS_SENT : Invoice::STATUS_DRAFT,
            'notes' => $this->notes,
            'terms' => $this->terms,
            'client_name' => $client->name,
            'client_email' => $client->email,
            'client_phone' => $client->phone,
            'client_address' => $client->address,
        ]);

        // Create invoice items from stored items
        foreach ($this->items as $item) {
            $invoice->items()->create($item);
        }

        // Update recurring invoice
        $nextDate = $this->calculateNextInvoiceDate();

        $updateData = [
            'last_generated_at' => now(),
            'occurrences_generated' => $this->occurrences_generated + 1,
        ];

        if ($nextDate) {
            $updateData['next_invoice_date'] = $nextDate;
        } else {
            $updateData['status'] = self::STATUS_COMPLETED;
        }

        $this->update($updateData);

        return $invoice;
    }

    /**
     * Scope for active recurring invoices
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for due recurring invoices
     */
    public function scopeDue($query)
    {
        return $query->active()
            ->where('next_invoice_date', '<=', now());
    }

    // Relationships

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function generatedInvoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'recurring_invoice_id');
    }
}
