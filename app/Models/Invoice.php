<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser, BelongsToOrganization;

    protected $table = 'invoices';

    protected $fillable = [
        'organization_id',
        'lease_id',
        'booking_deposit_id',
        'invoice_no',
        'issue_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'currency',
        'note',
        'deleted_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    /**
     * Get the organization that owns the invoice.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the lease that owns the invoice.
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the booking deposit that owns the invoice.
     */
    public function bookingDeposit()
    {
        return $this->belongsTo(BookingDeposit::class, 'booking_deposit_id');
    }

    /**
     * Get the invoice items for the invoice.
     */
    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the payments for the invoice.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the total paid amount for this invoice.
     */
    public function getPaidAmountAttribute()
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get the remaining amount for this invoice.
     */
    public function getRemainingAmountAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }

    /**
     * Check if invoice is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->paid_amount >= $this->total_amount;
    }

    /**
     * Check if invoice is overdue.
     */
    public function isOverdue()
    {
        return $this->due_date < now() && !$this->isFullyPaid() && $this->status !== 'cancelled';
    }

    /**
     * Scope to get invoices by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid')
                    ->where('status', '!=', 'cancelled');
    }

    /**
     * Scope to get invoices for a specific lease.
     */
    public function scopeForLease($query, $leaseId)
    {
        return $query->where('lease_id', $leaseId);
    }
}

