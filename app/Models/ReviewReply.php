<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReviewReply extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $fillable = [
        'review_id',
        'user_id',
        'parent_reply_id',
        'content',
        'user_type',
        'deleted_by',
    ];

    protected $casts = [
        'content' => 'string',
        'user_type' => 'string',
    ];

    /**
     * Get the review that this reply belongs to.
     */
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    /**
     * Get the user who wrote the reply.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent reply (for nested replies).
     */
    public function parentReply()
    {
        return $this->belongsTo(ReviewReply::class, 'parent_reply_id');
    }

    /**
     * Get child replies (nested replies).
     */
    public function childReplies()
    {
        return $this->hasMany(ReviewReply::class, 'parent_reply_id');
    }

    /**
     * Get the user who deleted the reply.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Get Vietnamese user type label.
     */
    public function getUserTypeLabelAttribute()
    {
        return match($this->user_type) {
            'tenant' => 'Người thuê',
            'manager' => 'Quản lý',
            'agent' => 'Nhân viên',
            'owner' => 'Chủ nhà',
            default => ucfirst($this->user_type)
        };
    }

    /**
     * Check if reply can be edited by user.
     */
    public function canBeEditedBy($user)
    {
        // Only reply author can edit within 24 hours
        if ($this->user_id === $user->id) {
            return $this->created_at->diffInHours(now()) <= 24;
        }
        return false;
    }

    /**
     * Check if reply can be deleted by user.
     */
    public function canBeDeletedBy($user)
    {
        // Reply author or review author can delete
        return $this->user_id === $user->id || $this->review->tenant_id === $user->id;
    }
}
