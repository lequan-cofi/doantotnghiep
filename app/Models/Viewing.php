<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Viewing extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'viewings';

    protected $fillable = [
        'lead_id',
        'listing_id',
        'agent_id',
        'unit_id',
        'lead_name',
        'lead_phone',
        'lead_email',
        'schedule_at',
        'status',
        'result_note',
        'note',
        'deleted_by',
    ];

    protected $casts = [
        'schedule_at' => 'datetime',
    ];

    /**
     * Get the lead user for the viewing.
     */
    public function lead()
    {
        return $this->belongsTo(User::class, 'lead_id');
    }

    /**
     * Get the property for the viewing.
     */
    public function property()
    {
        return $this->belongsTo(Property::class, 'listing_id');
    }

    /**
     * Get the unit for the viewing.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Get the agent for the viewing.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the user who deleted the viewing.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope for filtering by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope for filtering by agent
     */
    public function scopeByAgent($query, $agentId)
    {
        return $query->where('agent_id', $agentId);
    }

    /**
     * Scope for filtering by property
     */
    public function scopeByProperty($query, $propertyId)
    {
        return $query->where('listing_id', $propertyId);
    }

    /**
     * Scope for upcoming viewings
     */
    public function scopeUpcoming($query)
    {
        return $query->where('schedule_at', '>=', now())
                    ->whereIn('status', ['requested', 'confirmed']);
    }

    /**
     * Scope for today's viewings
     */
    public function scopeToday($query)
    {
        return $query->whereDate('schedule_at', today());
    }

    /**
     * Check if viewing is confirmed
     */
    public function isConfirmed()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if viewing is completed
     */
    public function isCompleted()
    {
        return in_array($this->status, ['done', 'no_show']);
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'requested' => 'badge-warning',
            'confirmed' => 'badge-info',
            'done' => 'badge-success',
            'no_show' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-light'
        };
    }

    /**
     * Get status text in Vietnamese
     */
    public function getStatusText()
    {
        return match($this->status) {
            'requested' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'done' => 'Hoàn thành',
            'no_show' => 'Không đến',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }
}

