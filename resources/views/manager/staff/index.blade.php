@extends('layouts.manager_dashboard')

@section('title', 'Quản lý nhân viên')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Quản lý nhân viên</h1>
                <p class="text-muted">Quản lý nhân viên, lương, hoa hồng và bất động sản được gắn</p>
                @if(auth()->user()->organizations()->first())
                <small class="text-info">
                    <i class="fas fa-building"></i> 
                    Tổ chức: {{ auth()->user()->organizations()->first()->name }}
                </small>
                @endif
            </div>
            <div class="btn-group" role="group">
                <a href="{{ route('manager.staff.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Thêm nhân viên mới
                </a>
                <button type="button" class="btn btn-outline-success" onclick="exportStaffData()" title="Xuất Excel">
                    <i class="fas fa-file-excel"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()" title="Xóa bộ lọc">
                    <i class="fas fa-filter-circle-xmark"></i>
                </button>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('manager.staff.index') }}">
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tìm kiếm</label>
                                    <input type="text" name="search" class="form-control" placeholder="Tên, email, số điện thoại..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Vai trò</label>
                                    <select name="role_id" class="form-select select2" data-placeholder="Chọn vai trò">
                                        <option value="">Tất cả vai trò</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="">Tất cả</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Từ ngày</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary w-100" onclick="performSearch()">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Actions -->
        @if($staff->count() > 0)
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Thao tác hàng loạt</h6>
                        <small class="text-muted">Chọn nhân viên và thực hiện thao tác</small>
                    </div>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-success btn-sm" onclick="performBulkAction('activate')" title="Kích hoạt">
                            <i class="fas fa-play"></i> Kích hoạt
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="performBulkAction('deactivate')" title="Tạm ngưng">
                            <i class="fas fa-pause"></i> Tạm ngưng
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="performBulkAction('delete')" title="Xóa">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Staff Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                @if($staff->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()" title="Chọn tất cả">
                                </th>
                                <th width="5%">#</th>
                                <th width="18%">Nhân viên</th>
                                <th width="13%">Vai trò</th>
                                <th width="13%">Lương cơ bản</th>
                                <th width="8%">BĐS quản lý</th>
                                <th width="8%">Hoa hồng</th>
                                <th width="8%">Trạng thái</th>
                                <th width="8%">Ngày tạo</th>
                                <th width="10%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staff as $member)
                            <tr>
                                <td>
                                    <input type="checkbox" name="staff_ids[]" value="{{ $member->id }}" class="staff-checkbox" onchange="updateBulkActions()">
                                </td>
                                <td>{{ $loop->iteration + ($staff->currentPage() - 1) * $staff->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($member->full_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $member->full_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $member->email }}</small>
                                            @if($member->phone)
                                            <br>
                                            <small class="text-muted"><i class="fas fa-phone fa-xs"></i> {{ $member->phone }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($member->organizationRoles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @php
                                        $activeSalary = $member->salaryContracts()->where('status', 'active')->latest('effective_from')->first();
                                    @endphp
                                    @if($activeSalary)
                                    <strong class="text-success">{{ number_format($activeSalary->base_salary, 0, ',', '.') }} VNĐ</strong>
                                    <br>
                                    <small class="text-muted">Kỳ lương: {{ $activeSalary->pay_day }}/tháng</small>
                                    @else
                                    <span class="text-muted">Chưa thiết lập</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $member->assignedProperties->count() }} BĐS</span>
                                </td>
                                <td>
                                    @php
                                        $totalCommission = DB::table('commission_events')
                                            ->where('user_id', $member->id)
                                            ->where('status', 'paid')
                                            ->sum('commission_total');
                                    @endphp
                                    @if($totalCommission > 0)
                                    <strong class="text-warning">{{ number_format($totalCommission, 0, ',', '.') }} VNĐ</strong>
                                    @else
                                    <span class="text-muted">0 VNĐ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $member->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="viewStaffDetails({{ $member->id }}, '{{ $member->full_name }}')" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="editStaff({{ $member->id }}, '{{ $member->full_name }}')" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-{{ $member->status ? 'warning' : 'success' }}" 
                                                onclick="toggleStaffStatus({{ $member->id }}, '{{ $member->full_name }}', {{ $member->status ? 'true' : 'false' }})" 
                                                title="{{ $member->status ? 'Tạm ngưng' : 'Kích hoạt' }}">
                                            <i class="fas fa-{{ $member->status ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStaff({{ $member->id }}, '{{ $member->full_name }}')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $staff->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có nhân viên nào.</p>
                    <a href="{{ route('manager.staff.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm nhân viên đầu tiên
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

/* Bulk actions disabled state */
.btn.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Checkbox styling */
input[type="checkbox"] {
    transform: scale(1.2);
    cursor: pointer;
}

/* Loading states */
.btn.loading {
    position: relative;
    color: transparent !important;
}

.btn.loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

/* Notification toast positioning */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: function() {
            return $(this).data('placeholder') || 'Chọn...';
        },
        allowClear: true,
        width: '100%'
    });

    // Show success message if redirected from create/edit
    @if(session('success'))
        Notify.success('{{ session('success') }}', 'Thành công!');
    @endif

    @if(session('error'))
        Notify.error('{{ session('error') }}', 'Lỗi!');
    @endif

    @if(session('warning'))
        Notify.warning('{{ session('warning') }}', 'Cảnh báo!');
    @endif

    @if(session('info'))
        Notify.info('{{ session('info') }}', 'Thông tin!');
    @endif

    // Initialize bulk actions state
    updateBulkActions();
});

