# Hệ thống Thông báo QLPhongTro

## Tổng quan

Hệ thống thông báo thống nhất cho toàn bộ ứng dụng QLPhongTro, bao gồm:
- **Popup xác nhận** (Confirmation Modal)
- **Toast notifications** (Thông báo nổi)
- **Styling đồng bộ** theo phong cách hệ thống

## Cài đặt

### 1. Thêm CSS và JS vào layout

```html
<!-- CSS -->
<link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}">

<!-- JavaScript -->
<script src="{{ asset('assets/js/notifications.js') }}"></script>
```

### 2. Khởi tạo (Tự động)

Hệ thống tự động khởi tạo khi load trang:
```javascript
window.Notify = new NotificationSystem();
```

## Sử dụng

### Toast Notifications

#### Cú pháp cơ bản
```javascript
// Thông báo thành công
Notify.success('Dữ liệu đã được lưu thành công!');

// Thông báo lỗi
Notify.error('Có lỗi xảy ra khi xử lý dữ liệu!');

// Thông báo cảnh báo
Notify.warning('Vui lòng kiểm tra lại thông tin!');

// Thông báo thông tin
Notify.info('Hệ thống đang cập nhật...');
```

#### Cú pháp nâng cao
```javascript
Notify.toast({
    title: 'Thành công!',
    message: 'Dữ liệu đã được lưu thành công!',
    type: 'success',
    duration: 8000,        // 8 giây (0 = không tự đóng)
    showProgress: true,    // Hiển thị thanh tiến trình
    actions: [             // Nút hành động
        {
            text: 'Xem chi tiết',
            icon: 'fas fa-eye',
            type: 'primary',
            action: 'view-details',
            handler: (toastId) => {
                // Xử lý khi click
                console.log('View details clicked');
            }
        }
    ]
});
```

#### Các loại Toast
- `success` - Thành công (màu xanh lá) - 5 giây
- `error` - Lỗi (màu đỏ) - 8 giây
- `warning` - Cảnh báo (màu vàng) - 6 giây
- `info` - Thông tin (màu xanh dương) - 4 giây

### Confirmation Popup

#### Cú pháp cơ bản
```javascript
// Xác nhận xóa
Notify.confirmDelete('bất động sản này', () => {
    // Xử lý khi xác nhận
    console.log('Đã xác nhận xóa');
});

// Xác nhận lưu
Notify.confirmSave(() => {
    // Xử lý khi xác nhận lưu
    console.log('Đã xác nhận lưu');
});
```

#### Cú pháp nâng cao
```javascript
Notify.confirm({
    title: 'Xác nhận xóa',
    message: 'Bạn có chắc chắn muốn xóa bất động sản này?',
    details: 'Hành động này không thể hoàn tác.',
    type: 'danger',           // danger, warning, info, success
    confirmText: 'Xóa',
    cancelText: 'Hủy',
    onConfirm: () => {
        // Xử lý khi xác nhận
        console.log('Confirmed');
    },
    onCancel: () => {
        // Xử lý khi hủy
        console.log('Cancelled');
    }
});
```

#### Các loại Confirmation
- `danger` - Nguy hiểm (màu đỏ)
- `warning` - Cảnh báo (màu vàng)
- `info` - Thông tin (màu xanh dương)
- `success` - Thành công (màu xanh lá)

## Ví dụ thực tế

### 1. Xử lý form submit
```javascript
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Show preloader
    if (window.Preloader) {
        window.Preloader.show();
    }
    
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Notify.success(data.message, 'Thành công!');
            setTimeout(() => {
                window.location.href = '/manager/properties';
            }, 1500);
        } else {
            Notify.error(data.message, 'Lỗi!');
        }
    })
    .catch(error => {
        Notify.error('Có lỗi xảy ra khi xử lý dữ liệu!', 'Lỗi hệ thống!');
    })
    .finally(() => {
        if (window.Preloader) {
            window.Preloader.hide();
        }
    });
});
```

