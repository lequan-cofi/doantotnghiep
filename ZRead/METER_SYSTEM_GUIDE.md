# Hệ thống Quản lý Công tơ đo - Meter Management System

## Tổng quan

Hệ thống quản lý công tơ đo được thiết kế để quản lý các công tơ điện, nước trong bất động sản và tự động tính toán hóa đơn theo tháng.

## Tính năng chính

### 1. Quản lý Công tơ đo (Meters)
- ✅ CRUD đầy đủ cho công tơ đo
- ✅ Liên kết với bất động sản, phòng và dịch vụ
- ✅ Theo dõi trạng thái hoạt động
- ✅ Quản lý số seri và ngày lắp đặt

### 2. Quản lý Số liệu đo (Meter Readings)
- ✅ Ghi nhận số liệu đo hàng ngày
- ✅ Upload hình ảnh công tơ
- ✅ Tính toán lượng sử dụng tự động
- ✅ Validation số liệu (không được nhỏ hơn số trước)

### 3. Tính toán Hóa đơn Tự động
- ✅ Tự động tạo hóa đơn theo tháng
- ✅ Tính tiền dựa trên lượng sử dụng và đơn giá
- ✅ Liên kết với hợp đồng thuê
- ✅ Lưu trữ lịch sử tính tiền

### 4. Giao diện Người dùng
- ✅ Giao diện responsive, thân thiện
- ✅ Notification system tích hợp
- ✅ AJAX forms với loading states
- ✅ Confirmation dialogs
- ✅ Image preview và upload

## Cấu trúc Database

### Bảng `meters`
```sql
- id (Primary Key)
- property_id (Foreign Key)
- unit_id (Foreign Key)
- service_id (Foreign Key)
- serial_no (String)
- installed_at (Date)
- status (Boolean)
- created_at, updated_at, deleted_at
```

### Bảng `meter_readings`
```sql
- id (Primary Key)
- meter_id (Foreign Key)
- reading_date (Date)
- value (Decimal)
- image_url (String, nullable)
- taken_by (Foreign Key to users)
- note (Text, nullable)
- created_at, updated_at
```

## Cách sử dụng

### 1. Truy cập hệ thống
```
URL: /agent/meters
```

### 2. Tạo công tơ đo mới
1. Click "Thêm công tơ mới"
2. Chọn bất động sản
3. Chọn phòng
4. Chọn loại dịch vụ (điện/nước)
5. Nhập số seri công tơ
6. Chọn ngày lắp đặt
7. Click "Lưu công tơ"

### 3. Thêm số liệu đo
1. Từ danh sách công tơ, click "Thêm số liệu đo"
2. Chọn công tơ
3. Nhập ngày đo
4. Nhập số liệu (phải >= số liệu trước)
5. Upload hình ảnh (tùy chọn)
6. Thêm ghi chú (tùy chọn)
7. Click "Lưu số liệu"

### 4. Xem lịch sử và tính tiền
1. Click vào công tơ để xem chi tiết
2. Xem lịch sử số liệu đo
3. Xem lịch sử tính tiền theo tháng
4. Theo dõi lượng sử dụng và chi phí

## API Endpoints

### Meters
- `GET /agent/meters` - Danh sách công tơ
- `GET /agent/meters/create` - Form tạo mới
- `POST /agent/meters` - Lưu công tơ mới
- `GET /agent/meters/{id}` - Chi tiết công tơ
- `GET /agent/meters/{id}/edit` - Form chỉnh sửa
- `PUT /agent/meters/{id}` - Cập nhật công tơ
- `DELETE /agent/meters/{id}` - Xóa công tơ
- `GET /agent/meters/get-units` - Lấy danh sách phòng theo bất động sản

### Meter Readings
- `GET /agent/meter-readings` - Danh sách số liệu đo
- `GET /agent/meter-readings/create` - Form tạo mới
- `POST /agent/meter-readings` - Lưu số liệu mới
- `GET /agent/meter-readings/{id}` - Chi tiết số liệu
- `GET /agent/meter-readings/{id}/edit` - Form chỉnh sửa
- `PUT /agent/meter-readings/{id}` - Cập nhật số liệu
- `DELETE /agent/meter-readings/{id}` - Xóa số liệu
- `GET /agent/meter-readings/get-last-reading` - Lấy số liệu cuối

