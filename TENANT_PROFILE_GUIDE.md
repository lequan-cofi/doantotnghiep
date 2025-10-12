# Hướng dẫn sử dụng Tenant Profile

## Tổng quan

Module Tenant Profile cho phép người thuê nhà (tenant) xem và cập nhật thông tin cá nhân của mình, bao gồm thông tin cơ bản và thay đổi mật khẩu.

## Tính năng

### 1. Xem hồ sơ cá nhân (`/tenant/profile`)

**Chức năng:**
- Xem thông tin cá nhân hiện tại
- Hiển thị avatar tự động tạo từ tên
- Nút chỉnh sửa để chuyển đến trang edit
- Thông báo thành công/lỗi

**Giao diện:**
- Card thông tin tài khoản với các trường readonly
- Card bảo mật với nút đổi mật khẩu (sắp ra mắt)
- Sidebar với avatar, tên, email và các nút hành động

### 2. Chỉnh sửa hồ sơ (`/tenant/profile/edit`)

**Chức năng:**
- Cập nhật thông tin cơ bản: họ tên, email, số điện thoại
- Thay đổi mật khẩu (tùy chọn)
- Validation đầy đủ cho tất cả trường
- Xác thực mật khẩu hiện tại khi đổi mật khẩu

**Giao diện:**
- Form 2 cột cho thông tin cơ bản
- Section riêng cho thay đổi mật khẩu
- Sidebar với avatar và hướng dẫn
- Nút hành động: Hủy và Cập nhật

## Cấu trúc file

### Controllers
- `app/Http/Controllers/Tenant/ProfileController.php` - Controller chính
  - `index()` - Hiển thị trang profile với thông tin KYC
  - `edit()` - Hiển thị form chỉnh sửa với form KYC
  - `update()` - Xử lý cập nhật thông tin cơ bản và KYC

### Models
- `app/Models/UserProfile.php` - Model cho thông tin KYC
  - Relationship với User model
  - Accessor methods cho formatting
  - KYC completion tracking methods

### Views
- `resources/views/tenant/profile.blade.php` - Trang xem profile
  - Hiển thị thông tin readonly
  - Card thông tin KYC với completion percentage
  - Nút chỉnh sửa và thông báo
- `resources/views/tenant/profile-edit.blade.php` - Trang chỉnh sửa
  - Form cập nhật thông tin cơ bản
  - Form thông tin KYC (dob, gender, id_number, etc.)
  - Form thay đổi mật khẩu
  - Validation và error handling

### Routes
```php
// Profile management
Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
```

## Validation Rules

### Thông tin cơ bản:
- `full_name`: Required, string, max 255 ký tự
- `email`: Required, email, unique (trừ user hiện tại)
- `phone`: Optional, string, max 30 ký tự, unique (trừ user hiện tại)

### Thông tin KYC:
- `dob`: Optional, date, before today
- `gender`: Optional, enum (male, female, other)
- `id_number`: Optional, string, max 50 ký tự
- `id_issued_at`: Optional, date, before or equal today
- `address`: Optional, string, max 255 ký tự
- `note`: Optional, string, max 1000 ký tự

### Mật khẩu:
- `current_password`: Optional, string (required nếu đổi mật khẩu)
- `password`: Optional, string, min 8 ký tự, confirmed
- `password_confirmation`: Phải khớp với password

## Bảo mật

- Sử dụng middleware `ensure.tenant` để kiểm tra quyền
- Xác thực mật khẩu hiện tại khi đổi mật khẩu
- Hash mật khẩu mới bằng Laravel Hash facade
- Validation unique cho email và phone
- CSRF protection cho tất cả form

## Database

### Bảng `users`:
- `id`: Primary key
- `full_name`: Họ và tên
- `email`: Email (unique)
- `phone`: Số điện thoại (unique, nullable)
- `password_hash`: Mật khẩu đã hash
- `status`: Trạng thái tài khoản
- `created_at`, `updated_at`: Timestamps

### Bảng `user_profiles` (KYC):
- `user_id`: Primary key, Foreign key to users.id
- `dob`: Ngày sinh (date, nullable)
- `gender`: Giới tính (enum: male, female, other, nullable)
- `id_number`: Số CMND/CCCD (varchar(50), nullable)
- `id_issued_at`: Ngày cấp CMND/CCCD (date, nullable)
- `id_images`: Hình ảnh CMND/CCCD (json, nullable)
- `address`: Địa chỉ thường trú (varchar(255), nullable)
- `note`: Ghi chú (text, nullable)

## Giao diện

### Responsive Design:
- Layout 2 cột trên desktop
- Layout 1 cột trên mobile
- Bootstrap 5 components
- FontAwesome icons

### Styling:
- Custom CSS cho cards và forms
- Alert messages với icons
- Avatar tự động tạo từ UI Avatars API
- Color scheme nhất quán

## Error Handling

### Validation Errors:
- Hiển thị lỗi validation inline
- Bootstrap validation classes
- Error messages tiếng Việt

### System Errors:
- Try-catch trong controller
- Generic error message cho user
- Log errors cho debugging

## User Experience

### Navigation:
- Breadcrumb navigation
- Nút quay lại và hủy
- Links đến dashboard

### Feedback:
- Success messages sau khi cập nhật
- Error messages với details
- Loading states (có thể thêm)

## Troubleshooting

### Lỗi thường gặp:

1. **Lỗi validation email/phone unique**
   - Kiểm tra email/phone có bị trùng với user khác
   - Sử dụng Rule::unique với ignore current user

2. **Lỗi mật khẩu hiện tại không đúng**
   - Kiểm tra Hash::check với password_hash
   - Đảm bảo user nhập đúng mật khẩu hiện tại

3. **Lỗi cập nhật database**
   - Kiểm tra fillable fields trong User model
   - Sử dụng DB facade nếu Eloquent có vấn đề

4. **Lỗi hiển thị avatar**
   - UI Avatars API có thể bị lỗi
   - Fallback image nên được thêm

## Cập nhật trong tương lai

- Thêm upload avatar thật
- Thêm 2FA authentication
- Thêm lịch sử thay đổi thông tin
- Thêm export dữ liệu cá nhân
- Thêm xóa tài khoản
- Thêm notification preferences
