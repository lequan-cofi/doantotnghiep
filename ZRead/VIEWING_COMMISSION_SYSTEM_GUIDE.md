# Hướng dẫn hệ thống sự kiện hoa hồng cho Viewing

## Tổng quan

Hệ thống sự kiện hoa hồng cho Viewing được thiết kế để tự động tạo sự kiện hoa hồng khi một viewing được hoàn thành (status = 'done'). Hệ thống sử dụng Observer pattern để tách biệt logic tạo sự kiện hoa hồng khỏi controller chính.

## Cấu trúc hệ thống

### 1. Models

#### Viewing Model
- **File**: `app/Models/Viewing.php`
- **Các trường quan trọng**:
  - `agent_id`: ID của agent thực hiện viewing
  - `organization_id`: ID của tổ chức
  - `unit_id`: ID của unit được xem
  - `status`: Trạng thái viewing ('requested', 'confirmed', 'done', 'no_show', 'cancelled')
  - `schedule_at`: Thời gian hẹn xem
  - `result_note`: Ghi chú kết quả

#### CommissionEvent Model
- **File**: `app/Models/CommissionEvent.php`
- **Các trường quan trọng**:
  - `trigger_event`: 'viewing_done' cho viewing
  - `ref_type`: 'viewing'
  - `ref_id`: ID của viewing
  - `agent_id`: ID của agent
  - `organization_id`: ID của tổ chức
  - `commission_total`: Tổng hoa hồng

### 2. Observers

#### ViewingObserver
- **File**: `app/Observers/ViewingObserver.php`
- **Chức năng**:
  - Tự động tạo sự kiện hoa hồng khi viewing status thay đổi thành 'done'
  - Không tạo sự kiện hoa hồng cho các status khác ('no_show', 'cancelled', etc.)
  - Sử dụng CommissionEventService để xử lý logic tạo sự kiện
  - **Không tạo hóa đơn** - sự kiện hoa hồng được tính thẳng vào phiếu lương

### 3. Services

#### CommissionEventService
- **File**: `app/Services/CommissionEventService.php`
- **Method chính**: `createCommissionEventsForViewing(Viewing $viewing)`
- **Chức năng**:
  - Tìm các chính sách hoa hồng có `trigger_event = 'viewing_done'`
  - Tính toán hoa hồng dựa trên chính sách
  - Tạo CommissionEvent records
  - Xử lý transaction để đảm bảo tính toàn vẹn dữ liệu
  - **Không tạo hóa đơn** - chỉ tạo sự kiện hoa hồng để tính vào phiếu lương

### 4. Database Schema

#### Bảng viewings
```sql
CREATE TABLE viewings (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    lead_id BIGINT UNSIGNED NULL,
    listing_id BIGINT UNSIGNED NULL,       -- Legacy field
    property_id BIGINT UNSIGNED NULL,      -- Thêm mới - chính
    agent_id BIGINT UNSIGNED NULL,
    organization_id BIGINT UNSIGNED NULL,  -- Thêm mới
    unit_id BIGINT UNSIGNED NULL,          -- Thêm mới
    lead_name VARCHAR(255) NULL,           -- Thêm mới
    lead_phone VARCHAR(255) NULL,          -- Thêm mới
    lead_email VARCHAR(255) NULL,          -- Thêm mới
    schedule_at DATETIME NOT NULL,
    status ENUM('requested','confirmed','done','no_show','cancelled') DEFAULT 'requested',
    result_note TEXT NULL,
    note TEXT NULL,                        -- Thêm mới
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    deleted_by BIGINT UNSIGNED NULL,
    
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL,
    INDEX idx_viewings_agent_time (agent_id, schedule_at),
    INDEX idx_viewings_status_time (status, schedule_at),
    INDEX idx_viewings_property_id (property_id)
);
```

## Cách hoạt động

### 1. Tạo Viewing
```php
$viewing = Viewing::create([
    'property_id' => $propertyId,        // Chính
    'agent_id' => $agentId,
    'organization_id' => $organizationId,
    'unit_id' => $unitId,
    'lead_name' => 'Tên khách hàng',
    'lead_phone' => '0901234567',
    'schedule_at' => now()->addHours(1),
    'status' => 'requested',
]);
```

### 2. Hoàn thành Viewing
```php
$viewing->update([
    'status' => 'done',
    'result_note' => 'Khách hàng hài lòng với phòng',
]);
```

### 3. Tự động tạo sự kiện hoa hồng
- ViewingObserver sẽ tự động phát hiện thay đổi status
- Gọi CommissionEventService để tạo sự kiện hoa hồng
- Tìm các chính sách hoa hồng có trigger_event = 'viewing_done'
- Tính toán và tạo CommissionEvent records
- **Không tạo hóa đơn** - sự kiện hoa hồng được tính thẳng vào phiếu lương

