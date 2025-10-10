@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Phiếu Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết Phiếu Lương</h1>
            <p class="mb-0">{{ $payrollPayslip->user->full_name }} - {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->format('m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('manager.payroll-payslips.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            @if($payrollPayslip->payrollCycle->status === 'open')
                <a href="{{ route('manager.payroll-payslips.edit', $payrollPayslip->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Payslip Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Phiếu Lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Nhân viên:</strong></td>
                                    <td>
                                        <div>
                                            <strong>{{ $payrollPayslip->user->full_name }}</strong>
                                            <br><small class="text-muted">{{ $payrollPayslip->user->email }}</small>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Kỳ lương:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ \Carbon\Carbon::createFromFormat('Y-m', $payrollPayslip->payrollCycle->period_month)->format('m/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
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
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$payrollPayslip->status] }}">
                                            {{ $statusLabels[$payrollPayslip->status] }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $payrollPayslip->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày thanh toán:</strong></td>
                                    <td>
                                        @if($payrollPayslip->paid_at)
                                            {{ \Carbon\Carbon::parse($payrollPayslip->paid_at)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Phương thức thanh toán:</strong></td>
                                    <td>
                                        @if($payrollPayslip->payment_method)
                                            {{ $payrollPayslip->payment_method }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($payrollPayslip->note)
                    <hr>
                    <div>
                        <strong>Ghi chú:</strong>
                        <p class="mb-0">{{ $payrollPayslip->note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Salary Breakdown -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết Lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Thu nhập</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Lương cơ bản:</td>
                                    <td class="text-end">
                                        <strong>{{ number_format($salaryContract->base_salary ?? 0, 0, ',', '.') }} VND</strong>
                                    </td>
                                </tr>
                                @if($salaryContract && $salaryContract->allowances_json)
                                    @foreach($salaryContract->allowances_json as $key => $allowance)
                                        <tr>
                                            <td>{{ ucfirst(str_replace('_', ' ', $key)) }}:</td>
                                            <td class="text-end">{{ number_format($allowance, 0, ',', '.') }} VND</td>
                                        </tr>
                                    @endforeach
                                @endif
                                <tr>
                                    <td>Hoa hồng:</td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ number_format($totalCommission, 0, ',', '.') }} VND</strong>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Tổng thu nhập:</strong></td>
                                    <td class="text-end">
                                        <strong class="text-primary">{{ number_format($payrollPayslip->gross_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Khấu trừ</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Khấu trừ:</td>
                                    <td class="text-end">
                                        <strong class="text-warning">{{ number_format($payrollPayslip->deduction_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                </tr>
                                <tr class="border-top">
                                    <td><strong>Thực lĩnh:</strong></td>
                                    <td class="text-end">
                                        <strong class="text-success">{{ number_format($payrollPayslip->net_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Commission Details -->
            @if($commissionEvents->count() > 0)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết Hoa hồng</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Ngày</th>
                                    <th>Chính sách</th>
                                    <th>Sự kiện</th>
                                    <th>Số tiền gốc</th>
                                    <th>Hoa hồng</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissionEvents as $event)
                                <tr>
                                    <td>{{ $event->occurred_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div>
                                            <strong>{{ $event->policy->title }}</strong>
                                            <br><small class="text-muted">{{ $event->policy->code }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        @php
                                            $triggerLabels = [
                                                'deposit_paid' => 'Thanh toán cọc',
                                                'lease_signed' => 'Ký hợp đồng',
                                                'invoice_paid' => 'Thanh toán hóa đơn',
                                                'viewing_done' => 'Hoàn thành xem phòng',
                                                'listing_published' => 'Đăng tin'
                                            ];
                                        @endphp
                                        <span class="badge bg-info">{{ $triggerLabels[$event->trigger_event] ?? $event->trigger_event }}</span>
                                    </td>
                                    <td>{{ number_format($event->amount_base, 0, ',', '.') }} VND</td>
                                    <td>
                                        <strong class="text-success">{{ number_format($event->commission_total, 0, ',', '.') }} VND</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Đã thanh toán</span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ number_format($payrollPayslip->gross_amount, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Tổng lương (VND)</p>
                        </div>
                        <div class="col-12 mb-3">
                            <h4 class="text-warning">{{ number_format($payrollPayslip->deduction_amount, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Khấu trừ (VND)</p>
                        </div>
                        <div class="col-12">
                            <h4 class="text-success">{{ number_format($payrollPayslip->net_amount, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Thực lĩnh (VND)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    @if($payrollPayslip->payrollCycle->status === 'open')
                        <a href="{{ route('manager.payroll-payslips.edit', $payrollPayslip->id) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <button type="button" class="btn btn-info w-100 mb-2" onclick="recalculatePayslip({{ $payrollPayslip->id }})">
                            <i class="fas fa-calculator"></i> Tính lại
                        </button>
                    @endif
                    @if($payrollPayslip->status === 'pending')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="markAsPaid({{ $payrollPayslip->id }})">
                            <i class="fas fa-check"></i> Đánh dấu đã thanh toán
                        </button>
                    @endif
                    @if($payrollPayslip->payrollCycle->status === 'open')
                        <button type="button" class="btn btn-danger w-100" onclick="deletePayslip({{ $payrollPayslip->id }})">
                            <i class="fas fa-trash"></i> Xóa phiếu lương
                        </button>
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

function recalculatePayslip(payslipId) {
    Notify.confirm({
        title: 'Tính lại phiếu lương',
        message: 'Bạn có chắc chắn muốn tính lại phiếu lương này?',
        details: 'Hệ thống sẽ tính lại tất cả các khoản lương, phụ cấp và khấu trừ.',
        type: 'warning',
        confirmText: 'Tính lại',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tính lại...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang tính lại phiếu lương...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/payroll-payslips/${payslipId}/recalculate`, {
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
                    Notify.success(data.message, 'Tính lại thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi tính lại phiếu lương');
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
                
                Notify.error('Có lỗi xảy ra khi tính lại phiếu lương. Vui lòng thử lại.', 'Lỗi hệ thống');
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


