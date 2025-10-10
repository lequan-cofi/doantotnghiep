@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Kỳ Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kỳ Lương</h1>
            <p class="mb-0">Quản lý các kỳ lương và phiếu lương nhân viên</p>
        </div>
        <a href="{{ route('manager.payroll-cycles.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo kỳ lương mới
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.payroll-cycles.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Tháng hoặc ghi chú...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="">Tất cả</option>
                            <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Mở</option>
                            <option value="locked" {{ request('status') == 'locked' ? 'selected' : '' }}>Đã khóa</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Năm</label>
                        <select class="form-select" name="year">
                            <option value="">Tất cả</option>
                            @for($year = date('Y'); $year >= date('Y') - 5; $year--)
                                <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Cycles Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Kỳ Lương</h6>
        </div>
        <div class="card-body">
            @if($cycles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kỳ lương</th>
                                <th>Trạng thái</th>
                                <th>Số phiếu lương</th>
                                <th>Tổng lương</th>
                                <th>Ngày khóa</th>
                                <th>Ngày thanh toán</th>
                                <th>Ghi chú</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cycles as $cycle)
                            <tr>
                                <td>
                                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $cycle->period_month)->format('m/Y') }}</strong>
                                </td>
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
                                        
                                        // Handle unexpected status values
                                        $status = (string) $cycle->status;
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
                                    <span class="badge bg-primary">{{ $cycle->payslips_count ?? 0 }}</span>
                                </td>
                                <td>
                                    @php
                                        $totalGross = $cycle->payslips->sum('gross_amount') ?? 0;
                                    @endphp
                                    <strong>{{ number_format($totalGross, 0, ',', '.') }} VND</strong>
                                </td>
                                <td>
                                    @if($cycle->locked_at)
                                        {{ \Carbon\Carbon::parse($cycle->locked_at)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cycle->paid_at)
                                        {{ \Carbon\Carbon::parse($cycle->paid_at)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($cycle->note)
                                        <span class="text-truncate d-inline-block" style="max-width: 150px;" title="{{ $cycle->note }}">
                                            {{ $cycle->note }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.payroll-cycles.show', $cycle->id) }}" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($cycle->status === 'open')
                                            <a href="{{ route('manager.payroll-cycles.edit', $cycle->id) }}" 
                                               class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="generatePayslips({{ $cycle->id }})" title="Tạo phiếu lương">
                                                <i class="fas fa-calculator"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                    onclick="lockCycle({{ $cycle->id }})" title="Khóa kỳ lương">
                                                <i class="fas fa-lock"></i>
                                            </button>
                                        @endif
                                        @if($cycle->status === 'open' && $cycle->payslips_count == 0)
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteCycle({{ $cycle->id }})" title="Xóa">
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
                    {{ $cycles->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có kỳ lương nào</h5>
                    <p class="text-muted">Hãy tạo kỳ lương đầu tiên để bắt đầu quản lý lương.</p>
                    <a href="{{ route('manager.payroll-cycles.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo kỳ lương mới
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
                <p>Bạn có chắc chắn muốn xóa kỳ lương này?</p>
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
function deleteCycle(cycleId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/payroll-cycles/${cycleId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

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


