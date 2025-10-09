<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;

class Ticket extends Model
{
    use HasSoftDeletesWithUser, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'lease_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'priority',
        'status',
    ];

    protected $casts = [
        'priority' => 'string',
        'status' => 'string',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }
}