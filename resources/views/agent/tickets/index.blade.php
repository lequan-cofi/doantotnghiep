@extends('layouts.agent_dashboard')

@section('title', 'Quản lý Ticket')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Quản lý Ticket</h1>
            <p class="mb-0">Quản lý các ticket bảo trì và sự cố</p>
        </div>
        <a href="{{ route('agent.tickets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo Ticket Mới
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.tickets.index') }}" class="row g-3">
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
                    <label class="form-label">Tòa nhà</label>
                    <select name="property_id" id="filter_property_id" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                {{ $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Phòng</label>
                    <select name="unit_id" id="filter_unit_id" class="form-select" {{ !request('property_id') ? 'disabled' : '' }}>
                        <option value="">Tất cả</option>
                        @if(request('property_id'))
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->code }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Hợp đồng</label>
                    <select name="lease_id" id="filter_lease_id" class="form-select" {{ !request('unit_id') ? 'disabled' : '' }}>
                        <option value="">Tất cả</option>
                        @if(request('unit_id'))
                            @foreach($leases as $lease)
                                <option value="{{ $lease->id }}" {{ request('lease_id') == $lease->id ? 'selected' : '' }}>
                                    {{ $lease->contract_no ?: 'HD#' . $lease->id }} - {{ $lease->tenant->full_name ?? 'Chưa có khách thuê' }}
                                </option>
                            @endforeach
                        @endif
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
                            <a href="{{ route('agent.tickets.index') }}" class="btn btn-outline-secondary">
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
                                        <a href="{{ route('agent.tickets.show', $ticket->id) }}" 
                                           class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('agent.tickets.edit', $ticket->id) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
                    <a href="{{ route('agent.tickets.create') }}" class="btn btn-primary">
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
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('filter_property_id');
    const unitSelect = document.getElementById('filter_unit_id');
    const leaseSelect = document.getElementById('filter_lease_id');
    
    // Cascading dropdowns for filters
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        
        // Reset unit and lease selects
        unitSelect.innerHTML = '<option value="">Tất cả</option>';
        leaseSelect.innerHTML = '<option value="">Tất cả</option>';
        leaseSelect.disabled = true;
        
        if (propertyId) {
            // Fetch units for selected property
            fetch(`/agent/api/tickets/properties/${propertyId}/units`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(units => {
                units.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.code;
                    unitSelect.appendChild(option);
                });
                unitSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching units:', error);
            });
        } else {
            unitSelect.disabled = true;
        }
    });
    
    unitSelect.addEventListener('change', function() {
        const unitId = this.value;
        
        // Reset lease select
        leaseSelect.innerHTML = '<option value="">Tất cả</option>';
        
        if (unitId) {
            // Fetch leases for selected unit
            fetch(`/agent/api/tickets/units/${unitId}/leases`, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(leases => {
                leases.forEach(lease => {
                    const option = document.createElement('option');
                    option.value = lease.id;
                    option.textContent = `${lease.contract_no || 'HD#' + lease.id} - ${lease.tenant ? lease.tenant.full_name : 'Chưa có khách thuê'}`;
                    leaseSelect.appendChild(option);
                });
                leaseSelect.disabled = false;
            })
            .catch(error => {
                console.error('Error fetching leases:', error);
            });
        } else {
            leaseSelect.disabled = true;
        }
    });
});
</script>
@endpush
