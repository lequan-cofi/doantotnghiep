<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionEventSplit extends Model
{
    protected $table = 'commission_event_splits';

    public $timestamps = false;

    protected $fillable = [
        'event_id',
        'user_id',
        'role_key',
        'percent_share',
        'amount',
        'payroll_item_id',
        'status',
    ];

    protected $casts = [
        'percent_share' => 'decimal:2',
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    /**
     * Get the event that owns the split.
     */
    public function event()
    {
        return $this->belongsTo(CommissionEvent::class, 'event_id');
    }

    /**
     * Get the user that owns the split.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payroll item for the split.
     */
    public function payrollItem()
    {
        return $this->belongsTo(PayrollItem::class, 'payroll_item_id');
    }
}

