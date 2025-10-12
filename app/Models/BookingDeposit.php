<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasSoftDeletesWithUser;
use App\Traits\BelongsToOrganization;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDeposit extends Model
{
    use SoftDeletes, HasSoftDeletesWithUser, BelongsToOrganization;

    protected $table = 'booking_deposits';

    protected $fillable = [
        'organization_id',
        'unit_id',
        'tenant_user_id',
        'lead_id',
        'agent_id',
        'invoice_id',
        'amount',
        'payment_status',
        'deposit_type',
        'hold_until',
        'paid_at',
        'expired_at',
        'notes',
        'payment_details',
        'reference_number',
        'deleted_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'hold_until' => 'datetime',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'payment_details' => 'array',
    ];

    /**
     * Get the organization for the booking deposit.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the unit for the booking deposit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the tenant user for the booking deposit.
     */
    public function tenantUser()
    {
        return $this->belongsTo(User::class, 'tenant_user_id');
    }

    /**
     * Get the lead for the booking deposit.
     */
    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the agent for the booking deposit.
     */
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the invoice for the booking deposit.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the invoices for the booking deposit (hasMany relationship).
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'booking_deposit_id');
    }

    /**
     * Get the property through unit relationship.
     */
    public function property()
    {
        return $this->hasOneThrough(Property::class, Unit::class, 'id', 'id', 'unit_id', 'property_id');
    }

    /**
     * Scope for active deposits (not expired or cancelled).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('payment_status', ['pending', 'paid'])
                    ->where('hold_until', '>', now());
    }

    /**
     * Scope for expired deposits.
     */
    public function scopeExpired($query)
    {
        return $query->where(function($q) {
            $q->where('payment_status', 'expired')
              ->orWhere('hold_until', '<=', now());
        });
    }

    /**
     * Scope for deposits by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope for deposits by type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('deposit_type', $type);
    }

    /**
     * Check if deposit is expired.
     */
    public function isExpired()
    {
        return $this->hold_until <= now() || $this->payment_status === 'expired';
    }

    /**
     * Check if deposit is active.
     */
    public function isActive()
    {
        return in_array($this->payment_status, ['pending', 'paid']) && !$this->isExpired();
    }

    /**
     * Check if deposit can be refunded.
     */
    public function canBeRefunded()
    {
        return $this->payment_status === 'paid' && $this->isActive();
    }

    /**
     * Get tenant information - either from User or Lead.
     */
    public function getTenantInfo()
    {
        if ($this->tenant_user_id) {
            return [
                'type' => 'user',
                'id' => $this->tenant_user_id,
                'name' => $this->tenantUser->full_name ?? $this->tenantUser->name ?? 'N/A',
                'phone' => $this->tenantUser->phone ?? 'N/A',
                'email' => $this->tenantUser->email ?? 'N/A',
            ];
        } elseif ($this->lead_id) {
            return [
                'type' => 'lead',
                'id' => $this->lead_id,
                'name' => $this->lead->name ?? 'N/A',
                'phone' => $this->lead->phone ?? 'N/A',
                'email' => $this->lead->email ?? 'N/A',
            ];
        }
        
        return null;
    }

    /**
     * Generate reference number.
     */
    public static function generateReferenceNumber()
    {
        $prefix = 'BD';
        $date = date('Ymd');
        $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $random;
    }

    /**
     * Get status badge class.
     */
    public function getStatusBadgeClass()
    {
        return match($this->payment_status) {
            'pending' => 'badge-warning',
            'paid' => 'badge-success',
            'refunded' => 'badge-info',
            'expired' => 'badge-danger',
            'cancelled' => 'badge-secondary',
            default => 'badge-light'
        };
    }

    /**
     * Get status text in Vietnamese.
     */
    public function getStatusText()
    {
        return match($this->payment_status) {
            'pending' => 'Chờ thanh toán',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
            'expired' => 'Hết hạn',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    /**
     * Get deposit type text in Vietnamese.
     */
    public function getTypeText()
    {
        return match($this->deposit_type) {
            'booking' => 'Đặt cọc',
            'security' => 'Cọc an toàn',
            'advance' => 'Trả trước',
            default => 'Không xác định'
        };
    }
}

