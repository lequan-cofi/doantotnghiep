<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDeposit extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'booking_deposits';

    protected $fillable = [
        'unit_id',
        'tenant_user_id',
        'lead_id',
        'amount',
        'invoice_id',
        'payment_status',
        'hold_until',
        'deleted_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'hold_until' => 'datetime',
    ];

    /**
     * Get the unit for the booking deposit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tenant user for the booking deposit.
     */
    public function tenantUser()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Get the lead for the booking deposit.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the invoice for the booking deposit.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}

