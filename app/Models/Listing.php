<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Listing extends Model
{
    use SoftDeletes;

    protected $table = 'listings';

    protected $fillable = [
        'unit_id',
        'title',
        'slug',
        'description',
        'price_display',
        'publish_status',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'price_display' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
