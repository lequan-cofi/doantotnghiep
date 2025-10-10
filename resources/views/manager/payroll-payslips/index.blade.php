@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Phiếu Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Phiếu Lương</h1>
            <p class="mb-0">Quản lý phiếu lương nhân viên</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.payroll-payslips.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Tên nhân viên hoặc email...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Kỳ lương</label>
                        <select class="form-select" name="cycle_id">
                            <option value="">Tất cả</option>
                            @foreach($cycles as $cycle)
                                <option value="{{ $cycle->id }}" {{ request('cycle_id') == $cycle->id ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::createFromFormat('Y-m', $cycle->period_month)->format('m/Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('manager.payroll-payslips.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payslips Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Phiếu Lương</h6>
        </div>
        <div class="card-body">
            @if($payslips->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nhân viên</th>
                                <th>Kỳ lương</th>
                                <th>Lương cơ bản</th>
                                <th>Hoa hồng</th>
                                <th>Tổng lương</th>
                                <th>Khấu trừ</th>
                                <th>Thực lĩnh</th>
                                <th>Trạng thái</th>
                                <th>Ngày thanh toán</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payslips as $payslip)
                            <tr>
                                <td>
                                    <div>
                                        <strong>{{ $payslip->user->full_name }}</strong>
                                        <br><small class="text-muted">{{ $payslip->user->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ \Carbon\Carbon::createFromFormat('Y-m', $payslip->payrollCycle->period_month)->format('m/Y') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $salaryContract = \App\Models\SalaryContract::where('user_id', $payslip->user_id)
                                            ->where('status', 'active')
                                            ->first();
                                        $basicSalary = $salaryContract ? $salaryContract->base_salary : 0;
                                    @endphp
                                    <strong>{{ number_format($basicSalary, 0, ',', '.') }} VND</strong>
                                </td>
                                <td>
                                    @php
                                        $periodStart = \Carbon\Carbon::createFromFormat('Y-m', $payslip->payrollCycle->period_month)->startOfMonth();
                                        $periodEnd = \Carbon\Carbon::createFromFormat('Y-m', $payslip->payrollCycle->period_month)->endOfMonth();
                                        $commission = \App\Models\CommissionEvent::where('agent_id', $payslip->user_id)
                                            ->where('status', 'paid')
                                            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
                                            ->sum('commission_total');
                                    @endphp
                                    <span class="text-success">{{ number_format($commission, 0, ',', '.') }} VND</span>
                                </td>
                                <td>
                                    <strong class="text-primary">{{ number_format($payslip->gross_amount, 0, ',', '.') }} VND</strong>
                                </td>
                                <td>
                                    {{ number_format($payslip->deduction_amount, 0, ',', '.') }} VND
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($payslip->net_amount, 0, ',', '.') }} VND</strong>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Chờ thanh toán',
                                            'paid' => 'Đã thanh toán'
                                        ];
                                        
                                        // Handle unexpected status values
                                        $status = (string) $payslip->status;
                                        $color = $statusColors[$status] ?? 'secondary';
                                        $label = $statusLabels[$status] ?? 'Không xác định';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ $label }}
                                    </span>
                                    @if(config('app.debug'))
                                        <small class="text-muted">({{ $status }})</small>
                                    @endif
                                </td>
                                <td>
                                    @if($payslip->paid_at)
                                        {{ \Carbon\Carbon::parse($payslip->paid_at)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.payroll-payslips.show', $payslip->id) }}" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($payslip->payrollCycle->status === 'open')
                                            <a href="{{ route('manager.payroll-payslips.edit', $payslip->id) }}" 
                                               class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @if($payslip->status === 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="markAsPaid({{ $payslip->id }})" title="Đánh dấu đã thanh toán">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if($payslip->payrollCycle->status === 'open')
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deletePayslip({{ $payslip->id }})" title="Xóa">
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
                    {{ $payslips->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có phiếu lương nào</h5>
                    <p class="text-muted">Phiếu lương sẽ xuất hiện khi tạo kỳ lương và tạo phiếu lương cho nhân viên.</p>
                    <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-primary">
                        <i class="fas fa-calendar-alt"></i> Quản lý kỳ lương
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
                <p>Bạn có chắc chắn muốn xóa phiếu lương này?</p>
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
function deletePayslip(payslipId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/payroll-payslips/${payslipId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function markAsPaid(payslipId) {
    Notify.confirm({
        title: 'Đánh dấu đã thanh toán',
        message: 'Bạn có chắc chắn muốn đánh dấu phiếu lương này là đã thanh toán?',
        details: 'Sau khi đánh dấu, phiếu lương sẽ được chuyển sang trạng thái đã thanh toán.',
        type: 'success',
        confirmText: 'Đánh dấu đã thanh toán',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang xử lý...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/payroll-payslips/${payslipId}/mark-as-paid`, {
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
                    Notify.success(data.message, 'Đánh dấu thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi đánh dấu phiếu lương');
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
                
                Notify.error('Có lỗi xảy ra khi đánh dấu phiếu lương. Vui lòng thử lại.', 'Lỗi hệ thống');
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

