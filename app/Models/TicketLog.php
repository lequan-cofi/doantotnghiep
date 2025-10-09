<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketLog extends Model
{
    protected $table = 'ticket_logs';

    protected $fillable = [
        'ticket_id',
        'actor_id',
        'action',
        'detail',
        'cost_amount',
        'cost_note',
        'charge_to',
        'linked_invoice_id',
    ];

    protected $casts = [
        'cost_amount' => 'decimal:2',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function linkedInvoice()
    {
        return $this->belongsTo(Invoice::class, 'linked_invoice_id');
    }
}