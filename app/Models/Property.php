<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;

class Property extends Model
{
    use HasSoftDeletesWithUser, BelongsToOrganization;
    protected $table = 'properties';

    protected $fillable = [
        'organization_id',
        'owner_id',
        'property_type_id',
        'name',
        'location_id',
        'location_id_2025',
        'description',
        'images',
        'total_floors',
        'total_rooms',
        'status',
        'prop_payment_cycle',
        'prop_payment_day',
        'prop_payment_notes',
        'prop_custom_months',
        'deleted_by',
    ];

    protected $casts = [
        'status' => 'integer',
        'total_floors' => 'integer',
        'total_rooms' => 'integer',
        'images' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->prop_custom_months !== null) {
                if ($model->prop_custom_months < 1 || $model->prop_custom_months > 60) {
                    throw new \InvalidArgumentException('Số tháng tùy chỉnh phải từ 1 đến 60.');
                }
            }
        });
    }

    // Relationships
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function propertyType()
    {
        return $this->belongsTo(PropertyType::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function location2025()
    {
        return $this->belongsTo(Location2025::class, 'location_id_2025');
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'properties_user', 'property_id', 'user_id')
            ->withPivot('role_key', 'assigned_at', 'updated_by', 'deleted_by')
            ->withTimestamps()
            ->whereNull('properties_user.deleted_at');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeWithType($query, $typeId)
    {
        return $query->where('property_type_id', $typeId);
    }

    // Helper methods for occupancy calculation
    public function getOccupiedUnitsCount()
    {
        // Count units that have active leases instead of units.status
        return $this->units()->whereHas('leases', function($query) {
            $query->where('status', 'active')->whereNull('deleted_at');
        })->count();
    }

    public function getAvailableUnitsCount()
    {
        // Available = total - occupied - reserved - maintenance
        $total = $this->getTotalUnitsCount();
        $occupied = $this->getOccupiedUnitsCount();
        $reserved = $this->getReservedUnitsCount();
        $maintenance = $this->getMaintenanceUnitsCount();
        
        return max(0, $total - $occupied - $reserved - $maintenance);
    }

    public function getReservedUnitsCount()
    {
        // Count units that have pending leases
        return $this->units()->whereHas('leases', function($query) {
            $query->where('status', 'pending')->whereNull('deleted_at');
        })->count();
    }

    public function getMaintenanceUnitsCount()
    {
        return $this->units()->where('status', 'maintenance')->count();
    }

    public function getTotalUnitsCount()
    {
        return $this->units()->count();
    }

    public function getOccupancyRate()
    {
        $totalUnits = $this->getTotalUnitsCount();
        if ($totalUnits == 0) {
            return 0;
        }
        
        $occupiedUnits = $this->getOccupiedUnitsCount();
        return round(($occupiedUnits / $totalUnits) * 100, 2);
    }

    public function getOccupancyRateByTotalRooms()
    {
        if ($this->total_rooms == 0) {
            return 0;
        }
        
        $occupiedUnits = $this->getOccupiedUnitsCount();
        return round(($occupiedUnits / $this->total_rooms) * 100, 2);
    }

    /**
     * Get full address string from location (backward compatible)
     */
    public function getFullAddressAttribute()
    {
        if (!$this->location) {
            return 'Địa chỉ chưa cập nhật';
        }

        $addressParts = [];
        
        if ($this->location->street) {
            $addressParts[] = $this->location->street;
        }
        
        if ($this->location->ward) {
            $addressParts[] = $this->location->ward;
        }
        
        if ($this->location->district) {
            $addressParts[] = $this->location->district;
        }
        
        if ($this->location->city) {
            $addressParts[] = $this->location->city;
        }
        
        if ($this->location->country && $this->location->country !== 'Vietnam') {
            $addressParts[] = $this->location->country;
        }

        return !empty($addressParts) ? implode(', ', $addressParts) : 'Địa chỉ chưa cập nhật';
    }

    // Accessor for formatted occupancy rate
    public function getFormattedOccupancyRateAttribute()
    {
        return $this->getOccupancyRate() . '%';
    }

    // Accessor for occupancy status
    public function getOccupancyStatusAttribute()
    {
        $rate = $this->getOccupancyRate();
        
        if ($rate >= 90) {
            return 'full';
        } elseif ($rate >= 70) {
            return 'high';
        } elseif ($rate >= 50) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    // Accessor for occupancy status text
    public function getOccupancyStatusTextAttribute()
    {
        $status = $this->getOccupancyStatusAttribute();
        
        return match($status) {
            'full' => 'Đầy',
            'high' => 'Cao',
            'medium' => 'Trung bình',
            'low' => 'Thấp',
            default => 'Không xác định'
        };
    }

    // Accessor for old address (location)
    public function getOldAddressAttribute()
    {
        if (!$this->location) {
            return 'Chưa có địa chỉ cũ';
        }

        $parts = [];
        
        if ($this->location->street) {
            $parts[] = $this->location->street;
        }
        
        if ($this->location->ward) {
            $parts[] = $this->location->ward;
        }
        
        if ($this->location->district) {
            $parts[] = $this->location->district;
        }
        
        if ($this->location->city) {
            $parts[] = $this->location->city;
        }

        return empty($parts) ? 'Chưa có địa chỉ cũ' : implode(', ', $parts);
    }

    // Accessor for new address (location2025)
    public function getNewAddressAttribute()
    {
        if (!$this->location2025) {
            return 'Chưa có địa chỉ mới';
        }

        $parts = [];
        
        if ($this->location2025->street) {
            $parts[] = $this->location2025->street;
        }
        
        if ($this->location2025->ward) {
            $parts[] = $this->location2025->ward;
        }
        
        if ($this->location2025->district) {
            $parts[] = $this->location2025->district;
        }
        
        if ($this->location2025->city) {
            $parts[] = $this->location2025->city;
        }

        return empty($parts) ? 'Chưa có địa chỉ mới' : implode(', ', $parts);
    }

    // Accessor for owner name
    public function getOwnerNameAttribute()
    {
        return $this->owner ? $this->owner->full_name : 'Chưa có thông tin chủ trọ';
    }
}

