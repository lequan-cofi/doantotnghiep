# Hướng dẫn tính năng "Tùy chỉnh số tháng" cho chu kỳ thanh toán

## Tổng quan
Tính năng này cho phép người dùng thiết lập chu kỳ thanh toán tùy chỉnh bằng cách nhập số tháng cụ thể (1-60 tháng) thay vì chỉ sử dụng các tùy chọn cố định như "Hàng tháng", "Hàng quý", "Hàng năm".

## Các thay đổi đã thực hiện

### 1. Database Schema
- **Migration**: `2025_10_13_154842_add_custom_months_to_payment_cycle_tables.php`
- **Các cột mới được thêm**:
  - `organizations.org_custom_months` (INT, nullable)
  - `properties.prop_custom_months` (INT, nullable) 
  - `leases.lease_custom_months` (INT, nullable)

### 2. Models
- **Organization.php**: Thêm `org_custom_months` vào `$fillable` và validation
- **Property.php**: Thêm `prop_custom_months` vào `$fillable` và validation
- **Lease.php**: Thêm `lease_custom_months` vào `$fillable` và validation

**Validation rules**:
- Số tháng phải từ 1 đến 60
- Chỉ áp dụng khi `custom_months` không null
- Validation được thực hiện ở model level trong `boot()` method

### 3. Controller
- **PaymentCycleSettingController.php**:
  - Thêm tùy chọn `'custom' => 'Tùy chỉnh (nhập số tháng)'` vào `$paymentCycleOptions`
  - Cập nhật validation rules cho tất cả methods:
    - `updateOrganization()`: Thêm `org_custom_months` validation
    - `updateProperty()`: Thêm `prop_custom_months` validation  
    - `updateLease()`: Thêm `lease_custom_months` validation
  - Cập nhật các method apply để copy `custom_months`:
    - `applyToProperties()`: Copy `org_custom_months` → `prop_custom_months`
    - `applyToLeases()`: Copy `prop_custom_months` → `lease_custom_months`
  - Cập nhật `getPropertyLeases()` để trả về `custom_months` trong response

### 4. Views
- **index.blade.php**:
  - Thêm trường nhập số tháng tùy chỉnh cho organization form
  - Thêm trường nhập số tháng tùy chỉnh cho property modal form
  - Cập nhật hiển thị trong bảng properties để hiển thị "X tháng" khi chọn custom
  - Cập nhật hiển thị trong modal leases để hiển thị "X tháng" khi chọn custom
  - Thêm JavaScript để hiển thị/ẩn trường custom months khi chọn "custom"

## Cách sử dụng

### 1. Cài đặt cho Tổ chức
1. Truy cập `/manager/payment-cycle-settings`
2. Trong phần "Cài đặt tổ chức":
   - Chọn "Tùy chỉnh (nhập số tháng)" từ dropdown
   - Trường "Số tháng tùy chỉnh" sẽ xuất hiện
   - Nhập số tháng (1-60)
   - Nhập ngày thanh toán và ghi chú (tùy chọn)
   - Nhấn "Cập nhật tổ chức"

### 2. Cài đặt cho Bất động sản
1. Trong cùng trang, nhấn nút ⚙️ bên cạnh tên bất động sản
2. Trong modal:
   - Chọn "Tùy chỉnh (nhập số tháng)" từ dropdown
   - Trường "Số tháng tùy chỉnh" sẽ xuất hiện
   - Nhập số tháng (1-60)
   - Nhấn "Cập nhật"

### 3. Áp dụng cài đặt
- **Áp dụng tổ chức → bất động sản**: Nhấn "Áp dụng cho tất cả BĐS"
- **Áp dụng bất động sản → hợp đồng**: Nhấn "Áp dụng cho HĐ" trong modal

## Hiển thị dữ liệu

### Trong bảng Properties
- Chu kỳ cố định: Hiển thị "Hàng tháng", "Hàng quý", "Hàng năm"
- Chu kỳ tùy chỉnh: Hiển thị "X tháng" (ví dụ: "6 tháng")

### Trong modal Leases
- Chu kỳ cố định: Hiển thị "Hàng tháng", "Hàng quý", "Hàng năm"  
- Chu kỳ tùy chỉnh: Hiển thị "X tháng" (ví dụ: "3 tháng")

## Validation

### Frontend
- Trường số tháng chỉ hiển thị khi chọn "Tùy chỉnh"
- Input type="number" với min="1" max="60"
- Placeholder text hướng dẫn

### Backend
- **Controller validation**: `nullable|integer|min:1|max:60`
- **Model validation**: Kiểm tra trong `boot()` method
- **Error messages**: Tiếng Việt, rõ ràng

## Ví dụ sử dụng

### Ví dụ 1: Chu kỳ 6 tháng
```
Chu kỳ thanh toán: Tùy chỉnh (nhập số tháng)
Số tháng tùy chỉnh: 6
Ngày thanh toán: 15
Ghi chú: Thanh toán 6 tháng một lần
```
**Hiển thị**: "6 tháng"

### Ví dụ 2: Chu kỳ 2 tháng  
```
Chu kỳ thanh toán: Tùy chỉnh (nhập số tháng)
Số tháng tùy chỉnh: 2
Ngày thanh toán: 1
Ghi chú: Thanh toán 2 tháng một lần
```
**Hiển thị**: "2 tháng"

## Lưu ý kỹ thuật

### Database
- Các cột `custom_months` là nullable
- Chỉ lưu giá trị khi `payment_cycle = 'custom'`
- Giá trị từ 1-60 tháng

### JavaScript
- Sử dụng event delegation cho modal forms
- Trigger change event khi load modal để hiển thị đúng trạng thái
- Toggle visibility của trường custom months

### API Response
- `getPropertyLeases()` trả về `custom_months` trong property và leases data
- Frontend sử dụng data này để hiển thị đúng

## Troubleshooting

### Lỗi thường gặp
1. **Trường custom months không hiển thị**: Kiểm tra JavaScript console, đảm bảo jQuery loaded
2. **Validation failed**: Kiểm tra giá trị nhập vào (1-60)
3. **Data không lưu**: Kiểm tra fillable attributes trong model

### Debug
```php
// Kiểm tra custom months trong database
$org = Organization::first();
echo $org->org_custom_months; // Should show number or null

// Kiểm tra validation
try {
    $org->update(['org_custom_months' => 0]);
} catch (Exception $e) {
    echo $e->getMessage(); // Should show validation error
}
```

## Tương lai
- Có thể mở rộng để hỗ trợ chu kỳ theo tuần
- Thêm tính năng tính toán ngày thanh toán tiếp theo
- Tích hợp với hệ thống invoice tự động
