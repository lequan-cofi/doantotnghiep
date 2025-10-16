# Hệ Thống Thông Báo (Notification System)

## Tổng Quan

Hệ thống thông báo đồng bộ cho toàn bộ ứng dụng quản lý phòng trọ, bao gồm:
- **Toast Notifications**: Thông báo tạm thời
- **Confirmation Popups**: Xác nhận hành động
- **Loading States**: Trạng thái đang tải
- **Error Handling**: Xử lý lỗi thống nhất

## Cấu Trúc File

```
resources/
├── views/
│   ├── layouts/
│   │   └── app.blade.php (chứa notification system)
│   └── partials/
│       └── notification-system.blade.php
public/
├── css/
│   └── notification-system.css
└── js/
    └── notification-system.js
```

## Cách Sử Dụng

### 1. Toast Notifications

```javascript
// Thông báo thành công
Notify.success('Dữ liệu đã được lưu thành công!', 'Thành công');

// Thông báo lỗi
Notify.error('Không thể kết nối đến server', 'Lỗi kết nối');

// Thông báo cảnh báo
Notify.warning('Dữ liệu có thể bị mất', 'Cảnh báo');

// Thông báo thông tin
Notify.info('Đang xử lý dữ liệu...', 'Thông tin');

// Toast tùy chỉnh
Notify.toast({
    title: 'Tiêu đề',
    message: 'Nội dung thông báo',
    type: 'success', // success, error, warning, info
    duration: 5000   // 0 = không tự động đóng
});
```

### 2. Confirmation Popups

```javascript
// Xác nhận đơn giản
Notify.confirm('Bạn có chắc muốn xóa?', function() {
    // Hành động khi xác nhận
    console.log('User confirmed');
});

// Xác nhận với callback hủy
Notify.confirm('Bạn có chắc muốn xóa?', function() {
    // Hành động khi xác nhận
    console.log('User confirmed');
}, function() {
    // Hành động khi hủy
    console.log('User cancelled');
});

// Xác nhận xóa
Notify.confirmDelete('Bạn có chắc muốn xóa item này?', function() {
    // Thực hiện xóa
});

// Xác nhận lưu
Notify.confirmSave('Bạn có chắc muốn lưu thay đổi?', function() {
    // Thực hiện lưu
});
```

### 3. Loading States

```javascript
// Hiển thị loading
const loadingToast = Notify.toast({
    title: 'Đang tải...',
    message: 'Vui lòng chờ trong giây lát',
    type: 'info',
    duration: 0 // Không tự động đóng
});

// Ẩn loading
const toastElement = document.getElementById(loadingToast);
if (toastElement) {
    const bsToast = bootstrap.Toast.getInstance(toastElement);
    if (bsToast) bsToast.hide();
}
```

## Các Loại Thông Báo

### Toast Types
- **success**: Màu xanh lá (thành công)
- **error**: Màu đỏ (lỗi)
- **warning**: Màu vàng (cảnh báo)
- **info**: Màu xanh dương (thông tin)

### Icons
- **success**: ✓ (checkmark)
- **error**: ✕ (cross)
- **warning**: ⚠ (warning)
- **info**: ℹ (information)

## Tùy Chỉnh

### CSS Variables
```css
:root {
    --notification-success-color: #28a745;
    --notification-error-color: #dc3545;
    --notification-warning-color: #ffc107;
    --notification-info-color: #17a2b8;
    --notification-duration: 5000ms;
    --notification-z-index: 9999;
}
```

### JavaScript Options
```javascript
// Cấu hình mặc định
Notify.config({
    duration: 5000,
    position: 'top-right',
    maxToasts: 5,
    animation: 'slide'
});
```

## Tích Hợp Với Laravel

### Backend Response Format
```php
// Thành công
return response()->json([
    'success' => true,
    'message' => 'Dữ liệu đã được lưu thành công!',
    'data' => $data
]);

// Lỗi
return response()->json([
    'success' => false,
    'message' => 'Có lỗi xảy ra khi xử lý dữ liệu',
    'errors' => $errors
]);
```

### Frontend Handling
```javascript
fetch('/api/endpoint', {
    method: 'POST',
    body: formData,
    headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
    }
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        Notify.success(data.message, 'Thành công!');
    } else {
        Notify.error(data.message, 'Lỗi!');
    }
})
.catch(error => {
    Notify.error('Có lỗi xảy ra. Vui lòng thử lại.', 'Lỗi hệ thống');
});
```

## Best Practices

### 1. Sử Dụng Đúng Loại Thông Báo
- **success**: Khi thao tác thành công
- **error**: Khi có lỗi xảy ra
- **warning**: Khi cần cảnh báo người dùng
- **info**: Khi cung cấp thông tin

### 2. Thông Báo Rõ Ràng
- Sử dụng tiếng Việt
- Mô tả cụ thể hành động
- Cung cấp thông tin hữu ích

### 3. Xử Lý Lỗi
- Luôn có error handling
- Hiển thị thông báo lỗi thân thiện
- Cung cấp hướng dẫn khắc phục

### 4. Performance
- Giới hạn số lượng toast hiển thị
- Tự động ẩn sau thời gian nhất định
- Sử dụng animation mượt mà

## Troubleshooting

### Lỗi Thường Gặp

1. **Toast không hiển thị**
   - Kiểm tra Bootstrap JS đã load
   - Kiểm tra CSS đã include
   - Kiểm tra console errors

2. **Confirmation không hoạt động**
   - Kiểm tra function callback
   - Kiểm tra modal HTML structure
   - Kiểm tra event listeners

3. **Loading không ẩn**
   - Đảm bảo gọi hide() đúng cách
   - Kiểm tra toast ID
   - Kiểm tra Bootstrap instance

## Cập Nhật

### Version 2.0
- Thêm animation mượt mà
- Cải thiện responsive design
- Thêm dark mode support
- Tối ưu performance

### Version 1.0
- Toast notifications cơ bản
- Confirmation popups
- Loading states
- Error handling
