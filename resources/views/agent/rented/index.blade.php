@extends('layouts.agent_dashboard')

@section('title', 'Phòng đã cho thuê')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-home me-2"></i>Phòng đã cho thuê
                    </h1>
                    <p class="text-muted mb-0">Quản lý các phòng đang được thuê</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.units.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-door-open me-1"></i>Quản lý phòng
                    </a>
                    <a href="{{ route('agent.leases.index') }}" class="btn btn-outline-success">
                        <i class="fas fa-file-contract me-1"></i>Hợp đồng
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Notify.success('{{ session('success') }}');
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Notify.error('{{ session('error') }}');
            });
        </script>
    @endif

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $rentedUnits->total() }}</h4>
                            <p class="mb-0">Tổng phòng cho thuê</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-home fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $rentedUnits->filter(function($unit) { return $unit->current_lease && $unit->current_lease->status == 'active'; })->count() }}</h4>
                            <p class="mb-0">Hợp đồng đang hoạt động</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ $rentedUnits->filter(function($unit) { return $unit->current_lease && $unit->current_lease->status == 'expired'; })->count() }}</h4>
                            <p class="mb-0">Hợp đồng hết hạn</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4 class="mb-0">{{ number_format($rentedUnits->sum(function($unit) { return $unit->current_lease ? $unit->current_lease->rent_amount : 0; })) }}đ</h4>
                            <p class="mb-0">Tổng thu nhập/tháng</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-dollar-sign fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('agent.rented.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="property_id" class="form-label">Bất động sản</label>
                    <select class="form-select" id="property_id" name="property_id">
                        <option value="">Tất cả bất động sản</option>
                        @foreach($properties as $property)
                            <option value="{{ $property->id }}" {{ $request->property_id == $property->id ? 'selected' : '' }}>
                                {{ $property->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lease_status" class="form-label">Trạng thái hợp đồng</label>
                    <select class="form-select" id="lease_status" name="lease_status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="active" {{ $request->lease_status == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="expired" {{ $request->lease_status == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                        <option value="terminated" {{ $request->lease_status == 'terminated' ? 'selected' : '' }}>Đã chấm dứt</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ $request->search }}" placeholder="Mã phòng, tên BĐS, tên người thuê...">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <a href="{{ route('agent.rented.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Rented Units Table -->
    @if($rentedUnits->count() > 0)
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'code', 'sort_order' => request('sort_by') == 'code' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Mã phòng
                                        @if(request('sort_by') == 'code')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">Bất động sản</th>
                                <th class="border-0">Người thuê</th>
                                <th class="border-0">
                                    <a href="{{ request()->fullUrlWithQuery(['sort_by' => 'lease_start_date', 'sort_order' => request('sort_by') == 'lease_start_date' && request('sort_order') == 'asc' ? 'desc' : 'asc']) }}" 
                                       class="text-decoration-none text-dark">
                                        Ngày bắt đầu
                                        @if(request('sort_by') == 'lease_start_date')
                                            <i class="fas fa-sort-{{ request('sort_order') == 'asc' ? 'up' : 'down' }} ms-1"></i>
                                        @else
                                            <i class="fas fa-sort ms-1 text-muted"></i>
                                        @endif
                                    </a>
                                </th>
                                <th class="border-0">Ngày kết thúc</th>
                                <th class="border-0">Tiền thuê/tháng</th>
                                <th class="border-0">Trạng thái HĐ</th>
                                <th class="border-0 text-center">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rentedUnits as $unit)
                                <tr>
                                    <td class="align-middle">
                                        <div class="d-flex align-items-center">
                                            @if($unit->images && count($unit->images) > 0)
                                                <img src="{{ asset('storage/' . $unit->images[0]) }}" 
                                                     class="rounded me-2" 
                                                     alt="{{ $unit->code }}"
                                                     style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" 
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-home text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-primary">{{ $unit->code }}</div>
                                                <small class="text-muted">{{ $unit->area_m2 ? $unit->area_m2 . ' m²' : 'N/A' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <div class="fw-bold">{{ $unit->property->name }}</div>
                                            <small class="text-muted">
                                                <i class="fas fa-map-marker-alt me-1"></i>
                                                {{ $unit->property->location->address ?? 'N/A' }}
                                            </small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->current_lease && $unit->current_lease->tenant)
                                            <div>
                                                <div class="fw-bold">{{ $unit->current_lease->tenant->full_name }}</div>
                                                <small class="text-muted">{{ $unit->current_lease->tenant->phone }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->current_lease)
                                            <span class="fw-bold">{{ $unit->current_lease->start_date ? $unit->current_lease->start_date->format('d/m/Y') : 'N/A' }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->current_lease)
                                            <span class="fw-bold">{{ $unit->current_lease->end_date ? $unit->current_lease->end_date->format('d/m/Y') : 'N/A' }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->current_lease)
                                            <div class="fw-bold text-success">{{ number_format($unit->current_lease->rent_amount) }}đ</div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        @if($unit->current_lease)
                                            @switch($unit->current_lease->status)
                                                @case('active')
                                                    <span class="badge bg-success">Đang hoạt động</span>
                                                    @break
                                                @case('expired')
                                                    <span class="badge bg-warning">Hết hạn</span>
                                                    @break
                                                @case('terminated')
                                                    <span class="badge bg-danger">Đã chấm dứt</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $unit->current_lease->status }}</span>
                                            @endswitch
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.rented.show', $unit->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($unit->current_lease)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-info" 
                                                        title="Chi tiết hợp đồng"
                                                        onclick="showLeaseDetails({{ $unit->current_lease->id }})">
                                                    <i class="fas fa-file-contract"></i>
                                                </button>
                                                @if($unit->current_lease->tenant)
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-success" 
                                                            title="Profile người thuê"
                                                            onclick="showTenantProfile({{ $unit->current_lease->tenant->id }})">
                                                        <i class="fas fa-user"></i>
                                                    </button>
                                                @endif
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

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $rentedUnits->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-home fa-4x text-muted mb-4"></i>
                <h4 class="text-muted">Chưa có phòng nào được cho thuê</h4>
                <p class="text-muted mb-4">
                    Hiện tại chưa có phòng nào đang được cho thuê trong các bất động sản được gán.
                </p>
                <a href="{{ route('agent.units.index') }}" class="btn btn-primary">
                    <i class="fas fa-door-open me-1"></i>Quản lý phòng
                </a>
            </div>
        </div>
    @endif
</div>

<!-- Lease Details Modal -->
<div class="modal fade" id="leaseDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="leaseDetailsContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tenant Profile Modal -->
<div class="modal fade" id="tenantProfileModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Profile người thuê</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="tenantProfileContent">
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Đang tải...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function showLeaseDetails(leaseId) {
    const modal = new bootstrap.Modal(document.getElementById('leaseDetailsModal'));
    const content = document.getElementById('leaseDetailsContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch lease details
    fetch(`{{ url('agent/rented/lease') }}/${leaseId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateLeaseDetailsHTML(data.lease);
            } else {
                content.innerHTML = '<div class="alert alert-danger">Không thể tải thông tin hợp đồng</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>';
        });
}

function showTenantProfile(tenantId) {
    const modal = new bootstrap.Modal(document.getElementById('tenantProfileModal'));
    const content = document.getElementById('tenantProfileContent');
    
    // Show loading
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Đang tải...</span>
            </div>
        </div>
    `;
    
    modal.show();
    
    // Fetch tenant profile
    fetch(`{{ url('agent/rented/tenant') }}/${tenantId}/profile`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                content.innerHTML = generateTenantProfileHTML(data.tenant);
            } else {
                content.innerHTML = '<div class="alert alert-danger">Không thể tải thông tin người thuê</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            content.innerHTML = '<div class="alert alert-danger">Có lỗi xảy ra khi tải dữ liệu</div>';
        });
}

function generateLeaseDetailsHTML(lease) {
    return `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-muted">Thông tin hợp đồng</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Mã hợp đồng:</strong></td>
                        <td>${lease.contract_number || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày bắt đầu:</strong></td>
                        <td>${lease.start_date ? new Date(lease.start_date).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày kết thúc:</strong></td>
                        <td>${lease.end_date ? new Date(lease.end_date).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Tiền thuê/tháng:</strong></td>
                        <td class="text-success fw-bold">${new Intl.NumberFormat('vi-VN').format(lease.rent_amount)}đ</td>
                    </tr>
                    <tr>
                        <td><strong>Tiền cọc:</strong></td>
                        <td>${lease.deposit_amount ? new Intl.NumberFormat('vi-VN').format(lease.deposit_amount) + 'đ' : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Trạng thái:</strong></td>
                        <td><span class="badge bg-${getStatusColor(lease.status)}">${getStatusText(lease.status)}</span></td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted">Thông tin phòng</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Mã phòng:</strong></td>
                        <td>${lease.unit.code}</td>
                    </tr>
                    <tr>
                        <td><strong>Bất động sản:</strong></td>
                        <td>${lease.unit.property.name}</td>
                    </tr>
                    <tr>
                        <td><strong>Địa chỉ:</strong></td>
                        <td>${lease.unit.property.location?.address || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Diện tích:</strong></td>
                        <td>${lease.unit.area_m2 ? lease.unit.area_m2 + ' m²' : 'N/A'}</td>
                    </tr>
                </table>
            </div>
        </div>
        ${lease.residents && lease.residents.length > 0 ? `
        <div class="mt-3">
            <h6 class="text-muted">Người ở cùng</h6>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Họ và tên</th>
                            <th>Số điện thoại</th>
                            <th>Mối quan hệ</th>
                            <th>CMND/CCCD</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${lease.residents.map(resident => `
                            <tr>
                                <td>${resident.full_name}</td>
                                <td>${resident.phone || 'N/A'}</td>
                                <td>${resident.relationship || 'N/A'}</td>
                                <td>${resident.id_number || 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        ` : ''}
        ${lease.note ? `<div class="mt-3"><h6 class="text-muted">Ghi chú:</h6><p>${lease.note}</p></div>` : ''}
    `;
}

function generateTenantProfileHTML(tenant) {
    return `
        <div class="row">
            <div class="col-md-4 text-center">
                <div class="mb-3">
                    <i class="fas fa-user-circle fa-5x text-muted"></i>
                </div>
                <h5>${tenant.full_name}</h5>
                <p class="text-muted">${tenant.email}</p>
            </div>
            <div class="col-md-8">
                <h6 class="text-muted">Thông tin liên hệ</h6>
                <table class="table table-sm">
                    <tr>
                        <td><strong>Số điện thoại:</strong></td>
                        <td>${tenant.phone || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>${tenant.email}</td>
                    </tr>
                    <tr>
                        <td><strong>Địa chỉ:</strong></td>
                        <td>${tenant.user_profile?.address || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>CMND/CCCD:</strong></td>
                        <td>${tenant.user_profile?.id_number || 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày sinh:</strong></td>
                        <td>${tenant.user_profile?.date_of_birth ? new Date(tenant.user_profile.date_of_birth).toLocaleDateString('vi-VN') : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Giới tính:</strong></td>
                        <td>${tenant.user_profile?.gender === 'male' ? 'Nam' : tenant.user_profile?.gender === 'female' ? 'Nữ' : 'N/A'}</td>
                    </tr>
                    <tr>
                        <td><strong>Ngày tạo tài khoản:</strong></td>
                        <td>${new Date(tenant.created_at).toLocaleDateString('vi-VN')}</td>
                    </tr>
                </table>
                
                <h6 class="text-muted mt-3">Lịch sử thuê</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Phòng</th>
                                <th>BĐS</th>
                                <th>Ngày bắt đầu</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tenant.leases.map(lease => `
                                <tr>
                                    <td>${lease.unit.code}</td>
                                    <td>${lease.unit.property.name}</td>
                                    <td>${lease.start_date ? new Date(lease.start_date).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                    <td>${lease.end_date ? new Date(lease.end_date).toLocaleDateString('vi-VN') : 'N/A'}</td>
                                    <td><span class="badge bg-${getStatusColor(lease.status)}">${getStatusText(lease.status)}</span></td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;
}

function getStatusColor(status) {
    switch(status) {
        case 'active': return 'success';
        case 'expired': return 'warning';
        case 'terminated': return 'danger';
        default: return 'secondary';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'active': return 'Đang hoạt động';
        case 'expired': return 'Hết hạn';
        case 'terminated': return 'Đã chấm dứt';
        default: return status;
    }
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/units.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/agent/rented.css') }}">
@endpush
