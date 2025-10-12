@extends('layouts.app')

@section('title', 'Chi tiết lịch đặt')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-header-content">
                    <h1 class="page-title">
                        <i class="fas fa-calendar-check"></i>
                        Chi tiết lịch đặt
                    </h1>
                    <div class="page-actions">
                        <a href="{{ route('viewings.my-viewings') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <!-- Viewing Details -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i>
                                Thông tin lịch đặt
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Mã lịch đặt:</label>
                                        <span class="info-value">#{{ $viewing->id }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Trạng thái:</label>
                                        <span class="badge {{ $viewing->getStatusBadgeClass() }} fs-6">
                                            {{ $viewing->getStatusText() }}
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Thời gian đặt:</label>
                                        <span class="info-value">{{ $viewing->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Ngày xem:</label>
                                        <span class="info-value">{{ $viewing->schedule_at->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Giờ xem:</label>
                                        <span class="info-value">{{ $viewing->schedule_at->format('H:i') }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Thời gian còn lại:</label>
                                        <span class="info-value" id="timeRemaining">
                                            @if($viewing->schedule_at > now())
                                                {{ $viewing->schedule_at->diffForHumans() }}
                                            @else
                                                <span class="text-muted">Đã qua</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-building"></i>
                                Thông tin bất động sản
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h6 class="fw-semibold">{{ $viewing->property->name }}</h6>
                                    <p class="text-muted mb-2">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $viewing->property->location2025 ? $viewing->property->location2025->street . ', ' . $viewing->property->location2025->ward . ', ' . $viewing->property->location2025->city : 'Chưa cập nhật địa chỉ' }}
                                    </p>
                                    @if($viewing->property->description)
                                        <p class="text-muted">{{ Str::limit($viewing->property->description, 200) }}</p>
                                    @endif
                                </div>
                                <div class="col-md-4 text-end">
                                    <a href="{{ route('property.show', $viewing->property->id) }}" class="btn btn-outline-primary" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                        Xem chi tiết
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Unit Information -->
                    @if($viewing->unit)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-home"></i>
                                Thông tin phòng
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Mã phòng:</label>
                                        <span class="info-value">{{ $viewing->unit->code }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Diện tích:</label>
                                        <span class="info-value">{{ $viewing->unit->area_m2 ?? 'N/A' }}m²</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Sức chứa:</label>
                                        <span class="info-value">{{ $viewing->unit->max_occupancy }} người</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-item">
                                        <label class="info-label">Tầng:</label>
                                        <span class="info-value">Tầng {{ $viewing->unit->floor ?? 'N/A' }}</span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Giá thuê:</label>
                                        <span class="info-value text-success fw-semibold">
                                            {{ number_format($viewing->unit->base_rent, 0, ',', '.') }} VNĐ/tháng
                                        </span>
                                    </div>
                                    <div class="info-item">
                                        <label class="info-label">Trạng thái:</label>
                                        <span class="badge bg-success">Trống</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Notes -->
                    @if($viewing->note || $viewing->result_note)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-sticky-note"></i>
                                Ghi chú
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($viewing->note)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ghi chú của bạn:</label>
                                    <div class="alert alert-light">
                                        {{ $viewing->note }}
                                    </div>
                                </div>
                            @endif
                            
                            @if($viewing->result_note)
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Ghi chú từ agent:</label>
                                    <div class="alert alert-info">
                                        {{ $viewing->result_note }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Agent Information -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-tie"></i>
                                Thông tin agent
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="agent-info">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                        {{ substr($viewing->agent->name ?? 'A', 0, 1) }}
                                    </div>
                                    <div>
                                        <h6 class="mb-1">{{ $viewing->agent->name ?? 'Agent' }}</h6>
                                        <small class="text-muted">Agent bất động sản</small>
                                    </div>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <a href="tel:{{ $viewing->agent->phone ?? '0123456789' }}" class="btn btn-success">
                                        <i class="fas fa-phone"></i>
                                        Gọi agent
                                    </a>
                                    @if($viewing->agent->email)
                                        <a href="mailto:{{ $viewing->agent->email }}" class="btn btn-outline-primary">
                                            <i class="fas fa-envelope"></i>
                                            Gửi email
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    @if($viewing->status === 'requested' || $viewing->status === 'confirmed')
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-bolt"></i>
                                Thao tác nhanh
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-danger" onclick="cancelViewing({{ $viewing->id }})">
                                    <i class="fas fa-times"></i>
                                    Hủy lịch đặt
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Status History -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-history"></i>
                                Lịch sử trạng thái
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="timeline-content">
                                        <h6 class="timeline-title">Tạo lịch đặt</h6>
                                        <p class="timeline-text text-muted">{{ $viewing->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                
                                @if($viewing->status !== 'requested')
                                    <div class="timeline-item">
                                        <div class="timeline-marker bg-success"></div>
                                        <div class="timeline-content">
                                            <h6 class="timeline-title">{{ $viewing->getStatusText() }}</h6>
                                            <p class="timeline-text text-muted">{{ $viewing->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Confirmation Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xác nhận hủy lịch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hủy lịch đặt này không?</p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Lưu ý:</strong> Sau khi hủy, bạn sẽ cần đặt lịch mới nếu muốn xem phòng.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">Có, hủy lịch</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.page-header {
    background: white;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.page-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.page-title {
    margin: 0;
    color: #2c3e50;
    font-size: 1.5rem;
    font-weight: 600;
}

.page-actions {
    display: flex;
    gap: 10px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #495057;
    margin: 0;
}

.info-value {
    color: #212529;
}

.avatar-lg {
    width: 60px;
    height: 60px;
    font-size: 1.5rem;
}

.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 15px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -22px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline-title {
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: #495057;
}

.timeline-text {
    font-size: 0.8rem;
    margin: 0;
}

@media (max-width: 768px) {
    .page-header-content {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .page-actions {
        width: 100%;
        justify-content: flex-start;
    }
}
</style>
@endpush

@push('scripts')
<script>
function cancelViewing(viewingId) {
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
    
    // Store viewing ID for confirmation
    document.getElementById('cancelModal').setAttribute('data-viewing-id', viewingId);
}

function confirmCancel() {
    const modal = document.getElementById('cancelModal');
    const viewingId = modal.getAttribute('data-viewing-id');
    
    fetch(`/viewings/${viewingId}/cancel`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', data.message);
            setTimeout(() => {
                window.location.href = '{{ route("viewings.my-viewings") }}';
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    });
    
    const modalInstance = bootstrap.Modal.getInstance(modal);
    modalInstance.hide();
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Update time remaining every minute
setInterval(function() {
    const scheduleTime = new Date('{{ $viewing->schedule_at->toISOString() }}');
    const now = new Date();
    
    if (scheduleTime > now) {
        const diff = scheduleTime - now;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        
        let timeText = '';
        if (days > 0) timeText += `${days} ngày `;
        if (hours > 0) timeText += `${hours} giờ `;
        if (minutes > 0) timeText += `${minutes} phút`;
        
        document.getElementById('timeRemaining').textContent = timeText.trim();
    } else {
        document.getElementById('timeRemaining').innerHTML = '<span class="text-muted">Đã qua</span>';
    }
}, 60000);
</script>
@endpush