### 2. Xử lý xóa với xác nhận
```javascript
function deleteProperty(id) {
    Notify.confirmDelete('bất động sản này', () => {
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }
        
        fetch(`/manager/properties/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Đã xóa!');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Notify.error(data.message, 'Lỗi!');
            }
        })
        .catch(error => {
            Notify.error('Có lỗi xảy ra khi xóa!', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
}
```

### 3. Toast với hành động
```javascript
function saveProperty() {
    // ... save logic ...
    
    Notify.toast({
        title: 'Đã lưu thành công!',
        message: 'Bất động sản đã được lưu vào hệ thống.',
        type: 'success',
        duration: 0, // Không tự đóng
        actions: [
            {
                text: 'Xem',
                icon: 'fas fa-eye',
                type: 'primary',
                action: 'view',
                handler: (toastId) => {
                    window.location.href = '/manager/properties/1';
                }
            },
            {
                text: 'Đóng',
                icon: 'fas fa-times',
                type: 'secondary',
                action: 'close',
                handler: (toastId) => {
                    const toast = document.getElementById(toastId);
                    const bsToast = bootstrap.Toast.getInstance(toast);
                    bsToast.hide();
                }
            }
        ]
    });
}
```

### 4. Thông báo với nhiều thông tin
```javascript
function showValidationErrors(errors) {
    const errorList = Object.values(errors).flat().join('<br>');
    
        Notify.toast({
            title: 'Lỗi xác thực',
            message: errorList,
            type: 'error',
            duration: 12000, // 12 giây cho lỗi validation
            showProgress: true
        });
}
```

## Tùy chỉnh

### Thay đổi vị trí hiển thị
```css
.notification-container {
    top: 20px;
    right: 20px;
    /* Có thể thay đổi thành: */
    /* top: 80px; left: 20px; */
}
```

### Thay đổi màu sắc
```css
.toast-notification.toast-success {
    border-left-color: #your-color;
}

.toast-success .toast-progress-bar {
    background: linear-gradient(90deg, #your-color, #your-darker-color);
}
```

### Thay đổi thời gian hiển thị mặc định
```javascript
// Trong notifications.js, thay đổi:
duration: 5000, // Thành thời gian mong muốn
```

## API Reference

### NotificationSystem Class

#### Methods

##### `toast(options)`
Hiển thị toast notification
- `options.title` (string): Tiêu đề
- `options.message` (string): Nội dung
- `options.type` (string): Loại (success, error, warning, info)
- `options.duration` (number): Thời gian hiển thị (ms)
- `options.showProgress` (boolean): Hiển thị thanh tiến trình
- `options.actions` (array): Mảng các nút hành động

##### `confirm(options)`
Hiển thị popup xác nhận
- `options.title` (string): Tiêu đề
- `options.message` (string): Nội dung chính
- `options.details` (string): Chi tiết bổ sung
- `options.type` (string): Loại (danger, warning, info, success)
- `options.confirmText` (string): Text nút xác nhận
- `options.cancelText` (string): Text nút hủy
- `options.onConfirm` (function): Callback khi xác nhận
- `options.onCancel` (function): Callback khi hủy

##### `success(message, title)`
Toast thành công nhanh

##### `error(message, title)`
Toast lỗi nhanh

##### `warning(message, title)`
Toast cảnh báo nhanh

##### `info(message, title)`
Toast thông tin nhanh

##### `confirmDelete(itemName, onConfirm)`
Xác nhận xóa nhanh

##### `confirmSave(onConfirm)`
Xác nhận lưu nhanh

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Responsive

Hệ thống tự động responsive trên:
- Desktop (1200px+)
- Tablet (768px - 1199px)
- Mobile (< 768px)

## Accessibility

- Hỗ trợ screen readers
- Keyboard navigation
- High contrast mode
- Reduced motion support

## Performance

- Lazy loading
- Auto cleanup
- Minimal DOM manipulation
- Efficient animations
