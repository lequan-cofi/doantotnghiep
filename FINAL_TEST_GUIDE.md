# Final Test Guide: Kiểm tra hệ thống load phòng

## Dữ liệu hiện tại
- **7 Properties** (bất động sản)
- **34 Units** (phòng)
- **22 units available, 12 units occupied**

## Cách test hệ thống

### 1. Test Page
```
URL: http://127.0.0.1:8000/agent/test-units-page
```

**Các nút test:**
1. **Test Units Route** - Test route `/agent/units-test` với property ID
2. **Test Data Route** - Test route `/agent/data-test` để xem tất cả dữ liệu
3. **Test Basic Route** - Test route cơ bản
4. **Test Original Route** - Test route gốc `/agent/meters/get-units`

### 2. Test Form
```
URL: http://127.0.0.1:8000/agent/meters/create
```

**Cách test:**
1. Chọn property từ dropdown
2. Kiểm tra units dropdown được populate
3. Kiểm tra console cho errors

### 3. Test Data Route
```
URL: http://127.0.0.1:8000/agent/data-test
```

**Kết quả mong đợi:**
```json
{
  "properties_count": 7,
  "units_count": 34,
  "properties": [
    {"id": 1, "name": "Property 1"},
    {"id": 2, "name": "Property 2"},
    ...
  ],
  "units_by_property": {
    "1": {
      "property_name": "Property 1",
      "units_count": 5,
      "units": [
        {"id": 1, "code": "A101", "unit_type": "Studio", "status": "available"},
        ...
      ]
    }
  }
}
```

## Expected Results

### Test Units Route:
```json
{
  "message": "Units loaded successfully",
  "property_id": "1",
  "units": [
    {"id": 1, "code": "A101", "unit_type": "Studio"},
    {"id": 2, "code": "A102", "unit_type": "1BR"}
  ]
}
```

### Test Form:
- **Chọn property**: Units dropdown được populate với units
- **No units**: Hiển thị "Không có phòng nào"
- **Error**: Hiển thị error message + notification

## Troubleshooting

### Nếu Test Data Route hoạt động:
- Database connection OK
- Dữ liệu có sẵn
- Vấn đề là với route cụ thể

### Nếu Test Data Route không hoạt động:
- Database connection issue
- Model issue
- Laravel configuration issue

### Nếu Test Units Route hoạt động:
- Route test OK
- Sử dụng route này tạm thời
- Form sẽ hoạt động

### Nếu Test Units Route không hoạt động:
- Kiểm tra property ID
- Kiểm tra database query
- Kiểm tra Laravel logs

## Quick Fix

Nếu tất cả test routes hoạt động nhưng form không hoạt động:

1. **Kiểm tra JavaScript**:
   - Mở Developer Tools (F12)
   - Kiểm tra Console tab
   - Kiểm tra Network tab

2. **Kiểm tra CSRF Token**:
   - Đảm bảo có `<meta name="csrf-token" content="{{ csrf_token() }}">`
   - Kiểm tra token trong request headers

3. **Kiểm tra Authentication**:
   - Đảm bảo đã đăng nhập với role agent
   - Kiểm tra session

## Test Steps

### Bước 1: Test Data Route
```
GET /agent/data-test
```
- Kiểm tra có dữ liệu không
- Kiểm tra format JSON

### Bước 2: Test Units Route
```
GET /agent/units-test?property_id=1
```
- Kiểm tra có units cho property 1 không
- Kiểm tra format JSON

### Bước 3: Test Form
```
GET /agent/meters/create
```
- Chọn property từ dropdown
- Kiểm tra units dropdown
- Kiểm tra console errors

### Bước 4: Test Original Route
```
GET /agent/meters/get-units?property_id=1
```
- Kiểm tra route gốc có hoạt động không
- So sánh với test route

## Success Criteria

✅ **Test Data Route**: Trả về JSON với properties và units
✅ **Test Units Route**: Trả về JSON với units cho property
✅ **Test Form**: Units dropdown được populate
✅ **No Console Errors**: Không có JavaScript errors
✅ **No Network Errors**: Không có HTTP errors

## Next Steps

### Nếu tất cả test hoạt động:
1. Sử dụng test route tạm thời
2. Debug controller method
3. Fix controller method
4. Chuyển lại về route gốc

### Nếu test không hoạt động:
1. Kiểm tra database connection
2. Kiểm tra Laravel configuration
3. Kiểm tra authentication
4. Kiểm tra middleware

---

**Lưu ý**: Test page chỉ nên sử dụng trong development. Xóa trong production.
