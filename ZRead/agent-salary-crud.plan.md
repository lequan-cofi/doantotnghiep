<!-- d7003c18-60c6-47c9-9557-7e5173f80bdf fd8c0394-82a0-4d08-9e8d-bfa09133ae9b -->
# Triển khai Quản lý Lương cho Agent

## Phạm vi triển khai

### 1. Hợp đồng lương (Salary Contracts) - Read-only

**Controller**: `app/Http/Controllers/Agent/SalaryContractController.php`

- `index()`: Chỉ hiển thị hợp đồng lương của agent đang đăng nhập
- `show($id)`: Chi tiết hợp đồng, kiểm tra quyền sở hữu

**Views**: `resources/views/agent/salary-contracts/`

- `index.blade.php`: Danh sách hợp đồng lương với bộ lọc status
- `show.blade.php`: Chi tiết hợp đồng (lương cơ bản, phụ cấp, KPI)

**Key logic**:

```php
Auth::user()->salaryContracts()->with('organization')->get()
```

### 2. Kỳ lương (Payroll Cycles) - Read-only

**Controller**: `app/Http/Controllers/Agent/PayrollCycleController.php`

- `index()`: Hiển thị các kỳ lương của tổ chức mà agent có phiếu lương
- `show($id)`: Chi tiết kỳ lương và phiếu lương của agent

**Views**: `resources/views/agent/payroll-cycles/`

- `index.blade.php`: Danh sách kỳ lương
- `show.blade.php`: Chi tiết kỳ lương + phiếu lương của mình

**Key logic**:

```php
PayrollCycle::whereHas('payslips', function($q) {
    $q->where('user_id', Auth::id());
})->with(['payslips' => function($q) {
    $q->where('user_id', Auth::id());
}])->get()
```

### 3. Phiếu lương (Payslips) - Read-only

**Controller**: `app/Http/Controllers/Agent/PayslipController.php`

- `index()`: Danh sách phiếu lương của agent
- `show($id)`: Chi tiết phiếu lương, kiểm tra quyền sở hữu

**Views**: `resources/views/agent/payslips/`

- `index.blade.php`: Danh sách phiếu lương với bộ lọc
- `show.blade.php`: Chi tiết phiếu lương (gross, deduction, net)

**Key logic**:

```php
Auth::user()->payslips()->with('payrollCycle')->get()
```

### 4. Ứng lương (Salary Advances) - Full CRUD

**Controller**: `app/Http/Controllers/Agent/SalaryAdvanceController.php` (cập nhật từ placeholder)

**Các phương thức**:

- `index()`: Danh sách đơn ứng lương của agent với bộ lọc status
- `create()`: Form tạo đơn ứng lương mới
- `store()`: Tạo đơn, gửi notification, status = 'pending'
- `show($id)`: Chi tiết đơn ứng lương
- `edit($id)`: Form sửa đơn (chỉ khi status = pending/rejected)
- `update($id)`: Cập nhật đơn (kiểm tra canBeDeleted())
- `destroy($id)`: Xóa đơn (chỉ khi status = pending/rejected)

**Validation logic**:

- Kiểm tra quyền sở hữu: `$salaryAdvance->user_id === Auth::id()`
- Chặn sửa/xóa: `!in_array($status, ['pending', 'rejected'])`
- Thông báo lỗi khi không thể sửa/xóa

**Views**: `resources/views/agent/salary-advances/`

- `index.blade.php`: Danh sách đơn ứng lương với bộ lọc, thống kê
- `create.blade.php`: Form tạo đơn (amount, advance_date, expected_repayment_date, reason, repayment_method)
- `edit.blade.php`: Form sửa đơn (disable khi đã duyệt)
- `show.blade.php`: Chi tiết đơn (hiển thị approver/rejector, timeline)

**Notification integration**:

```javascript
// Sau khi tạo/sửa/xóa thành công
notify.success('Đơn ứng lương đã được tạo thành công!');
notify.error('Không thể chỉnh sửa đơn đã được duyệt');
```

### 5. Routes - `routes/web.php`

Routes đã tồn tại (dòng 379-406), cần cập nhật:

**Thêm routes mới cho payroll-cycles và payslips**:

```php
// Payroll Cycles (read-only)
Route::get('/payroll-cycles', [PayrollCycleController::class, 'index'])->name('payroll-cycles.index');
Route::get('/payroll-cycles/{id}', [PayrollCycleController::class, 'show'])->name('payroll-cycles.show');

// Payslips (read-only)
Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
Route::get('/payslips/{id}', [PayslipController::class, 'show'])->name('payslips.show');
```

Routes salary-contracts và salary-advances đã có, giữ nguyên.