## Cấu hình chính sách hoa hồng

### Tạo chính sách hoa hồng cho viewing
```php
CommissionPolicy::create([
    'organization_id' => $organizationId,
    'code' => 'VIEWING_DONE',
    'title' => 'Hoa hồng xem phòng',
    'trigger_event' => 'viewing_done',
    'basis' => 'cash',
    'calc_type' => 'flat',
    'flat_amount' => 100000, // 100K VND
    'active' => true,
]);
```

### Các loại tính toán hoa hồng
- **flat**: Hoa hồng cố định
- **percent**: Hoa hồng theo phần trăm (dựa trên giá thuê property)
- **tiered**: Hoa hồng theo bậc (chưa implement)

## Logging và Debugging

### Logs quan trọng
- `ViewingObserver`: Log khi tạo sự kiện hoa hồng
- `CommissionEventService`: Log chi tiết quá trình tạo sự kiện
- `CommissionEventObserver`: Log khi tạo invoice cho sự kiện hoa hồng

### Kiểm tra logs
```bash
tail -f storage/logs/laravel.log | grep "ViewingObserver\|CommissionEventService"
```

## Testing

### Test tự động
```php
// Tạo viewing test
$viewing = Viewing::create([...]);

// Cập nhật status để trigger commission events
$viewing->update(['status' => 'done']);

// Kiểm tra commission events được tạo
$events = CommissionEvent::where('ref_type', 'viewing')
    ->where('ref_id', $viewing->id)
    ->get();
```

### Test thủ công
1. Tạo viewing với status 'requested'
2. Cập nhật status thành 'done'
3. Kiểm tra bảng `commission_events` có record mới
4. Kiểm tra bảng `invoices` có invoice tương ứng

## Lưu ý quan trọng

### 1. Điều kiện tạo sự kiện hoa hồng
- Viewing phải có `organization_id`
- Viewing phải có `agent_id`
- Status phải là 'done'
- Phải có chính sách hoa hồng active với trigger_event = 'viewing_done'

### 2. Không tạo sự kiện hoa hồng cho
- Status 'no_show'
- Status 'cancelled'
- Viewing không có organization_id
- Viewing không có agent_id

### 3. Tính toán base amount
- **flat**: Sử dụng `flat_amount` từ policy
- **percent**: Sử dụng giá thuê từ property (ưu tiên `property_id` trực tiếp, fallback qua `unit.property`, default 1M VND nếu không có)

### 4. Transaction safety
- Tất cả operations được wrap trong DB transaction
- Rollback nếu có lỗi xảy ra
- Log chi tiết để debug

## Troubleshooting

### Lỗi thường gặp

#### 1. Không tạo được sự kiện hoa hồng
- Kiểm tra có chính sách hoa hồng với trigger_event = 'viewing_done'
- Kiểm tra viewing có organization_id và agent_id
- Kiểm tra status có phải 'done'

#### 2. Lỗi foreign key constraint
- Kiểm tra organization_id có tồn tại trong bảng organizations
- Kiểm tra agent_id có tồn tại trong bảng users
- Kiểm tra unit_id có tồn tại trong bảng units

#### 3. Lỗi tính toán hoa hồng
- Kiểm tra calc_type trong policy
- Kiểm tra flat_amount hoặc percent_amount
- Kiểm tra min_amount và max_amount

### Debug commands
```bash
# Kiểm tra viewing policies
php artisan tinker --execute="CommissionPolicy::where('trigger_event', 'viewing_done')->get();"

# Kiểm tra commission events
php artisan tinker --execute="CommissionEvent::where('ref_type', 'viewing')->get();"

# Kiểm tra viewings
php artisan tinker --execute="Viewing::where('status', 'done')->get();"
```

## Kết luận

Hệ thống sự kiện hoa hồng cho Viewing hoạt động hoàn toàn tự động thông qua Observer pattern, đảm bảo:
- Tách biệt logic khỏi controller
- Tự động tạo sự kiện hoa hồng khi viewing hoàn thành
- Tính toán chính xác theo chính sách
- Tích hợp trực tiếp với hệ thống lương
- Logging chi tiết để debug và monitoring

Hệ thống đã được test kỹ lưỡng và sẵn sàng sử dụng trong production.

## Tích hợp với Hệ thống Lương

### Cách sử dụng Commission Events cho Phiếu lương
```php
// Lấy tất cả sự kiện hoa hồng của agent trong tháng
$commissionEvents = CommissionEvent::where('agent_id', $agentId)
    ->where('status', 'pending') // Hoặc 'approved'
    ->whereMonth('occurred_at', $month)
    ->whereYear('occurred_at', $year)
    ->get();

// Tính tổng hoa hồng
$totalCommission = $commissionEvents->sum('commission_total');

// Cập nhật trạng thái sau khi tính vào lương
$commissionEvents->each(function($event) {
    $event->update(['status' => 'paid']);
});
```

