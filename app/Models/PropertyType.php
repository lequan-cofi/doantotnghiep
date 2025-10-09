<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;

class PropertyType extends Model
{
    use HasSoftDeletesWithUser;
    protected $table = 'property_types';

    protected $fillable = [
        'key_code',
        'name',
        'icon',
        'description',
        'status',
        'deleted_by',
    ];

    public function properties()
    {
        return $this->hasMany(Property::class);
    }
}

