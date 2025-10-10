@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Kỳ Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết Kỳ Lương</h1>
            <p class="mb-0">{{ \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->format('m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            @if($payrollCycle->status === 'open')
                <a href="{{ route('manager.payroll-cycles.edit', $payrollCycle->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <button type="button" class="btn btn-success" onclick="generatePayslips({{ $payrollCycle->id }})">
                    <i class="fas fa-calculator"></i> Tạo phiếu lương
                </button>
                <button type="button" class="btn btn-secondary" onclick="lockCycle({{ $payrollCycle->id }})">
                    <i class="fas fa-lock"></i> Khóa kỳ lương
                </button>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Cycle Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Kỳ Lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Kỳ lương:</strong></td>
                                    <td>{{ \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->format('m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'open' => 'success',
                                                'locked' => 'warning',
                                                'paid' => 'info'
                                            ];
                                            $statusLabels = [
                                                'open' => 'Mở',
                                                'locked' => 'Đã khóa',
                                                'paid' => 'Đã thanh toán'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$payrollCycle->status] }}">
                                            {{ $statusLabels[$payrollCycle->status] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $payrollCycle->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Số phiếu lương:</strong></td>
                                    <td><span class="badge bg-primary">{{ $totalEmployees }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày khóa:</strong></td>
                                    <td>
                                        @if($payrollCycle->locked_at)
                                            {{ \Carbon\Carbon::parse($payrollCycle->locked_at)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Chưa khóa</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày thanh toán:</strong></td>
                                    <td>
                                        @if($payrollCycle->paid_at)
                                            {{ \Carbon\Carbon::parse($payrollCycle->paid_at)->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">Chưa thanh toán</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    @if($payrollCycle->note)
                    <hr>
                    <div>
                        <strong>Ghi chú:</strong>
                        <p class="mb-0">{{ $payrollCycle->note }}</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Payslips Table -->
            @if($payrollCycle->payslips->count() > 0)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Danh sách Phiếu Lương</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nhân viên</th>
                                    <th>Lương cơ bản</th>
                                    <th>Phụ cấp</th>
                                    <th>Hoa hồng</th>
                                    <th>Tổng lương</th>
                                    <th>Khấu trừ</th>
                                    <th>Thực lĩnh</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payrollCycle->payslips as $payslip)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $payslip->user->full_name }}</strong>
                                            <br><small class="text-muted">{{ $payslip->user->email }}</small>
                                        </div>
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
                                            $allowances = 0;
                                            if ($salaryContract && $salaryContract->allowances_json) {
                                                foreach ($salaryContract->allowances_json as $allowance) {
                                                    $allowances += $allowance;
                                                }
                                            }
                                        @endphp
                                        {{ number_format($allowances, 0, ',', '.') }} VND
                                    </td>
                                    <td>
                                        @php
                                            $periodStart = \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->startOfMonth();
                                            $periodEnd = \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->endOfMonth();
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
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$payslip->status] }}">
                                            {{ $statusLabels[$payslip->status] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manager.payroll-payslips.show', $payslip->id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($payrollCycle->status === 'open')
                                                <a href="{{ route('manager.payroll-payslips.edit', $payslip->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
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

        <!-- Statistics -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $totalEmployees }}</h4>
                                <p class="mb-0 text-muted">Nhân viên</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ number_format($totalNet, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Tổng thực lĩnh (VND)</p>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="text-info">{{ number_format($totalGross, 0, ',', '.') }}</h5>
                                <p class="mb-0 text-muted">Tổng lương (VND)</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h5 class="text-warning">{{ number_format($totalDeductions, 0, ',', '.') }}</h5>
                            <p class="mb-0 text-muted">Tổng khấu trừ (VND)</p>
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
                    @if($payrollCycle->status === 'open')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="generatePayslips({{ $payrollCycle->id }})">
                            <i class="fas fa-calculator"></i> Tạo phiếu lương
                        </button>
                        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="lockCycle({{ $payrollCycle->id }})">
                            <i class="fas fa-lock"></i> Khóa kỳ lương
                        </button>
                        <a href="{{ route('manager.payroll-cycles.edit', $payrollCycle->id) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Kỳ lương đã được khóa và không thể chỉnh sửa
                        </div>
                    @endif
                </div>
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
function generatePayslips(cycleId) {
    Notify.confirm({
        title: 'Tạo phiếu lương',
        message: 'Bạn có chắc chắn muốn tạo phiếu lương cho kỳ lương này?',
        details: 'Hệ thống sẽ tự động tính toán lương cho tất cả nhân viên trong kỳ này.',
        type: 'info',
        confirmText: 'Tạo phiếu lương',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang tạo phiếu lương...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/payroll-cycles/${cycleId}/generate-payslips`, {
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
                    Notify.success(data.message, 'Tạo phiếu lương thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi tạo phiếu lương');
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
                
                Notify.error('Có lỗi xảy ra khi tạo phiếu lương. Vui lòng thử lại.', 'Lỗi hệ thống');
            })
            .finally(() => {
                // Restore button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
    });
}

function lockCycle(cycleId) {
    Notify.confirm({
        title: 'Khóa kỳ lương',
        message: 'Bạn có chắc chắn muốn khóa kỳ lương này?',
        details: 'Sau khi khóa sẽ không thể chỉnh sửa hoặc tạo phiếu lương mới.',
        type: 'warning',
        confirmText: 'Khóa kỳ lương',
        onConfirm: () => {
            // Show loading state
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang khóa...';
            button.disabled = true;

            // Show loading toast
            const loadingToast = Notify.toast({
                title: 'Đang khóa kỳ lương...',
                message: 'Vui lòng chờ trong giây lát',
                type: 'info',
                duration: 0
            });

            fetch(`/manager/payroll-cycles/${cycleId}/lock`, {
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
                    Notify.success(data.message, 'Khóa kỳ lương thành công!');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    Notify.error(data.message, 'Lỗi khóa kỳ lương');
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
                
                Notify.error('Có lỗi xảy ra khi khóa kỳ lương. Vui lòng thử lại.', 'Lỗi hệ thống');
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
