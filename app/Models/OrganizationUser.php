<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationUser extends Model
{
    protected $table = 'organization_users';

    protected $fillable = [
        'organization_id',
        'user_id',
        'role_id',
        'status',
    ];

    /**
     * Get the organization.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role.
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}

