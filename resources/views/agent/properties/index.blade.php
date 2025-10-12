@extends('layouts.agent_dashboard')

@section('title', 'Danh sách bất động sản')

@section('content')
<div class="content">
    <div class="content-header">
        <h1 class="content-title">Bất động sản được phân quản lý</h1>
        <p class="content-subtitle">Danh sách các bất động sản bạn được phân quản lý</p>
    </div>

    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('agent.properties.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Tên bất động sản...">
                </div>
                <div class="col-md-3">
                    <label for="property_type_id" class="form-label">Loại BĐS</label>
                    <select class="form-select" id="property_type_id" name="property_type_id">
                        <option value="">Tất cả loại</option>
                        @foreach($propertyTypes as $type)
                            <option value="{{ $type->id }}" {{ request('property_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">Tất cả trạng thái</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Không hoạt động</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="sort_by" class="form-label">Sắp xếp theo</label>
                    <select class="form-select" id="sort_by" name="sort_by">
                        <option value="name" {{ request('sort_by') == 'name' ? 'selected' : '' }}>Tên</option>
                        <option value="created_at" {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Ngày tạo</option>
                        <option value="total_rooms" {{ request('sort_by') == 'total_rooms' ? 'selected' : '' }}>Số phòng</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('agent.properties.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    @if($properties->count() > 0)
        <div class="row">
            @foreach($properties as $property)
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card h-100 property-card">
                        <!-- Property Image -->
                        <div class="property-image-container">
                            @if($property->images && count($property->images) > 0)
                                <img src="{{ asset('storage/' . $property->images[0]) }}" 
                                     class="card-img-top property-image" 
                                     alt="{{ $property->name }}"
                                     onerror="this.src='{{ asset('assets/images/default-property.jpg') }}'">
                            @else
                                <div class="property-image-placeholder">
                                    <i class="fas fa-building fa-3x text-muted"></i>
                                </div>
                            @endif
                            <div class="property-status-badge">
                                @if($property->status == 1)
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Không hoạt động</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <!-- Property Info -->
                            <h5 class="card-title">{{ $property->name }}</h5>
                            <p class="card-text text-muted small">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                {{ $property->new_address }}
                            </p>
                            
                            <p class="card-text text-muted small">
                                <i class="fas fa-user me-1"></i>
                                {{ $property->owner_name }}
                            </p>
                            
                            @if($property->propertyType)
                                <p class="card-text text-muted small">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ $property->propertyType->name }}
                                </p>
                            @endif

                            <!-- Property Stats -->
                            <div class="property-stats mt-auto">
                                <div class="row text-center">
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h6 class="mb-0">{{ $property->getTotalUnitsCount() }}</h6>
                                            <small class="text-muted">Tổng phòng</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h6 class="mb-0 text-success">{{ $property->getOccupiedUnitsCount() }}</h6>
                                            <small class="text-muted">Đã thuê</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="stat-item">
                                            <h6 class="mb-0 text-primary">{{ $property->getAvailableUnitsCount() }}</h6>
                                            <small class="text-muted">Trống</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Occupancy Rate -->
                                <div class="mt-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted">Tỷ lệ lấp đầy</small>
                                        <small class="text-muted">{{ $property->getOccupancyRate() }}%</small>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar 
                                            @if($property->getOccupancyRate() >= 90) bg-success
                                            @elseif($property->getOccupancyRate() >= 70) bg-warning
                                            @else bg-danger
                                            @endif" 
                                            style="width: {{ $property->getOccupancyRate() }}%">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-3">
                                <a href="{{ route('agent.properties.show', $property->id) }}" 
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($properties->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $properties->appends(request()->query())->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="text-center py-5">
            <div class="empty-state">
                <i class="fas fa-building fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Không có bất động sản nào</h4>
                <p class="text-muted">Bạn chưa được phân quản lý bất động sản nào hoặc không có kết quả phù hợp với bộ lọc.</p>
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.property-card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.property-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.property-image-container {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.property-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.property-card:hover .property-image {
    transform: scale(1.05);
}

.property-image-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
}

.property-status-badge {
    position: absolute;
    top: 10px;
    right: 10px;
}

.property-stats {
    border-top: 1px solid #e9ecef;
    padding-top: 15px;
}

.stat-item h6 {
    font-weight: 600;
    color: #495057;
}

.progress {
    background-color: #e9ecef;
    border-radius: 3px;
}

.progress-bar {
    border-radius: 3px;
    transition: width 0.3s ease;
}

.empty-state {
    max-width: 400px;
    margin: 0 auto;
}

.card-img-top {
    border-radius: 0.375rem 0.375rem 0 0;
}

@media (max-width: 768px) {
    .property-image-container {
        height: 180px;
    }
}
</style>
@endpush
