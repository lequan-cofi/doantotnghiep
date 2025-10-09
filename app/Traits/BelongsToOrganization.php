<?php

namespace App\Traits;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToOrganization
{
    /**
     * Boot the trait
     */
    protected static function bootBelongsToOrganization()
    {
        // Tự động scope queries theo organization (trừ admin)
        static::addGlobalScope('organization', function (Builder $builder) {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            
            if (!$user) {
                return;
            }

            // Admin có quyền xem tất cả
            $isAdmin = $user->userRoles()->where('key_code', 'admin')->exists();
            if ($isAdmin) {
                return;
            }

            // Lọc theo organization của user
            $userOrganization = $user->organizations()->first();
            if ($userOrganization) {
                $builder->where($builder->getModel()->getTable() . '.organization_id', $userOrganization->id);
            }
        });
    }

    /**
     * Relationship to organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Scope to filter by organization
     */
    public function scopeForOrganization(Builder $query, $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }

    /**
     * Check if user can access this record
     */
    public function canAccess($user = null): bool
    {
        $user = $user ?? Auth::user();
        
        if (!$user) {
            return false;
        }

        // Admin có quyền truy cập tất cả
        $isAdmin = $user->userRoles()->where('key_code', 'admin')->exists();
        if ($isAdmin) {
            return true;
        }

        // Kiểm tra cùng organization
        $userOrganization = $user->organizations()->first();
        return $userOrganization && $this->organization_id == $userOrganization->id;
    }
}
