@extends('layouts.agent_dashboard')

@section('title', 'Bất động sản được gán')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Bất động sản được gán</h1>
                <p>Danh sách các bất động sản bạn được phân công quản lý</p>
            </div>
            <div class="header-actions">
                <span class="badge bg-primary fs-6">{{ $properties->count() }} BĐS</span>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        @if($properties->count() > 0)
            <div class="row">
                @foreach($properties as $property)
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-building"></i> {{ $property->name }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                @if($property->location2025)
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-map-marker-alt text-primary"></i> 
                                        <strong>Địa chỉ mới:</strong> {{ $property->location2025->street }}, {{ $property->location2025->ward }}, {{ $property->location2025->city }}
                                    </p>
                                @endif
                                @if($property->location)
                                    <p class="text-muted mb-1">
                                        <i class="fas fa-map-marker-alt text-secondary"></i> 
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
                                    </p>
                                @endif
                                
                                @if($property->description)
                                    <p class="text-muted small">{{ Str::limit($property->description, 100) }}</p>
                                @endif
                            </div>

                            <!-- Statistics -->
                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-primary">{{ $property->total_units }}</div>
                                        <small class="text-muted">Tổng phòng</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-success">{{ $property->available_units }}</div>
                                        <small class="text-muted">Trống</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-warning">{{ $property->occupied_units }}</div>
                                        <small class="text-muted">Đã thuê</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-2 bg-light rounded">
                                        <div class="fw-bold text-info">{{ $property->active_leases }}</div>
                                        <small class="text-muted">Hợp đồng</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Occupancy Rate -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <small class="text-muted">Tỷ lệ lấp đầy:</small>
                                    <small class="fw-bold">{{ $property->occupancy_rate }}%</small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" 
                                         style="width: {{ $property->occupancy_rate }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Property Details -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Loại:</small>
                                    <small class="fw-bold">{{ $property->propertyType->name ?? 'Chưa xác định' }}</small>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Trạng thái:</small>
                                    <span class="badge bg-{{ $property->status ? 'success' : 'secondary' }}">
                                        {{ $property->status ? 'Hoạt động' : 'Tạm ngưng' }}
                                    </span>
                                </div>
                                @if($property->total_floors)
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Số tầng:</small>
                                    <small class="fw-bold">{{ $property->total_floors }}</small>
                                </div>
                                @endif
                                @if($property->monthly_revenue > 0)
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">Doanh thu:</small>
                                    <small class="fw-bold text-success">{{ number_format($property->monthly_revenue, 0, ',', '.') }} VNĐ</small>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer bg-transparent">
                            <a href="{{ route('agent.properties.show', $property->id) }}" class="btn btn-primary w-100">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-building fa-3x text-muted"></i>
                </div>
                <h4 class="text-muted">Chưa có bất động sản nào</h4>
                <p class="text-muted">Bạn chưa được gán quản lý bất động sản nào. Vui lòng liên hệ quản lý để được phân công.</p>
            </div>
        @endif
    </div>
</main>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.bg-light {
    background-color: #f8f9fa !important;
}
</style>
@endpush
