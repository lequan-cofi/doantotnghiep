<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollItem extends Model
{
    protected $table = 'payroll_items';

    public $timestamps = true;

    protected $fillable = [
        'payroll_cycle_id',
        'user_id',
        'item_type',
        'sign',
        'amount',
        'ref_type',
        'ref_id',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'sign' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the payroll cycle that owns the item.
     */
    public function payrollCycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    /**
     * Get the user that owns the item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