### 6. Navigation Menu - `resources/views/partials/agent/header.blade.php`

Thêm menu group "Quản lý lương" sau menu Tickets (sau dòng ~168):

```php
<div class="nav-group" data-group="salary">
    <a href="#" class="nav-item has-submenu nav-parent">
        <i class="fas fa-money-bill-wave"></i>
        <span>Quản lý lương</span>
        <i class="fas fa-chevron-down submenu-arrow"></i>
    </a>
    <div class="submenu">
        <a href="{{ route('agent.salary-contracts.index') }}" class="submenu-item">
            <i class="fas fa-file-signature"></i>
            <span>Hợp đồng lương</span>
        </a>
        <a href="{{ route('agent.payroll-cycles.index') }}" class="submenu-item">
            <i class="fas fa-calendar-check"></i>
            <span>Kỳ lương</span>
        </a>
        <a href="{{ route('agent.payslips.index') }}" class="submenu-item">
            <i class="fas fa-receipt"></i>
            <span>Phiếu lương</span>
        </a>
        <a href="{{ route('agent.salary-advances.index') }}" class="submenu-item">
            <i class="fas fa-hand-holding-usd"></i>
            <span>Ứng lương</span>
        </a>
    </div>
</div>
```

### 7. Notification System Integration

Sử dụng notification system có sẵn (`notifications.js`, `notifications.css`):

**Trong views**:

```javascript
@if(session('success'))
    notify.success('{{ session('success') }}');
@endif

@if(session('error'))
    notify.error('{{ session('error') }}');
@endif
```

**Trong controller**:

```php
return redirect()->route('agent.salary-advances.index')
    ->with('success', 'Đơn ứng lương đã được tạo thành công!');
```

**Confirmation xóa**:

```javascript
notify.confirmDelete('đơn ứng lương này', function() {
    document.getElementById('delete-form-' + id).submit();
});
```

### 8. Styling

Tạo file CSS mới: `public/assets/css/agent/salary.css`

- Style cho bảng danh sách
- Badge cho trạng thái (pending: warning, approved: success, rejected: danger)
- Card layout cho chi tiết
- Responsive design

**Include trong layout** `resources/views/layouts/agent_dashboard.blade.php`:

```html
<link rel="stylesheet" type="text/css" href="{{ asset('assets/css/agent/salary.css') }}">
```

## Các điểm quan trọng

1. **Bảo mật**: Agent chỉ xem được dữ liệu của chính mình
2. **Validation**: Chặn sửa/xóa ứng lương khi status không phải pending/rejected
3. **UI/UX**: Hiển thị rõ trạng thái, disable button khi không thể thao tác
4. **Notification**: Sử dụng notify.success/error/confirmDelete
5. **Tương thích**: Theo pattern hiện có (tickets, units, viewings)

## Files cần tạo/sửa

**Controllers** (3 mới, 1 cập nhật):

- `app/Http/Controllers/Agent/SalaryContractController.php` (cập nhật)
- `app/Http/Controllers/Agent/PayrollCycleController.php` (mới)
- `app/Http/Controllers/Agent/PayslipController.php` (mới)
- `app/Http/Controllers/Agent/SalaryAdvanceController.php` (cập nhật từ placeholder)

**Views** (16 files trong 4 folders):

- `resources/views/agent/salary-contracts/` (index, show)
- `resources/views/agent/payroll-cycles/` (index, show)
- `resources/views/agent/payslips/` (index, show)
- `resources/views/agent/salary-advances/` (index, create, edit, show)

**Routes**: `routes/web.php` (thêm payroll-cycles, payslips)

**Navigation**: `resources/views/partials/agent/header.blade.php`

**Styling**: `public/assets/css/agent/salary.css`, cập nhật `agent_dashboard.blade.php`

### To-dos

- [ ] Cập nhật SalaryContractController: index, show (read-only cho agent)
- [ ] Tạo views cho salary-contracts: index.blade.php, show.blade.php
- [ ] Tạo PayrollCycleController: index, show (read-only)
- [ ] Tạo views cho payroll-cycles: index.blade.php, show.blade.php
- [ ] Tạo PayslipController: index, show (read-only)
- [ ] Tạo views cho payslips: index.blade.php, show.blade.php
- [ ] Cập nhật SalaryAdvanceController: CRUD đầy đủ với validation chặn sửa/xóa khi đã duyệt
- [ ] Tạo views cho salary-advances: index, create, edit, show với notification integration
- [ ] Cập nhật routes/web.php: thêm routes cho payroll-cycles và payslips
- [ ] Thêm menu 'Quản lý lương' vào header navigation
- [ ] Tạo salary.css và cập nhật agent_dashboard layout