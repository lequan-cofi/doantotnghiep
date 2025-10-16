# Troubleshooting: Lỗi khi load phòng của bất động sản

## Vấn đề
Khi chọn bất động sản trong form tạo/chỉnh sửa công tơ đo, danh sách phòng không được load hoặc hiển thị lỗi.

## Các bước kiểm tra và sửa lỗi

### 1. Kiểm tra dữ liệu cơ sở

#### Truy cập trang debug:
```
URL: /agent/debug-units
```

Trang này sẽ giúp bạn:
- Test AJAX call với Property ID
- Kiểm tra endpoint `/agent/meters/get-units`
- Xem response data chi tiết

#### Kiểm tra dữ liệu trong database:
```sql
-- Kiểm tra có properties không
SELECT COUNT(*) FROM properties WHERE deleted_at IS NULL;

-- Kiểm tra có units không
SELECT COUNT(*) FROM units WHERE deleted_at IS NULL;

-- Kiểm tra units của một property cụ thể
SELECT u.id, u.code, u.unit_type, p.name as property_name 
FROM units u 
JOIN properties p ON u.property_id = p.id 
WHERE u.property_id = 1 AND u.deleted_at IS NULL;
```

### 2. Kiểm tra Browser Console

Mở Developer Tools (F12) và kiểm tra:

#### Console Tab:
- Có lỗi JavaScript nào không?
- Có lỗi network không?

#### Network Tab:
- Request có được gửi đi không?
- Response status code là gì? (200, 404, 500?)
- Response data có đúng không?

### 3. Kiểm tra Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

Tìm các lỗi liên quan đến:
- Database connection
- Model queries
- Route not found

### 4. Kiểm tra Routes

```bash
php artisan route:list | grep meters
```

Đảm bảo route `agent.meters.get-units` tồn tại.

### 5. Các lỗi thường gặp và cách sửa

#### Lỗi 1: "Route not found"
**Nguyên nhân:** Route chưa được đăng ký hoặc cache route
**Giải pháp:**
```bash
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

#### Lỗi 2: "Property not found"
**Nguyên nhân:** Không có dữ liệu properties hoặc units
**Giải pháp:**
1. Tạo properties và units trong database
2. Hoặc sử dụng seeder:
```bash
php artisan db:seed --class=PropertySeeder
php artisan db:seed --class=UnitSeeder
```

#### Lỗi 3: "CORS error"
**Nguyên nhân:** Cross-Origin Request bị chặn
**Giải pháp:** Kiểm tra middleware và headers trong request

#### Lỗi 4: "Database connection error"
**Nguyên nhân:** Không kết nối được database
**Giải pháp:**
1. Kiểm tra file `.env`
2. Test connection:
```bash
php artisan tinker
>>> DB::connection()->getPdo();
```

#### Lỗi 5: "JavaScript error"
**Nguyên nhân:** Lỗi trong code JavaScript
**Giải pháp:**
1. Kiểm tra console errors
2. Đảm bảo jQuery/Bootstrap được load
3. Kiểm tra syntax JavaScript

### 6. Test thủ công

#### Test endpoint trực tiếp:
```bash
curl -X GET "http://your-domain/agent/meters/get-units?property_id=1" \
     -H "Accept: application/json" \
     -H "X-Requested-With: XMLHttpRequest"
```

#### Test với Postman:
- Method: GET
- URL: `/agent/meters/get-units?property_id=1`
- Headers:
  - Accept: application/json
  - X-Requested-With: XMLHttpRequest

### 7. Code fixes đã áp dụng

#### Controller (MeterController.php):
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

#### JavaScript (create.blade.php & edit.blade.php):
```javascript
fetch(`/agent/meters/get-units?property_id=${propertyId}`, {
    method: 'GET',
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    }
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
```

### 8. Checklist để fix lỗi

- [ ] Kiểm tra có dữ liệu properties và units trong database
- [ ] Kiểm tra routes đã được đăng ký
- [ ] Clear cache Laravel
- [ ] Kiểm tra browser console cho JavaScript errors
- [ ] Kiểm tra network tab cho HTTP errors
- [ ] Kiểm tra Laravel logs
- [ ] Test endpoint trực tiếp với curl/Postman
- [ ] Kiểm tra middleware authentication
- [ ] Kiểm tra CSRF token
- [ ] Kiểm tra file permissions

### 9. Liên hệ hỗ trợ

Nếu vẫn gặp vấn đề, vui lòng cung cấp:
1. Error message chi tiết
2. Browser console logs
3. Laravel logs
4. Database data (properties và units)
5. Steps to reproduce

---

**Lưu ý:** Trang debug `/agent/debug-units` chỉ nên sử dụng trong môi trường development và nên xóa trong production.
