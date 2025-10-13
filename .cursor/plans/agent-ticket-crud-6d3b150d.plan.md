<!-- 6d3b150d-f707-4126-8d6f-f174e0e2d608 5e8e9893-5c9e-4bfc-b0c2-858c07e555e1 -->
# Agent Ticket CRUD Implementation

## Tổng quan

Tạo chức năng CRUD đầy đủ cho ticket dành cho Agent role, tuân theo pattern hiện có của hệ thống (giống như Units, Leases). Agent chỉ có thể quản lý tickets liên quan đến properties được giao cho họ.

## Phạm vi triển khai

### 1. Controller - `app/Http/Controllers/Agent/TicketController.php`

Tạo controller mới dựa trên `Manager\TicketController.php` với các điều chỉnh:

**Các phương thức chính:**

- `index()` - Danh sách tickets, lọc theo assigned properties
- `create()` - Form tạo ticket mới
- `store()` - Xử lý tạo ticket + log ban đầu
- `show()` - Chi tiết ticket + logs
- `edit()` - Form chỉnh sửa
- `update()` - Cập nhật ticket + ghi log thay đổi
- `addLog()` - Thêm nhật ký vào ticket (có chi phí)

**Điểm khác biệt quan trọng:**

- KHÔNG có method `destroy()` (agent không được xóa)
- Lọc units/leases theo properties được giao: `Auth::user()->assignedProperties()->pluck('properties.id')`
- Lọc tickets theo `unit.property_id IN assignedPropertyIds`
- Validate organization_id từ Auth::user()->organization_id

### 2. Routes - `routes/web.php`

Thêm vào agent route group (sau dòng 467, trước dòng 468):

```php
// Tickets management (CRU - no Delete)
Route::get('/tickets', [\App\Http\Controllers\Agent\TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create', [\App\Http\Controllers\Agent\TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [\App\Http\Controllers\Agent\TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/{id}', [\App\Http\Controllers\Agent\TicketController::class, 'show'])->name('tickets.show');
Route::get('/tickets/{id}/edit', [\App\Http\Controllers\Agent\TicketController::class, 'edit'])->name('tickets.edit');
Route::put('/tickets/{id}', [\App\Http\Controllers\Agent\TicketController::class, 'update'])->name('tickets.update');
Route::post('/tickets/{id}/logs', [\App\Http\Controllers\Agent\TicketController::class, 'addLog'])->name('tickets.addLog');

// API endpoints for tickets
Route::prefix('api/tickets')->group(function () {
    Route::get('/properties/{propertyId}/units', [\App\Http\Controllers\Agent\TicketController::class, 'getUnits']);
    Route::get('/units/{unitId}/leases', [\App\Http\Controllers\Agent\TicketController::class, 'getLeases']);
});
```

### 3. Views - `resources/views/agent/tickets/`

Tạo thư mục `tickets` với 4 files, dựa trên manager views:

**index.blade.php** - Danh sách tickets

- Extends: `layouts.agent_dashboard`
- Bộ lọc: status, priority, assigned_to, unit_id, lease_id, search
- Bảng hiển thị với badges cho status/priority
- Actions: View, Edit (KHÔNG có Delete button)
- Route prefix: `agent.tickets.*`

**create.blade.php** - Form tạo mới

- Extends: `layouts.agent_dashboard`  
- Fields: title*, priority*, assigned_to, description, unit_id, lease_id
- Chỉ hiển thị units/leases thuộc assigned properties
- AJAX submit với Notify system
- Route: `agent.tickets.store`

**show.blade.php** - Chi tiết ticket

- Extends: `layouts.agent_dashboard`
- Hiển thị: thông tin ticket, unit/lease/property liên kết, logs timeline
- Modal "Thêm nhật ký" với fields: action*, charge_to*, detail, cost_amount, cost_note
- Timeline CSS giống manager
- KHÔNG có nút Delete
- Routes: `agent.tickets.edit`, `agent.tickets.addLog`

**edit.blade.php** - Form cập nhật

- Extends: `layouts.agent_dashboard`
- Fields: title*, priority*, status*, assigned_to, description, unit_id, lease_id
- Pre-fill dữ liệu hiện tại
- Hiển thị thông tin người tạo, ngày tạo (read-only)
- Route: `agent.tickets.update`

### 4. Các điểm kỹ thuật quan trọng

**Database relationships đã có sẵn:**

- Ticket model: organization, unit, lease, createdBy, assignedTo, logs
- TicketLog model: ticket, actor, linkedInvoice

**Scope filtering:**

```php
$assignedPropertyIds = Auth::user()->assignedProperties()->pluck('properties.id');
$query = Ticket::whereHas('unit', function($q) use ($assignedPropertyIds) {
    $q->whereIn('property_id', $assignedPropertyIds);
})->orWhereDoesntHave('unit'); // Tickets không gắn unit cụ thể
```

**Validation rules:**

- title: required|string|max:255
- priority: required|in:low,medium,high,urgent
- status: required|in:open,in_progress,resolved,closed,cancelled (chỉ trong update)
- unit_id, lease_id, assigned_to: nullable, phải thuộc assigned properties

**Transaction handling:**

- Sử dụng DB::beginTransaction() cho store/update/addLog
- Tự động tạo log khi tạo ticket (action='created')
- Log changes khi update (status, assigned_to changes)

### 5. UI/UX Patterns

- Sử dụng notification system: `Notify.success()`, `Notify.error()`, `Notify.confirmDelete()`
- Bootstrap 5 classes
- FontAwesome icons
- Timeline component cho logs (CSS custom)
- Badge colors: status (success/warning/info/secondary/danger), priority (secondary/primary/warning/danger)

## Checklist triển khai

1. Tạo `app/Http/Controllers/Agent/TicketController.php`
2. Thêm routes vào `routes/web.php` (agent group)
3. Tạo thư mục `resources/views/agent/tickets/`
4. Tạo `index.blade.php` với filters và table
5. Tạo `create.blade.php` với form validation
6. Tạo `show.blade.php` với logs timeline và add log modal
7. Tạo `edit.blade.php` với pre-filled form
8. Test toàn bộ flow: list → create → view → edit → add log

## Lưu ý

- KHÔNG ảnh hưởng đến Manager ticket controller/views
- KHÔNG tạo migration (database đã có sẵn)
- Model Ticket và TicketLog đã tồn tại, không cần chỉnh sửa
- Tuân theo pattern hiện có của agent controllers (UnitController, LeaseController làm reference)

### To-dos

- [ ] Tạo Agent\TicketController với các methods: index, create, store, show, edit, update, addLog, getUnits, getLeases
- [ ] Thêm ticket routes vào agent route group trong routes/web.php
- [ ] Tạo resources/views/agent/tickets/index.blade.php với filters và danh sách tickets
- [ ] Tạo resources/views/agent/tickets/create.blade.php với form tạo ticket
- [ ] Tạo resources/views/agent/tickets/show.blade.php với chi tiết ticket, logs timeline và modal thêm log
- [ ] Tạo resources/views/agent/tickets/edit.blade.php với form cập nhật ticket