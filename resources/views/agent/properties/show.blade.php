@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết bất động sản - ' . $property->name)

@section('content')
<div class="content">
    <!-- Header -->
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="content-title">{{ $property->name }}</h1>
                <p class="content-subtitle">
                    <i class="fas fa-map-marker-alt me-1"></i>
                    {{ $property->new_address }}
                </p>
            </div>
            <div>
                <a href="{{ route('agent.properties.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- Property Status Badge -->
    <div class="mb-4">
        @if($property->status == 1)
            <span class="badge bg-success fs-6">
                <i class="fas fa-check-circle me-1"></i> Hoạt động
            </span>
        @else
            <span class="badge bg-secondary fs-6">
                <i class="fas fa-pause-circle me-1"></i> Không hoạt động
            </span>
        @endif
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Property Images -->
            @if($property->images && count($property->images) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>Hình ảnh bất động sản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($property->images as $index => $image)
                                <div class="col-md-4 col-sm-6 mb-3">
                                    <div class="property-detail-image-container">
                                        <img src="{{ asset('storage/' . $image) }}" 
                                             class="img-fluid property-detail-image" 
                                             alt="{{ $property->name }} - Hình {{ $index + 1 }}"
                                             data-bs-toggle="modal" 
                                             data-bs-target="#imageModal"
                                             data-bs-slide-to="{{ $index }}"
                                             onerror="this.src='{{ asset('assets/images/default-property.jpg') }}'">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Property Owner and Address Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin chủ trọ và địa chỉ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">
                                    <i class="fas fa-user me-1"></i>Chủ trọ
                                </label>
                                <p class="mb-0 fw-bold">{{ $property->owner_name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">
                                    <i class="fas fa-phone me-1"></i>Liên hệ
                                </label>
                                <p class="mb-0">
                                    @if($property->owner && $property->owner->phone)
                                        {{ $property->owner->phone }}
                                    @else
                                        <span class="text-muted">Chưa có số điện thoại</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>Địa chỉ cũ (2024)
                                </label>
                                <p class="mb-0">{{ $property->old_address }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">
                                    <i class="fas fa-map-marker-alt me-1"></i>Địa chỉ mới (2025)
                                </label>
                                <p class="mb-0 fw-bold text-primary">{{ $property->new_address }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Property Description -->
            @if($property->description)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Mô tả
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="card-text">{{ $property->description }}</p>
                    </div>
                </div>
            @endif

            <!-- Units Overview -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-door-open me-2"></i>Tổng quan phòng
                    </h5>
                </div>
                <div class="card-body">
                    @if($property->units->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã phòng</th>
                                        <th>Tầng</th>
                                        <th>Loại</th>
                                        <th>Diện tích</th>
                                        <th>Trạng thái</th>
                                        <th>Giá thuê</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($property->units as $unit)
                                        <tr>
                                            <td>
                                                <strong>{{ $unit->code }}</strong>
                                            </td>
                                            <td>
                                                @if($unit->floor)
                                                    Tầng {{ $unit->floor }}
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info">
                                                    @switch($unit->unit_type)
                                                        @case('room') Phòng trọ @break
                                                        @case('apartment') Căn hộ @break
                                                        @case('dorm') Ký túc xá @break
                                                        @case('shared') Phòng chung @break
                                                        @default {{ $unit->unit_type }}
                                                    @endswitch
                                                </span>
                                            </td>
                                            <td>
                                                @if($unit->area_m2)
                                                    {{ $unit->area_m2 }} m²
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @switch($unit->status)
                                                    @case('available')
                                                        <span class="badge bg-success">Trống</span>
                                                        @break
                                                    @case('occupied')
                                                        <span class="badge bg-primary">Đã thuê</span>
                                                        @break
                                                    @case('reserved')
                                                        <span class="badge bg-warning">Đã đặt</span>
                                                        @break
                                                    @case('maintenance')
                                                        <span class="badge bg-secondary">Bảo trì</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-light text-dark">{{ $unit->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                @if($unit->base_rent)
                                                    {{ number_format($unit->base_rent) }} đ/tháng
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('agent.units.show', $unit->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-door-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có phòng nào</h5>
                            <p class="text-muted">Bất động sản này chưa có phòng nào được tạo.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Property Info Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info me-2"></i>Thông tin cơ bản
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Tên bất động sản</label>
                        <p class="mb-0 fw-bold">{{ $property->name }}</p>
                    </div>
                    
                    @if($property->propertyType)
                        <div class="info-item mb-3">
                            <label class="form-label text-muted small">Loại bất động sản</label>
                            <p class="mb-0">{{ $property->propertyType->name }}</p>
                        </div>
                    @endif

                    @if($property->total_floors)
                        <div class="info-item mb-3">
                            <label class="form-label text-muted small">Số tầng</label>
                            <p class="mb-0">{{ $property->total_floors }} tầng</p>
                        </div>
                    @endif

                    @if($property->total_rooms)
                        <div class="info-item mb-3">
                            <label class="form-label text-muted small">Tổng số phòng</label>
                            <p class="mb-0">{{ $property->total_rooms }} phòng</p>
                        </div>
                    @endif

                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Ngày tạo</label>
                        <p class="mb-0">{{ $property->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Chủ sở hữu</label>
                        <p class="mb-0">{{ $property->owner_name }}</p>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Thống kê
                    </h5>
                </div>
                <div class="card-body">
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Tổng phòng</span>
                            <span class="fw-bold">{{ $property->getTotalUnitsCount() }}</span>
                        </div>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Đã thuê</span>
                            <span class="fw-bold text-success">{{ $property->getOccupiedUnitsCount() }}</span>
                        </div>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Trống</span>
                            <span class="fw-bold text-primary">{{ $property->getAvailableUnitsCount() }}</span>
                        </div>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Đã đặt</span>
                            <span class="fw-bold text-warning">{{ $property->getReservedUnitsCount() }}</span>
                        </div>
                    </div>
                    
                    <div class="stat-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Bảo trì</span>
                            <span class="fw-bold text-secondary">{{ $property->getMaintenanceUnitsCount() }}</span>
                        </div>
                    </div>

                    <hr>

                    <div class="stat-item">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted">Tỷ lệ lấp đầy</span>
                            <span class="fw-bold">{{ $property->getOccupancyRate() }}%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar 
                                @if($property->getOccupancyRate() >= 90) bg-success
                                @elseif($property->getOccupancyRate() >= 70) bg-warning
                                @else bg-danger
                                @endif" 
                                style="width: {{ $property->getOccupancyRate() }}%">
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            Trạng thái: {{ $property->occupancy_status_text }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.units.index', ['property_id' => $property->id]) }}" 
                           class="btn btn-primary">
                            <i class="fas fa-door-open me-1"></i> Xem tất cả phòng
                        </a>
                        <a href="{{ route('agent.units.create', ['property_id' => $property->id]) }}" 
                           class="btn btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Thêm phòng mới
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
@if($property->images && count($property->images) > 0)
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Hình ảnh bất động sản</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div id="imageCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($property->images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image) }}" 
                                     class="d-block w-100" 
                                     alt="{{ $property->name }} - Hình {{ $index + 1 }}"
                                     onerror="this.src='{{ asset('assets/images/default-property.jpg') }}'">
                            </div>
                        @endforeach
                    </div>
                    @if(count($property->images) > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#imageCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#imageCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.property-detail-image-container {
    position: relative;
    height: 150px;
    overflow: hidden;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s ease;
}

.property-detail-image-container:hover {
    transform: scale(1.02);
}

.property-detail-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 8px;
}

.info-item {
    border-bottom: 1px solid #f0f0f0;
    padding-bottom: 12px;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.stat-item {
    padding: 8px 0;
}

.progress {
    background-color: #e9ecef;
    border-radius: 4px;
}

.progress-bar {
    border-radius: 4px;
    transition: width 0.3s ease;
}

.carousel-item img {
    max-height: 500px;
    object-fit: contain;
}

@media (max-width: 768px) {
    .property-detail-image-container {
        height: 120px;
    }
    
    .content-header {
        text-align: center;
    }
    
    .content-header .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
}
</style>
@endpush
