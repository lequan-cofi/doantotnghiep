# Hướng dẫn Agent Units Management

## Tổng quan

Module Agent Units Management cho phép agent quản lý các phòng trong bất động sản được gán. Agent có thể xem, tạo, chỉnh sửa và xóa phòng với đầy đủ thông tin chi tiết.

## Tính năng chính

### 1. **Danh sách phòng** (`/agent/units`)
- ✅ **Grid Layout**: Hiển thị phòng dạng card với hình ảnh
- ✅ **Filters**: Lọc theo bất động sản, trạng thái, tìm kiếm
- ✅ **Sorting**: Sắp xếp theo ID, mã phòng, tầng, ngày tạo
- ✅ **Status Badges**: Hiển thị trạng thái phòng (Có sẵn, Đã đặt, Đã thuê, Bảo trì)
- ✅ **Quick Actions**: Dropdown menu với các thao tác nhanh
- ✅ **Responsive**: Tối ưu cho mobile và desktop

### 2. **Tạo phòng mới** (`/agent/units/create`)
- ✅ **Form Validation**: Validation đầy đủ cho tất cả trường
- ✅ **Image Upload**: Upload nhiều hình ảnh với preview
- ✅ **Amenities Selection**: Chọn tiện ích theo danh mục
- ✅ **Property Selection**: Chọn bất động sản được gán
- ✅ **Real-time Preview**: Xem trước hình ảnh trước khi upload

### 3. **Chi tiết phòng** (`/agent/units/{id}`)
- ✅ **Image Gallery**: Hiển thị tất cả hình ảnh với modal
- ✅ **Complete Information**: Thông tin đầy đủ về phòng
- ✅ **Lease Information**: Thông tin thuê nếu có
- ✅ **Property Details**: Thông tin bất động sản
- ✅ **Quick Actions**: Các thao tác nhanh (edit, delete, change status)

### 4. **Chỉnh sửa phòng** (`/agent/units/{id}/edit`)
- ✅ **Edit Form**: Form chỉnh sửa với dữ liệu hiện tại
- ✅ **Image Management**: Xóa hình cũ, thêm hình mới
- ✅ **Amenities Update**: Cập nhật tiện ích
- ✅ **Validation**: Validation tương tự form tạo mới

## Cấu trúc file

### Controllers
- `app/Http/Controllers/Agent/UnitController.php` - Controller chính
  - `index()` - Danh sách phòng với filters
  - `create()` - Form tạo phòng mới
  - `store()` - Lưu phòng mới
  - `show()` - Chi tiết phòng
  - `edit()` - Form chỉnh sửa
  - `update()` - Cập nhật phòng
  - `destroy()` - Xóa phòng

### Views
- `resources/views/agent/units/index.blade.php` - Danh sách phòng
- `resources/views/agent/units/create.blade.php` - Tạo phòng mới
- `resources/views/agent/units/show.blade.php` - Chi tiết phòng
- `resources/views/agent/units/edit.blade.php` - Chỉnh sửa phòng

### Styling
- `public/assets/css/agent/units.css` - CSS cho unit views
- `resources/views/layouts/agent_dashboard.blade.php` - Layout với CSS

### Navigation
- `resources/views/partials/agent/header.blade.php` - Navigation menu

## Routes

```php
// Agent Units Management
Route::resource('units', \App\Http\Controllers\Agent\UnitController::class);
```

### Available Routes:
- `GET /agent/units` - Danh sách phòng
- `GET /agent/units/create` - Form tạo phòng
- `POST /agent/units` - Lưu phòng mới
- `GET /agent/units/{unit}` - Chi tiết phòng
- `GET /agent/units/{unit}/edit` - Form chỉnh sửa
- `PUT /agent/units/{unit}` - Cập nhật phòng
- `DELETE /agent/units/{unit}` - Xóa phòng

## Database Schema

### Bảng `units`:
- `id`: Primary key
- `property_id`: Foreign key to properties
- `code`: Mã phòng (unique trong property)
- `floor`: Tầng
- `area_m2`: Diện tích (m²)
- `unit_type`: Loại phòng (room, apartment, dorm, shared)
- `base_rent`: Giá thuê cơ bản
- `deposit_amount`: Tiền cọc
- `max_occupancy`: Số người tối đa
- `status`: Trạng thái (available, reserved, occupied, maintenance)
- `note`: Ghi chú
- `images`: JSON array hình ảnh
- `created_at`, `updated_at`: Timestamps

### Relationships:
- `Unit belongsTo Property`
- `Unit belongsToMany Amenities`
- `Unit hasMany Leases`

## Validation Rules

### Tạo/Cập nhật phòng:
```php
'property_id' => 'required|exists:properties,id',
'code' => 'required|string|max:50',
'floor' => 'nullable|integer|min:1|max:100',
'area_m2' => 'nullable|numeric|min:0|max:1000',
'unit_type' => 'required|in:room,apartment,dorm,shared',
'base_rent' => 'required|numeric|min:0',
'deposit_amount' => 'nullable|numeric|min:0',
'max_occupancy' => 'required|integer|min:1|max:10',
'status' => 'required|in:available,reserved,occupied,maintenance',
'note' => 'nullable|string|max:1000',
'images' => 'nullable|array',
'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
'amenities' => 'nullable|array',
'amenities.*' => 'exists:amenities,id'
```

## Giao diện người dùng

### Index Page Features:
- **Card Layout**: Mỗi phòng hiển thị dạng card với hình ảnh
- **Status Indicators**: Badge màu sắc cho trạng thái
- **Quick Actions**: Dropdown menu với edit, delete, view
- **Filters**: Form lọc với nhiều tiêu chí
- **Empty State**: Hiển thị khi chưa có phòng
- **Responsive**: Tối ưu cho mobile

