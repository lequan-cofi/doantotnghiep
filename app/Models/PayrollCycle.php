<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollCycle extends Model
{
    protected $table = 'payroll_cycles';

    public $timestamps = true;

    protected $fillable = [
        'organization_id',
        'period_month',
        'status',
        'locked_at',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'locked_at' => 'datetime',
        'paid_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * Get the organization that owns the payroll cycle.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the payroll items for the cycle.
     */
    public function items()
    {
        return $this->hasMany(PayrollItem::class, 'payroll_cycle_id');
    }

    /**
     * Get the payslips for the cycle.
     */
    public function payslips()
    {
        return $this->hasMany(PayrollPayslip::class, 'payroll_cycle_id');
    }
}

