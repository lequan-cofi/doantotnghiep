@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết bất động sản')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-building me-2"></i>Chi tiết bất động sản
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('manager.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('manager.properties.index') }}">Bất động sản</a></li>
                    <li class="breadcrumb-item active">{{ $property->name }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('manager.properties.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('manager.properties.edit', $property->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <!-- Property Details -->
    <div class="row">
        <!-- Main Info -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tên bất động sản:</label>
                                <div class="p-2 bg-light rounded">{{ $property->name }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Loại bất động sản:</label>
                                <div class="p-2 bg-light rounded">
                                    @if ($property->propertyType)
                                        <i class="{{ $property->propertyType->icon }} me-2"></i>
                                        {{ $property->propertyType->name }}
                                    @else
                                        <span class="text-muted">Chưa phân loại</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chủ sở hữu:</label>
                                <div class="p-2 bg-light rounded">
                                    @if ($property->owner)
                                        <i class="fas fa-user me-2"></i>
                                        {{ $property->owner->full_name }}
                                    @else
                                        <span class="text-muted">Chưa xác định</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái:</label>
                                <div class="p-2 bg-light rounded">
                                    @if ($property->status == 1)
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-warning">Tạm ngưng</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tổng số tầng:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->total_floors ?? 'Chưa xác định' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tổng số phòng:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->total_rooms ?? 0 }}
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mô tả:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->description ?? 'Chưa có mô tả' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Old Location Info -->
            @if ($property->location)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ (Hệ thống cũ)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quốc gia:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location->country ?? 'Việt Nam' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tỉnh/Thành phố:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location->city ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quận/Huyện:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location->district ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phường/Xã:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location->ward ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Đường:</label>
                                <div class="p-2 bg-light rounded">{{ $property->location->street ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @if ($property->location->lat && $property->location->lng)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tọa độ:</label>
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $property->location->lat }}, {{ $property->location->lng }}
                                </div>
                            </div>
                        </div>
                        @endif
                        @if ($property->location->postal_code)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mã bưu điện:</label>
                                <div class="p-2 bg-light rounded">{{ $property->location->postal_code }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- New Location Info -->
            @if ($property->location2025)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ (Hệ thống mới 2025)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Quốc gia:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location2025->country ?? 'Việt Nam' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tỉnh/Thành phố:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location2025->city ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phường/Xã:</label>
                                <div class="p-2 bg-light rounded">
                                    {{ $property->location2025->ward ?? 'N/A' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Đường:</label>
                                <div class="p-2 bg-light rounded">{{ $property->location2025->street ?? 'N/A' }}</div>
                            </div>
                        </div>
                        @if ($property->location2025->lat && $property->location2025->lng)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Tọa độ:</label>
                                <div class="p-2 bg-light rounded">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    {{ $property->location2025->lat }}, {{ $property->location2025->lng }}
                                </div>
                            </div>
                        </div>
                        @endif
                        @if ($property->location2025->postal_code)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Mã bưu điện:</label>
                                <div class="p-2 bg-light rounded">{{ $property->location2025->postal_code }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Occupancy Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Tỷ lệ lấp đầy
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $totalUnits = $property->units->count();
                        // Count units with active leases instead of units.status
                        $occupiedUnits = $property->units->filter(function($unit) {
                            return $unit->leases()->where('status', 'active')->whereNull('deleted_at')->exists();
                        })->count();
                        $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;
                        $totalRooms = $property->total_rooms ?? 0;
                        $occupancyRateByRooms = $totalRooms > 0 ? round(($totalUnits / $totalRooms) * 100, 1) : 0;
                    @endphp
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Phòng đã sử dụng</span>
                            <span class="fw-bold">{{ $occupiedUnits }}/{{ $totalUnits }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar 
                                @if($occupancyRate >= 90) bg-danger
                                @elseif($occupancyRate >= 70) bg-warning
                                @elseif($occupancyRate >= 50) bg-info
                                @else bg-success
                                @endif"
                                style="width: {{ $occupancyRate }}%">
                            </div>
                        </div>
                        <small class="text-muted">{{ $occupancyRate }}%</small>
                    </div>

                    @if ($totalRooms > 0)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Tỷ lệ so với tổng phòng</span>
                            <span class="fw-bold">{{ $totalUnits }}/{{ $totalRooms }}</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" style="width: {{ $occupancyRateByRooms }}%"></div>
                        </div>
                        <small class="text-muted">{{ $occupancyRateByRooms }}%</small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt me-2"></i>Hành động nhanh
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('manager.properties.edit', $property->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                        <a href="#" class="btn btn-outline-info">
                            <i class="fas fa-plus"></i> Thêm phòng
                        </a>
                        <a href="#" class="btn btn-outline-success">
                            <i class="fas fa-file-contract"></i> Tạo hợp đồng
                        </a>
                        <a href="#" class="btn btn-outline-warning">
                            <i class="fas fa-chart-line"></i> Xem báo cáo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Property Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info me-2"></i>Thông tin hệ thống
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">ID:</small>
                        <span class="fw-bold">{{ $property->id }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Ngày tạo:</small>
                        <span class="fw-bold">{{ $property->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Cập nhật cuối:</small>
                        <span class="fw-bold">{{ $property->updated_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if ($property->deleted_at)
                    <div class="mb-2">
                        <small class="text-muted">Đã xóa:</small>
                        <span class="fw-bold text-danger">{{ $property->deleted_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Units List -->
    @if ($property->units->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-door-open me-2"></i>Danh sách phòng ({{ $property->units->count() }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tên phòng</th>
                                    <th>Diện tích</th>
                                    <th>Giá thuê</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($property->units as $unit)
                                <tr>
                                    <td>
                                        <strong>{{ $unit->name }}</strong>
                                        @if ($unit->description)
                                        <br><small class="text-muted">{{ $unit->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $unit->area ?? 'N/A' }} m²</td>
                                    <td>
                                        @if ($unit->rent_price)
                                            {{ number_format($unit->rent_price) }} VNĐ
                                        @else
                                            <span class="text-muted">Chưa xác định</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($unit->status == 'available')
                                            <span class="badge bg-success">Trống</span>
                                        @elseif ($unit->status == 'occupied')
                                            <span class="badge bg-danger">Đã thuê</span>
                                        @elseif ($unit->status == 'maintenance')
                                            <span class="badge bg-warning">Bảo trì</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $unit->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $unit->created_at->format('d/m/Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" class="btn btn-sm btn-outline-warning">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Any additional JavaScript for the show page
    console.log('Property show page loaded');
});
</script>
@endpush
