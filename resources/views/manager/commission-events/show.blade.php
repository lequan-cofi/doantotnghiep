@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Sự kiện Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết Sự kiện Hoa hồng</h1>
            <p class="mb-0">#{{ $commissionEvent->id }} - {{ $commissionEvent->policy->title }}</p>
        </div>
        <div>
            <button type="button" class="btn btn-secondary" onclick="backToIndex()">
                <i class="fas fa-arrow-left"></i> Quay lại
            </button>
            <button type="button" class="btn btn-warning" onclick="editEvent({{ $commissionEvent->id }})">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </button>
        </div>
    </div>

    <div class="row">
        <!-- Event Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Sự kiện</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID Sự kiện:</strong></td>
                                    <td><span class="badge bg-secondary">#{{ $commissionEvent->id }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Nhân viên:</strong></td>
                                    <td>
                                        @if($commissionEvent->agent)
                                            <div>
                                                <strong>{{ $commissionEvent->agent->full_name }}</strong>
                                                <br><small class="text-muted">{{ $commissionEvent->agent->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Chưa gán</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Người nhận hoa hồng:</strong></td>
                                    <td>
                                        @if($commissionEvent->user)
                                            <div>
                                                <strong>{{ $commissionEvent->user->full_name }}</strong>
                                                <br><small class="text-muted">{{ $commissionEvent->user->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Chưa gán</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Chính sách:</strong></td>
                                    <td>
                                        <div>
                                            <strong>{{ $commissionEvent->policy->title }}</strong>
                                            <br><small class="text-muted">{{ $commissionEvent->policy->code }}</small>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Sự kiện kích hoạt:</strong></td>
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
                                        <span class="badge bg-info">{{ $triggerLabels[$commissionEvent->trigger_event] ?? $commissionEvent->trigger_event }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Ngày xảy ra:</strong></td>
                                    <td>{{ $commissionEvent->occurred_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @php
                                            $statusColors = [
                                                'pending' => 'warning',
                                                'approved' => 'info',
                                                'paid' => 'success',
                                                'reversed' => 'danger',
                                                'cancelled' => 'secondary'
                                            ];
                                            $statusLabels = [
                                                'pending' => 'Chờ duyệt',
                                                'approved' => 'Đã duyệt',
                                                'paid' => 'Đã thanh toán',
                                                'reversed' => 'Đã hoàn',
                                                'cancelled' => 'Đã hủy'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$commissionEvent->status] }}">
                                            {{ $statusLabels[$commissionEvent->status] }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $commissionEvent->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật cuối:</strong></td>
                                    <td>{{ $commissionEvent->updated_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Records -->
            @if($commissionEvent->lease || $commissionEvent->unit)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Bản ghi liên quan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if($commissionEvent->lease)
                        <div class="col-md-6">
                            <h6>Hợp đồng thuê</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td><a href="{{ route('manager.leases.show', $commissionEvent->lease->id) }}">#{{ $commissionEvent->lease->id }}</a></td>
                                </tr>
                                <tr>
                                    <td><strong>Khách thuê:</strong></td>
                                    <td>{{ $commissionEvent->lease->tenant->full_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phòng:</strong></td>
                                    <td>{{ $commissionEvent->lease->unit->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày bắt đầu:</strong></td>
                                    <td>{{ $commissionEvent->lease->start_date ? $commissionEvent->lease->start_date->format('d/m/Y') : 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                        @if($commissionEvent->unit)
                        <div class="col-md-6">
                            <h6>Phòng/Đơn vị</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Tên:</strong></td>
                                    <td>{{ $commissionEvent->unit->name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Bất động sản:</strong></td>
                                    <td>{{ $commissionEvent->unit->property->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Giá thuê:</strong></td>
                                    <td>{{ number_format($commissionEvent->unit->rent_price, 0, ',', '.') }} VND/tháng</td>
                                </tr>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Financial Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin tài chính</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-12 mb-3">
                            <h4 class="text-primary">{{ number_format($commissionEvent->amount_base, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Số tiền gốc (VND)</p>
                        </div>
                        <div class="col-12 mb-3">
                            <h4 class="text-success">{{ number_format($commissionEvent->commission_total, 0, ',', '.') }}</h4>
                            <p class="mb-0 text-muted">Hoa hồng (VND)</p>
                        </div>
                        @if($commissionEvent->commission_total > 0 && $commissionEvent->amount_base > 0)
                        <div class="col-12">
                            <h4 class="text-info">{{ number_format(($commissionEvent->commission_total / $commissionEvent->amount_base) * 100, 2) }}%</h4>
                            <p class="mb-0 text-muted">Tỷ lệ hoa hồng</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>


            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    @if($commissionEvent->status == 'pending')
                        <button type="button" class="btn btn-success w-100 mb-2" onclick="approveEvent({{ $commissionEvent->id }})">
                            <i class="fas fa-check"></i> Duyệt sự kiện
                        </button>
                    @endif
                    @if($commissionEvent->status == 'approved')
                        <button type="button" class="btn btn-primary w-100 mb-2" onclick="markAsPaid({{ $commissionEvent->id }})">
                            <i class="fas fa-money-bill"></i> Đánh dấu đã thanh toán
                        </button>
                    @endif
                    <button type="button" class="btn btn-warning w-100 mb-2" onclick="editEvent({{ $commissionEvent->id }})">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </button>
                    <button type="button" class="btn btn-danger w-100" onclick="deleteEvent({{ $commissionEvent->id }})">
                        <i class="fas fa-trash"></i> Xóa sự kiện
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn xóa sự kiện hoa hồng này?</p>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show session messages
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
});

// Delete event function with enhanced notifications
function deleteEvent(eventId) {
    Notify.confirmDelete('Bạn có chắc chắn muốn xóa sự kiện hoa hồng này?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xóa...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch(`/manager/commission-events/${eventId}`, {
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
                Notify.success('Xóa sự kiện hoa hồng thành công!', 'Thành công!');
                // Redirect to index page after a short delay
                setTimeout(() => {
                    window.location.href = '{{ route('manager.commission-events.index') }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể xóa sự kiện hoa hồng. Vui lòng thử lại.', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            Notify.error('Đã xảy ra lỗi khi xóa sự kiện hoa hồng. Vui lòng kiểm tra kết nối và thử lại.', 'Lỗi hệ thống!');
        });
    });
}

// Approve event function with enhanced notifications
function approveEvent(eventId) {
    Notify.confirm('Bạn có chắc chắn muốn duyệt sự kiện hoa hồng này?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang duyệt...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch(`/manager/commission-events/${eventId}/approve`, {
            method: 'POST',
            headers: {
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
                Notify.success('Duyệt sự kiện hoa hồng thành công!', 'Thành công!');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể duyệt sự kiện hoa hồng.', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Approve error:', error);
            Notify.error('Đã xảy ra lỗi khi duyệt sự kiện hoa hồng. Vui lòng thử lại.', 'Lỗi hệ thống!');
        });
    });
}

// Mark as paid function with enhanced notifications
function markAsPaid(eventId) {
    Notify.confirm('Bạn có chắc chắn muốn đánh dấu sự kiện hoa hồng này là đã thanh toán?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang cập nhật...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        fetch(`/manager/commission-events/${eventId}/mark-as-paid`, {
            method: 'POST',
            headers: {
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
                Notify.success('Đánh dấu đã thanh toán thành công!', 'Thành công!');
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể đánh dấu đã thanh toán.', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Mark as paid error:', error);
            Notify.error('Đã xảy ra lỗi khi đánh dấu đã thanh toán. Vui lòng thử lại.', 'Lỗi hệ thống!');
        });
    });
}

// Edit event function
function editEvent(eventId) {
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang tải...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });

    // Navigate to edit page
    window.location.href = `/manager/commission-events/${eventId}/edit`;
}

// Back to index function
function backToIndex() {
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang chuyển hướng...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 1000
    });

    // Navigate to index page
    window.location.href = '{{ route('manager.commission-events.index') }}';
}
</script>
@endpush
