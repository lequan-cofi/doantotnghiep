# Hướng dẫn sử dụng Soft Deletes

## Tổng quan

Hệ thống đã được cấu hình với **Soft Deletes** (xóa mềm) cho các bảng quan trọng. Khi xóa bản ghi, dữ liệu không bị xóa vĩnh viễn mà chỉ được đánh dấu là đã xóa (`deleted_at`).

## Các bảng đã có Soft Deletes

### Bảng chính:
1. **users** - Người dùng
2. **organizations** - Tổ chức
3. **properties** - Bất động sản
4. **units** - Phòng/căn
5. **listings** - Tin đăng
6. **leases** - Hợp đồng thuê
7. **invoices** - Hóa đơn
8. **payments** - Thanh toán
9. **tickets** - Ticket bảo trì
10. **leads** - Khách hàng tiềm năng
11. **viewings** - Lịch xem phòng
12. **booking_deposits** - Đặt cọc

### Bảng phụ trợ:
1. **property_types** - Loại bất động sản
2. **amenities** - Tiện ích
3. **services** - Dịch vụ
4. **meters** - Đồng hồ
5. **documents** - Tài liệu
6. **salary_contracts** - Hợp đồng lương
7. **commission_policies** - Chính sách hoa hồng
8. **commission_events** - Sự kiện hoa hồng
9. **locations** - Địa chỉ

## Sử dụng trong Code

### 1. Xóa mềm (Soft Delete)

```php
// Xóa bản ghi (soft delete)
$property = Property::find(1);
$property->delete();

// hoặc
Property::destroy(1);
Property::destroy([1, 2, 3]);
```

### 2. Truy vấn bao gồm bản ghi đã xóa

```php
// Chỉ lấy bản ghi chưa xóa (mặc định)
$properties = Property::all();

// Lấy TẤT CẢ bản ghi (kể cả đã xóa)
$properties = Property::withTrashed()->get();

// Chỉ lấy bản ghi đã xóa
$properties = Property::onlyTrashed()->get();
```

### 3. Khôi phục bản ghi đã xóa

```php
// Khôi phục một bản ghi
$property = Property::withTrashed()->find(1);
$property->restore();

// Khôi phục nhiều bản ghi
Property::onlyTrashed()
    ->where('status', 1)
    ->restore();
```

### 4. Xóa vĩnh viễn (Force Delete)

```php
// Xóa VĨNH VIỄN (không thể khôi phục)
$property = Property::withTrashed()->find(1);
$property->forceDelete();

// hoặc
Property::withTrashed()->where('id', 1)->forceDelete();
```

### 5. Kiểm tra trạng thái xóa

```php
$property = Property::withTrashed()->find(1);

// Kiểm tra đã bị xóa mềm chưa
if ($property->trashed()) {
    echo "Đã bị xóa";
}
```

## Trường `deleted_by`

Mỗi bảng có thêm trường `deleted_by` để lưu thông tin người xóa.

### Tự động ghi nhận người xóa

```php
// Khi xóa, hệ thống tự động ghi nhận user_id của người đang đăng nhập
auth()->loginUsingId(1);
$property->delete();
// deleted_by = 1 (được tự động lưu)
```

### Lấy thông tin người xóa

```php
$property = Property::withTrashed()->find(1);
$deletedByUser = $property->deletedBy; // Relationship
echo $deletedByUser->full_name;
```

## Controller Examples

### Index - Danh sách với bản ghi đã xóa

```php
public function index(Request $request)
{
    $query = Property::query();
    
    // Nếu muốn xem cả bản ghi đã xóa
    if ($request->has('show_deleted')) {
        $query->withTrashed();
    }
    
    // Nếu chỉ xem bản ghi đã xóa
    if ($request->has('only_deleted')) {
        $query->onlyTrashed();
    }
    
    $properties = $query->paginate(20);
    
    return view('properties.index', compact('properties'));
}
```

### Destroy - Xóa mềm

```php
public function destroy($id)
{
    $property = Property::findOrFail($id);
    $property->delete(); // Soft delete
    
    return redirect()->back()->with('success', 'Đã xóa thành công. Có thể khôi phục sau.');
}
```

### Restore - Khôi phục

```php
public function restore($id)
{
    $property = Property::onlyTrashed()->findOrFail($id);
    $property->restore();
    
    return redirect()->back()->with('success', 'Đã khôi phục thành công.');
}
```

### Force Delete - Xóa vĩnh viễn

```php
public function forceDelete($id)
{
    $property = Property::withTrashed()->findOrFail($id);
    $property->forceDelete();
    
    return redirect()->back()->with('success', 'Đã xóa vĩnh viễn.');
}
```

## Routes Example

```php
// Soft delete
Route::delete('/properties/{id}', [PropertyController::class, 'destroy'])
    ->name('properties.destroy');

// Restore
Route::post('/properties/{id}/restore', [PropertyController::class, 'restore'])
    ->name('properties.restore');

// Force delete
Route::delete('/properties/{id}/force', [PropertyController::class, 'forceDelete'])
    ->name('properties.force-delete');
```

## Blade View Example

```blade
@foreach($properties as $property)
    <tr class="{{ $property->trashed() ? 'text-muted' : '' }}">
        <td>{{ $property->name }}</td>
        <td>{{ $property->status }}</td>
        <td>
            @if($property->trashed())
                <span class="badge bg-danger">Đã xóa</span>
                <form action="{{ route('properties.restore', $property->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Khôi phục</button>
                </form>
                <form action="{{ route('properties.force-delete', $property->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Xóa vĩnh viễn?')">
                        Xóa vĩnh viễn
                    </button>
                </form>
            @else
                <form action="{{ route('properties.destroy', $property->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-warning">Xóa</button>
                </form>
            @endif
        </td>
    </tr>
@endforeach
```

## Query Scopes với Relationships

```php
// Lấy properties kể cả đã xóa, với units chưa xóa
Property::withTrashed()->with('units')->get();

// Lấy properties chưa xóa, với units kể cả đã xóa
Property::with(['units' => function($q) {
    $q->withTrashed();
}])->get();

// Lấy TẤT CẢ kể cả đã xóa
Property::withTrashed()
    ->with(['units' => function($q) {
        $q->withTrashed();
    }])
    ->get();
```

## Best Practices

1. **Không xóa vĩnh viễn** các bản ghi quan trọng như:
   - Hợp đồng thuê (leases)
   - Hóa đơn (invoices)
   - Thanh toán (payments)
   - Users

2. **Thường xuyên review** các bản ghi đã xóa để quyết định có cần xóa vĩnh viễn không.

3. **Tạo scheduled job** để tự động xóa vĩnh viễn các bản ghi đã xóa quá X ngày:

```php
// app/Console/Commands/CleanOldSoftDeletes.php
Property::onlyTrashed()
    ->where('deleted_at', '<', now()->subMonths(6))
    ->forceDelete();
```

4. **Ghi log** khi xóa vĩnh viễn để audit.

## Lưu ý

- Soft deletes chỉ hoạt động với Eloquent ORM, không hoạt động với raw queries
- Khi dùng relationships, cần chú ý các bản ghi liên quan có thể đã bị xóa
- Foreign key constraints nên dùng `onDelete('cascade')` hoặc `nullOnDelete()` thay vì `onDelete('restrict')`

