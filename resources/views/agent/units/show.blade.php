@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết phòng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>{{ $unit->code }}</h1>
                <p>
                    <i class="fas fa-building text-primary"></i> {{ $unit->property->name }}
                    @if($unit->property->owner)
                        - <i class="fas fa-user text-secondary"></i> {{ $unit->property->owner->full_name }}
                    @endif
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('agent.units.edit', $unit->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Unit Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mã phòng</label>
                                <p class="mb-0">{{ $unit->code }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Bất động sản</label>
                                <p class="mb-0">{{ $unit->property->name }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tầng</label>
                                <p class="mb-0">
                                    @if($unit->floor)
                                        Tầng {{ $unit->floor }}
                                    @else
                                        <span class="text-muted">Chưa xác định</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Loại phòng</label>
                                <p class="mb-0">
                                    @switch($unit->unit_type)
                                        @case('room')
                                            <span class="badge bg-info">Phòng trọ</span>
                                            @break
                                        @case('apartment')
                                            <span class="badge bg-primary">Căn hộ</span>
                                            @break
                                        @case('dorm')
                                            <span class="badge bg-warning">Ký túc xá</span>
                                            @break
                                        @case('shared')
                                            <span class="badge bg-secondary">Phòng chung</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Diện tích</label>
                                <p class="mb-0">
                                    @if($unit->area_m2)
                                        {{ $unit->area_m2 }} m²
                                    @else
                                        <span class="text-muted">Chưa xác định</span>
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Số người tối đa</label>
                                <p class="mb-0">{{ $unit->max_occupancy }} người</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Thông tin tài chính</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá thuê cơ bản</label>
                                <p class="mb-0 h5 text-success">
                                    {{ number_format($unit->base_rent, 0, ',', '.') }} VNĐ
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tiền cọc</label>
                                <p class="mb-0 h5 text-warning">
                                    {{ number_format($unit->deposit_amount, 0, ',', '.') }} VNĐ
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status and Notes -->
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-clipboard-list"></i> Trạng thái và ghi chú</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <p class="mb-0">
                                    @switch($unit->status)
                                        @case('available')
                                            <span class="badge bg-success fs-6">Trống</span>
                                            @break
                                        @case('reserved')
                                            <span class="badge bg-warning fs-6">Đã đặt</span>
                                            @break
                                        @case('occupied')
                                            <span class="badge bg-danger fs-6">Đã thuê</span>
                                            @break
                                        @case('maintenance')
                                            <span class="badge bg-secondary fs-6">Bảo trì</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày tạo</label>
                                <p class="mb-0">{{ $unit->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @if($unit->note)
                            <div class="col-12 mb-3">
                                <label class="form-label fw-bold">Ghi chú</label>
                                <p class="mb-0">{{ $unit->note }}</p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Current Lease Information -->
                @if($unit->is_rented && $unit->current_lease)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0"><i class="fas fa-user-check"></i> Thông tin thuê hiện tại</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Khách thuê</label>
                            <p class="mb-0">{{ $unit->current_lease->tenant->full_name }}</p>
                            <small class="text-muted">{{ $unit->current_lease->tenant->email }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Giá thuê thực tế</label>
                            <p class="mb-0 h6 text-success">
                                {{ number_format($unit->current_lease->rent_amount, 0, ',', '.') }} VNĐ
                            </p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày bắt đầu</label>
                            <p class="mb-0">{{ $unit->current_lease->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ngày kết thúc</label>
                            <p class="mb-0">{{ $unit->current_lease->end_date->format('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                @else
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-door-open"></i> Phòng trống</h6>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-home fa-2x text-success mb-3"></i>
                        <p class="text-muted mb-0">Phòng hiện đang trống và sẵn sàng cho thuê</p>
                    </div>
                </div>
                @endif

                <!-- Property Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-building"></i> Thông tin bất động sản</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên bất động sản</label>
                            <p class="mb-0">{{ $unit->property->name }}</p>
                        </div>
                        @if($unit->property->owner)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chủ trọ</label>
                            <p class="mb-0">
                                <i class="fas fa-user text-primary"></i> {{ $unit->property->owner->full_name }}
                            </p>
                        </div>
                        @endif
                        @if($unit->property->location2025)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <p class="mb-0 small">
                                {{ $unit->property->location2025->street }}, {{ $unit->property->location2025->ward }}, {{ $unit->property->location2025->city }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-cogs"></i> Thao tác</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('agent.units.edit', $unit->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa phòng
                            </a>
                            <form action="{{ route('agent.units.destroy', $unit->id) }}" method="POST" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Xóa phòng
                                </button>
                            </form>
                        </div>
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

.badge.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endpush
