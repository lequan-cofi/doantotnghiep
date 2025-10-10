@extends('layouts.manager_dashboard')

@section('title', 'Quản lý hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-contract text-primary"></i>
                Quản lý hợp đồng lương
            </h1>
            <p class="text-muted mb-0">Quản lý các hợp đồng lương của nhân viên</p>
        </div>
        <a href="{{ route('manager.salary-contracts.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo hợp đồng lương
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.salary-contracts.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="status">Trạng thái</label>
                            <select name="status" id="status" class="form-control">
                                <option value="">Tất cả trạng thái</option>
                                @foreach($statuses as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="user_id">Nhân viên</label>
                            <select name="user_id" id="user_id" class="form-control">
                                <option value="">Tất cả nhân viên</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->full_name ?? 'N/A' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_from">Từ ngày</label>
                            <input type="date" name="date_from" id="date_from" class="form-control" 
                                   value="{{ request('date_from') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="date_to">Đến ngày</label>
                            <input type="date" name="date_to" id="date_to" class="form-control" 
                                   value="{{ request('date_to') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="search">Tìm kiếm</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Tên, email..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('manager.salary-contracts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Contracts List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách hợp đồng lương</h6>
        </div>
        <div class="card-body">
            @if($salaryContracts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nhân viên</th>
                                <th>Lương cơ bản</th>
                                <th>Chu kỳ trả</th>
                                <th>Ngày hiệu lực</th>
                                <th>Ngày hết hạn</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryContracts as $contract)
                                <tr>
                                    <td>{{ $contract->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $contract->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $contract->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($contract->base_salary) }} {{ $contract->currency }}</strong>
                                    </td>
                                    <td>
                                        @switch($contract->pay_cycle)
                                            @case('monthly')
                                                <span class="badge bg-info">Hàng tháng</span>
                                                @break
                                            @case('weekly')
                                                <span class="badge bg-warning">Hàng tuần</span>
                                                @break
                                            @case('daily')
                                                <span class="badge bg-success">Hàng ngày</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>{{ $contract->effective_from->format('d/m/Y') }}</td>
                                    <td>{{ $contract->effective_to ? $contract->effective_to->format('d/m/Y') : 'Không giới hạn' }}</td>
                                    <td>
                                        @switch($contract->status)
                                            @case('active')
                                                <span class="badge bg-success">Đang hoạt động</span>
                                                @break
                                            @case('inactive')
                                                <span class="badge bg-warning">Tạm dừng</span>
                                                @break
                                            @case('terminated')
                                                <span class="badge bg-danger">Đã chấm dứt</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manager.salary-contracts.show', $contract->id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($contract->status !== 'terminated')
                                                <a href="{{ route('manager.salary-contracts.edit', $contract->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                            @if($contract->status === 'active')
                                                <button type="button" class="btn btn-sm btn-secondary" 
                                                        onclick="terminateContract({{ $contract->id }})" title="Chấm dứt">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                            @elseif($contract->status === 'inactive')
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="activateContract({{ $contract->id }})" title="Kích hoạt">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                            @if($contract->status === 'inactive')
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteContract({{ $contract->id }})" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $salaryContracts->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có hợp đồng lương nào</h5>
                    <p class="text-muted">Hãy tạo hợp đồng lương đầu tiên để bắt đầu quản lý.</p>
                    <a href="{{ route('manager.salary-contracts.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo hợp đồng lương mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa hợp đồng lương này?</p>
                <p class="text-danger"><strong>Hành động này không thể hoàn tác!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/notifications.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script>
function deleteContract(contractId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/salary-contracts/${contractId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function terminateContract(contractId) {
    Notify.confirm({
        title: 'Chấm dứt hợp đồng lương',
        message: 'Bạn có chắc chắn muốn chấm dứt hợp đồng lương này?',
        details: 'Sau khi chấm dứt, hợp đồng sẽ không thể kích hoạt lại.',
        type: 'warning',
        confirmText: 'Chấm dứt',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang chấm dứt...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/salary-contracts/${contractId}/terminate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide loading toast
                const toastElement = document.getElementById(loadingToast);
                if (toastElement) {
                    const bsToast = bootstrap.Toast.getInstance(toastElement);
                    if (bsToast) bsToast.hide();
                }

                if (data.success) {
                    Notify.success(data.message, 'Chấm dứt thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi chấm dứt hợp đồng');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading toast
                const toastElement = document.getElementById(loadingToast);
                if (toastElement) {
                    const bsToast = bootstrap.Toast.getInstance(toastElement);
                    if (bsToast) bsToast.hide();
                }
                
                Notify.error('Có lỗi xảy ra khi chấm dứt hợp đồng. Vui lòng thử lại.', 'Lỗi hệ thống');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    });
}

function activateContract(contractId) {
    Notify.confirm({
        title: 'Kích hoạt hợp đồng lương',
        message: 'Bạn có chắc chắn muốn kích hoạt hợp đồng lương này?',
        details: 'Hợp đồng sẽ được chuyển sang trạng thái đang hoạt động.',
        type: 'success',
        confirmText: 'Kích hoạt',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang kích hoạt...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/salary-contracts/${contractId}/activate`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                // Hide loading toast
                const toastElement = document.getElementById(loadingToast);
                if (toastElement) {
                    const bsToast = bootstrap.Toast.getInstance(toastElement);
                    if (bsToast) bsToast.hide();
                }

                if (data.success) {
                    Notify.success(data.message, 'Kích hoạt thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi kích hoạt hợp đồng');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Hide loading toast
                const toastElement = document.getElementById(loadingToast);
                if (toastElement) {
                    const bsToast = bootstrap.Toast.getInstance(toastElement);
                    if (bsToast) bsToast.hide();
                }
                
                Notify.error('Có lỗi xảy ra khi kích hoạt hợp đồng. Vui lòng thử lại.', 'Lỗi hệ thống');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    });
}
</script>
@endpush