// Delete staff function with enhanced notifications
function deleteStaff(id, name) {
    Notify.confirmDelete(`Bạn có chắc chắn muốn xóa nhân viên "${name}"?`, () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xóa...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch(`/manager/staff/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            // Hide loading notification
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success('Xóa nhân viên thành công!', 'Thành công!');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể xóa nhân viên. Vui lòng thử lại.', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            Notify.error('Đã xảy ra lỗi khi xóa nhân viên. Vui lòng kiểm tra kết nối và thử lại.', 'Lỗi hệ thống!');
        });
    });
}

// Toggle staff status function
function toggleStaffStatus(id, name, currentStatus) {
    const action = currentStatus ? 'tạm ngưng' : 'kích hoạt';
    const newStatus = currentStatus ? 0 : 1;
    
    Notify.confirm(`Bạn có chắc chắn muốn ${action} nhân viên "${name}"?`, () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang cập nhật...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch(`/manager/staff/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                status: newStatus
            })
        })
        .then(response => {
            // Hide loading notification
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const actionText = newStatus ? 'kích hoạt' : 'tạm ngưng';
                Notify.success(`Đã ${actionText} nhân viên thành công!`, 'Thành công!');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể cập nhật trạng thái nhân viên.', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Toggle status error:', error);
            Notify.error('Đã xảy ra lỗi khi cập nhật trạng thái. Vui lòng thử lại.', 'Lỗi hệ thống!');
        });
    });
}

// View staff details function
function viewStaffDetails(id, name) {
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang tải...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });

    // Navigate to staff details page
    window.location.href = `/manager/staff/${id}`;
}

// Edit staff function
function editStaff(id, name) {
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang tải...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });

    // Navigate to staff edit page
    window.location.href = `/manager/staff/${id}/edit`;
}

// Search function with loading state
function performSearch() {
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang tìm kiếm...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 2000
        });

        // Submit form
        searchForm.submit();
    }
}

// Clear filters function
function clearFilters() {
    Notify.confirm('Bạn có chắc chắn muốn xóa tất cả bộ lọc?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xóa bộ lọc...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 1000
        });

        // Navigate to clean URL
        window.location.href = '{{ route('manager.staff.index') }}';
    });
}

