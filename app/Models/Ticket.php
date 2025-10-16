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
        'image',
        'priority',
        'status',
        'cancelled_at',
        'cancelled_by',
    ];

    protected $casts = [
        'priority' => 'string',
        'status' => 'string',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Get the image URL
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        return null;
    }

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

    /**
     * Get Vietnamese status label
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'open' => 'Đang mở',
            'in_progress' => 'Đang xử lý',
            'resolved' => 'Đã giải quyết',
            'closed' => 'Đã đóng',
            'cancelled' => 'Đã hủy',
            default => ucfirst(str_replace('_', ' ', $this->status))
        };
    }

    /**
     * Get Vietnamese priority label
     */
    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'low' => 'Thấp',
            'medium' => 'Trung bình',
            'high' => 'Cao',
            'urgent' => 'Khẩn cấp',
            default => ucfirst($this->priority)
        };
    }
}