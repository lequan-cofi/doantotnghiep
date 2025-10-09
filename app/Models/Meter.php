<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;

class Meter extends Model
{
    use HasSoftDeletesWithUser;

    protected $fillable = [
        'property_id',
        'unit_id',
        'service_id',
        'serial_no',
        'installed_at',
        'status',
    ];

    protected $casts = [
        'installed_at' => 'date',
        'status' => 'boolean',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function readings()
    {
        return $this->hasMany(MeterReading::class);
    }
}