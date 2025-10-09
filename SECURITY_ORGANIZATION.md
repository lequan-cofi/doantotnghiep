# Hệ Thống Bảo Mật Theo Organization

## Tổng Quan

Hệ thống đã được cập nhật để đảm bảo mỗi user chỉ có thể truy cập và chỉnh sửa dữ liệu thuộc organization của mình. **Admin** có quyền truy cập tất cả dữ liệu của tất cả organization.

## Kiến Trúc Bảo Mật

### 1. Middleware: `CheckOrganizationAccess`

**Vị trí:** `app/Http/Middleware/CheckOrganizationAccess.php`

**Chức năng:**
- Kiểm tra user đã đăng nhập chưa
- Nếu là admin → cho phép truy cập tất cả
- Nếu không phải admin → kiểm tra có thuộc organization nào không
- Lưu `user_organization_id` vào request để sử dụng trong controller

**Áp dụng:** 
```php
Route::prefix('manager')->middleware(['ensure.manager', 'check.organization'])
```

### 2. Trait: `BelongsToOrganization`

**Vị trí:** `app/Traits/BelongsToOrganization.php`

**Chức năng:**
- Tự động thêm global scope để lọc dữ liệu theo organization
- Admin được bypass scope (xem tất cả)
- Non-admin chỉ thấy dữ liệu của organization mình

**Các phương thức:**
```php
// Relationship
public function organization()

// Scope
public function scopeForOrganization(Builder $query, $organizationId)

// Check access
public function canAccess($user = null): bool
```

**Sử dụng trong Model:**
```php
use App\Traits\BelongsToOrganization;

class Property extends Model
{
    use BelongsToOrganization;
    
    // Model sẽ tự động lọc theo organization
}
```

### 3. Models Đã Áp Dụng

Các model sau đã được áp dụng trait `BelongsToOrganization`:

1. **Property** - Bất động sản
2. **Lease** - Hợp đồng thuê
3. **Invoice** - Hóa đơn
4. **Ticket** - Ticket bảo trì
5. **SalaryContract** - Hợp đồng lương
6. **CommissionPolicy** - Chính sách hoa hồng
7. **CommissionEvent** - Sự kiện hoa hồng
8. **PayrollCycle** - Kỳ lương

**Lưu ý:** Model `User` KHÔNG áp dụng trait này vì user có thể thuộc nhiều organization thông qua bảng `organization_users`.

## Luồng Hoạt Động

### Khi Manager Đăng Nhập

1. **Login** → Manager đăng nhập
2. **Middleware** → `CheckOrganizationAccess` kiểm tra organization
3. **Global Scope** → Tự động lọc queries theo organization_id
4. **Controller** → Không cần thêm logic lọc organization

### Ví Dụ: Xem Danh Sách Properties

```php
// Trong PropertyController
public function index()
{
    // Tự động chỉ lấy properties của organization
    $properties = Property::all(); 
    // Admin: lấy tất cả
    // Manager: chỉ lấy của organization mình
}
```

### Ví Dụ: Tạo Property Mới

```php
public function store(Request $request)
{
    $user = Auth::user();
    $organization = $user->organizations()->first();
    
    Property::create([
        'organization_id' => $organization->id,
        'name' => $request->name,
        // ...
    ]);
}
```

### Ví Dụ: Kiểm Tra Quyền Truy Cập

```php
$property = Property::find($id);

// Tự động kiểm tra
if (!$property->canAccess()) {
    abort(403, 'Bạn không có quyền truy cập bất động sản này');
}
```

## Cách Áp Dụng Cho Model Mới

### Bước 1: Thêm Trait Vào Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToOrganization;

class YourModel extends Model
{
    use BelongsToOrganization;
    