### Trạng thái Commission Events
- **pending**: Chờ xử lý
- **approved**: Đã duyệt, sẵn sàng tính vào lương
- **paid**: Đã thanh toán trong phiếu lương
- **cancelled**: Đã hủy

### Lợi ích của việc tích hợp với Lương
1. **Tự động hóa**: Không cần tạo hóa đơn thủ công
2. **Tập trung**: Tất cả thu nhập trong một phiếu lương
3. **Minh bạch**: Dễ theo dõi và báo cáo
4. **Hiệu quả**: Giảm thời gian xử lý hành chính

## Cập nhật Property ID

### Thay đổi quan trọng
- **Thêm cột `property_id`**: Trực tiếp liên kết viewing với property
- **Tối ưu query**: Sử dụng `property_id` thay vì `whereHas('unit')`
- **Backward compatibility**: Vẫn giữ `listing_id` để tương thích ngược

### Lợi ích của property_id
1. **Performance**: Query trực tiếp nhanh hơn `whereHas`
2. **Simplicity**: Không cần join qua bảng units
3. **Clarity**: Rõ ràng hơn về mối quan hệ
4. **Flexibility**: Có thể xem property mà không cần unit cụ thể

### Controller Updates
```php
// Cũ (chậm)
$viewings = Viewing::whereHas('unit', function($q) use ($propertyIds) {
    $q->whereIn('property_id', $propertyIds);
})->get();

// Mới (nhanh)
$viewings = Viewing::whereIn('property_id', $propertyIds)->get();
```

### CommissionEventService Updates
```php
// Ưu tiên property_id trực tiếp
if ($viewing->property) {
    return $viewing->property->price ?? 1000000;
} elseif ($viewing->unit && $viewing->unit->property) {
    return $viewing->unit->property->price ?? 1000000;
}
```

### Migration History
1. `2025_10_13_043501_add_organization_id_to_viewings_table.php`
2. `2025_10_13_043841_add_missing_columns_to_viewings_table.php`
3. `2025_10_13_044237_add_property_id_to_viewings_table.php`

### Testing Results
- ✅ Database structure: All required columns exist
- ✅ Viewing model: property_id field and relationships working
- ✅ CommissionEventService: Can access property directly via property_id
- ✅ Controller optimization: Direct property_id queries working
- ✅ Commission events: Auto-created when viewing status = 'done'
- ✅ System fully functional with property_id integration

## Loại bỏ Hệ thống Hóa đơn

### Thay đổi quan trọng
- **Loại bỏ CommissionEventObserver**: Không còn tạo hóa đơn tự động
- **Loại bỏ CommissionInvoiceService**: Service tạo hóa đơn đã bị xóa
- **Loại bỏ invoice_id**: Cột invoice_id đã bị xóa khỏi bảng commission_events
- **Tích hợp phiếu lương**: Sự kiện hoa hồng được tính thẳng vào phiếu lương

### Lý do thay đổi
1. **Đơn giản hóa**: Không cần tạo hóa đơn riêng cho từng sự kiện hoa hồng
2. **Tích hợp lương**: Hoa hồng được tính trực tiếp vào phiếu lương
3. **Giảm phức tạp**: Loại bỏ logic tạo và quản lý hóa đơn
4. **Hiệu quả**: Tập trung vào việc tính toán hoa hồng chính xác

### Files đã loại bỏ
- `app/Observers/CommissionEventObserver.php`
- `app/Services/CommissionInvoiceService.php`
- Routes: `/commission-events/{id}/sync-invoice`
- Routes: `/commission-events/bulk-sync-invoices`

### Database Changes
```sql
-- Migration: remove_invoice_id_from_commission_events_table
ALTER TABLE commission_events 
DROP FOREIGN KEY commission_events_invoice_id_foreign,
DROP INDEX commission_events_invoice_id_index,
DROP COLUMN invoice_id;
```

### CommissionEvent Model Updates
```php
// Removed from fillable array
'invoice_id', // Removed

// Removed relationship
public function invoice() // Removed
{
    return $this->belongsTo(Invoice::class);
}
```

### Testing Results (Without Invoice)
- ✅ Database structure: invoice_id column removed from commission_events
- ✅ CommissionEventObserver: Successfully removed
- ✅ CommissionInvoiceService: Successfully removed
- ✅ CommissionEvent model: Working without invoice_id field
- ✅ Commission events: Created without invoice generation
- ✅ System working correctly without invoice creation
- ✅ Commission events ready for salary integration
