<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    protected $table = 'meter_readings';

    protected $fillable = [
        'meter_id',
        'reading_date',
        'value',
        'image_url',
        'taken_by',
        'note',
    ];

    protected $casts = [
        'reading_date' => 'date',
        'value' => 'decimal:3',
    ];

    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }

    public function takenBy()
    {
        return $this->belongsTo(User::class, 'taken_by');
    }
}