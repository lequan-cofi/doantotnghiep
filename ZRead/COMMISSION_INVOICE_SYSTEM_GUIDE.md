# Hệ Thống Hóa Đơn Hoa Hồng Tự Động

## Tổng Quan

Hệ thống hóa đơn hoa hồng tự động được thiết kế để tự động tạo hóa đơn cho các sự kiện hoa hồng khi:
- Hợp đồng được ký (lease_signed)
- Tiền cọc được thanh toán (deposit_paid)

## Các Thành Phần Chính

### 1. CommissionInvoiceService
**File:** `app/Services/CommissionInvoiceService.php`

Service chính xử lý logic tạo và cập nhật hóa đơn hoa hồng.

**Các method chính:**
- `createInvoiceForCommissionEvent($commissionEvent)`: Tạo hóa đơn cho sự kiện hoa hồng
- `updateInvoiceForCommissionEvent($commissionEvent)`: Cập nhật hóa đơn khi sự kiện thay đổi
- `createInvoicesForMultipleEvents($eventIds)`: Tạo hóa đơn cho nhiều sự kiện
- `syncInvoiceWithLeaseData($commissionEvent)`: Đồng bộ hóa đơn với dữ liệu hợp đồng

### 2. CommissionEventObserver
**File:** `app/Observers/CommissionEventObserver.php`

Observer tự động tạo hóa đơn khi sự kiện hoa hồng được tạo hoặc cập nhật.

**Các event được xử lý:**
- `created`: Tự động tạo hóa đơn khi sự kiện hoa hồng được tạo
- `updated`: Cập nhật hóa đơn khi có thay đổi quan trọng
- `deleted`: Xử lý khi sự kiện hoa hồng bị xóa

### 3. Cập Nhật Model
**File:** `app/Models/CommissionEvent.php`

Đã thêm:
- Cột `invoice_id` vào fillable
- Relationship `invoice()` để liên kết với hóa đơn

### 4. Migration
**File:** `database/migrations/2025_10_13_041652_add_invoice_id_to_commission_events_table.php`

Thêm cột `invoice_id` vào bảng `commission_events` với foreign key constraint.

## Cách Hoạt Động

### 1. Tự Động Tạo Hóa Đơn
Khi một sự kiện hoa hồng được tạo (thông qua LeaseController), hệ thống sẽ:

1. **CommissionEventObserver** được kích hoạt
2. Gọi **CommissionInvoiceService** để tạo hóa đơn
3. Tạo hóa đơn với thông tin:
   - Số hóa đơn: `CI{YYYYMMDD}{ORG_ID}{RANDOM}`
   - Trạng thái: `draft`
   - Ngày phát hành: Ngày hiện tại
   - Ngày đến hạn: 30 ngày sau
   - Tổng tiền: Số tiền hoa hồng
4. Tạo chi tiết hóa đơn với:
   - Loại: `other`
   - Mô tả: Chi tiết về hoa hồng
   - Số tiền: Số tiền hoa hồng
   - Metadata: Thông tin chính sách và sự kiện

### 2. Cập Nhật Hóa Đơn
Khi sự kiện hoa hồng được cập nhật, hệ thống sẽ:
1. Kiểm tra các trường quan trọng có thay đổi không
2. Cập nhật hóa đơn tương ứng
3. Cập nhật chi tiết hóa đơn

### 3. Đồng Bộ Dữ Liệu
Hệ thống tự động đồng bộ thông tin từ:
- Hợp đồng thuê (lease)
- Tiền gửi (booking deposit)
- Chính sách hoa hồng (commission policy)

## API Endpoints

### Agent Routes
- `POST /commission-events/{id}/sync-invoice`: Đồng bộ hóa đơn cho sự kiện cụ thể
- `POST /commission-events/bulk-sync-invoices`: Đồng bộ hóa đơn cho nhiều sự kiện

## Cấu Trúc Dữ Liệu

### Hóa Đơn Hoa Hồng
```php
[
    'organization_id' => 1,
    'lease_id' => 13,
    'invoice_no' => 'CI202510130010043',
    'issue_date' => '2025-10-13',
    'due_date' => '2025-11-12',
    'status' => 'draft',
    'subtotal' => 150000,
    'tax_amount' => 0,
    'discount_amount' => 0,
    'total_amount' => 150000,
    'currency' => 'VND',
    'note' => 'Chi tiết hoa hồng...'
]
```

### Chi Tiết Hóa Đơn
```php
[
    'invoice_id' => 42,
    'item_type' => 'other',
    'description' => 'Hoa hồng ký hợp đồng - Tên BDS (Mã phòng) - Agent: Tên Agent',
    'quantity' => 1,
    'unit_price' => 150000,
    'amount' => 150000,
    'meta_json' => [
        'commission_event_id' => 2,
        'policy_id' => 1,
        'trigger_event' => 'lease_signed',
        'base_amount' => 3000000,
        'calc_type' => 'percent',
        'percent_value' => 5.00
    ]
]
```

## Logging

Hệ thống ghi log chi tiết cho:
- Tạo hóa đơn thành công/thất bại
- Cập nhật hóa đơn
- Lỗi xử lý
- Đồng bộ dữ liệu

## Xử Lý Lỗi

### Các Lỗi Thường Gặp
1. **Thiếu dữ liệu liên quan**: Kiểm tra policy, organization, lease, unit, agent
2. **Lỗi enum**: Đảm bảo status và item_type đúng giá trị cho phép
3. **Lỗi foreign key**: Kiểm tra tồn tại của các bản ghi liên quan

### Khôi Phục
- Hệ thống sử dụng database transaction
- Rollback tự động khi có lỗi
- Log chi tiết để debug

## Testing

Đã test thành công:
- ✅ Tạo hóa đơn cho sự kiện hoa hồng
- ✅ Cập nhật hóa đơn khi sự kiện thay đổi
- ✅ Tạo hàng loạt hóa đơn
- ✅ Đồng bộ dữ liệu
- ✅ Xử lý lỗi và rollback

## Lưu Ý Quan Trọng

1. **Enum Values**: 
   - Invoice status: `draft`, `issued`, `paid`, `overdue`, `cancelled`
   - Invoice item type: `rent`, `service`, `meter`, `deposit`, `other`

2. **Observer Registration**: Đã đăng ký trong `AppServiceProvider`

3. **Migration**: Đã chạy migration để thêm cột `invoice_id`

4. **Performance**: Sử dụng database transaction để đảm bảo tính nhất quán

## Mở Rộng

Để mở rộng hệ thống:
1. Thêm trigger events mới trong `CommissionPolicy`
2. Cập nhật logic trong `CommissionInvoiceService`
3. Thêm validation và business rules
4. Tích hợp với hệ thống thanh toán

## Kết Luận

Hệ thống hóa đơn hoa hồng tự động đã được triển khai thành công, cung cấp:
- Tự động tạo hóa đơn khi có sự kiện hoa hồng
- Đồng bộ dữ liệu real-time
- Xử lý lỗi robust
- Logging chi tiết
- API để quản lý thủ công

Hệ thống sẵn sàng sử dụng trong môi trường production.
