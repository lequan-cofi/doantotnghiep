# Hướng dẫn sử dụng Agent Properties

## Tổng quan

Module Agent Properties cho phép agent xem danh sách và chi tiết các bất động sản được phân quản lý. Agent chỉ có quyền xem (read-only), không thể tạo, sửa hoặc xóa bất động sản.

## Tính năng

### 1. Danh sách bất động sản (`/agent/properties`)

**Chức năng:**
- Xem danh sách tất cả bất động sản được phân quản lý
- Lọc theo tên, loại bất động sản, trạng thái
- Sắp xếp theo tên, ngày tạo, số phòng
- Hiển thị thống kê tỷ lệ lấp đầy cho mỗi bất động sản

**Giao diện:**
- Layout dạng grid với card cho mỗi bất động sản
- Hiển thị hình ảnh, tên, địa chỉ mới (2025), tên chủ trọ, loại bất động sản
- Thống kê: tổng phòng, đã thuê, trống
- Thanh tiến trình tỷ lệ lấp đầy với màu sắc phân biệt
- Nút "Xem chi tiết" để chuyển đến trang chi tiết

### 2. Chi tiết bất động sản (`/agent/properties/{id}`)

**Chức năng:**
- Xem thông tin chi tiết của bất động sản
- Xem thông tin chủ trọ và số điện thoại liên hệ
- Xem địa chỉ cũ (2024) và địa chỉ mới (2025)
- Xem hình ảnh bất động sản (có modal xem ảnh)
- Xem danh sách tất cả phòng trong bất động sản
- Thống kê chi tiết về tình trạng phòng
- Liên kết nhanh đến quản lý phòng

**Giao diện:**
- Header với tên và địa chỉ mới (2025) của bất động sản
- Badge trạng thái (hoạt động/không hoạt động)
- Card thông tin chủ trọ và địa chỉ (cũ/mới)
- Gallery hình ảnh với modal xem ảnh
- Bảng danh sách phòng với thông tin chi tiết
- Sidebar với thông tin cơ bản và thống kê
- Nút thao tác nhanh

## Cấu trúc file

### Controllers
- `app/Http/Controllers/Agent/PropertyController.php` - Controller chính
  - Load thêm thông tin owner, location, location2025 với relationships
  - Eager loading để tối ưu performance

### Models
- `app/Models/Property.php` - Model chính với các accessor methods:
  - `getOwnerNameAttribute()` - Tên chủ trọ (sử dụng `owner->full_name`)
  - `getOldAddressAttribute()` - Địa chỉ cũ (2024)
  - `getNewAddressAttribute()` - Địa chỉ mới (2025)

### Views
- `resources/views/agent/properties/index.blade.php` - Trang danh sách
  - Hiển thị tên chủ trọ và địa chỉ mới
- `resources/views/agent/properties/show.blade.php` - Trang chi tiết
  - Card thông tin chủ trọ và địa chỉ cũ/mới

### CSS
- `public/assets/css/agent/properties.css` - Styles riêng cho module

### Routes
```php
// Properties management (read-only for agents)
Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/properties/{id}', [PropertyController::class, 'show'])->name('properties.show');
```

## Quyền truy cập

### Agent chỉ có thể:
- ✅ Xem danh sách bất động sản được phân quản lý
- ✅ Xem chi tiết bất động sản được phân quản lý
- ✅ Xem thống kê và báo cáo
- ✅ Chuyển đến quản lý phòng của bất động sản

### Agent KHÔNG thể:
- ❌ Tạo bất động sản mới
- ❌ Sửa thông tin bất động sản
- ❌ Xóa bất động sản
- ❌ Quản lý loại bất động sản

## Bảo mật

- Agent chỉ có thể xem bất động sản được phân quản lý thông qua bảng `properties_user`
- Sử dụng middleware `ensure.agent` để kiểm tra quyền
- Kiểm tra quyền truy cập trong controller trước khi hiển thị dữ liệu

## Navigation

Menu navigation đã được cập nhật:
- Bỏ link "Thêm BĐS mới" và "Loại BĐS"
- Chỉ giữ lại "Danh sách BĐS"
- Thêm active state cho menu properties

## Responsive Design

- Hỗ trợ đầy đủ trên mobile, tablet và desktop
- Grid layout tự động điều chỉnh theo kích thước màn hình
- Modal xem ảnh tối ưu cho mobile

## Styling

- Sử dụng Bootstrap 5.3.0
- Custom CSS với animations và transitions
- Color scheme nhất quán với theme agent
- Loading states và empty states

## API Endpoints

Không có API endpoints riêng cho module này vì agent chỉ có quyền xem.

## Troubleshooting

### Lỗi thường gặp:

1. **Agent không thấy bất động sản nào**
   - Kiểm tra xem agent có được phân quản lý bất động sản trong bảng `properties_user`
   - Kiểm tra trạng thái của bất động sản (status = 1)

2. **Lỗi 404 khi truy cập chi tiết**
   - Kiểm tra ID bất động sản có tồn tại
   - Kiểm tra agent có quyền truy cập bất động sản đó

3. **Hình ảnh không hiển thị**
   - Kiểm tra đường dẫn storage
   - Kiểm tra quyền truy cập file
   - Fallback image sẽ hiển thị nếu ảnh gốc lỗi

4. **Tên chủ trọ không hiển thị**
   - Kiểm tra bảng `users` có cột `full_name` (không phải `name`)
   - Kiểm tra `owner_id` trong bảng `properties` có trỏ đúng user
   - Accessor `getOwnerNameAttribute()` sử dụng `owner->full_name`

## Cập nhật trong tương lai

- Thêm tính năng export danh sách bất động sản
- Thêm bộ lọc nâng cao
- Thêm tính năng tìm kiếm theo địa chỉ
- Thêm thống kê biểu đồ
