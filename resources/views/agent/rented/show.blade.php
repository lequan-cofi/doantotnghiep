@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết phòng đã cho thuê')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-home me-2"></i>{{ $unit->code }}
                    </h1>
                    <p class="text-muted mb-0">{{ $unit->property->name }} - Phòng đã cho thuê</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.rented.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                    <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-outline-primary">
                        <i class="fas fa-door-open me-1"></i>Xem phòng
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

            <!-- Current Lease Information -->
            @if($unit->current_lease)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract me-2"></i>Hợp đồng hiện tại
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Mã hợp đồng</label>
                                    <p class="mb-0 fw-bold">{{ $unit->current_lease->contract_no ?? 'N/A' }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Ngày bắt đầu</label>
                                    <p class="mb-0">{{ $unit->current_lease->start_date ? $unit->current_lease->start_date->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Ngày kết thúc</label>
                                    <p class="mb-0">{{ $unit->current_lease->end_date ? $unit->current_lease->end_date->format('d/m/Y') : 'N/A' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Tiền thuê/tháng</label>
                                    <p class="mb-0 fw-bold text-success fs-5">{{ number_format($unit->current_lease->rent_amount) }}đ</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Tiền cọc</label>
                                    <p class="mb-0 fw-bold">{{ $unit->current_lease->deposit_amount ? number_format($unit->current_lease->deposit_amount) . 'đ' : 'Không có' }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Trạng thái</label>
                                    <p class="mb-0">
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
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($unit->current_lease->note)
                            <hr>
                            <div class="info-item">
                                <label class="form-label text-muted small">Ghi chú hợp đồng</label>
                                <p class="mb-0">{{ $unit->current_lease->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Tenant Information -->
            @if($unit->current_lease && $unit->current_lease->tenant)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Thông tin người thuê
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Họ và tên</label>
                                    <p class="mb-0 fw-bold">{{ $unit->current_lease->tenant->full_name }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Số điện thoại</label>
                                    <p class="mb-0">{{ $unit->current_lease->tenant->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Email</label>
                                    <p class="mb-0">{{ $unit->current_lease->tenant->email }}</p>
                                </div>
                                @if($unit->current_lease->tenant->userProfile)
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Ngày sinh</label>
                                        <p class="mb-0">{{ $unit->current_lease->tenant->userProfile->formatted_dob ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Giới tính</label>
                                        <p class="mb-0">{{ $unit->current_lease->tenant->userProfile->gender_text ?? 'N/A' }}</p>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Ngày tạo tài khoản</label>
                                    <p class="mb-0">{{ $unit->current_lease->tenant->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Trạng thái tài khoản</label>
                                    <p class="mb-0">
                                        @if($unit->current_lease->tenant->email_verified_at)
                                            <span class="badge bg-success">Đã xác thực</span>
                                        @else
                                            <span class="badge bg-warning">Chưa xác thực</span>
                                        @endif
                                    </p>
                                </div>
                                @if($unit->current_lease->tenant->userProfile)
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Số CMND/CCCD</label>
                                        <p class="mb-0">{{ $unit->current_lease->tenant->userProfile->id_number ?? 'N/A' }}</p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Ngày cấp CMND/CCCD</label>
                                        <p class="mb-0">{{ $unit->current_lease->tenant->userProfile->formatted_id_issued_at ?? 'N/A' }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($unit->current_lease->tenant->userProfile && $unit->current_lease->tenant->userProfile->address)
                            <hr>
                            <div class="info-item">
                                <label class="form-label text-muted small">Địa chỉ thường trú</label>
                                <p class="mb-0">{{ $unit->current_lease->tenant->userProfile->address }}</p>
                            </div>
                        @endif
                        
                        @if($unit->current_lease->tenant->userProfile)
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="form-label text-muted small">Trạng thái KYC</label>
                                        <p class="mb-0">
                                            @if($unit->current_lease->tenant->userProfile->isKycComplete())
                                                <span class="badge bg-success">Hoàn thành</span>
                                            @else
                                                <span class="badge bg-warning">{{ $unit->current_lease->tenant->userProfile->getKycCompletionPercentage() }}% hoàn thành</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    @if(!$unit->current_lease->tenant->userProfile->isKycComplete())
                                        <div class="info-item">
                                            <label class="form-label text-muted small">Thông tin còn thiếu</label>
                                            <p class="mb-0 text-muted small">
                                                {{ implode(', ', $unit->current_lease->tenant->userProfile->getMissingKycFields()) }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Lease Residents -->
            @if($unit->current_lease && $unit->current_lease->residents->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-users me-2"></i>Người ở cùng
                        </h5>
                    </div>
                    <div class="card-body">
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
                                    @foreach($unit->current_lease->residents as $resident)
                                        <tr>
                                            <td>{{ $resident->name ?? 'N/A' }}</td>
                                            <td>{{ $resident->phone ?? 'N/A' }}</td>
                                            <td>{{ $resident->note ?? 'N/A' }}</td>
                                            <td>{{ $resident->id_number ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Recent Payments -->
            @if($recentPayments->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>Thanh toán gần đây
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Ngày thanh toán</th>
                                        <th>Số tiền</th>
                                        <th>Loại thanh toán</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                        <tr>
                                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="fw-bold text-success">{{ number_format($payment->amount) }}đ</td>
                                            <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                                            <td>
                                                @switch($payment->status)
                                                    @case('completed')
                                                        <span class="badge bg-success">Hoàn thành</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">Chờ xử lý</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-danger">Thất bại</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $payment->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $payment->note ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
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
                        <label class="form-label text-muted small">Địa chỉ cũ</label>
                        <p class="mb-0">{{ $unit->property->old_address ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Địa chỉ mới (2025)</label>
                        <p class="mb-0">{{ $unit->property->new_address ?? 'N/A' }}</p>
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

            <!-- Unit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin phòng
                    </h5>
                </div>
                <div class="card-body">
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
                        <label class="form-label text-muted small">Giá thuê cơ bản</label>
                        <p class="mb-0 fw-bold text-primary">{{ number_format($unit->base_rent) }}đ/tháng</p>
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

            <!-- Lease History -->
            @if($leaseHistory->count() > 1)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Lịch sử hợp đồng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            @foreach($leaseHistory->skip(1) as $lease)
                                <div class="timeline-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="timeline-marker bg-secondary"></div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1">{{ $lease->tenant->full_name ?? 'N/A' }}</h6>
                                            <p class="mb-1 text-muted small">
                                                {{ $lease->start_date ? $lease->start_date->format('d/m/Y') : 'N/A' }} - 
                                                {{ $lease->end_date ? $lease->end_date->format('d/m/Y') : 'N/A' }}
                                            </p>
                                            <span class="badge bg-{{ $lease->status == 'active' ? 'success' : ($lease->status == 'expired' ? 'warning' : 'danger') }}">
                                                {{ $lease->status == 'active' ? 'Đang hoạt động' : ($lease->status == 'expired' ? 'Hết hạn' : 'Đã chấm dứt') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-primary">
                            <i class="fas fa-door-open me-1"></i>Xem chi tiết phòng
                        </a>
                        @if($unit->current_lease)
                            <a href="{{ route('agent.leases.show', $unit->current_lease->id) }}" class="btn btn-outline-info">
                                <i class="fas fa-file-contract me-1"></i>Xem hợp đồng
                            </a>
                        @endif
                        <a href="{{ route('agent.rented.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>Danh sách phòng thuê
                        </a>
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
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/units.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/agent/rented.css') }}">
<style>
.timeline {
    position: relative;
}

.timeline-item {
    position: relative;
}

.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 6px;
}

.amenity-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.amenity-tags .badge {
    font-size: 0.75rem;
}
</style>
@endpush
