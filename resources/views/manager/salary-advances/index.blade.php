@extends('layouts.manager_dashboard')

@section('title', 'Quản lý ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-money-bill-wave text-primary"></i>
                Quản lý ứng lương
            </h1>
            <p class="text-muted mb-0">Quản lý các đơn ứng lương của nhân viên</p>
        </div>
        <a href="{{ route('manager.salary-advances.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo đơn ứng lương
        </a>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.salary-advances.index') }}">
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
                                        {{ $user->name }}
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
                        <a href="{{ route('manager.salary-advances.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Advances List -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn ứng lương</h6>
        </div>
        <div class="card-body">
            @if($salaryAdvances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nhân viên</th>
                                <th>Số tiền</th>
                                <th>Ngày ứng</th>
                                <th>Ngày trả dự kiến</th>
                                <th>Trạng thái</th>
                                <th>Phương thức trả</th>
                                <th>Còn lại</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryAdvances as $advance)
                                <tr>
                                    <td>{{ $advance->id }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $advance->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $advance->user->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($advance->amount) }} {{ $advance->currency }}</strong>
                                    </td>
                                    <td>{{ $advance->advance_date->format('d/m/Y') }}</td>
                                    <td>{{ $advance->expected_repayment_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $advance->status_color }}">
                                            {{ $advance->status_label }}
                                        </span>
                                    </td>
                                    <td>{{ $advance->repayment_method_label }}</td>
                                    <td>
                                        <strong class="text-danger">
                                            {{ number_format($advance->remaining_amount) }} {{ $advance->currency }}
                                        </strong>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manager.salary-advances.show', $advance->id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($advance->canBeDeleted())
                                                <a href="{{ route('manager.salary-advances.edit', $advance->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="deleteAdvance({{ $advance->id }})" title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                            @if($advance->canBeApproved())
                                                <button type="button" class="btn btn-sm btn-success" 
                                                        onclick="approveAdvance({{ $advance->id }})" title="Duyệt">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                        onclick="rejectAdvance({{ $advance->id }})" title="Từ chối">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                            @if($advance->canBeRepaid())
                                                <button type="button" class="btn btn-sm btn-primary" 
                                                        onclick="addRepayment({{ $advance->id }})" title="Thêm thanh toán">
                                                    <i class="fas fa-plus"></i>
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
                    {{ $salaryAdvances->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có đơn ứng lương nào</h5>
                    <p class="text-muted">Hãy tạo đơn ứng lương đầu tiên để bắt đầu quản lý.</p>
                    <a href="{{ route('manager.salary-advances.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo đơn ứng lương mới
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
                <p>Bạn có chắc chắn muốn xóa đơn ứng lương này?</p>
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

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectModalLabel">Từ chối đơn ứng lương</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="rejection_reason">Lý do từ chối</label>
                        <textarea name="rejection_reason" id="rejection_reason" class="form-control" 
                                  rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Repayment Modal -->
<div class="modal fade" id="repaymentModal" tabindex="-1" aria-labelledby="repaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="repaymentModalLabel">Thêm thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="repaymentForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="repayment_amount">Số tiền thanh toán</label>
                        <input type="number" name="amount" id="repayment_amount" class="form-control" 
                               step="0.01" min="0.01" required>
                        <small class="form-text text-muted">Số tiền còn lại: <span id="remaining-amount">0</span> VND</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm thanh toán</button>
                </div>
            </form>
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
function deleteAdvance(advanceId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/salary-advances/${advanceId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function approveAdvance(advanceId) {
    Notify.confirm({
        title: 'Duyệt đơn ứng lương',
        message: 'Bạn có chắc chắn muốn duyệt đơn ứng lương này?',
        details: 'Sau khi duyệt, đơn ứng lương sẽ được chuyển sang trạng thái đã duyệt.',
        type: 'success',
        confirmText: 'Duyệt',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang duyệt...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang duyệt...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/salary-advances/${advanceId}/approve`, {
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
                    Notify.success(data.message, 'Duyệt thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi duyệt đơn ứng lương');
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
                
                Notify.error('Có lỗi xảy ra khi duyệt đơn ứng lương. Vui lòng thử lại.', 'Lỗi hệ thống');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    });
}

function rejectAdvance(advanceId) {
    const rejectForm = document.getElementById('rejectForm');
    rejectForm.action = `/manager/salary-advances/${advanceId}/reject`;
    
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    rejectModal.show();
}

function addRepayment(advanceId) {
    // Get remaining amount from the table row
    const row = event.target.closest('tr');
    const remainingAmount = row.querySelector('td:nth-child(8) strong').textContent.replace(/[^\d]/g, '');
    
    document.getElementById('remaining-amount').textContent = new Intl.NumberFormat('vi-VN').format(remainingAmount);
    document.getElementById('repayment_amount').max = remainingAmount;
    
    const repaymentForm = document.getElementById('repaymentForm');
    repaymentForm.action = `/manager/salary-advances/${advanceId}/repayment`;
    
    const repaymentModal = new bootstrap.Modal(document.getElementById('repaymentModal'));
    repaymentModal.show();
}
</script>
@endpush