    protected $fillable = [
        'organization_id',
        // ... other fields
    ];
}
```

### Bước 2: Đảm Bảo Table Có Column `organization_id`

```sql
ALTER TABLE your_table 
ADD COLUMN organization_id BIGINT UNSIGNED,
ADD FOREIGN KEY (organization_id) REFERENCES organizations(id);
```

### Bước 3: Lưu Organization Khi Tạo Mới

```php
public function store(Request $request)
{
    $user = Auth::user();
    $organization = $user->organizations()->first();
    
    YourModel::create([
        'organization_id' => $organization->id,
        // ... other fields
    ]);
}
```

## Quyền Admin

Admin có 2 đặc quyền:

1. **Bypass Global Scope**: Xem tất cả dữ liệu của tất cả organization
2. **Bypass Middleware**: Không bị giới hạn bởi organization check

```php
// Kiểm tra admin
$isAdmin = $user->userRoles()->where('key_code', 'admin')->exists();

if ($isAdmin) {
    // Có quyền truy cập mọi thứ
}
```

## User Quản Lý Organization

### Gắn User Vào Organization

```php
DB::table('organization_users')->insert([
    'organization_id' => $orgId,
    'user_id' => $userId,
    'role_id' => $roleId,
    'status' => 'active',
]);
```

### Lấy Organization Của User

```php
$user = Auth::user();
$organization = $user->organizations()->first();
$organizationId = $organization?->id;
```

### Lấy Tất Cả Users Trong Organization

```php
$organization = $user->organizations()->first();
$users = $organization->users()->get();
```

## Kiểm Tra Bảo Mật

### Test Case 1: Manager Không Thể Xem Properties Của Organization Khác

```php
// Manager A thuộc Org 1
// Manager B thuộc Org 2

// Login as Manager A
$properties = Property::all();
// Chỉ trả về properties của Org 1

// Cố tìm property của Org 2
$property = Property::find($propertyIdOfOrg2);
// Trả về null hoặc không tìm thấy
```

### Test Case 2: Admin Xem Tất Cả

```php
// Login as Admin
$properties = Property::all();
// Trả về tất cả properties của tất cả organizations
```

### Test Case 3: Không Thể Sửa Dữ Liệu Của Organization Khác

```php
// Manager A cố sửa property của Org 2
$property = Property::find($propertyIdOfOrg2);
// $property = null (global scope đã lọc)

// Hoặc nếu bypass scope:
if (!$property->canAccess()) {
    // Trả về 403 Forbidden
}
```

## Lưu Ý Quan Trọng

1. **Luôn kiểm tra organization khi tạo mới:**
   ```php
   'organization_id' => Auth::user()->organizations()->first()?->id
   ```

2. **Với queries phức tạp, đảm bảo join đúng:**
   ```php
   Property::with(['units', 'leases'])
       ->whereHas('leases', function($q) {
           // Lease model cũng có global scope
       })
       ->get();
   ```

3. **Disable scope khi cần (admin only):**
   ```php
   Property::withoutGlobalScope('organization')->get();
   ```

4. **StaffController đã được cập nhật:** Manager chỉ quản lý staff trong organization của mình (đã implement).

## Debug

### Xem SQL Query Với Scope

```php
DB::listen(function($query) {
    Log::info($query->sql);
    Log::info($query->bindings);
});

$properties = Property::all();
// Sẽ log: SELECT * FROM properties WHERE organization_id = ?
```

### Tạm Tắt Scope Để Debug

```php
// Trong controller (chỉ dùng khi debug)
$allProperties = Property::withoutGlobalScope('organization')->get();
```

## Kết Luận

Hệ thống bảo mật theo organization đã được thiết lập:

✅ Manager chỉ quản lý dữ liệu của organization mình  
✅ Admin có full access  
✅ Tự động lọc queries (không cần thêm code)  
✅ Dễ dàng mở rộng cho models mới  
✅ StaffController đã được bảo vệ  

**Tất cả controllers hiện tại đã được bảo vệ thông qua global scope, không cần sửa code thêm!**

