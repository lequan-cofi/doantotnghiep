# Bug Fix Summary

## Fixed: RelationNotFoundException - Missing roles relationship on Organization model

**Error:** `Call to undefined relationship [roles] on model [App\Models\Organization]`

**Location:** `app/Http/Controllers/SuperAdmin/UserController@show` line 125

**Root Cause:** The `Organization` model was missing a `roles` relationship method, but the controller was trying to eager load `organizations.roles`.

**Solution:** Added the missing `roles()` relationship method to the `Organization` model:

```php
/**
 * Get the roles for the organization through organization_users pivot table.
 */
public function roles()
{
    return $this->belongsToMany(Role::class, 'organization_users', 'organization_id', 'role_id')
        ->withPivot('user_id', 'status')
        ->withTimestamps();
}
```

**Files Modified:**
- `app/Models/Organization.php` - Added roles relationship

**Date Fixed:** January 11, 2025
