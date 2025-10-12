@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết đặt cọc')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-hand-holding-usd me-2"></i>Chi tiết đặt cọc
                    </h1>
                    <p class="text-muted mb-0">Đặt cọc #{{ $deposit->reference_number }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.booking-deposits.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại danh sách
                    </a>
                    @if($deposit->payment_status === 'pending')
                        <a href="{{ route('agent.booking-deposits.edit', $deposit->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <!-- Deposit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin đặt cọc
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Số tham chiếu</label>
                                <p class="mb-0 fw-bold">{{ $deposit->reference_number }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Trạng thái thanh toán</label>
                                <p class="mb-0">
                                    <span class="badge {{ $deposit->getStatusBadgeClass() }}">
                                        {{ $deposit->getStatusText() }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Loại đặt cọc</label>
                                <p class="mb-0">{{ $deposit->getTypeText() }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Số tiền</label>
                                <p class="mb-0 fw-bold text-primary">{{ number_format($deposit->amount, 0, ',', '.') }} VNĐ</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Giữ chỗ đến</label>
                                <p class="mb-0">{{ $deposit->hold_until->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày tạo</label>
                                <p class="mb-0">{{ $deposit->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @if($deposit->paid_at)
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày thanh toán</label>
                                <p class="mb-0">{{ $deposit->paid_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        @endif
                        @if($deposit->notes)
                        <div class="col-12">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ghi chú</label>
                                <p class="mb-0">{{ $deposit->notes }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Unit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-home me-2"></i>Thông tin phòng/căn hộ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Bất động sản</label>
                                <p class="mb-0 fw-bold">{{ $deposit->unit->property->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Phòng/Căn hộ</label>
                                <p class="mb-0 fw-bold">{{ $deposit->unit->code }} - {{ $deposit->unit->unit_type }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Giá thuê cơ bản</label>
                                <p class="mb-0">{{ number_format($deposit->unit->base_rent, 0, ',', '.') }} VNĐ/tháng</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Trạng thái</label>
                                <p class="mb-0">
                                    <span class="badge badge-{{ $deposit->unit->status === 'available' ? 'success' : 'warning' }}">
                                        {{ ucfirst($deposit->unit->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Information -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Thông tin khách hàng
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $customerInfo = $deposit->getTenantInfo();
                    @endphp
                    @if($customerInfo)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Tên khách hàng</label>
                                    <p class="mb-0 fw-bold">{{ $customerInfo['name'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Số điện thoại</label>
                                    <p class="mb-0">{{ $customerInfo['phone'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Email</label>
                                    <p class="mb-0">{{ $customerInfo['email'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Loại</label>
                                    <p class="mb-0">
                                        <span class="badge badge-{{ $customerInfo['type'] === 'user' ? 'primary' : 'info' }}">
                                            {{ $customerInfo['type'] === 'user' ? 'Người dùng' : 'Lead' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted">
                            <i class="fas fa-user-slash fa-2x mb-2"></i>
                            <p>Không có thông tin khách hàng</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Thao tác
                    </h5>
                </div>
                <div class="card-body">
                    @if($deposit->payment_status === 'pending')
                        <div class="d-grid gap-2">
                            <form action="{{ route('agent.booking-deposits.mark-as-paid', $deposit->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn btn-success w-100 btn-mark-paid">
                                    <i class="fas fa-check me-1"></i>Đánh dấu đã thanh toán
                                </button>
                            </form>
                            
                            <form action="{{ route('agent.booking-deposits.cancel', $deposit->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="button" class="btn btn-warning w-100 btn-cancel-deposit">
                                    <i class="fas fa-times me-1"></i>Hủy đặt cọc
                                </button>
                            </form>
                            
                            <a href="{{ route('agent.booking-deposits.edit', $deposit->id) }}" class="btn btn-outline-primary w-100">
                                <i class="fas fa-edit me-1"></i>Chỉnh sửa
                            </a>
                        </div>
                    @else
                        <div class="d-grid gap-2">
                            <div class="text-center text-muted mb-3">
                                <i class="fas fa-lock fa-2x mb-2"></i>
                                <p>Đặt cọc đã được xử lý</p>
                            </div>
                            
                            <!-- Soft Delete Option -->
                            <form action="{{ route('agent.booking-deposits.destroy', $deposit->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger w-100 btn-soft-delete">
                                    <i class="fas fa-trash me-1"></i>Xóa đặt cọc
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Agent Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-tie me-2"></i>Thông tin agent
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Agent xử lý</label>
                        <p class="mb-0 fw-bold">{{ $deposit->agent->full_name ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Email</label>
                        <p class="mb-0">{{ $deposit->agent->email ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Số điện thoại</label>
                        <p class="mb-0">{{ $deposit->agent->phone ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Organization Information -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Thông tin tổ chức
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Tổ chức</label>
                        <p class="mb-0 fw-bold">{{ $deposit->organization->name ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Mã tổ chức</label>
                        <p class="mb-0">{{ $deposit->organization->code ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show notifications from session
    @if(session('success'))
        Notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        Notify.error('{{ session('error') }}');
    @endif

    @if(session('warning'))
        Notify.warning('{{ session('warning') }}');
    @endif

    @if(session('info'))
        Notify.info('{{ session('info') }}');
    @endif

    // Mark as paid confirmation
    $(document).on('click', '.btn-mark-paid', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const referenceNumber = '{{ $deposit->reference_number }}';
        
        Notify.confirm({
            title: 'Đánh dấu đã thanh toán',
            message: `Bạn có chắc chắn muốn đánh dấu đặt cọc ${referenceNumber} là đã thanh toán?`,
            details: 'Đặt cọc sẽ chuyển sang trạng thái "Đã thanh toán" và không thể chỉnh sửa.',
            type: 'success',
            confirmText: 'Đánh dấu',
            onConfirm: function() {
                form.submit();
            }
        });
    });

    // Cancel deposit confirmation
    $(document).on('click', '.btn-cancel-deposit', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const referenceNumber = '{{ $deposit->reference_number }}';
        
        Notify.confirm({
            title: 'Hủy đặt cọc',
            message: `Bạn có chắc chắn muốn hủy đặt cọc ${referenceNumber}?`,
            details: 'Đặt cọc sẽ chuyển sang trạng thái "Đã hủy" và không thể khôi phục.',
            type: 'warning',
            confirmText: 'Hủy đặt cọc',
            onConfirm: function() {
                form.submit();
            }
        });
    });

    // Soft delete confirmation
    $(document).on('click', '.btn-soft-delete', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const referenceNumber = '{{ $deposit->reference_number }}';
        
        Notify.confirmDelete(`đặt cọc ${referenceNumber}`, function() {
            form.submit();
        });
    });

    // Show deposit type info
    @if($deposit->deposit_type === 'booking')
        Notify.info('Đây là đặt cọc giữ chỗ phòng/căn hộ', 'Loại đặt cọc');
    @elseif($deposit->deposit_type === 'security')
        Notify.info('Đây là cọc an toàn', 'Loại đặt cọc');
    @elseif($deposit->deposit_type === 'advance')
        Notify.info('Đây là tiền trả trước', 'Loại đặt cọc');
    @endif

    // Status change notifications
    const status = '{{ $deposit->payment_status }}';
    const statusMessages = {
        'pending': 'Đặt cọc đang chờ thanh toán',
        'paid': 'Đặt cọc đã được thanh toán',
        'refunded': 'Đặt cọc đã được hoàn tiền',
        'expired': 'Đặt cọc đã hết hạn',
        'cancelled': 'Đặt cọc đã bị hủy'
    };

    if (statusMessages[status]) {
        setTimeout(() => {
            Notify.info(statusMessages[status], 'Trạng thái đặt cọc');
        }, 1000);
    }

    // Auto-refresh page every 30 seconds if deposit is pending
    @if($deposit->payment_status === 'pending')
    setInterval(function() {
        // Check if deposit is still pending
        fetch('{{ route("agent.booking-deposits.show", $deposit->id) }}')
            .then(response => response.text())
            .then(html => {
                // Simple check if status changed
                if (!html.includes('badge-warning') || html.includes('badge-success')) {
                    Notify.info('Trạng thái đặt cọc đã thay đổi, đang tải lại trang...', 'Cập nhật');
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                }
            })
            .catch(error => {
                console.log('Auto-refresh check failed:', error);
            });
    }, 30000);
    @endif
});
</script>
@endpush
