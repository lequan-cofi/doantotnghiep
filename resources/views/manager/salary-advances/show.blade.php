@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết đơn ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye text-primary"></i>
                Chi tiết đơn ứng lương #{{ $salaryAdvance->id }}
            </h1>
            <p class="text-muted mb-0">Thông tin chi tiết đơn ứng lương</p>
        </div>
        <div>
            <a href="{{ route('manager.salary-advances.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            @if($salaryAdvance->canBeDeleted())
                <a href="{{ route('manager.salary-advances.edit', $salaryAdvance->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $salaryAdvance->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nhân viên:</strong></td>
                                    <td>
                                        <div>
                                            <strong>{{ $salaryAdvance->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $salaryAdvance->user->email }}</small>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Số tiền:</strong></td>
                                    <td>
                                        <strong class="text-primary">
                                            {{ number_format($salaryAdvance->amount) }} {{ $salaryAdvance->currency }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày ứng:</strong></td>
                                    <td>{{ $salaryAdvance->advance_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày trả dự kiến:</strong></td>
                                    <td>{{ $salaryAdvance->expected_repayment_date->format('d/m/Y') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        <span class="badge bg-{{ $salaryAdvance->status_color }}">
                                            {{ $salaryAdvance->status_label }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Phương thức trả:</strong></td>
                                    <td>{{ $salaryAdvance->repayment_method_label }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Đã trả:</strong></td>
                                    <td>
                                        <strong class="text-success">
                                            {{ number_format($salaryAdvance->repaid_amount) }} {{ $salaryAdvance->currency }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Còn lại:</strong></td>
                                    <td>
                                        <strong class="text-danger">
                                            {{ number_format($salaryAdvance->remaining_amount) }} {{ $salaryAdvance->currency }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $salaryAdvance->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reason and Notes -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lý do và ghi chú</h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><strong>Lý do ứng lương:</strong></label>
                        <p class="form-control-plaintext">{{ $salaryAdvance->reason }}</p>
                    </div>
                    
                    @if($salaryAdvance->note)
                        <div class="form-group">
                            <label><strong>Ghi chú:</strong></label>
                            <p class="form-control-plaintext">{{ $salaryAdvance->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Repayment Details -->
            @if($salaryAdvance->repayment_method === 'payroll_deduction' || $salaryAdvance->repayment_method === 'installment')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Chi tiết trả nợ</h6>
                    </div>
                    <div class="card-body">
                        @if($salaryAdvance->repayment_method === 'installment' && $salaryAdvance->installment_months)
                            <div class="form-group">
                                <label><strong>Số tháng trả góp:</strong></label>
                                <p class="form-control-plaintext">{{ $salaryAdvance->installment_months }} tháng</p>
                            </div>
                        @endif
                        
                        @if($salaryAdvance->repayment_method === 'payroll_deduction' && $salaryAdvance->monthly_deduction)
                            <div class="form-group">
                                <label><strong>Số tiền trừ hàng tháng:</strong></label>
                                <p class="form-control-plaintext">
                                    {{ number_format($salaryAdvance->monthly_deduction) }} {{ $salaryAdvance->currency }}
                                </p>
                            </div>
                        @endif
                        
                        <div class="form-group">
                            <label><strong>Số tiền trừ tháng này:</strong></label>
                            <p class="form-control-plaintext">
                                <strong class="text-warning">
                                    {{ number_format($salaryAdvance->calculateMonthlyDeduction()) }} {{ $salaryAdvance->currency }}
                                </strong>
                            </p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Approval Information -->
            @if($salaryAdvance->status === 'approved' || $salaryAdvance->status === 'rejected')
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            Thông tin {{ $salaryAdvance->status === 'approved' ? 'duyệt' : 'từ chối' }}
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($salaryAdvance->status === 'approved')
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Người duyệt:</strong> {{ $salaryAdvance->approver->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Ngày duyệt:</strong> {{ $salaryAdvance->approved_at->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @else
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Người từ chối:</strong> {{ $salaryAdvance->rejector->name ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong>Ngày từ chối:</strong> {{ $salaryAdvance->rejected_at->format('d/m/Y H:i') ?? 'N/A' }}</p>
                                </div>
                            </div>
                            @if($salaryAdvance->rejection_reason)
                                <div class="form-group">
                                    <label><strong>Lý do từ chối:</strong></label>
                                    <p class="form-control-plaintext text-danger">{{ $salaryAdvance->rejection_reason }}</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    @if($salaryAdvance->canBeApproved())
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="approveAdvance({{ $salaryAdvance->id }})">
                            <i class="fas fa-check"></i> Duyệt đơn ứng lương
                        </button>
                        <button type="button" class="btn btn-danger w-100 mb-2" onclick="rejectAdvance({{ $salaryAdvance->id }})">
                            <i class="fas fa-times"></i> Từ chối đơn ứng lương
                        </button>
                    @endif
                    
                    @if($salaryAdvance->canBeRepaid())
                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="addRepayment({{ $salaryAdvance->id }})">
                            <i class="fas fa-plus"></i> Thêm thanh toán
                        </button>
                    @endif
                    
                    @if($salaryAdvance->canBeDeleted())
                        <button type="button" class="btn btn-danger w-100" onclick="deleteAdvance({{ $salaryAdvance->id }})">
                            <i class="fas fa-trash"></i> Xóa đơn ứng lương
                        </button>
                    @endif
                </div>
            </div>

            <!-- Summary -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Số tiền ứng:</span>
                        <strong>{{ number_format($salaryAdvance->amount) }} {{ $salaryAdvance->currency }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Đã trả:</span>
                        <strong class="text-success">{{ number_format($salaryAdvance->repaid_amount) }} {{ $salaryAdvance->currency }}</strong>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span><strong>Còn lại:</strong></span>
                        <strong class="text-danger">{{ number_format($salaryAdvance->remaining_amount) }} {{ $salaryAdvance->currency }}</strong>
                    </div>
                    
                    @if($salaryAdvance->remaining_amount > 0)
                        <div class="mt-3">
                            <div class="progress">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ ($salaryAdvance->repaid_amount / $salaryAdvance->amount) * 100 }}%">
                                </div>
                            </div>
                            <small class="text-muted">
                                Đã trả: {{ number_format(($salaryAdvance->repaid_amount / $salaryAdvance->amount) * 100, 1) }}%
                            </small>
                        </div>
                    @endif
                </div>
            </div>
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
                               step="0.01" min="0.01" max="{{ $salaryAdvance->remaining_amount }}" required>
                        <small class="form-text text-muted">Số tiền còn lại: {{ number_format($salaryAdvance->remaining_amount) }} {{ $salaryAdvance->currency }}</small>
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
    const repaymentForm = document.getElementById('repaymentForm');
    repaymentForm.action = `/manager/salary-advances/${advanceId}/repayment`;
    
    const repaymentModal = new bootstrap.Modal(document.getElementById('repaymentModal'));
    repaymentModal.show();
}
</script>
@endpush
