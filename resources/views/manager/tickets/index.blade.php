@extends('layouts.manager_dashboard')

@section('title', 'Quản lý Ticket')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quản lý Ticket</h1>
            <p class="mb-0">Quản lý các ticket bảo trì và sự cố</p>
        </div>
        <a href="{{ route('manager.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Ticket Mới
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.tickets.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Mở</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Độ ưu tiên</label>
                    <select name="priority" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Cao</option>
                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Người phụ trách</label>
                    <select name="assigned_to" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Người tạo</label>
                    <select name="created_by" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('created_by') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Phòng</label>
                    <select name="unit_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->property->name }} - {{ $unit->code }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hợp đồng</label>
                    <select name="lease_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($leases as $lease)
                            <option value="{{ $lease->id }}" {{ request('lease_id') == $lease->id ? 'selected' : '' }}>
                                {{ $lease->contract_no ?: 'HD#' . $lease->id }} - {{ $lease->tenant->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" 
                                   value="{{ request('search') }}" 
                                   placeholder="Tìm theo tiêu đề hoặc mô tả...">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fas fa-search"></i> Lọc
                            </button>
                            <a href="{{ route('manager.tickets.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách Ticket ({{ $tickets->total() }} kết quả)</h6>
        </div>
        <div class="card-body">
            @if($tickets->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Tiêu đề</th>
                                <th>Trạng thái</th>
                                <th>Độ ưu tiên</th>
                                <th>Phòng/Hợp đồng</th>
                                <th>Người tạo</th>
                                <th>Người phụ trách</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <td>#{{ $ticket->id }}</td>
                                <td>
                                    <div class="fw-bold">{{ $ticket->title }}</div>
                                    @if($ticket->description)
                                        <small class="text-muted">{{ Str::limit($ticket->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'open' => 'success',
                                            'in_progress' => 'warning',
                                            'resolved' => 'info',
                                            'closed' => 'secondary',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusLabels = [
                                            'open' => 'Mở',
                                            'in_progress' => 'Đang xử lý',
                                            'resolved' => 'Đã giải quyết',
                                            'closed' => 'Đã đóng',
                                            'cancelled' => 'Đã hủy'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$ticket->status] }}">
                                        {{ $statusLabels[$ticket->status] }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $priorityColors = [
                                            'low' => 'secondary',
                                            'medium' => 'primary',
                                            'high' => 'warning',
                                            'urgent' => 'danger'
                                        ];
                                        $priorityLabels = [
                                            'low' => 'Thấp',
                                            'medium' => 'Trung bình',
                                            'high' => 'Cao',
                                            'urgent' => 'Khẩn cấp'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $priorityColors[$ticket->priority] }}">
                                        {{ $priorityLabels[$ticket->priority] }}
                                    </span>
                                </td>
                                <td>
                                    @if($ticket->unit)
                                        <div class="small">
                                            <strong>{{ $ticket->unit->property->name }}</strong><br>
                                            Phòng: {{ $ticket->unit->code }}
                                        </div>
                                    @endif
                                    @if($ticket->lease)
                                        <div class="small text-muted">
                                            HĐ: {{ $ticket->lease->contract_no ?: 'HD#' . $ticket->lease->id }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $ticket->createdBy->full_name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $ticket->assignedTo->full_name ?? 'Chưa giao' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.tickets.show', $ticket->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.tickets.edit', $ticket->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteTicket({{ $ticket->id }}, '{{ $ticket->title }}')" 
                                                title="Xóa">
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
                    {{ $tickets->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có ticket nào</h5>
                    <p class="text-muted">Chưa có ticket nào được tạo hoặc không tìm thấy kết quả phù hợp.</p>
                    <a href="{{ route('manager.tickets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo Ticket Đầu Tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Session Messages -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@endsection

@push('scripts')
<script>
function deleteTicket(id, name) {
    // Sử dụng notification system
    Notify.confirmDelete(`ticket "${name}"`, function() {
        // Hiển thị loading toast
        const loadingToast = Notify.toast({
            title: 'Đang xử lý...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0 // Không tự động đóng
        });
        
        fetch(`/manager/tickets/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            // Đóng loading toast
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                Notify.success(data.message, 'Xóa thành công!');
                
                // Reload trang sau 1.5 giây
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Hiển thị thông báo lỗi
                Notify.error(data.message, 'Không thể xóa ticket');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hiển thị thông báo lỗi
            Notify.error('Có lỗi xảy ra khi xóa ticket. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
}
</script>
@endpush
