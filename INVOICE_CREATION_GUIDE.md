# Hướng dẫn xử lý vấn đề tạo hóa đơn tự động

## Tổng quan

Hệ thống được thiết kế để tự động tạo hóa đơn cọc và hóa đơn tháng đầu khi tạo hợp đồng thuê. Tuy nhiên, trong một số trường hợp, hóa đơn có thể không được tạo tự động do:

1. Lỗi trong quá trình tạo hợp đồng
2. Transaction rollback
3. Lỗi trong LeaseObserver
4. Vấn đề với database connection

## Kiểm tra trạng thái hóa đơn

### 1. Kiểm tra hợp đồng có hóa đơn hay không

```bash
php check_leases.php
```

### 2. Sử dụng command để kiểm tra

```bash
# Kiểm tra mà không tạo hóa đơn
php artisan invoices:create-missing --dry-run

# Tạo hóa đơn cho các hợp đồng thiếu
php artisan invoices:create-missing
```

## Xử lý vấn đề

### 1. Tạo hóa đơn cho hợp đồng thiếu

Nếu phát hiện hợp đồng không có hóa đơn, sử dụng command sau:

```bash
php artisan invoices:create-missing
```

Command này sẽ:
- Tìm tất cả hợp đồng không có hóa đơn
- Hiển thị danh sách hợp đồng cần tạo hóa đơn
- Tạo hóa đơn tự động với thông tin:
  - Tiền thuê tháng đầu
  - Tiền cọc (nếu có)
  - Trạng thái: draft
  - Ngày đáo hạn: 30 ngày từ ngày bắt đầu hợp đồng

### 2. Tạo hóa đơn thủ công cho hợp đồng cụ thể

```php
// Trong tinker hoặc script
$lease = App\Models\Lease::find(LEASE_ID);
App\Observers\LeaseObserver::createInvoiceForExistingLease($lease);
```

### 3. Kiểm tra log để debug

```bash
# Xem log gần đây
Get-Content storage/logs/laravel.log -Tail 50

# Tìm log liên quan đến hóa đơn
Get-Content storage/logs/laravel.log | Select-String "invoice"
```

## Cấu trúc hóa đơn tự động

Khi tạo hợp đồng, hệ thống sẽ tự động tạo:

1. **Hóa đơn chính** với thông tin:
   - Mã hóa đơn: INV-YYYYMMDD-XXXX
   - Tổ chức: Tổ chức của agent
   - Hợp đồng: ID hợp đồng
   - Trạng thái: draft
   - Ngày phát hành: Ngày bắt đầu hợp đồng
   - Ngày đáo hạn: 30 ngày sau ngày bắt đầu

2. **Chi tiết hóa đơn**:
   - **Tiền thuê tháng đầu**: Số tiền thuê
   - **Tiền cọc**: Số tiền cọc (nếu có)

## Troubleshooting

### Vấn đề: Observer không hoạt động

**Nguyên nhân có thể:**
- Observer chưa được đăng ký
- Lỗi trong CommissionEventService
- Transaction rollback

**Giải pháp:**
1. Kiểm tra AppServiceProvider.php có đăng ký Observer
2. Kiểm tra log lỗi
3. Sử dụng command tạo hóa đơn thủ công

### Vấn đề: Hóa đơn bị trùng

**Nguyên nhân:**
- Chạy command nhiều lần
- Observer được kích hoạt nhiều lần

**Giải pháp:**
- Command tự động kiểm tra hóa đơn đã tồn tại
- Chỉ tạo hóa đơn mới nếu chưa có

### Vấn đề: Hóa đơn có thông tin sai

**Nguyên nhân:**
- Dữ liệu hợp đồng bị thay đổi sau khi tạo hóa đơn
- Lỗi trong quá trình tính toán

**Giải pháp:**
1. Cập nhật thông tin hợp đồng
2. Observer sẽ tự động cập nhật hóa đơn liên quan
3. Hoặc tạo hóa đơn mới và hủy hóa đơn cũ

## Monitoring

### 1. Kiểm tra định kỳ

Chạy command kiểm tra hàng tuần:

```bash
php artisan invoices:create-missing --dry-run
```

### 2. Log monitoring

Theo dõi log để phát hiện lỗi:

```bash
# Tìm log lỗi tạo hóa đơn
Get-Content storage/logs/laravel.log | Select-String "Error creating first month rent invoice"
```

### 3. Database monitoring

Kiểm tra số lượng hóa đơn:

```sql
-- Đếm hợp đồng không có hóa đơn
SELECT COUNT(*) as leases_without_invoices
FROM leases l
LEFT JOIN invoices i ON l.id = i.lease_id AND i.deleted_at IS NULL
WHERE l.deleted_at IS NULL AND i.id IS NULL;
```

## Best Practices

1. **Luôn kiểm tra** sau khi tạo hợp đồng mới
2. **Sử dụng command** thay vì tạo hóa đơn thủ công
3. **Theo dõi log** để phát hiện vấn đề sớm
4. **Backup database** trước khi chạy command tạo hàng loạt
5. **Test trong môi trường dev** trước khi chạy production

## Liên hệ hỗ trợ

Nếu gặp vấn đề không thể giải quyết:

1. Kiểm tra log chi tiết
2. Chụp ảnh màn hình lỗi
3. Cung cấp thông tin:
   - ID hợp đồng
   - Thời gian xảy ra lỗi
   - Mô tả chi tiết vấn đề
   - Log lỗi (nếu có)
