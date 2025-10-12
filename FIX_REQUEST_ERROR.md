# Fix: Lỗi Request Property

## Vấn đề
```
"error": "Undefined property: Illuminate\\Support\\Facades\\Request::$property_id",
"units": []
```

## Nguyên nhân
Lỗi này xảy ra vì:
1. **Import sai class Request** - Sử dụng `Request` thay vì `\Illuminate\Http\Request`
2. **Syntax sai** - Sử dụng `$request->property_id` thay vì `request('property_id')`

## Giải pháp đã áp dụng

### 1. Sửa Route Test
```php
// Trước (SAI):
Route::get('/simple-test', function(Request $request) {
    $propertyId = $request->property_id;
});

// Sau (ĐÚNG):
Route::get('/units-test', function() {
    $propertyId = request('property_id');
});
```

### 2. Tạo Route Test Đơn Giản
```php
Route::get('/units-test', function() {
    $propertyId = request('property_id');
    
    if (!$propertyId) {
        return response()->json([
            'message' => 'No property ID provided',
            'property_id' => $propertyId,
            'units' => []
        ]);
    }
    
    try {
        $units = \App\Models\Unit::where('property_id', $propertyId)
            ->select('id', 'code', 'unit_type')
            ->get();
        
        return response()->json([
            'message' => 'Units loaded successfully',
            'property_id' => $propertyId,
            'units' => $units
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error loading units',
            'property_id' => $propertyId,
            'error' => $e->getMessage(),
            'units' => []
        ]);
    }
});
```

### 3. Cập nhật JavaScript
```javascript
// Thay đổi từ:
fetch(`/agent/simple-test?property_id=${propertyId}`, {

// Thành:
fetch(`/agent/units-test?property_id=${propertyId}`, {
```

## Cách Test

### 1. Truy cập Test Page
```
URL: http://127.0.0.1:8000/agent/test-units-page
```

### 2. Test Routes
1. **Test Units Route**: Test route mới với logic đầy đủ
2. **Test Basic Route**: Test route cơ bản
3. **Test Original Route**: Test route gốc

### 3. Test Form
1. Truy cập: `/agent/meters/create`
2. Chọn property từ dropdown
3. Kiểm tra units dropdown được populate

## Expected Results

### Test Page:
- **Units Route**: Trả về JSON với units data
- **Basic Route**: Trả về JSON với message
- **Original Route**: Trả về JSON hoặc error

### Form:
- **Chọn property**: Units dropdown được populate
- **No units**: Hiển thị "Không có phòng nào"
- **Error**: Hiển thị error message

## Troubleshooting

### Nếu Units Route hoạt động:
- Vấn đề đã được fix
- Sử dụng route này tạm thời

### Nếu Units Route không hoạt động:
- Kiểm tra database connection
- Kiểm tra có dữ liệu units không
- Kiểm tra Laravel logs

### Nếu tất cả routes đều lỗi:
- Kiểm tra authentication
- Kiểm tra middleware
- Kiểm tra server status

## Code Changes Made

### 1. routes/web.php
```php
// Thêm route test mới
Route::get('/units-test', function() {
    $propertyId = request('property_id');
    
    if (!$propertyId) {
        return response()->json([
            'message' => 'No property ID provided',
            'property_id' => $propertyId,
            'units' => []
        ]);
    }
    
    try {
        $units = \App\Models\Unit::where('property_id', $propertyId)
            ->select('id', 'code', 'unit_type')
            ->get();
        
        return response()->json([
            'message' => 'Units loaded successfully',
            'property_id' => $propertyId,
            'units' => $units
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error loading units',
            'property_id' => $propertyId,
            'error' => $e->getMessage(),
            'units' => []
        ]);
    }
});
```

### 2. JavaScript (create.blade.php)
```javascript
// Thay đổi URL
fetch(`/agent/units-test?property_id=${propertyId}`, {
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

### 3. Test Page (test-units.blade.php)
```html
<button class="btn btn-primary" onclick="testRoute('units-test')">Test Units Route</button>
```

## Quick Fix

Để fix nhanh, thay đổi JavaScript:

```javascript
// Trong create.blade.php và edit.blade.php
// Thay đổi từ:
fetch(`/agent/simple-test?property_id=${propertyId}`, {

// Thành:
fetch(`/agent/units-test?property_id=${propertyId}`, {
```

## Next Steps

### Nếu Units Route hoạt động:
1. Sử dụng route này tạm thời
2. Debug controller method sau
3. Fix controller method
4. Chuyển lại về route gốc

### Nếu Units Route không hoạt động:
1. Kiểm tra database
2. Kiểm tra dữ liệu
3. Kiểm tra Laravel logs
4. Kiểm tra authentication

---

**Lưu ý**: Route test chỉ nên sử dụng trong development. Xóa trong production.
