@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Sự kiện Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Sự kiện Hoa hồng</h1>
            <p class="mb-0">Theo dõi và quản lý các sự kiện hoa hồng</p>
        </div>
        <div class="btn-group" role="group">
            <a href="{{ route('manager.commission-events.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo sự kiện mới
            </a>
            <button type="button" class="btn btn-outline-success" onclick="exportEventsData()" title="Xuất Excel">
                <i class="fas fa-file-excel"></i>
            </button>
            <button type="button" class="btn btn-outline-secondary" onclick="clearFilters()" title="Xóa bộ lọc">
                <i class="fas fa-filter-circle-xmark"></i>
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.commission-events.index') }}">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Tìm kiếm</label>
                        <input type="text" class="form-control" name="search" 
                               value="{{ request('search') }}" placeholder="Tên nhân viên hoặc chính sách...">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Trạng thái</label>
                        <select class="form-select" name="status">
                            <option value="">Tất cả</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sự kiện kích hoạt</label>
                        <select class="form-select" name="trigger_event">
                            <option value="">Tất cả</option>
                            @foreach($triggerEvents as $key => $label)
                                <option value="{{ $key }}" {{ request('trigger_event') == $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Nhân viên</label>
                        <select class="form-select" name="agent_id">
                            <option value="">Tất cả</option>
                            @foreach($agents as $agent)
                                <option value="{{ $agent->id }}" {{ request('agent_id') == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Chính sách</label>
                        <select class="form-select" name="policy_id">
                            <option value="">Tất cả</option>
                            @foreach($policies as $policy)
                                <option value="{{ $policy->id }}" {{ request('policy_id') == $policy->id ? 'selected' : '' }}>
                                    {{ $policy->title }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Từ ngày</label>
                        <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Đến ngày</label>
                        <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" onclick="performSearch()">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('manager.commission-events.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Events Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Sự kiện Hoa hồng</h6>
        </div>
        <div class="card-body">
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'id', 'sort_order' => request('sort_by') == 'id' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        ID
                                        @if(request('sort_by') == 'id')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Nhân viên</th>
                                <th>Chính sách</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'trigger_event', 'sort_order' => request('sort_by') == 'trigger_event' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Sự kiện
                                        @if(request('sort_by') == 'trigger_event')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Số tiền gốc</th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'commission_total', 'sort_order' => request('sort_by') == 'commission_total' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Hoa hồng
                                        @if(request('sort_by') == 'commission_total')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'status', 'sort_order' => request('sort_by') == 'status' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Trạng thái
                                        @if(request('sort_by') == 'status')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'occurred_at', 'sort_order' => request('sort_by') == 'occurred_at' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Ngày xảy ra
                                        @if(request('sort_by') == 'occurred_at')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                            <tr>
                                <td>
                                    <span class="badge bg-secondary">#{{ $event->id }}</span>
                                </td>
                                <td>
                                    @if($event->agent)
                                        <div>
                                            <strong>{{ $event->agent->full_name }}</strong>
                                            <br><small class="text-muted">{{ $event->agent->email }}</small>
                                        </div>
                                    @else
                                        <span class="text-muted">Chưa gán</span>
                                    @endif
                                </td>
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
                                <td>
                                    <strong>{{ number_format($event->amount_base, 0, ',', '.') }} VND</strong>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($event->commission_total, 0, ',', '.') }} VND</strong>
                                </td>
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
                                    <span class="badge bg-{{ $statusColors[$event->status] }}">
                                        {{ $statusLabels[$event->status] }}
                                    </span>
                                </td>
                                <td>{{ $event->occurred_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-info" 
                                                onclick="viewEventDetails({{ $event->id }})" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" 
                                                onclick="editEvent({{ $event->id }})" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if($event->status == 'pending')
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="approveEvent({{ $event->id }})" title="Duyệt">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        @if($event->status == 'approved')
                                            <button type="button" class="btn btn-sm btn-primary" 
                                                    onclick="markAsPaid({{ $event->id }})" title="Đánh dấu đã thanh toán">
                                                <i class="fas fa-money-bill"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger" 
                                                onclick="deleteEvent({{ $event->id }})" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination temporarily removed --}}
            @else
                <div class="text-center py-4">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có sự kiện hoa hồng nào</h5>
                    <p class="text-muted">Các sự kiện hoa hồng sẽ xuất hiện khi có hoạt động liên quan.</p>
                </div>
            @endif
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

@push('styles')
<style>
/* Sorting Styles */
.table th a {
    color: inherit;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}

.table th a:hover {
    color: #0d6efd;
    text-decoration: none;
}

.table th a i {
    font-size: 0.8rem;
    opacity: 0.7;
}

.table th a:hover i {
    opacity: 1;
}
</style>
@endpush

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
                // Reload page after a short delay
                setTimeout(() => {
                    location.reload();
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

// View event details function
function viewEventDetails(eventId) {
    // Show loading notification
    const loadingToast = Notify.toast({
        title: 'Đang tải...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });

    // Navigate to event details page
    window.location.href = `/manager/commission-events/${eventId}`;
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

    // Navigate to event edit page
    window.location.href = `/manager/commission-events/${eventId}/edit`;
}

// Search function with loading state
function performSearch() {
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang tìm kiếm...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 2000
        });

        // Submit form
        searchForm.submit();
    }
}

// Clear filters function
function clearFilters() {
    Notify.confirm('Bạn có chắc chắn muốn xóa tất cả bộ lọc?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xóa bộ lọc...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 1000
        });

        // Navigate to clean URL
        window.location.href = '{{ route('manager.commission-events.index') }}';
    });
}

// Export events data function
function exportEventsData() {
    Notify.confirm('Bạn có muốn xuất danh sách sự kiện hoa hồng ra file Excel?', () => {
        // Show loading notification
        const loadingToast = Notify.toast({
            title: 'Đang xuất dữ liệu...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });

        // Get current search parameters
        const urlParams = new URLSearchParams(window.location.search);
        const exportUrl = `{{ route('manager.commission-events.index') }}/export?${urlParams.toString()}`;

        // Create temporary link for download
        const link = document.createElement('a');
        link.href = exportUrl;
        link.download = 'danh-sach-su-kien-hoa-hong.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Hide loading notification after a delay
        setTimeout(() => {
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            Notify.success('Xuất dữ liệu thành công!', 'Thành công!');
        }, 2000);
    });
}

// Form validation with notifications
function validateSearchForm() {
    const dateFrom = document.querySelector('input[name="date_from"]').value;
    const dateTo = document.querySelector('input[name="date_to"]').value;
    
    if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
        Notify.warning('Ngày bắt đầu không thể lớn hơn ngày kết thúc.', 'Cảnh báo!');
        return false;
    }
    
    return true;
}

// Add form validation to search form
document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.querySelector('form[method="GET"]');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            if (!validateSearchForm()) {
                e.preventDefault();
            }
        });
    }
});
</script>
@endpush