// Export staff data function
function exportStaffData() {
    Notify.confirm('Bạn có muốn xuất danh sách nhân viên ra file Excel?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xuất dữ liệu...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        // Get current search parameters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = `{{ route('manager.staff.index') }}/export?${urlParams.toString()}`;

        // Create temporary link for download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = 'danh-sach-nhan-vien.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Hide loading notification after a delay
        setTimeout(() => {
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            Notify.success('Xuất dữ liệu thành công!', 'Thành công!');
        }, 2000);
    });
}

// Bulk actions function
function performBulkAction(action) {
    const selectedIds = getSelectedStaffIds();
    
    if (selectedIds.length === 0) {
        Notify.warning('Vui lòng chọn ít nhất một nhân viên để thực hiện thao tác.', 'Cảnh báo!');
        return;
    }

    let actionText = '';
    let confirmMessage = '';
    
    switch(action) {
        case 'delete':
            actionText = 'xóa';
            confirmMessage = `Bạn có chắc chắn muốn xóa ${selectedIds.length} nhân viên đã chọn?`;
            break;
        case 'activate':
            actionText = 'kích hoạt';
            confirmMessage = `Bạn có chắc chắn muốn kích hoạt ${selectedIds.length} nhân viên đã chọn?`;
            break;
        case 'deactivate':
            actionText = 'tạm ngưng';
            confirmMessage = `Bạn có chắc chắn muốn tạm ngưng ${selectedIds.length} nhân viên đã chọn?`;
            break;
        default:
            Notify.error('Thao tác không hợp lệ.', 'Lỗi!');
            return;
    }

    Notify.confirm(confirmMessage, () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: `Đang ${actionText}...`,
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch('/manager/staff/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                ids: selectedIds
            })
        })
        .then(response => {
            // Hide loading notification
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(`${actionText.charAt(0).toUpperCase() + actionText.slice(1)} ${selectedIds.length} nhân viên thành công!`, 'Thành công!');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message || `Không thể ${actionText} nhân viên.`, 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Bulk action error:', error);
            Notify.error(`Đã xảy ra lỗi khi ${actionText} nhân viên. Vui lòng thử lại.`, 'Lỗi hệ thống!');
        });
    });
}

// Helper function to get selected staff IDs
function getSelectedStaffIds() {
    const checkboxes = document.querySelectorAll('input[name="staff_ids[]"]:checked');
    return Array.from(checkboxes).map(checkbox => checkbox.value);
}

// Toggle select all checkboxes
function toggleSelectAll() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const staffCheckboxes = document.querySelectorAll('.staff-checkbox');
    
    staffCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
    
    updateBulkActions();
}

// Update bulk actions based on selected items
function updateBulkActions() {
    const selectedIds = getSelectedStaffIds();
    const bulkActionButtons = document.querySelectorAll('.btn-group button[onclick*="performBulkAction"]');
    
    if (selectedIds.length > 0) {
        bulkActionButtons.forEach(button => {
            button.disabled = false;
            button.classList.remove('disabled');
        });
    } else {
        bulkActionButtons.forEach(button => {
            button.disabled = true;
            button.classList.add('disabled');
        });
    }
    
    // Update select all checkbox state
    const selectAllCheckbox = document.getElementById('selectAll');
    const staffCheckboxes = document.querySelectorAll('.staff-checkbox');
    const checkedCount = document.querySelectorAll('.staff-checkbox:checked').length;
    
    if (checkedCount === 0) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = false;
    } else if (checkedCount === staffCheckboxes.length) {
        selectAllCheckbox.indeterminate = false;
        selectAllCheckbox.checked = true;
    } else {
        selectAllCheckbox.indeterminate = true;
    }
}

// Form validation with notifications
function validateSearchForm() {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;
    
    if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
        Notify.warning('Ngày bắt đầu không thể lớn hơn ngày kết thúc.', 'Cảnh báo!');
        return false;
    }
    
    return true;
}

// Add form validation to search form
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (!validateSearchForm()) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
@endsection

