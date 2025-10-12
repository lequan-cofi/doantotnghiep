<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    protected $table = 'user_profiles';
    
    // Use user_id as primary key
    protected $primaryKey = 'user_id';
    public $incrementing = false;
    protected $keyType = 'int';
    
    // Disable timestamps
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'dob',
        'gender',
        'id_number',
        'id_issued_at',
        'id_images',
        'address',
        'note',
    ];

    protected $casts = [
        'dob' => 'date',
        'id_issued_at' => 'date',
        'id_images' => 'array',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the formatted gender text.
     */
    public function getGenderTextAttribute(): string
    {
        return match($this->gender) {
            'male' => 'Nam',
            'female' => 'Nữ',
            'other' => 'Khác',
            default => 'Chưa xác định'
        };
    }

    /**
     * Get the age from date of birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (!$this->dob) {
            return null;
        }

        return $this->dob->age;
    }

    /**
     * Get formatted date of birth.
     */
    public function getFormattedDobAttribute(): ?string
    {
        return $this->dob ? $this->dob->format('d/m/Y') : null;
    }

    /**
     * Get formatted ID issued date.
     */
    public function getFormattedIdIssuedAtAttribute(): ?string
    {
        return $this->id_issued_at ? $this->id_issued_at->format('d/m/Y') : null;
    }

    /**
     * Check if profile is complete for KYC.
     */
    public function isKycComplete(): bool
    {
        return !empty($this->dob) &&
               !empty($this->gender) &&
               !empty($this->id_number) &&
               !empty($this->id_issued_at) &&
               !empty($this->address);
    }

    /**
     * Get KYC completion percentage.
     */
    public function getKycCompletionPercentage(): int
    {
        $fields = [
            'dob',
            'gender', 
            'id_number',
            'id_issued_at',
            'address'
        ];

        $completed = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $completed++;
            }
        }

        return round(($completed / count($fields)) * 100);
    }

    /**
     * Get missing KYC fields.
     */
    public function getMissingKycFields(): array
    {
        $fields = [
            'dob' => 'Ngày sinh',
            'gender' => 'Giới tính',
            'id_number' => 'Số CMND/CCCD',
            'id_issued_at' => 'Ngày cấp CMND/CCCD',
            'address' => 'Địa chỉ thường trú'
        ];

        $missing = [];
        foreach ($fields as $field => $label) {
            if (empty($this->$field)) {
                $missing[] = $label;
            }
        }

        return $missing;
    }
}