### Create/Edit Forms:
- **Multi-step Layout**: Form chia thành các section
- **Image Upload**: Drag & drop với preview
- **Amenities Selection**: Checkbox theo danh mục
- **Real-time Validation**: Validation inline
- **Help Section**: Hướng dẫn sử dụng

### Show Page:
- **Image Gallery**: Modal hiển thị hình ảnh
- **Information Cards**: Thông tin chia thành cards
- **Sidebar**: Thông tin bất động sản và thống kê
- **Quick Actions**: Các thao tác nhanh

## JavaScript Features

### Image Management:
```javascript
// Image preview on upload
imageInput.addEventListener('change', function(e) {
    // Preview uploaded images
});

// Remove image preview
function removeImagePreview(button) {
    button.closest('.col-md-4').remove();
}

// Image modal
imageModal.addEventListener('show.bs.modal', function (event) {
    const imageSrc = button.getAttribute('data-image');
    modalImage.src = imageSrc;
});
```

### Form Enhancements:
- **Dynamic Validation**: Real-time form validation
- **Image Preview**: Preview trước khi upload
- **Amenity Selection**: Toggle amenities
- **Status Change**: Quick status change

## Security & Access Control

### Authorization:
- ✅ **Agent Only**: Chỉ agent có thể truy cập
- ✅ **Assigned Properties**: Chỉ xem phòng trong BĐS được gán
- ✅ **Property Validation**: Kiểm tra quyền truy cập BĐS
- ✅ **Code Uniqueness**: Mã phòng unique trong BĐS

### Data Protection:
- ✅ **CSRF Protection**: Tất cả forms có CSRF token
- ✅ **File Upload Security**: Validate file types và size
- ✅ **SQL Injection**: Sử dụng Eloquent ORM
- ✅ **XSS Protection**: Escape output

## Performance Optimization

### Database:
- ✅ **Eager Loading**: Load relationships hiệu quả
- ✅ **Indexes**: Index trên các trường quan trọng
- ✅ **Query Optimization**: Tối ưu queries

### Frontend:
- ✅ **Image Optimization**: Compress và resize images
- ✅ **Lazy Loading**: Load images khi cần
- ✅ **Caching**: Cache static assets
- ✅ **Minification**: Minify CSS/JS

## Error Handling

### Validation Errors:
- ✅ **Inline Validation**: Hiển thị lỗi ngay tại field
- ✅ **Custom Messages**: Thông báo lỗi tiếng Việt
- ✅ **Error Summary**: Tóm tắt lỗi ở đầu form

### System Errors:
- ✅ **Try-Catch**: Xử lý lỗi trong controller
- ✅ **User-friendly Messages**: Thông báo lỗi dễ hiểu
- ✅ **Logging**: Log lỗi để debug

## Mobile Responsiveness

### Breakpoints:
- ✅ **Desktop**: Full layout với sidebar
- ✅ **Tablet**: 2-column layout
- ✅ **Mobile**: Single column với stacked cards

### Touch Optimization:
- ✅ **Touch Targets**: Buttons đủ lớn cho touch
- ✅ **Swipe Gestures**: Swipe để xem hình ảnh
- ✅ **Mobile Navigation**: Navigation tối ưu cho mobile

## Testing

### Test Cases:
- ✅ **CRUD Operations**: Test tạo, đọc, cập nhật, xóa
- ✅ **Validation**: Test validation rules
- ✅ **Authorization**: Test quyền truy cập
- ✅ **File Upload**: Test upload hình ảnh
- ✅ **Responsive**: Test trên các device

### Manual Testing:
- ✅ **Browser Compatibility**: Test trên các browser
- ✅ **Device Testing**: Test trên mobile/tablet
- ✅ **Performance**: Test tốc độ load
- ✅ **Usability**: Test trải nghiệm người dùng

## Troubleshooting

### Lỗi thường gặp:

1. **Lỗi upload hình ảnh**
   - Kiểm tra quyền ghi thư mục storage
   - Verify file size và type
   - Check PHP upload limits

2. **Lỗi validation**
   - Kiểm tra validation rules
   - Verify form data
   - Check error messages

3. **Lỗi quyền truy cập**
   - Kiểm tra agent có được gán BĐS
   - Verify middleware
   - Check user roles

4. **Lỗi hiển thị**
   - Kiểm tra CSS file
   - Verify Bootstrap classes
   - Check JavaScript errors

## Best Practices

### Code Organization:
- ✅ **MVC Pattern**: Tách biệt logic và view
- ✅ **Reusable Components**: Tái sử dụng code
- ✅ **Consistent Naming**: Đặt tên nhất quán
- ✅ **Documentation**: Comment code rõ ràng

### Security:
- ✅ **Input Validation**: Validate tất cả input
- ✅ **Output Escaping**: Escape output
- ✅ **File Upload Security**: Kiểm tra file upload
- ✅ **Access Control**: Kiểm soát quyền truy cập

### Performance:
- ✅ **Database Optimization**: Tối ưu queries
- ✅ **Image Optimization**: Compress images
- ✅ **Caching**: Cache khi có thể
- ✅ **Lazy Loading**: Load khi cần

## Tương lai và mở rộng

### Tính năng sắp tới:
- ✅ **Bulk Operations**: Thao tác hàng loạt
- ✅ **Advanced Filters**: Bộ lọc nâng cao
- ✅ **Export/Import**: Xuất/nhập dữ liệu
- ✅ **Analytics**: Thống kê và báo cáo
- ✅ **Mobile App**: Ứng dụng mobile
- ✅ **API Integration**: API cho mobile app
