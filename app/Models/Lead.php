<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'leads';

    protected $fillable = [
        'source',
        'name',
        'phone',
        'email',
        'desired_city',
        'budget_min',
        'budget_max',
        'note',
        'status',
        'deleted_by',
    ];

    protected $casts = [
        'budget_min' => 'decimal:2',
        'budget_max' => 'decimal:2',
    ];

    /**
     * Get the viewings for the lead.
     */
    public function viewings()
    {
        return $this->hasMany(Viewing::class);
    }

    /**
     * Get the booking deposits for the lead.
     */
    public function bookingDeposits()
    {
        return $this->hasMany(BookingDeposit::class);
    }
}

