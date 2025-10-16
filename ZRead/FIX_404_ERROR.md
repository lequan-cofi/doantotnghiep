# Fix: Lỗi 404 khi load phòng của bất động sản

## Vấn đề
```
GET http://127.0.0.1:8000/agent/meters/get-units?property_id=5 404 (Not Found)
Error loading units: Error: HTTP error! status: 404
```

## Nguyên nhân
Lỗi 404 có thể do:
1. **Route cache** - Routes chưa được clear
2. **Authentication** - Endpoint cần authentication nhưng không có session
3. **CSRF Token** - Thiếu CSRF token trong AJAX request
4. **Middleware** - Middleware `ensure.agent` chặn request

## Giải pháp đã áp dụng

### 1. Clear Cache Laravel
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 2. Cập nhật JavaScript với CSRF Token
```javascript
fetch(`/agent/meters/get-units?property_id=${propertyId}`, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    credentials: 'same-origin'
})
```

### 3. Đảm bảo CSRF Token có trong layout
```html
<meta name="csrf-token" content="{{ csrf_token() }}">
```

### 4. Thêm error handling tốt hơn
```javascript
.then(response => {
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
})
.catch(error => {
    console.error('Error loading units:', error);
    unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
    
    if (typeof Notify !== 'undefined') {
        Notify.error('Không thể tải danh sách phòng. Vui lòng thử lại.');
    }
});
```

## Cách kiểm tra

### 1. Kiểm tra Routes
```bash
php artisan route:list | findstr meters
```

Kết quả mong đợi:
```
GET|HEAD  agent/meters/get-units agent.meters.get-units › Agent\MeterController@getUnits
```

### 2. Kiểm tra Authentication
- Đảm bảo đã đăng nhập với role `agent`
- Kiểm tra session có `auth_role_key = 'agent'`

### 3. Test với Debug Page
Truy cập: `/agent/debug-units`
- Test endpoint trực tiếp
- Xem response data
- Kiểm tra console errors

### 4. Kiểm tra Browser Console
- Mở Developer Tools (F12)
- Kiểm tra Console tab cho JavaScript errors
- Kiểm tra Network tab cho HTTP requests

## Troubleshooting Steps

### Bước 1: Clear Cache
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### Bước 2: Kiểm tra Authentication
- Đăng nhập lại với tài khoản agent
- Kiểm tra session trong browser

### Bước 3: Test Endpoint
```bash
# Test với curl (sẽ trả về 401 vì không có session)
curl -X GET "http://127.0.0.1:8000/agent/meters/get-units?property_id=1"
```

### Bước 4: Kiểm tra Database
```sql
-- Kiểm tra có properties không
SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL;

-- Kiểm tra có units không  
SELECT COUNT(*) FROM units WHERE deleted_at IS NULL;
```

### Bước 5: Kiểm tra Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

## Code Changes Made

### 1. MeterController.php
```php
public function getUnits(Request $request)
{
    try {
        $propertyId = $request->property_id;
        
        if (!$propertyId) {
            return response()->json(['units' => []]);
        }

        $units = Unit::where('property_id', $propertyId)
            ->select('id', 'code', 'unit_type')
            ->get();

        return response()->json(['units' => $units]);

    } catch (\Exception $e) {
        \Log::error('Error loading units for property: ' . $e->getMessage(), [
            'property_id' => $request->property_id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'error' => 'Có lỗi xảy ra khi tải danh sách phòng',
            'units' => []
        ], 500);
    }
}
```

### 2. JavaScript (create.blade.php & edit.blade.php)
```javascript
// Load units when property changes
propertySelect.addEventListener('change', function() {
    const propertyId = this.value;
    unitSelect.innerHTML = '<option value="">Đang tải...</option>';
    
    if (propertyId) {
        fetch(`/agent/meters/get-units?property_id=${propertyId}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            credentials: 'same-origin'
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
            
            if (data.units && data.units.length > 0) {
                data.units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = `${unit.code} - ${unit.unit_type}`;
                    unitSelect.appendChild(option);
                });
            } else {
                unitSelect.innerHTML = '<option value="">Không có phòng nào</option>';
            }
        })
        .catch(error => {
            console.error('Error loading units:', error);
            unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
            
            if (typeof Notify !== 'undefined') {
                Notify.error('Không thể tải danh sách phòng. Vui lòng thử lại.');
            }
        });
    } else {
        unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
    }
});
```

## Expected Results

Sau khi áp dụng các fix:

1. **Route được tìm thấy**: Không còn lỗi 404
2. **Authentication OK**: Request được xử lý với session hợp lệ
3. **CSRF Token**: Request có đầy đủ headers
4. **Error Handling**: Hiển thị thông báo lỗi rõ ràng
5. **Data Loading**: Units được load thành công

## Test Cases

### Test 1: Load Units Successfully
- Chọn property có units
- Kết quả: Dropdown được populate với units

### Test 2: No Units Available
- Chọn property không có units
- Kết quả: Hiển thị "Không có phòng nào"

### Test 3: Network Error
- Disconnect internet
- Kết quả: Hiển thị "Lỗi tải dữ liệu" + notification

### Test 4: Server Error
- Server trả về 500 error
- Kết quả: Hiển thị error message + notification

---

**Lưu ý**: Nếu vẫn gặp lỗi 404, hãy kiểm tra:
1. Server có đang chạy không
2. URL có đúng không
3. Route có được đăng ký không
4. Middleware có chặn request không
