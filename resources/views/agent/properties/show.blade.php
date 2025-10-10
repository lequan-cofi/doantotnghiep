@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết bất động sản')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>{{ $property->name }}</h1>
                <p>
                    @if($property->location2025)
                        <i class="fas fa-map-marker-alt text-primary"></i> 
                        <strong>Địa chỉ mới:</strong> {{ $property->location2025->street }}, {{ $property->location2025->ward }}, {{ $property->location2025->city }}
                    @endif
                    @if($property->location)
                        <br><i class="fas fa-map-marker-alt text-secondary"></i> 
                        <strong>Địa chỉ cũ:</strong> 
                        @if($property->location->street)
                            {{ $property->location->street }}, 
                        @endif
                        @if($property->location->ward)
                            {{ $property->location->ward }}, 
                        @endif
                        @if($property->location->district)
                            {{ $property->location->district }}, 
                        @endif
                        {{ $property->location->city }}
                    @endif
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.properties.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Property Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tên bất động sản</label>
                                <p class="mb-0">{{ $property->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Loại bất động sản</label>
                                <p class="mb-0">{{ $property->propertyType->name ?? 'Chưa xác định' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $property->status ? 'success' : 'secondary' }}">
                                        {{ $property->status ? 'Hoạt động' : 'Tạm ngưng' }}
                                    </span>
                                </p>
                            </div>
                            @if($property->total_floors)
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số tầng</label>
                                <p class="mb-0">{{ $property->total_floors }} tầng</p>
                            </div>
                            @endif
                            @if($property->description)
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Mô tả</label>
                                <p class="mb-0">{{ $property->description }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 text-primary mb-1">{{ $stats['total_units'] }}</div>
                                    <small class="text-muted">Tổng phòng</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 text-success mb-1">{{ $stats['available_units'] }}</div>
                                    <small class="text-muted">Phòng trống</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 text-warning mb-1">{{ $stats['occupied_units'] }}</div>
                                    <small class="text-muted">Phòng đã thuê</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="h4 text-info mb-1">{{ $stats['active_leases'] }}</div>
                                    <small class="text-muted">Hợp đồng hoạt động</small>
                                </div>
                            </div>
                        </div>
                        @if($stats['monthly_revenue'] > 0)
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                    <div class="h5 text-success mb-1">{{ number_format($stats['monthly_revenue'], 0, ',', '.') }} VNĐ</div>
                                    <small class="text-muted">Tổng doanh thu hàng tháng</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Units List -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-door-open"></i> Danh sách phòng</h5>
                    </div>
                    <div class="card-body">
                        @if($units->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Mã phòng</th>
                                            <th>Tầng</th>
                                            <th>Diện tích</th>
                                            <th>Trạng thái</th>
                                            <th>Khách thuê</th>
                                            <th>Giá thuê</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($units as $unit)
                                        <tr>
                                            <td>
                                                <span class="fw-bold">{{ $unit->code ?? 'Phòng ' . $unit->id }}</span>
                                            </td>
                                            <td>
                                                @if($unit->floor)
                                                    <span class="badge bg-secondary">Tầng {{ $unit->floor }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($unit->area)
                                                    {{ $unit->area }} m²
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($unit->is_available)
                                                    <span class="badge bg-success">Trống</span>
                                                @else
                                                    <span class="badge bg-warning">Đã thuê</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($unit->current_lease && $unit->current_lease->tenant)
                                                    <div>
                                                        <div class="fw-bold">{{ $unit->current_lease->tenant->full_name }}</div>
                                                        <small class="text-muted">{{ $unit->current_lease->tenant->email }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($unit->current_lease)
                                                    <span class="fw-bold text-success">
                                                        {{ number_format($unit->current_lease->rent_amount, 0, ',', '.') }} VNĐ
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-door-open fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Chưa có phòng nào trong bất động sản này</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Stats -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-tachometer-alt"></i> Tổng quan</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Tỷ lệ lấp đầy:</span>
                                <span class="fw-bold">
                                    @if($stats['total_units'] > 0)
                                        {{ round(($stats['occupied_units'] / $stats['total_units']) * 100, 1) }}%
                                    @else
                                        0%
                                    @endif
                                </span>
                            </div>
                            <div class="progress mt-1" style="height: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $stats['total_units'] > 0 ? ($stats['occupied_units'] / $stats['total_units']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Phòng trống:</span>
                                <span class="fw-bold text-success">{{ $stats['available_units'] }}</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Hợp đồng hoạt động:</span>
                                <span class="fw-bold text-info">{{ $stats['active_leases'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin bổ sung</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <small class="text-muted">Ngày tạo:</small>
                            <div>{{ $property->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted">Cập nhật lần cuối:</small>
                            <div>{{ $property->updated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @if($property->location2025 || $property->location)
                        <div class="mb-2">
                            <small class="text-muted">Địa chỉ đầy đủ:</small>
                            @if($property->location2025)
                                <div class="mb-1">
                                    <strong class="text-primary">Địa chỉ mới (2025):</strong><br>
                                    {{ $property->location2025->street }}<br>
                                    {{ $property->location2025->ward }}, {{ $property->location2025->city }}
                                    @if($property->location2025->country)
                                        , {{ $property->location2025->country }}
                                    @endif
                                </div>
                            @endif
                            @if($property->location)
                                <div class="mb-1">
                                    <strong class="text-secondary">Địa chỉ cũ:</strong><br>
                                    @if($property->location->street)
                                        {{ $property->location->street }}<br>
                                    @endif
                                    @if($property->location->ward)
                                        {{ $property->location->ward }}, 
                                    @endif
                                    @if($property->location->district)
                                        {{ $property->location->district }}, 
                                    @endif
                                    {{ $property->location->city }}
                                </div>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Info Card -->
                <div class="card shadow-sm border-info">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Lưu ý</h6>
                    </div>
                    <div class="card-body">
                        <ul class="small mb-0">
                            <li>Bạn chỉ có thể xem thông tin bất động sản</li>
                            <li>Không thể chỉnh sửa thông tin</li>
                            <li>Liên hệ quản lý để được hỗ trợ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
}

.bg-light {
    background-color: #f8f9fa !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.progress {
    background-color: #e9ecef;
}
</style>
@endpush