## Services

### MeterBillingService
Service chính xử lý logic tính tiền:

```php
$billingService = new MeterBillingService();

// Xử lý billing cho một reading
$billingService->processBilling($reading);

// Lấy lịch sử billing
$history = $billingService->getBillingHistory($meterId);

// Tính usage theo tháng
$usage = $billingService->calculateMonthlyUsage($meterId, $year, $month);

// Tạo báo cáo tháng
$report = $billingService->generateMonthlyReport($organizationId, $year, $month);
```

## Notification System

Hệ thống sử dụng notification system tích hợp:

```javascript
// Success notification
Notify.success('Thành công!', 'Tiêu đề');

// Error notification
Notify.error('Có lỗi xảy ra!', 'Lỗi');

// Warning notification
Notify.warning('Cảnh báo!', 'Cảnh báo');

// Info notification
Notify.info('Thông tin', 'Thông tin');

// Confirmation dialog
Notify.confirm({
    title: 'Xác nhận xóa',
    message: 'Bạn có chắc chắn muốn xóa?',
    onConfirm: function() {
        // Xử lý xác nhận
    }
});
```

## Validation Rules

### Meter
- `property_id`: Required, exists in properties table
- `unit_id`: Required, exists in units table
- `service_id`: Required, exists in services table
- `serial_no`: Required, string, max 255 characters
- `installed_at`: Required, date
- `status`: Boolean

### Meter Reading
- `meter_id`: Required, exists in meters table
- `reading_date`: Required, date
- `value`: Required, numeric, min 0
- `image`: Optional, image file, max 2MB
- `note`: Optional, string, max 1000 characters

## Business Logic

### Tính tiền tự động
1. Khi thêm số liệu đo mới, hệ thống tự động:
   - Tìm số liệu đo trước đó
   - Tính lượng sử dụng = số hiện tại - số trước
   - Lấy đơn giá từ hợp đồng thuê
   - Tính chi phí = lượng sử dụng × đơn giá
   - Tạo/cập nhật hóa đơn tháng

### Validation số liệu
- Số liệu mới phải >= số liệu trước
- Không được có 2 số liệu cùng ngày
- Phải có hợp đồng thuê đang hoạt động
- Phải có cấu hình giá dịch vụ

## File Structure

```
app/
├── Http/Controllers/Agent/
│   ├── MeterController.php
│   └── MeterReadingController.php
├── Models/
│   ├── Meter.php
│   └── MeterReading.php
├── Services/
│   └── MeterBillingService.php
└── Traits/
    └── NotificationTrait.php

resources/views/agent/
├── meters/
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── show.blade.php
│   └── edit.blade.php
└── meter-readings/
    ├── index.blade.php
    ├── create.blade.php
    ├── show.blade.php
    └── edit.blade.php

public/assets/
├── css/notifications.css
└── js/notifications.js
```

## Testing

Chạy test script để kiểm tra hệ thống:

```bash
php test_meter_system.php
```

## Troubleshooting

### Lỗi thường gặp

1. **"Không thể xóa công tơ đã có số liệu đo"**
   - Giải pháp: Xóa tất cả số liệu đo trước khi xóa công tơ

2. **"Số liệu đo mới không được nhỏ hơn số liệu trước"**
   - Giải pháp: Kiểm tra lại số liệu, có thể công tơ đã được reset

3. **"Đã tồn tại số liệu đo cho ngày này"**
   - Giải pháp: Chọn ngày khác hoặc cập nhật số liệu hiện có

4. **"Không tìm thấy hợp đồng thuê"**
   - Giải pháp: Đảm bảo phòng có hợp đồng thuê đang hoạt động

## Future Enhancements

- [ ] Báo cáo thống kê sử dụng
- [ ] Export dữ liệu Excel/PDF
- [ ] API cho mobile app
- [ ] Tự động gửi hóa đơn qua email
- [ ] Dashboard với biểu đồ
- [ ] Cảnh báo sử dụng bất thường
- [ ] Tích hợp thanh toán online

## Support

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra log file: `storage/logs/laravel.log`
2. Chạy test script: `php test_meter_system.php`
3. Kiểm tra database connection
4. Verify file permissions

---

**Phiên bản:** 1.0.0  
**Ngày tạo:** 12/10/2025  
**Tác giả:** QLPhongTro Team
