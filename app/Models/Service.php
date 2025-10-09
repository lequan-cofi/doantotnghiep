<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;

class Service extends Model
{
    use HasSoftDeletesWithUser;

    protected $fillable = [
        'key_code',
        'name',
        'pricing_type',
        'unit_label',
        'description',
    ];

    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

    public function leaseServices()
    {
        return $this->hasMany(LeaseService::class);
    }
}