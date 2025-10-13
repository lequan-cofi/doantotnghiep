<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lease extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser, BelongsToOrganization;

    protected $table = 'leases';

    protected $fillable = [
        'organization_id',
        'unit_id',
        'tenant_id',
        'lead_id',
        'agent_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'billing_day',
        'lease_payment_cycle',
        'lease_payment_day',
        'lease_payment_notes',
        'lease_custom_months',
        'status',
        'contract_no',
        'signed_at',
        'deleted_by',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->lease_custom_months !== null) {
                if ($model->lease_custom_months < 1 || $model->lease_custom_months > 60) {
                    throw new \InvalidArgumentException('Số tháng tùy chỉnh phải từ 1 đến 60.');
                }
            }
        });
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function residents()
    {
        return $this->hasMany(LeaseResident::class);
    }

    public function services()
    {
        return $this->hasMany(LeaseService::class);
    }

    public function leaseServices()
    {
        return $this->hasMany(LeaseService::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function bookingDeposits()
    {
        return $this->hasMany(BookingDeposit::class);
    }

    public function commissionEvents()
    {
        return $this->hasMany(CommissionEvent::class);
    }

    /**
     * Get tenant information - either from User or Lead
     */
    public function getTenantInfo()
    {
        if ($this->tenant_id) {
            return [
                'type' => 'user',
                'id' => $this->tenant_id,
                'name' => $this->tenant->full_name ?? $this->tenant->name ?? 'N/A',
                'phone' => $this->tenant->phone ?? 'N/A',
                'email' => $this->tenant->email ?? 'N/A',
            ];
        } elseif ($this->lead_id) {
            return [
                'type' => 'lead',
                'id' => $this->lead_id,
                'name' => $this->lead->name ?? 'N/A',
                'phone' => $this->lead->phone ?? 'N/A',
                'email' => $this->lead->email ?? 'N/A',
            ];
        }
        
        return null;
    }

    /**
     * Check if lease is created from lead (not yet converted to user)
     */
    public function isFromLead()
    {
        return $this->lead_id && !$this->tenant_id;
    }

    /**
     * Check if lease has tenant user account
     */
    public function hasTenantAccount()
    {
        return $this->tenant_id !== null;
    }
}
