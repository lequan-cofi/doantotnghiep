<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\HasSoftDeletesWithUser;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany organizations()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany organizationUsers()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany userRoles()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany commissionEvents()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany commissionEventSplits()
 * @method \Illuminate\Database\Eloquent\Relations\BelongsToMany assignedProperties()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany salaryContracts()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne activeSalaryContract()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany leasesAsTenant()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany leasesAsAgent()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany viewingsAsAgent()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany payments()
 * @method \Illuminate\Database\Eloquent\Relations\HasMany bookingDepositsAsTenant()
 * @method \Illuminate\Database\Eloquent\Relations\HasOne userProfile()
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasSoftDeletesWithUser;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'status',
        'deleted_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'last_login_at' => 'datetime',
            'status' => 'integer',
        ];
    }

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the user's name (alias for full_name)
     */
    public function getNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Get the roles that belong to the user through organization_users.
     */
    public function organizationRoles($organizationId = null)
    {
        $query = $this->belongsToMany(Role::class, 'organization_users', 'user_id', 'role_id')
            ->withPivot('organization_id', 'status')
            ->withTimestamps();
        
        if ($organizationId) {
            $query->wherePivot('organization_id', $organizationId);
        }
        
        return $query;
    }

    /**
     * Alias for organizationRoles() for backward compatibility.
     */
    public function roles()
    {
        return $this->organizationRoles();
    }

    /**
     * Legacy method - redirects to organizationRoles for backward compatibility.
     */
    public function userRoles()
    {
        return $this->organizationRoles();
    }

    /**
     * Get the user's primary role.
     */
    public function primaryRole()
    {
        return $this->organizationRoles()->first();
    }

    /**
     * Get the salary contracts for the user.
     */
    public function salaryContracts()
    {
        return $this->hasMany(SalaryContract::class);
    }

    /**
     * Get the active salary contract for the user.
     */
    public function activeSalaryContract()
    {
        return $this->hasOne(SalaryContract::class)->where('status', 'active')->latest('effective_from');
    }

    /**
     * Get the properties assigned to the user.
     */
    public function assignedProperties()
    {
        return $this->belongsToMany(Property::class, 'properties_user')
            ->withPivot('role_key', 'assigned_at')
            ->withTimestamps();
    }

    /**
     * Get the commission events for the user.
     */
    public function commissionEvents()
    {
        return $this->hasMany(CommissionEvent::class);
    }

    /**
     * Get the organizations the user belongs to.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function organizations()
    {
        return $this->belongsToMany(Organization::class, 'organization_users')
            ->withPivot('role_id', 'status')
            ->withTimestamps();
    }

    /**
     * Get the organization users pivot records.
     */
    public function organizationUsers()
    {
        return $this->hasMany(\App\Models\OrganizationUser::class);
    }

    /**
     * Get the leases where user is tenant.
     */
    public function leasesAsTenant()
    {
        return $this->hasMany(Lease::class, 'tenant_id');
    }

    /**
     * Get the leases where user is agent.
     */
    public function leasesAsAgent()
    {
        return $this->hasMany(Lease::class, 'agent_id');
    }

    /**
     * Get the viewings where user is agent.
     */
    public function viewingsAsAgent()
    {
        return $this->hasMany(Viewing::class, 'agent_id');
    }

    /**
     * Get the payments made by user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'payer_user_id');
    }

    /**
     * Get the booking deposits where user is tenant.
     */
    public function bookingDepositsAsTenant()
    {
        return $this->hasMany(BookingDeposit::class, 'tenant_user_id');
    }

    /**
     * Get the salary advances for the user.
     */
    public function salaryAdvances()
    {
        return $this->hasMany(SalaryAdvance::class);
    }

    /**
     * Get the payslips for the user.
     */
    public function payslips()
    {
        return $this->hasMany(PayrollPayslip::class);
    }

    /**
     * Get the user's profile.
     */
    public function userProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get or create user profile.
     */
    public function getOrCreateProfile()
    {
        return $this->userProfile ?: $this->userProfile()->create([]);
    }

    /**
     * Get the chat conversations this user participates in.
     */
    public function chatConversations()
    {
        return $this->belongsToMany(ChatConversation::class, 'chat_participants', 'user_id', 'conversation_id')
            ->withPivot('role', 'joined_at', 'last_read_at', 'is_muted', 'left_at')
            ->withTimestamps();
    }

    /**
     * Get the chat participants records for this user.
     */
    public function chatParticipants()
    {
        return $this->hasMany(ChatParticipant::class);
    }

    /**
     * Get the messages sent by this user.
     */
    public function sentChatMessages()
    {
        return $this->hasMany(ChatMessage::class, 'sender_id');
    }

    /**
     * Get the message reactions made by this user.
     */
    public function chatMessageReactions()
    {
        return $this->hasMany(ChatMessageReaction::class);
    }

    /**
     * Get the message read receipts for this user.
     */
    public function chatMessageReads()
    {
        return $this->hasMany(ChatMessageRead::class);
    }

    /**
     * Get the typing indicators for this user.
     */
    public function chatTypingIndicators()
    {
        return $this->hasMany(ChatTypingIndicator::class);
    }

    /**
     * Get the chat attachments uploaded by this user.
     */
    public function chatAttachments()
    {
        return $this->hasMany(ChatAttachment::class, 'uploaded_by');
    }

    /**
     * Get unread chat message count for this user.
     */
    public function getUnreadChatCount()
    {
        $conversations = $this->chatConversations()
            ->wherePivotNull('left_at')
            ->get();

        $totalUnread = 0;
        foreach ($conversations as $conversation) {
            $totalUnread += $conversation->getUnreadCountForUser($this->id);
        }

        return $totalUnread;
    }
}
