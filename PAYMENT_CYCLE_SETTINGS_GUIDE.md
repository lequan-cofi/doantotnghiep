# Hướng dẫn sử dụng chức năng Cài đặt chu kỳ thanh toán

## Tổng quan

Chức năng **Cài đặt chu kỳ thanh toán** cho phép Manager quản lý chu kỳ thanh toán cho:
- **Tổ chức** (Organization)
- **Bất động sản** (Property) 
- **Hợp đồng thuê** (Lease)

## Cấu trúc dữ liệu

### 1. Tổ chức (Organizations)
- `org_payment_cycle`: Chu kỳ thanh toán (monthly, quarterly, yearly)
- `org_payment_day`: Ngày thanh toán trong chu kỳ (1-31)
- `org_payment_notes`: Ghi chú về chu kỳ thanh toán

### 2. Bất động sản (Properties)
- `prop_payment_cycle`: Chu kỳ thanh toán (monthly, quarterly, yearly)
- `prop_payment_day`: Ngày thanh toán trong chu kỳ (1-31)
- `prop_payment_notes`: Ghi chú về chu kỳ thanh toán

### 3. Hợp đồng thuê (Leases)
- `lease_payment_cycle`: Chu kỳ thanh toán (monthly, quarterly, yearly)
- `lease_payment_day`: Ngày thanh toán trong chu kỳ (1-31)
- `lease_payment_notes`: Ghi chú về chu kỳ thanh toán

## Cách truy cập

1. Đăng nhập với tài khoản Manager
2. Vào menu **Cài đặt** → **Chu kỳ thanh toán**
3. Hoặc truy cập trực tiếp: `/manager/payment-cycle-settings`

## Hướng dẫn sử dụng

### 1. Cài đặt chu kỳ thanh toán cho Tổ chức

**Bước 1:** Trên trang chính, bạn sẽ thấy phần "Cài đặt tổ chức"

**Bước 2:** Điền thông tin:
- **Chu kỳ thanh toán**: Chọn từ dropdown (Hàng tháng, Hàng quý, Hàng năm)
- **Ngày thanh toán**: Nhập số từ 1-31
- **Ghi chú**: Thêm ghi chú (tùy chọn)

**Bước 3:** Nhấn **"Cập nhật tổ chức"**

**Bước 4:** (Tùy chọn) Nhấn **"Áp dụng cho tất cả BĐS"** để áp dụng cài đặt cho tất cả bất động sản

### 2. Cài đặt chu kỳ thanh toán cho Bất động sản

**Bước 1:** Trong danh sách bất động sản, nhấn nút **⚙️** bên cạnh tên BĐS

**Bước 2:** Modal sẽ hiển thị với:
- Form cài đặt cho BĐS
- Danh sách hợp đồng thuê của BĐS đó

**Bước 3:** Điền thông tin cài đặt cho BĐS:
- **Chu kỳ thanh toán**: Chọn từ dropdown
- **Ngày thanh toán**: Nhập số từ 1-31
- **Ghi chú**: Thêm ghi chú (tùy chọn)

**Bước 4:** Nhấn **"Cập nhật"** để lưu cài đặt BĐS

**Bước 5:** (Tùy chọn) Nhấn **"Áp dụng cho HĐ"** để áp dụng cài đặt cho tất cả hợp đồng thuê của BĐS

### 3. Cài đặt chu kỳ thanh toán cho Hợp đồng thuê

**Bước 1:** Mở modal cài đặt BĐS (như hướng dẫn trên)

**Bước 2:** Trong phần "Hợp đồng thuê", bạn sẽ thấy danh sách các hợp đồng

**Bước 3:** Để cài đặt riêng cho từng hợp đồng:
- Sử dụng API endpoint: `PUT /manager/payment-cycle-settings/lease/{leaseId}`
- Hoặc tích hợp vào form riêng (có thể phát triển thêm)

## Các tùy chọn chu kỳ thanh toán

| Giá trị | Mô tả | Sử dụng |
|---------|-------|---------|
| `monthly` | Hàng tháng | Thanh toán mỗi tháng |
| `quarterly` | Hàng quý | Thanh toán mỗi 3 tháng |
| `yearly` | Hàng năm | Thanh toán mỗi năm |

## Tính năng nâng cao

### 1. Áp dụng hàng loạt
- **Áp dụng tổ chức → BĐS**: Cài đặt tổ chức sẽ được áp dụng cho tất cả BĐS
- **Áp dụng BĐS → Hợp đồng**: Cài đặt BĐS sẽ được áp dụng cho tất cả hợp đồng thuê

### 2. Hiển thị trạng thái
- Badge màu xanh: Đã cài đặt chu kỳ
- Badge màu xám: Đã cài đặt ngày thanh toán
- Text "Chưa cài đặt": Chưa có cài đặt

### 3. Ghi chú
- Tất cả các cấp đều có trường ghi chú
- Ghi chú giúp giải thích lý do hoặc điều kiện đặc biệt

## API Endpoints

### Quản lý cài đặt
```
GET    /manager/payment-cycle-settings                    # Trang chính
PUT    /manager/payment-cycle-settings/organization       # Cập nhật tổ chức
PUT    /manager/payment-cycle-settings/property/{id}      # Cập nhật BĐS
PUT    /manager/payment-cycle-settings/lease/{id}         # Cập nhật hợp đồng
```

### Áp dụng hàng loạt
```
POST   /manager/payment-cycle-settings/apply-to-properties     # Áp dụng cho BĐS
POST   /manager/payment-cycle-settings/apply-to-leases/{id}    # Áp dụng cho hợp đồng
```

### Lấy dữ liệu
```
GET    /manager/payment-cycle-settings/property/{id}/leases    # Lấy hợp đồng của BĐS
```

## Bảo mật và quyền hạn

- Chỉ **Manager** mới có quyền truy cập
- Manager chỉ có thể quản lý cài đặt của tổ chức mình
- Tất cả thao tác đều được ghi log

## Logging

Hệ thống ghi log chi tiết cho tất cả thao tác:
- Cập nhật cài đặt tổ chức
- Cập nhật cài đặt BĐS
- Cập nhật cài đặt hợp đồng
- Áp dụng hàng loạt

## Troubleshooting

### Vấn đề: Không thể truy cập trang
**Nguyên nhân:** Không có quyền Manager
**Giải pháp:** Đăng nhập với tài khoản Manager

### Vấn đề: Không thấy BĐS nào
**Nguyên nhân:** Tổ chức chưa có BĐS
**Giải pháp:** Tạo BĐS trước khi cài đặt

### Vấn đề: Áp dụng hàng loạt không hoạt động
**Nguyên nhân:** Có thể do lỗi database hoặc quyền hạn
**Giải pháp:** Kiểm tra log và thử lại

## Best Practices

1. **Thiết lập từ trên xuống**: Tổ chức → BĐS → Hợp đồng
2. **Sử dụng ghi chú**: Giải thích lý do cài đặt đặc biệt
3. **Kiểm tra trước khi áp dụng hàng loạt**: Đảm bảo cài đặt đúng
4. **Backup dữ liệu**: Trước khi thay đổi lớn
5. **Theo dõi log**: Để phát hiện vấn đề sớm

## Tương lai

Các tính năng có thể phát triển thêm:
- Tự động tạo hóa đơn theo chu kỳ
- Thông báo nhắc nhở thanh toán
- Báo cáo chu kỳ thanh toán
- Import/Export cài đặt
- Template cài đặt chu kỳ
