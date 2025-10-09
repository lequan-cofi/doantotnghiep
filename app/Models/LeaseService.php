<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaseService extends Model
{
    protected $table = 'lease_services';

    protected $fillable = [
        'lease_id',
        'service_id',
        'price',
        'meta_json',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'meta_json' => 'array',
    ];

    public $timestamps = false;

    public function lease()
    {
        return $this->belongsTo(Lease::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}