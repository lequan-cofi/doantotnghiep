<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser, BelongsToOrganization;

    protected $table = 'invoices';

    protected $fillable = [
        'organization_id',
        'is_auto_created',
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
        'is_auto_created' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // Automatically set organization_id when creating invoice
        static::creating(function ($invoice) {
            if (!$invoice->organization_id) {
                // Try to get organization_id from lease
                if ($invoice->lease_id) {
                    $lease = \App\Models\Lease::find($invoice->lease_id);
                    if ($lease && $lease->organization_id) {
                        $invoice->organization_id = $lease->organization_id;
                    }
                }
                
                // Try to get organization_id from booking deposit
                if (!$invoice->organization_id && $invoice->booking_deposit_id) {
                    $bookingDeposit = \App\Models\BookingDeposit::find($invoice->booking_deposit_id);
                    if ($bookingDeposit && $bookingDeposit->organization_id) {
                        $invoice->organization_id = $bookingDeposit->organization_id;
                    }
                }
                
                // If still no organization_id, try to get from authenticated user
                if (!$invoice->organization_id && auth()->check()) {
                    $user = auth()->user();
                    $userOrganization = $user->organizations()->first();
                    if ($userOrganization) {
                        $invoice->organization_id = $userOrganization->id;
                    }
                }
            }
        });
    }

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

    /**
     * Check if invoice was created automatically (from lease or booking deposit)
     */
    public function isAutoCreated()
    {
        return $this->is_auto_created;
    }

    /**
     * Get the source type of auto-created invoice
     */
    public function getAutoCreatedSource()
    {
        if ($this->booking_deposit_id) {
            return 'booking_deposit';
        } elseif ($this->lease_id) {
            return 'lease';
        }
        return null;
    }

    /**
     * Get human-readable description of auto-created source
     */
    public function getAutoCreatedDescription()
    {
        $source = $this->getAutoCreatedSource();
        
        switch ($source) {
            case 'booking_deposit':
                return 'Hóa đơn đặt cọc được tạo tự động';
            case 'lease':
                return 'Hóa đơn hợp đồng thuê được tạo tự động';
            default:
                return 'Hóa đơn được tạo thủ công';
        }
    }

    /**
     * Generate a unique invoice number
     * This method is thread-safe and prevents duplicate invoice numbers
     */
    public static function generateInvoiceNumber()
    {
        $year = date('Y');
        $month = date('m');
        
        // Use a more robust approach with database sequence
        return DB::transaction(function () use ($year, $month) {
            // Get the next sequence number for this month
            $sequenceKey = "invoice_sequence_{$year}_{$month}";
            
            // Try to get the current sequence value
            $currentSequence = DB::table('invoice_sequences')
                ->where('sequence_key', $sequenceKey)
                ->lockForUpdate()
                ->value('current_value');
            
            if ($currentSequence === null) {
                // First time using sequence for this month, find the highest existing invoice number
                $existingInvoices = static::where('invoice_no', 'like', "HD-{$year}{$month}-%")
                    ->get();
                
                $maxNumber = 0;
                foreach ($existingInvoices as $invoice) {
                    $parts = explode('-', $invoice->invoice_no);
                    if (count($parts) >= 3) {
                        $number = (int) $parts[2];
                        if ($number > $maxNumber) {
                            $maxNumber = $number;
                        }
                    }
                }
                
                $currentSequence = $maxNumber;
                DB::table('invoice_sequences')->insert([
                    'sequence_key' => $sequenceKey,
                    'current_value' => $maxNumber,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            
            // Increment the sequence
            $newSequence = $currentSequence + 1;
            DB::table('invoice_sequences')
                ->where('sequence_key', $sequenceKey)
                ->update(['current_value' => $newSequence]);
            
            // Generate the invoice number
            return "HD-{$year}{$month}-" . str_pad($newSequence, 4, '0', STR_PAD_LEFT);
        });
    }
}

