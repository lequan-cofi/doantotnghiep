@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết phòng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-door-open me-2"></i>{{ $unit->code }}
                    </h1>
                    <p class="text-muted mb-0">{{ $unit->property->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.units.edit', $unit->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                    <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Unit Images -->
            @if($unit->images && count($unit->images) > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-images me-2"></i>Hình ảnh phòng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            @foreach($unit->images as $index => $image)
                                <div class="col-md-4">
                                    <img src="{{ asset('storage/' . $image) }}" 
                                         class="img-thumbnail unit-image-detail" 
                                         alt="{{ $unit->code }} - {{ $index + 1 }}"
                                         data-bs-toggle="modal" 
                                         data-bs-target="#imageModal"
                                         data-image="{{ asset('storage/' . $image) }}"
                                         style="cursor: pointer; height: 200px; object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Unit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin phòng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Mã phòng</label>
                                <p class="mb-0 fw-bold">{{ $unit->code }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Tầng</label>
                                <p class="mb-0">{{ $unit->floor ?? 'N/A' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Diện tích</label>
                                <p class="mb-0">{{ $unit->area_m2 ? $unit->area_m2 . ' m²' : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Loại phòng</label>
                                <p class="mb-0">
                                    @switch($unit->unit_type)
                                        @case('room')
                                            <span class="badge bg-primary">Phòng</span>
                                            @break
                                        @case('apartment')
                                            <span class="badge bg-info">Căn hộ</span>
                                            @break
                                        @case('dorm')
                                            <span class="badge bg-warning">Ký túc xá</span>
                                            @break
                                        @case('shared')
                                            <span class="badge bg-secondary">Chung</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Số người tối đa</label>
                                <p class="mb-0">{{ $unit->max_occupancy }} người</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Trạng thái</label>
                                <p class="mb-0">
                                    @switch($unit->status)
                                        @case('available')
                                            <span class="badge bg-success">Có sẵn</span>
                                            @break
                                        @case('reserved')
                                            <span class="badge bg-warning">Đã đặt</span>
                                            @break
                                        @case('occupied')
                                            <span class="badge bg-danger">Đã thuê</span>
                                            @break
                                        @case('maintenance')
                                            <span class="badge bg-info">Bảo trì</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($unit->note)
                        <hr>
                        <div class="info-item">
                            <label class="form-label text-muted small">Ghi chú</label>
                            <p class="mb-0">{{ $unit->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-dollar-sign me-2"></i>Thông tin giá cả
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Giá thuê cơ bản</label>
                                <p class="mb-0 fw-bold text-primary fs-5">{{ number_format($unit->base_rent) }}đ/tháng</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Tiền cọc</label>
                                <p class="mb-0 fw-bold">{{ $unit->deposit_amount ? number_format($unit->deposit_amount) . 'đ' : 'Không có' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Amenities -->
            @if($unit->amenities->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>Tiện ích
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="amenities-grid">
                            @foreach($unit->amenities->groupBy('category') as $category => $categoryAmenities)
                                <div class="amenity-category mb-3">
                                    <h6 class="text-muted small mb-2">{{ $category }}</h6>
                                    <div class="amenity-tags">
                                        @foreach($categoryAmenities as $amenity)
                                            <span class="badge bg-light text-dark me-2 mb-2">
                                                <i class="fas fa-check me-1"></i>{{ $amenity->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Lease Information -->
            @if($unit->is_rented)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract me-2"></i>Thông tin thuê
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($unit->current_lease)
                            <div class="alert alert-info">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-item mb-2">
                                            <label class="form-label text-muted small">Người thuê</label>
                                            <p class="mb-0 fw-bold">{{ $unit->current_lease->tenant->full_name ?? 'N/A' }}</p>
                                        </div>
                                        <div class="info-item mb-2">
                                            <label class="form-label text-muted small">Số điện thoại</label>
                                            <p class="mb-0">{{ $unit->current_lease->tenant->phone ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-item mb-2">
                                            <label class="form-label text-muted small">Ngày bắt đầu</label>
                                            <p class="mb-0">{{ $unit->current_lease->start_date ? $unit->current_lease->start_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                        <div class="info-item mb-2">
                                            <label class="form-label text-muted small">Ngày kết thúc</label>
                                            <p class="mb-0">{{ $unit->current_lease->end_date ? $unit->current_lease->end_date->format('d/m/Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Meters Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Công tơ và số liệu
                    </h5>
                    <a href="{{ route('agent.meters.index', ['unit_id' => $unit->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Quản lý công tơ
                    </a>
                </div>
                <div class="card-body">
                    @if($meters->count() > 0)
                        @foreach($meters as $meter)
                            <div class="meter-item mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-tachometer-alt me-1"></i>
                                            {{ $meter->service->name ?? 'Dịch vụ không xác định' }}
                                        </h6>
                                        <small class="text-muted">
                                            Số seri: {{ $meter->serial_no ?? 'N/A' }} | 
                                            Lắp đặt: {{ $meter->installed_at ? $meter->installed_at->format('d/m/Y') : 'N/A' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $meter->status ? 'success' : 'danger' }}">
                                        {{ $meter->status ? 'Hoạt động' : 'Ngừng hoạt động' }}
                                    </span>
                                </div>
                                
                                @if($meter->readings->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ngày đọc</th>
                                                    <th>Chỉ số</th>
                                                    <th>Người đọc</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($meter->readings->take(5) as $reading)
                                                    <tr>
                                                        <td>{{ $reading->reading_date->format('d/m/Y') }}</td>
                                                        <td class="fw-bold">{{ number_format($reading->value, 3) }}</td>
                                                        <td>{{ $reading->takenBy->full_name ?? 'N/A' }}</td>
                                                        <td>{{ $reading->note ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($meter->readings->count() > 5)
                                        <div class="text-center mt-2">
                                            <small class="text-muted">
                                                Hiển thị 5 chỉ số gần nhất. 
                                                <a href="{{ route('agent.meters.show', $meter->id) }}">Xem tất cả</a>
                                            </small>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-2">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        <span class="text-muted">Chưa có số liệu đọc công tơ</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-tachometer-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Chưa có công tơ nào</p>
                            <small class="text-muted">Công tơ cần được lắp đặt cho phòng này</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Property Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Bất động sản
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Tên bất động sản</label>
                        <p class="mb-0 fw-bold">{{ $unit->property->name }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Địa chỉ</label>
                        <p class="mb-0">{{ $unit->property->new_address ?? $unit->property->old_address ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Chủ sở hữu</label>
                        <p class="mb-0">{{ $unit->property->owner_name }}</p>
                    </div>
                    <div class="d-grid">
                        <a href="{{ route('agent.properties.show', $unit->property->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết BĐS
                        </a>
                    </div>
                </div>
            </div>

            <!-- Unit Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Ngày tạo</label>
                        <p class="mb-0">{{ $unit->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Cập nhật cuối</label>
                        <p class="mb-0">{{ $unit->updated_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Số hợp đồng thuê</label>
                        <p class="mb-0">{{ $unit->leases->count() }}</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.units.edit', $unit->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa phòng
                        </a>
                        @if(!$unit->is_rented)
                            <button class="btn btn-success" onclick="changeStatus('available')">
                                <i class="fas fa-check me-1"></i>Đánh dấu có sẵn
                            </button>
                        @endif
                        <button class="btn btn-warning" onclick="changeStatus('maintenance')">
                            <i class="fas fa-tools me-1"></i>Bảo trì
                        </button>
                        <form method="POST" action="{{ route('agent.units.destroy', $unit->id) }}" 
                              id="delete-form-{{ $unit->id }}" class="d-grid">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger"
                                    onclick="confirmDeleteUnit({{ $unit->id }}, '{{ $unit->code }}')">
                                <i class="fas fa-trash me-1"></i>Xóa phòng
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hình ảnh phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Unit Image">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image modal
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    imageModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image');
        modalImage.src = imageSrc;
    });
});

function changeStatus(status) {
    // This would typically make an AJAX request to update the unit status
    Notify.info('Chức năng thay đổi trạng thái sẽ được triển khai sau');
}

function confirmDeleteUnit(unitId, unitCode) {
    Notify.confirmDelete(
        `phòng "${unitCode}"`,
        function() {
            // Show loading notification
            Notify.info('Đang xóa phòng...');
            
            // Submit the form
            const form = document.getElementById(`delete-form-${unitId}`);
            if (form) {
                form.submit();
            }
        }
    );
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/units.css') }}">
@endpush

@push('scripts')
@endpush
