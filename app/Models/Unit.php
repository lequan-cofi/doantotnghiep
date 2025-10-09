<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;

class Unit extends Model
{
    use HasSoftDeletesWithUser;

    protected $fillable = [
        'property_id',
        'code',
        'floor',
        'area_m2',
        'unit_type',
        'base_rent',
        'deposit_amount',
        'max_occupancy',
        'status',
        'note',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'base_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'max_occupancy' => 'integer',
        'floor' => 'integer',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class);
    }

    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'unit_amenities');
    }
}