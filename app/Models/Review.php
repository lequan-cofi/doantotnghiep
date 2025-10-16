<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser, BelongsToOrganization;

    protected $fillable = [
        'organization_id',
        'unit_id',
        'lease_id',
        'tenant_id',
        'overall_rating',
        'location_rating',
        'quality_rating',
        'service_rating',
        'price_rating',
        'title',
        'content',
        'highlights',
        'images',
        'recommend',
        'helpful_count',
        'view_count',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'overall_rating' => 'decimal:1',
        'location_rating' => 'decimal:1',
        'quality_rating' => 'decimal:1',
        'service_rating' => 'decimal:1',
        'price_rating' => 'decimal:1',
        'highlights' => 'array',
        'images' => 'array',
        'helpful_count' => 'integer',
        'view_count' => 'integer',
    ];

    /**
     * Get the organization that owns the review.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the unit that was reviewed.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the lease associated with the review.
     */
    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    /**
     * Get the tenant who wrote the review.
     */
    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    /**
     * Get the replies for the review.
     */
    public function replies()
    {
        return $this->hasMany(ReviewReply::class)->whereNull('parent_reply_id');
    }

    /**
     * Get all replies (including nested) for the review.
     */
    public function allReplies()
    {
        return $this->hasMany(ReviewReply::class);
    }

    /**
     * Get the count of replies for the review.
     */
    public function getRepliesCountAttribute()
    {
        return $this->allReplies()->count();
    }

    /**
     * Get the user who deleted the review.
     */
    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Scope to get only published reviews.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get reviews by specific tenant.
     */
    public function scopeByTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get average of detail ratings.
     */
    public function getAverageDetailRatingAttribute()
    {
        $ratings = array_filter([
            $this->location_rating,
            $this->quality_rating,
            $this->service_rating,
            $this->price_rating,
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : null;
    }


    /**
     * Check if review can be deleted by user.
     */
    public function canBeDeletedBy($user)
    {
        // Only tenant can delete their own review
        return $this->tenant_id === $user->id;
    }

    /**
     * Check if agent can access this review.
     */
    public function canBeAccessedByAgent($user)
    {
        // Check if the agent has access to the property
        $assignedPropertyIds = $user->assignedProperties()->pluck('properties.id')->toArray();
        return in_array($this->unit->property_id, $assignedPropertyIds);
    }

    /**
     * Get Vietnamese status label.
     */
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'published' => 'Đã đăng',
            'hidden' => 'Đã ẩn',
            default => ucfirst($this->status)
        };
    }

    /**
     * Get Vietnamese recommend label.
     */
    public function getRecommendLabelAttribute()
    {
        return match($this->recommend) {
            'yes' => 'Có, tôi sẽ giới thiệu',
            'maybe' => 'Có thể',
            'no' => 'Không',
            default => 'Chưa chọn'
        };
    }
}
