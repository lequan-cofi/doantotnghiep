@extends('layouts.agent_dashboard')

@section('title', 'Quản lý hợp đồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-contract me-2"></i>Quản lý hợp đồng
                    </h1>
                    <p class="text-muted mb-0">Danh sách hợp đồng thuê phòng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-info">
                        <i class="fas fa-users me-1"></i>Quản lý Leads
                    </a>
                    <a href="{{ route('agent.leases.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo hợp đồng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('agent.leases.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $search }}" placeholder="Mã hợp đồng, tên khách thuê, email...">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả</option>
                                <option value="draft" {{ $selectedStatus == 'draft' ? 'selected' : '' }}>Nháp</option>
                                <option value="active" {{ $selectedStatus == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                <option value="terminated" {{ $selectedStatus == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                                <option value="expired" {{ $selectedStatus == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="property_id" class="form-label">Bất động sản</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">Tất cả</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ $selectedProperty == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_by" class="form-label">Sắp xếp theo</label>
                            <select class="form-select" id="sort_by" name="sort_by">
                                <option value="id" {{ request('sort_by') == 'id' ? 'selected' : '' }}>ID</option>
                                <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                                <option value="contract_no" {{ request('sort_by') == 'contract_no' ? 'selected' : '' }}>Mã hợp đồng</option>
                                <option value="start_date" {{ request('sort_by') == 'start_date' ? 'selected' : '' }}>Ngày bắt đầu</option>
                                <option value="end_date" {{ request('sort_by') == 'end_date' ? 'selected' : '' }}>Ngày kết thúc</option>
                                <option value="rent_amount" {{ request('sort_by') == 'rent_amount' ? 'selected' : '' }}>Tiền thuê</option>
                                <option value="status" {{ request('sort_by') == 'status' ? 'selected' : '' }}>Trạng thái</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="sort_order" class="form-label">Thứ tự</label>
                            <select class="form-select" id="sort_order" name="sort_order">
                                <option value="desc" {{ request('sort_order') == 'desc' ? 'selected' : '' }}>Giảm dần</option>
                                <option value="asc" {{ request('sort_order') == 'asc' ? 'selected' : '' }}>Tăng dần</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i>Tìm kiếm
                            </button>
                            <a href="{{ route('agent.leases.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Xóa bộ lọc
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Leases Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Danh sách hợp đồng ({{ $leases->count() }})
                    </h5>
                </div>
                <div class="card-body">
                    @if($leases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã hợp đồng</th>
                                        <th>Khách thuê</th>
                                        <th>Bất động sản</th>
                                        <th>Phòng</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Tiền thuê</th>
                                        <th>Chu kỳ thanh toán</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($leases as $lease)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $lease->contract_no ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $lease->tenant->full_name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $lease->tenant->phone ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $lease->unit->property->name ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $lease->unit->property->location->address ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $lease->unit->code ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                {{ $lease->start_date ? $lease->start_date->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $lease->end_date ? $lease->end_date->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <span class="fw-bold text-success">
                                                    {{ number_format($lease->rent_amount) }}đ
                                                </span>
                                            </td>
                                            <td>
                                                @if($lease->lease_payment_cycle)
                                                    @switch($lease->lease_payment_cycle)
                                                        @case('monthly')
                                                            <span class="badge bg-primary">Hàng tháng</span>
                                                            @break
                                                        @case('quarterly')
                                                            <span class="badge bg-info">Hàng quý</span>
                                                            @break
                                                        @case('yearly')
                                                            <span class="badge bg-success">Hàng năm</span>
                                                            @break
                                                        @case('custom')
                                                            <span class="badge bg-warning">
                                                                {{ $lease->lease_custom_months ? $lease->lease_custom_months . ' tháng' : 'Tùy chỉnh' }}
                                                            </span>
                                                            @break
                                                        @default
                                                            <span class="badge bg-secondary">{{ $lease->lease_payment_cycle }}</span>
                                                    @endswitch
                                                    @if($lease->lease_payment_day)
                                                        <br><small class="text-muted">Hạn: Ngày {{ $lease->lease_payment_day }}</small>
                                                    @endif
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($lease->status)
                                                    @case('draft')
                                                        <span class="badge bg-secondary">Nháp</span>
                                                        @break
                                                    @case('active')
                                                        <span class="badge bg-success">Hoạt động</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-danger">Đã chấm dứt</span>
                                                        @break
                                                    @case('expired')
                                                        <span class="badge bg-warning">Hết hạn</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $lease->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.leases.show', $lease->id) }}" 
                                                       class="btn btn-outline-primary btn-sm" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('agent.leases.edit', $lease->id) }}" 
                                                       class="btn btn-outline-warning btn-sm" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-lease-btn" 
                                                            data-lease-id="{{ $lease->id }}" 
                                                            data-lease-contract="{{ $lease->contract_no ?? 'Hợp đồng #' . $lease->id }}" 
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
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có hợp đồng nào</h5>
                            <p class="text-muted">Bắt đầu tạo hợp đồng đầu tiên của bạn</p>
                            <a href="{{ route('agent.leases.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tạo hợp đồng
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group .btn {
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete lease functionality
    document.querySelectorAll('.delete-lease-btn').forEach(button => {
        button.addEventListener('click', function() {
            const leaseId = this.dataset.leaseId;
            const leaseContract = this.dataset.leaseContract;
            
            // Show confirmation dialog
            if (typeof Notify !== 'undefined') {
                Notify.confirmDelete(`hợp đồng "${leaseContract}"`, () => {
                    // User confirmed deletion
                    deleteLease(leaseId);
                });
            } else {
                // Fallback to browser confirm
                if (confirm(`Bạn có chắc chắn muốn xóa hợp đồng "${leaseContract}"?`)) {
                    deleteLease(leaseId);
                }
            }
        });
    });

    // Delete lease function
    function deleteLease(leaseId) {
        fetch(`/agent/leases/${leaseId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Xóa thành công');
                } else {
                    alert(data.message);
                }
                // Remove the row from table
                const row = document.querySelector(`[data-lease-id="${leaseId}"]`).closest('tr');
                if (row) {
                    row.remove();
                }
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi xóa');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi xóa hợp đồng', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi xóa hợp đồng');
            }
        });
    }
});
</script>
@endpush
