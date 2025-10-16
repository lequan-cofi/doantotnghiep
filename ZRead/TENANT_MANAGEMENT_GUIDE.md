# Hướng dẫn Quản lý Khách hàng (Tenant Management)

## Tổng quan

Hệ thống quản lý khách hàng cho phép Agent quản lý:
- **Khách hàng có tài khoản**: Người dùng đã được tạo tài khoản trong hệ thống
- **Lead**: Khách hàng tiềm năng chưa có tài khoản, có thể chuyển đổi thành khách hàng

## Tính năng chính

### 1. Quản lý Khách hàng (CRUD)
- **Xem danh sách**: Tất cả khách hàng và lead thuộc hợp đồng của Agent
- **Tạo mới**: Tạo tài khoản khách hàng mới với quyền truy cập hệ thống
- **Chỉnh sửa**: Cập nhật thông tin khách hàng
- **Xem chi tiết**: Thông tin đầy đủ về khách hàng và hợp đồng
- **Xóa**: Xóa khách hàng (chỉ khi không có hợp đồng hoạt động)

### 2. Chuyển đổi Lead thành Khách hàng
- Chuyển đổi lead thành tài khoản khách hàng
- Tự động liên kết lead với tài khoản khách hàng (tenant_id)
- Tự động cập nhật hợp đồng liên quan
- Gán role phù hợp trong tổ chức

### 3. Quản lý Người ở cùng
- Thêm người ở cùng vào hợp đồng
- Tạo tài khoản cho người ở cùng (tùy chọn)
- Quản lý thông tin người ở cùng

## Cách sử dụng

### Truy cập hệ thống
1. Đăng nhập với tài khoản Agent
2. Trong menu sidebar, click vào **"Khách hàng"**
3. URL: `/agent/tenants`

### Tạo khách hàng mới
1. Click nút **"Thêm khách hàng mới"**
2. Điền thông tin:
   - Họ và tên (bắt buộc)
   - Số điện thoại (bắt buộc, duy nhất)
   - Email (tùy chọn, duy nhất)
   - Mật khẩu (bắt buộc)
   - Vai trò (bắt buộc)
   - Tạo lead tương ứng (tùy chọn)
3. Click **"Tạo khách hàng"**

### Chuyển đổi Lead thành Khách hàng
1. Trong danh sách, tìm lead cần chuyển đổi
2. Click nút **"Chuyển đổi"** (màu xanh)
3. Điền mật khẩu và chọn vai trò
4. Click **"Chuyển đổi"**

### Quản lý thông tin khách hàng
1. Click vào icon **"Xem"** (mắt) để xem chi tiết
2. Click vào icon **"Sửa"** (bút chì) để chỉnh sửa
3. Click vào icon **"Xóa"** (thùng rác) để xóa

## Cấu trúc dữ liệu

### Bảng Users
- `id`: ID khách hàng
- `full_name`: Họ và tên
- `phone`: Số điện thoại
- `email`: Email
- `password_hash`: Mật khẩu đã mã hóa
- `status`: Trạng thái (1: hoạt động, 0: không hoạt động)

### Bảng OrganizationUsers
- `organization_id`: ID tổ chức
- `user_id`: ID người dùng
- `role_id`: ID vai trò
- `status`: Trạng thái trong tổ chức

### Bảng Leads
- `id`: ID lead
- `tenant_id`: ID khách hàng liên kết (nullable)
- `name`: Tên
- `phone`: Số điện thoại
- `email`: Email
- `status`: Trạng thái lead (1: hoạt động, 0: không hoạt động)

### Bảng Leases
- `id`: ID hợp đồng
- `tenant_id`: ID khách hàng (có thể null nếu từ lead)
- `lead_id`: ID lead (có thể null nếu đã có tài khoản)
- `organization_id`: ID tổ chức
- `agent_id`: ID agent

## Quyền hạn và bảo mật

### Quyền Agent
- Chỉ xem và quản lý khách hàng thuộc tổ chức của mình
- Không thể xem khách hàng của tổ chức khác
- Có thể tạo khách hàng mới với role thuộc tổ chức

### Bảo mật
- Tất cả thao tác đều được kiểm tra quyền
- Mật khẩu được mã hóa bằng Hash
- Soft delete để bảo toàn dữ liệu
- Validation đầy đủ cho tất cả input

## API Endpoints

### Routes chính
```
GET    /agent/tenants              # Danh sách khách hàng
GET    /agent/tenants/create       # Form tạo mới
POST   /agent/tenants              # Lưu khách hàng mới
GET    /agent/tenants/{id}         # Chi tiết khách hàng
GET    /agent/tenants/{id}/edit    # Form chỉnh sửa
PUT    /agent/tenants/{id}         # Cập nhật khách hàng
DELETE /agent/tenants/{id}         # Xóa khách hàng
```

### Routes bổ sung
```
POST   /agent/tenants/convert-lead/{leadId}     # Chuyển đổi lead
POST   /agent/tenants/add-resident/{leaseId}    # Thêm người ở cùng
```

## Xử lý lỗi thường gặp

### 1. Lỗi "Bạn không thuộc tổ chức nào"
- **Nguyên nhân**: User không có organization
- **Giải pháp**: Liên hệ admin để gán user vào organization

### 2. Lỗi "Số điện thoại đã tồn tại"
- **Nguyên nhân**: Số điện thoại đã được sử dụng
- **Giải pháp**: Sử dụng số điện thoại khác hoặc kiểm tra khách hàng hiện có

### 3. Lỗi "Không thể xóa khách hàng đang có hợp đồng hoạt động"
- **Nguyên nhân**: Khách hàng có hợp đồng đang hoạt động
- **Giải pháp**: Kết thúc hợp đồng trước khi xóa khách hàng

## Tích hợp với hệ thống khác

### 1. Hệ thống Lead
- Tự động tạo lead khi tạo khách hàng (nếu chọn)
- Tự động liên kết lead với tài khoản khách hàng (tenant_id)
- Chuyển đổi lead thành khách hàng
- Đồng bộ thông tin giữa lead và khách hàng
- Hiển thị trạng thái liên kết trong giao diện

### 2. Hệ thống Hợp đồng
- Liên kết khách hàng với hợp đồng
- Quản lý người ở cùng trong hợp đồng
- Theo dõi trạng thái hợp đồng

### 3. Hệ thống Phân quyền
- Gán role phù hợp cho khách hàng
- Kiểm soát quyền truy cập theo organization
- Bảo mật thông tin khách hàng

## Mở rộng trong tương lai

### Tính năng có thể thêm
1. **Import/Export**: Nhập/xuất danh sách khách hàng
2. **Thông báo**: Gửi thông báo cho khách hàng
3. **Lịch sử**: Theo dõi lịch sử thay đổi thông tin
4. **Báo cáo**: Thống kê khách hàng theo nhiều tiêu chí
5. **API**: Cung cấp API cho ứng dụng mobile

### Cải tiến UI/UX
1. **Tìm kiếm nâng cao**: Tìm kiếm theo nhiều tiêu chí
2. **Phân trang**: Hiển thị danh sách lớn hiệu quả
3. **Responsive**: Tối ưu cho mobile
4. **Dark mode**: Chế độ tối
