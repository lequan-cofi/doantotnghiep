<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viewing extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'viewings';

    protected $fillable = [
        'lead_id',
        'listing_id',
        'agent_id',
        'schedule_at',
        'status',
        'result_note',
        'deleted_by',
    ];

    protected $casts = [
        'schedule_at' => 'datetime',
    ];

    /**
     * Get the lead for the viewing.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the listing for the viewing.
     */
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    /**
     * Get the agent for the viewing.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}

