<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollPayslip extends Model
{
    protected $table = 'payroll_payslips';

    protected $fillable = [
        'payroll_cycle_id',
        'user_id',
        'gross_amount',
        'deduction_amount',
        'net_amount',
        'status',
        'paid_at',
        'payment_method',
        'note',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'deduction_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the payroll cycle that owns the payslip.
     */
    public function payrollCycle()
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    /**
     * Get the user that owns the payslip.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

