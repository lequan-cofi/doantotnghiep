# Tóm tắt Migration từ user_roles sang organization_users

## Tổng quan
Đã thực hiện migration toàn bộ hệ thống từ việc sử dụng bảng `user_roles`, `role_permissions`, `permissions` sang sử dụng bảng `organization_users` để quản lý role theo tổ chức.

## Các thay đổi đã thực hiện

### 1. Cập nhật Models

#### User.php
- **Thay đổi**: Cập nhật method `organizationRoles()` để sử dụng bảng `organization_users`
- **Thêm**: Method `userRoles()` để backward compatibility
- **Cập nhật**: Method `primaryRole()` sử dụng `organizationRoles()`

#### Role.php
- **Thay đổi**: Method `users()` sử dụng bảng `organization_users` thay vì `user_roles`

#### Xóa file
- **Xóa**: `app/Models/User_role.php` (không còn cần thiết)

### 2. Cập nhật Controllers

#### EmailAuthController.php
- **Thay đổi**: Import `OrganizationUser` thay vì `User_role`
- **Cập nhật**: Method `resolvePrimaryRole()` sử dụng `organization_users`
- **Cập nhật**: Method `register()` gắn role thông qua `organization_users` với default organization

#### StaffController.php
- **Thay đổi**: Tất cả queries sử dụng `organizationRoles` thay vì `userRoles`
- **Cập nhật**: Filter và search logic sử dụng `organization_users`
- **Cập nhật**: CRUD operations sử dụng `organization_users`

#### SuperAdminController.php
- **Cập nhật**: User metrics sử dụng `organization_users` thay vì `user_roles`

### 3. Cập nhật Views

#### manager/staff/index.blade.php
- **Thay đổi**: `$member->userRoles` → `$member->organizationRoles`

#### manager/staff/show.blade.php
- **Thay đổi**: `$staff->userRoles` → `$staff->organizationRoles`

#### manager/staff/edit.blade.php
- **Thay đổi**: `$staff->userRoles->first()?->id` → `$staff->organizationRoles->first()?->id`

### 4. Database Migrations

#### 2025_01_11_000000_remove_old_role_tables.php
- **Xóa bảng**: `user_roles`, `role_permissions`, `permissions`
- **Rollback**: Có thể khôi phục lại các bảng nếu cần

#### 2025_01_11_000001_create_default_organization.php
- **Tạo**: Default organization cho user mới đăng ký
- **Đảm bảo**: Có organization mặc định cho tenant role

## Lợi ích của việc migration

### 1. Quản lý Role theo Organization
- Mỗi user có thể có role khác nhau trong các organization khác nhau
- Dễ dàng quản lý quyền hạn theo từng tổ chức
- Hỗ trợ multi-tenant architecture

### 2. Tính nhất quán
- Tất cả role management đều thông qua `organization_users`
- Loại bỏ sự trùng lặp giữa `user_roles` và `organization_users`
- Đơn giản hóa logic xác thực

### 3. Bảo mật tốt hơn
- Role được gắn với organization cụ thể
- Tránh conflict role giữa các organization
- Dễ dàng kiểm soát quyền truy cập

## Cách sử dụng mới

### 1. Lấy role của user trong organization
```php
$user = User::find(1);
$roles = $user->organizationRoles($organizationId)->get();
```

### 2. Kiểm tra role của user
```php
$user = User::find(1);
$hasRole = $user->organizationRoles($organizationId)
    ->where('key_code', 'manager')
    ->exists();
```

### 3. Gắn role cho user trong organization
```php
OrganizationUser::create([
    'organization_id' => $organizationId,
    'user_id' => $userId,
    'role_id' => $roleId,
    'status' => 'active'
]);
```

## Lưu ý quan trọng

1. **Backward Compatibility**: Các method cũ như `userRoles()` vẫn hoạt động nhưng redirect đến `organizationRoles()`

2. **Default Organization**: User mới đăng ký sẽ được gắn vào "Default Organization" với role "tenant"

3. **Migration Order**: Chạy migration theo thứ tự:
   - `2025_01_11_000001_create_default_organization.php`
   - `2025_01_11_000000_remove_old_role_tables.php`

4. **Testing**: Cần test kỹ các chức năng:
   - Đăng nhập/đăng ký
   - Quản lý staff
   - Phân quyền theo organization

## Rollback Plan

Nếu cần rollback, có thể:
1. Chạy `php artisan migrate:rollback` để khôi phục các bảng cũ
2. Cập nhật lại code để sử dụng `user_roles`
3. Restore file `User_role.php` model

## Kết luận

Migration đã hoàn thành thành công, hệ thống giờ đây sử dụng `organization_users` làm bảng chính để quản lý role, đảm bảo tính nhất quán và hỗ trợ multi-tenant architecture tốt hơn.
