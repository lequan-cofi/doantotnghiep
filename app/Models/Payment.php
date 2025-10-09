<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser;

    protected $table = 'payments';
    
    public $timestamps = false;

    protected $fillable = [
        'invoice_id',
        'method_id',
        'amount',
        'paid_at',
        'txn_ref',
        'status',
        'payer_user_id',
        'attachment_url',
        'note',
        'deleted_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the invoice that owns the payment.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the payment method for the payment.
     */
    public function method()
    {
        return $this->belongsTo(PaymentMethod::class, 'method_id');
    }

    /**
     * Get the payer user for the payment.
     */
    public function payerUser()
    {
        return $this->belongsTo(User::class, 'payer_user_id');
    }
}

