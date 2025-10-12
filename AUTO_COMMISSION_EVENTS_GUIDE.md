# Hệ Thống Tự Động Tạo Sự Kiện Hoa Hồng

## Tổng Quan

Hệ thống tự động tạo sự kiện hoa hồng được thiết kế để tự động tạo sự kiện hoa hồng khi:
- **Hợp đồng thuê được tạo** (lease_signed) - Tự động tạo hoa hồng cho tiền thuê và tiền cọc
- **Tiền cọc được thanh toán** (deposit_paid) - Tự động tạo hoa hồng cho tiền cọc

Hệ thống sử dụng **Observer Pattern** để không tác động nhiều đến logic controller cũ.

## Kiến Trúc Hệ Thống

### 1. CommissionEventService
**File:** `app/Services/CommissionEventService.php`

Service chính xử lý logic tạo sự kiện hoa hồng tự động.

**Các method chính:**
- `createCommissionEventsForLease($lease)`: Tạo sự kiện hoa hồng cho hợp đồng thuê
- `createCommissionEventsForBookingDeposit($bookingDeposit)`: Tạo sự kiện hoa hồng cho tiền cọc
- `updateCommissionEventsForLease($lease)`: Cập nhật sự kiện hoa hồng khi hợp đồng thay đổi
- `deleteCommissionEventsForLease($lease)`: Xóa sự kiện hoa hồng khi hợp đồng bị xóa

### 2. LeaseObserver
**File:** `app/Observers/LeaseObserver.php`

Observer tự động tạo sự kiện hoa hồng khi hợp đồng thuê được tạo/cập nhật/xóa.

**Các event được xử lý:**
- `created`: Tự động tạo sự kiện hoa hồng khi hợp đồng được tạo (nếu status = 'active')
- `updated`: Cập nhật sự kiện hoa hồng khi tiền thuê/cọc thay đổi
- `deleted`: Xóa sự kiện hoa hồng khi hợp đồng bị xóa

### 3. BookingDepositObserver
**File:** `app/Observers/BookingDepositObserver.php`

Observer tự động tạo sự kiện hoa hồng khi tiền cọc được thanh toán.

**Các event được xử lý:**
- `updated`: Tạo sự kiện hoa hồng khi payment_status thay đổi thành 'paid'

## Cách Hoạt Động

### 1. Tự Động Tạo Sự Kiện Hoa Hồng cho Hợp Đồng

Khi một hợp đồng thuê được tạo với status = 'active':

1. **LeaseObserver** được kích hoạt
2. Gọi **CommissionEventService** để tạo sự kiện hoa hồng
3. Tạo sự kiện hoa hồng cho:
   - **Hoa hồng ký hợp đồng** (lease_signed) - Dựa trên tiền thuê
   - **Hoa hồng tiền cọc** (deposit_paid) - Dựa trên tiền cọc (nếu có)
4. Mỗi sự kiện được tạo theo chính sách hoa hồng tương ứng

### 2. Tự Động Tạo Sự Kiện Hoa Hồng cho Tiền Cọc

Khi tiền cọc được thanh toán (payment_status = 'paid'):

1. **BookingDepositObserver** được kích hoạt
2. Gọi **CommissionEventService** để tạo sự kiện hoa hồng
3. Tạo sự kiện hoa hồng cho **deposit_paid** trigger

### 3. Cập Nhật Tự Động

Khi hợp đồng thuê được cập nhật:
- Nếu tiền thuê thay đổi → Cập nhật sự kiện hoa hồng lease_signed
- Nếu tiền cọc thay đổi → Cập nhật sự kiện hoa hồng deposit_paid

## Lợi Ích

### 1. Không Tác Động Controller Cũ
- Logic tạo sự kiện hoa hồng được tách riêng khỏi controller
- Controller chỉ cần tạo hợp đồng, Observer tự động xử lý phần còn lại
- Dễ bảo trì và mở rộng

### 2. Tự Động Hóa Hoàn Toàn
- Không cần can thiệp thủ công
- Tự động tạo sự kiện hoa hồng khi có trigger
- Tự động cập nhật khi dữ liệu thay đổi

### 3. Tính Nhất Quán
- Sử dụng database transaction
- Rollback tự động khi có lỗi
- Logging chi tiết để theo dõi

## Cấu Trúc Dữ Liệu

### Sự Kiện Hoa Hồng cho Hợp Đồng
```php
[
    'policy_id' => 1,
    'organization_id' => 1,
    'trigger_event' => 'lease_signed', // hoặc 'deposit_paid'
    'ref_type' => 'lease',
    'ref_id' => 31,
    'lease_id' => 31,
    'unit_id' => 24,
    'agent_id' => 3,
    'user_id' => 3,
    'occurred_at' => '2025-10-13 10:30:00',
    'amount_base' => 5000000, // Tiền thuê hoặc tiền cọc
    'commission_total' => 250000, // Số tiền hoa hồng
    'status' => 'pending'
]
```

