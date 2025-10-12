<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'leads';

    protected $fillable = [
        'tenant_id',
        'source',
        'name',
        'phone',
        'email',
        'desired_city',
        'budget_min',
        'budget_max',
        'note',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    /**
     * Get the viewings for the lead.
     */
    public function viewings()
    {
        return $this->hasMany(Viewing::class);
    }

    /**
     * Get the booking deposits for the lead.
     */
    public function bookingDeposits()
    {
        return $this->hasMany(BookingDeposit::class);
    }

    /**
     * Get the tenant user associated with this lead.
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the leases created from this lead.
     */
    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    /**
     * Check if lead has been converted to a user account
     */
    public function hasUserAccount()
    {
        return $this->tenant_id !== null || $this->leases()->whereNotNull('tenant_id')->exists();
    }

    /**
     * Get the user account if lead has been converted
     */
    public function getUserAccount()
    {
        // First check if lead has direct tenant_id
        if ($this->tenant_id) {
            return $this->tenant;
        }
        
        // Fallback to check through leases
        $lease = $this->leases()->whereNotNull('tenant_id')->first();
        return $lease ? $lease->tenant : null;
    }

    /**
     * Check if lead is linked to a tenant user
     */
    public function isLinkedToTenant()
    {
        return $this->tenant_id !== null;
    }
}

