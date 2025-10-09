<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'units';

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
        'deleted_by',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'base_rent' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function leases()
    {
        return $this->hasMany(Lease::class);
    }
}
