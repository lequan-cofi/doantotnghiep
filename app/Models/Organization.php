<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'organizations';

    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
        'tax_code',
        'address',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    /**
     * Get the users for the organization.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'organization_users')
            ->withPivot('role_id', 'status')
            ->withTimestamps();
    }

    /**
     * Get the properties for the organization.
     */
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    /**
     * Get the salary contracts for the organization.
     */
    public function salaryContracts()
    {
        return $this->hasMany(SalaryContract::class);
    }

    /**
     * Get the commission policies for the organization.
     */
    public function commissionPolicies()
    {
        return $this->hasMany(CommissionPolicy::class);
    }

    /**
     * Scope a query to only include active organizations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}
