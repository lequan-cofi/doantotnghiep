# Hướng dẫn sử dụng Preloader

## Tổng quan

Preloader là component loading screen đồng bộ với thiết kế hệ thống nhà trọ, giúp cải thiện trải nghiệm người dùng khi tải trang hoặc xử lý dữ liệu.

## Các phong cách (Styles)

### 1. Default Style
- Logo icon với hiệu ứng float
- Spinner xoay tròn
- Loading bar
- Loading dots
- Percentage counter

```blade
<x-preloader />
<!-- hoặc -->
<x-preloader style="default" />
```

### 2. House Style
- Icon ngôi nhà với animation bounce
- Phù hợp cho trang chủ hoặc landing page
- Thiết kế trực quan, dễ nhận biết

```blade
<x-preloader style="house" />
```

### 3. Minimal Style
- Thiết kế tối giản
- Chỉ có logo và spinner
- Phù hợp cho dashboard/admin panel

```blade
<x-preloader style="minimal" />
```

## Cách sử dụng

### 1. Full Page Preloader

#### Trong Layout
```blade
<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
    @stack('styles')
</head>
<body>
    {{-- Thêm preloader --}}
    <x-preloader />
    
    {{-- Nội dung trang --}}
    <main>
        @yield('content')
    </main>
    
    @stack('scripts')
</body>
</html>
```

#### Tuỳ chọn
```blade
{{-- Không hiển thị percentage --}}
<x-preloader :showPercentage="false" />

{{-- Chọn style --}}
<x-preloader style="house" />
<x-preloader style="minimal" />
```

### 2. Inline Preloader (AJAX/Loading states)

```blade
{{-- Default (medium, primary color) --}}
<x-preloader-inline />

{{-- Small size, success color --}}
<x-preloader-inline size="sm" color="success" />

{{-- Large size, danger color --}}
<x-preloader-inline size="lg" color="danger" />
```

#### Các tuỳ chọn:
- **Size:** `sm`, `md`, `lg`
- **Color:** `primary`, `success`, `danger`, `warning`, `info`

### 3. JavaScript API

#### Hiển thị preloader thủ công
```javascript
window.Preloader.show();
```

#### Ẩn preloader thủ công
```javascript
window.Preloader.hide();
```

#### Lắng nghe sự kiện preloader ẩn
```javascript
window.addEventListener('preloaderHidden', function() {
    console.log('Preloader đã được ẩn');
    // Thực hiện các hành động sau khi preloader ẩn
});
```

## Use Cases

### 1. Trang chủ / Public pages
```blade
@extends('layouts.app')

@section('content')
    {{-- Preloader đã được thêm trong layout --}}
    <div class="container">
        <!-- Nội dung -->
    </div>
@endsection
```

### 2. Dashboard / Admin
```blade
@extends('layouts.manager_dashboard')

@section('content')
    {{-- Preloader minimal style đã được thêm --}}
    <div class="dashboard-content">
        <!-- Nội dung dashboard -->
    </div>
@endsection
```

### 3. AJAX Request
```javascript
// Hiển thị preloader trước khi gọi API
window.Preloader.show();

fetch('/api/properties')
    .then(response => response.json())
    .then(data => {
        // Xử lý dữ liệu
        console.log(data);
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        // Ẩn preloader sau khi xong
        window.Preloader.hide();
    });
```

### 4. Form Submission
```javascript
document.getElementById('myForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Hiển thị preloader
    window.Preloader.show();
    
    // Gửi form
    const formData = new FormData(this);
    
    fetch('/api/submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Thành công!');
        }
    })
    .finally(() => {
        window.Preloader.hide();
    });
});
```

### 5. Loading state trong component
```blade
<div id="content-area">
    {{-- Hiển thị inline preloader khi đang tải --}}
    <div id="loading" style="display: none;">
        <x-preloader-inline size="lg" />
    </div>
    
    {{-- Nội dung --}}
    <div id="data-content">
        <!-- Data sẽ được load vào đây -->
    </div>
</div>

<script>
function loadData() {
    document.getElementById('loading').style.display = 'block';
    document.getElementById('data-content').style.display = 'none';
    
    fetch('/api/data')
        .then(response => response.json())
        .then(data => {
            document.getElementById('data-content').innerHTML = data.html;
        })
        .finally(() => {
            document.getElementById('loading').style.display = 'none';
            document.getElementById('data-content').style.display = 'block';
        });
}
</script>
```

## Configuration

File cấu hình: `public/assets/js/preloader.js`

```javascript
const config = {
    minDisplayTime: 500,        // Thời gian hiển thị tối thiểu (ms)
    maxDisplayTime: 5000,       // Thời gian hiển thị tối đa (ms)
    fadeOutDuration: 500,       // Thời gian fade out (ms)
    showPercentage: true,       // Hiển thị phần trăm
    simulateLoading: true       // Mô phỏng tiến trình loading
};
```

## Tuỳ chỉnh CSS

File CSS: `public/assets/css/preloader.css`

### Thay đổi màu sắc
```css
.preloader {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    /* Đổi thành màu của bạn */
}

.preloader__logo-icon i {
    color: #667eea;
    /* Đổi màu icon */
}
```

### Thay đổi kích thước
```css
.preloader__logo {
    width: 120px;
    height: 120px;
    /* Đổi kích thước logo */
}
```

### Thêm animation mới
```css
@keyframes myCustomAnimation {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

.preloader__logo {
    animation: myCustomAnimation 2s infinite;
}
```

## Best Practices

1. **Luôn ẩn preloader sau khi xong:** Đảm bảo gọi `hide()` trong `finally()` block
2. **Đặt timeout tối đa:** Tránh preloader hiển thị quá lâu nếu có lỗi
3. **Sử dụng đúng style:** Minimal cho dashboard, Default/House cho public pages
4. **Inline preloader cho loading states:** Dùng cho từng phần nhỏ trong trang
5. **Test trên mobile:** Đảm bảo preloader hiển thị tốt trên mọi thiết bị

## Demo

Truy cập `/demo/preloader` để xem các demo và ví dụ tương tác.

## Troubleshooting

### Preloader không ẩn
- Kiểm tra console có lỗi JavaScript không
- Đảm bảo đã include `preloader.js`
- Kiểm tra `window.Preloader` có tồn tại không

### Preloader không hiển thị
- Kiểm tra đã include `preloader.css`
- Xem z-index có bị override không
- Kiểm tra element có bị `display: none` không

### Animation không mượt
- Kiểm tra CSS transitions/animations
- Đảm bảo không có quá nhiều repaints/reflows
- Sử dụng `will-change` property nếu cần

## Support

Nếu có vấn đề hoặc câu hỏi, vui lòng:
1. Kiểm tra documentation này
2. Xem demo tại `/demo/preloader`
3. Kiểm tra console log
4. Liên hệ team phát triển

