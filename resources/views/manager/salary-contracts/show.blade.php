@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-eye text-primary"></i>
                Chi tiết hợp đồng lương #{{ $salaryContract->id }}
            </h1>
            <p class="text-muted mb-0">Thông tin chi tiết hợp đồng lương</p>
        </div>
        <div>
            <a href="{{ route('manager.salary-contracts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            @if($salaryContract->status !== 'terminated')
                <a href="{{ route('manager.salary-contracts.edit', $salaryContract->id) }}" class="btn btn-warning">
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
                                    <td>{{ $salaryContract->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nhân viên:</strong></td>
                                    <td>
                                        <div>
                                            <strong>{{ $salaryContract->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $salaryContract->user->email }}</small>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Lương cơ bản:</strong></td>
                                    <td>
                                        <strong class="text-primary">
                                            {{ number_format($salaryContract->base_salary) }} {{ $salaryContract->currency }}
                                        </strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Chu kỳ trả:</strong></td>
                                    <td>
                                        @switch($salaryContract->pay_cycle)
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
                                </tr>
                                <tr>
                                    <td><strong>Ngày trả lương:</strong></td>
                                    <td>Ngày {{ $salaryContract->pay_day }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @switch($salaryContract->status)
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
                                </tr>
                                <tr>
                                    <td><strong>Ngày hiệu lực:</strong></td>
                                    <td>{{ $salaryContract->effective_from->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày hết hạn:</strong></td>
                                    <td>{{ $salaryContract->effective_to ? $salaryContract->effective_to->format('d/m/Y') : 'Không giới hạn' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $salaryContract->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật cuối:</strong></td>
                                    <td>{{ $salaryContract->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allowances -->
            @if($salaryContract->allowances_json && count($salaryContract->allowances_json) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Phụ cấp</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tên phụ cấp</th>
                                        <th>Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryContract->allowances_json as $name => $amount)
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>
                                                <strong class="text-success">
                                                    {{ number_format($amount) }} {{ $salaryContract->currency }}
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Tổng phụ cấp:</th>
                                        <th>
                                            <strong class="text-primary">
                                                {{ number_format(array_sum($salaryContract->allowances_json)) }} {{ $salaryContract->currency }}
                                            </strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPI Targets -->
            @if($salaryContract->kpi_target_json && count($salaryContract->kpi_target_json) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Mục tiêu KPI</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Tên KPI</th>
                                        <th>Mục tiêu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryContract->kpi_target_json as $name => $target)
                                        <tr>
                                            <td>{{ $name }}</td>
                                            <td>
                                                <strong class="text-info">
                                                    {{ number_format($target) }}
                                                    @if(strpos($name, 'tỷ lệ') !== false || strpos($name, 'phần trăm') !== false)
                                                        %
                                                    @else
                                                        {{ $salaryContract->currency }}
                                                    @endif
                                                </strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Salary Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Lương cơ bản:</span>
                                <strong>{{ number_format($salaryContract->base_salary) }} {{ $salaryContract->currency }}</strong>
                            </div>
                            @if($salaryContract->allowances_json && count($salaryContract->allowances_json) > 0)
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phụ cấp:</span>
                                    <strong class="text-success">{{ number_format(array_sum($salaryContract->allowances_json)) }} {{ $salaryContract->currency }}</strong>
                                </div>
                            @endif
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Tổng lương:</strong></span>
                                <strong class="text-primary">
                                    {{ number_format($salaryContract->base_salary + ($salaryContract->allowances_json ? array_sum($salaryContract->allowances_json) : 0)) }} {{ $salaryContract->currency }}
                                </strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Thông tin bổ sung</h6>
                                <ul class="mb-0">
                                    <li>Chu kỳ trả: {{ $salaryContract->pay_cycle === 'monthly' ? 'Hàng tháng' : ($salaryContract->pay_cycle === 'weekly' ? 'Hàng tuần' : 'Hàng ngày') }}</li>
                                    <li>Ngày trả: {{ $salaryContract->pay_day }}</li>
                                    <li>Trạng thái: 
                                        @switch($salaryContract->status)
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
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    @if($salaryContract->status === 'active')
                        <button type="button" class="btn btn-secondary w-100 mb-2" onclick="terminateContract({{ $salaryContract->id }})">
                            <i class="fas fa-stop"></i> Chấm dứt hợp đồng
                        </button>
                    @elseif($salaryContract->status === 'inactive')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="activateContract({{ $salaryContract->id }})">
                            <i class="fas fa-play"></i> Kích hoạt hợp đồng
                        </button>
                        <button type="button" class="btn btn-danger w-100" onclick="deleteContract({{ $salaryContract->id }})">
                            <i class="fas fa-trash"></i> Xóa hợp đồng
                        </button>
                    @endif
                </div>
            </div>

            <!-- Contract Status -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng thái hợp đồng</h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        @switch($salaryContract->status)
                            @case('active')
                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                <h5 class="text-success">Đang hoạt động</h5>
                                <p class="text-muted">Hợp đồng đang có hiệu lực và được sử dụng để tính lương.</p>
                                @break
                            @case('inactive')
                                <i class="fas fa-pause-circle fa-3x text-warning mb-3"></i>
                                <h5 class="text-warning">Tạm dừng</h5>
                                <p class="text-muted">Hợp đồng đã bị tạm dừng, có thể kích hoạt lại.</p>
                                @break
                            @case('terminated')
                                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                                <h5 class="text-danger">Đã chấm dứt</h5>
                                <p class="text-muted">Hợp đồng đã được chấm dứt và không thể kích hoạt lại.</p>
                                @break
                        @endswitch
                    </div>
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