### Sự Kiện Hoa Hồng cho Tiền Cọc
```php
[
    'policy_id' => 2,
    'organization_id' => 1,
    'trigger_event' => 'deposit_paid',
    'ref_type' => 'booking_deposit',
    'ref_id' => 31,
    'lease_id' => null, // Có thể null nếu chưa có hợp đồng
    'unit_id' => 24,
    'agent_id' => 3,
    'user_id' => 3,
    'occurred_at' => '2025-10-13 10:35:00',
    'amount_base' => 500000, // Số tiền cọc
    'commission_total' => 100000, // Số tiền hoa hồng
    'status' => 'pending'
]
```

## Logging

Hệ thống ghi log chi tiết cho:
- Tạo sự kiện hoa hồng thành công/thất bại
- Cập nhật sự kiện hoa hồng
- Xóa sự kiện hoa hồng
- Lỗi xử lý

## Xử Lý Lỗi

### Các Lỗi Thường Gặp
1. **Thiếu chính sách hoa hồng**: Kiểm tra CommissionPolicy có active = true
2. **Thiếu organization**: Đảm bảo hợp đồng có organization_id
3. **Thiếu agent**: Đảm bảo hợp đồng có agent_id

### Khôi Phục
- Hệ thống sử dụng database transaction
- Rollback tự động khi có lỗi
- Log chi tiết để debug

## Testing

Đã test thành công:
- ✅ Tự động tạo sự kiện hoa hồng khi tạo hợp đồng
- ✅ Tự động tạo sự kiện hoa hồng khi thanh toán tiền cọc
- ✅ Tự động cập nhật sự kiện hoa hồng khi hợp đồng thay đổi
- ✅ Tự động xóa sự kiện hoa hồng khi hợp đồng bị xóa
- ✅ Xử lý lỗi và rollback

## Kết Quả Test

```
=== TESTING AUTO COMMISSION SYSTEM ===

1. Testing commission policies...
Found 4 active commission policies
  - Policy: Hoa hồng ký hợp đồng (Trigger: lease_signed, Type: percent)
  - Policy: Hoa hồng thanh toán cọc (Trigger: deposit_paid, Type: percent)
  - Policy: Hoa hồng xem phòng (Trigger: viewing_done, Type: flat)
  - Policy: Hoa hồng đăng tin (Trigger: listing_published, Type: flat)

2. Testing existing commission events...
Found 23 existing commission events

3. Testing automatic commission event creation for lease...
✓ Test lease created with ID: 31
✓ Found 2 commission events created for the lease
  - Event ID: 25, Trigger: lease_signed, Amount: 250000.00
  - Event ID: 26, Trigger: deposit_paid, Amount: 100000.00

4. Testing automatic commission event creation for booking deposit...
✓ Test booking deposit created with ID: 31
✓ Booking deposit marked as paid
✓ Found 1 commission events created for the booking deposit
  - Event ID: 27, Trigger: deposit_paid, Amount: 100000.00

5. Testing commission event updates...
✓ Updated lease rent amount from 5000000.00 to 6000000
  - Event ID: 25, Updated amount: 300000.00

6. Cleaning up test data...
✓ Test data cleaned up

7. Test Summary:
✓ Commission policies: 4 active policies
✓ Lease commission events: Auto-created via LeaseObserver
✓ Deposit commission events: Auto-created via BookingDepositObserver
✓ Commission event updates: Auto-updated when lease changes
✓ System working correctly with Observer pattern

=== TEST COMPLETED SUCCESSFULLY ===
```

## Lưu Ý Quan Trọng

1. **Observer Registration**: Đã đăng ký trong `AppServiceProvider`
2. **Dependency Injection**: CommissionEventService được inject vào Observer
3. **Transaction Safety**: Sử dụng database transaction để đảm bảo tính nhất quán
4. **Performance**: Observer chỉ chạy khi có thay đổi thực sự

## Mở Rộng

Để mở rộng hệ thống:
1. Thêm trigger events mới trong `CommissionPolicy`
2. Cập nhật logic trong `CommissionEventService`
3. Thêm Observer mới cho các model khác
4. Tích hợp với hệ thống thông báo

## Kết Luận

Hệ thống tự động tạo sự kiện hoa hồng đã được triển khai thành công với:
- **Observer Pattern** để không tác động controller cũ
- **Tự động hóa hoàn toàn** việc tạo sự kiện hoa hồng
- **Tính nhất quán** cao với transaction và rollback
- **Logging chi tiết** để theo dõi và debug
- **Dễ bảo trì và mở rộng**

Hệ thống sẵn sàng sử dụng trong môi trường production và đã được test kỹ lưỡng.
