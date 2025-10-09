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
        'agent_id',
        'start_date',
        'end_date',
        'rent_amount',
        'deposit_amount',
        'billing_day',
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
}
