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
        <a href="{{ route('manager.commission-events.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo sự kiện mới
        </a>
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
                            <button type="submit" class="btn btn-primary">
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
                                <th>ID</th>
                                <th>Nhân viên</th>
                                <th>Chính sách</th>
                                <th>Sự kiện</th>
                                <th>Số tiền gốc</th>
                                <th>Hoa hồng</th>
                                <th>Trạng thái</th>
                                <th>Ngày xảy ra</th>
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
                                        <a href="{{ route('manager.commission-events.show', $event->id) }}" 
                                           class="btn btn-sm btn-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.commission-events.edit', $event->id) }}" 
                                           class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
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

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $events->links() }}
                </div>
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

@push('scripts')
<script>
function deleteEvent(eventId) {
    const deleteForm = document.getElementById('deleteForm');
    deleteForm.action = `/manager/commission-events/${eventId}`;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}

function approveEvent(eventId) {
    if (confirm('Bạn có chắc chắn muốn duyệt sự kiện hoa hồng này?')) {
        fetch(`/manager/commission-events/${eventId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi duyệt sự kiện hoa hồng');
        });
    }
}

function markAsPaid(eventId) {
    if (confirm('Bạn có chắc chắn muốn đánh dấu sự kiện hoa hồng này là đã thanh toán?')) {
        fetch(`/manager/commission-events/${eventId}/mark-as-paid`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Có lỗi xảy ra: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra khi đánh dấu sự kiện hoa hồng');
        });
    }
}
</script>
@endpush
