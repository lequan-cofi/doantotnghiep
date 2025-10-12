@extends('layouts.app')

@section('title', 'Lịch đặt của tôi')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <div class="page-header-content">
                    <h1 class="page-title">
                        <i class="fas fa-calendar-alt"></i>
                        Lịch đặt của tôi
                    </h1>
                    <div class="page-actions">
                        <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i>
                            Quay lại
                        </a>
                    </div>
                </div>
            </div>

            @if($viewings->count() > 0)
                <div class="row">
                    @foreach($viewings as $viewing)
                    <div class="col-lg-6 col-xl-4 mb-4">
                        <div class="card viewing-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="viewing-time">
                                    <i class="fas fa-clock"></i>
                                    {{ $viewing->schedule_at->format('d/m/Y H:i') }}
                                </div>
                                <span class="badge {{ $viewing->getStatusBadgeClass() }}">
                                    {{ $viewing->getStatusText() }}
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="viewing-info">
                                    <h6 class="viewing-title">{{ $viewing->property->name }}</h6>
                                    <p class="viewing-location text-muted">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $viewing->property->location2025 ? $viewing->property->location2025->city : 'Chưa cập nhật' }}
                                    </p>
                                    
                                    @if($viewing->unit)
                                        <div class="unit-info">
                                            <span class="badge bg-info">{{ $viewing->unit->code }}</span>
                                            <small class="text-muted ms-2">{{ $viewing->unit->area_m2 ?? 'N/A' }}m²</small>
                                            <div class="unit-price mt-1">
                                                <strong class="text-success">
                                                    {{ number_format($viewing->unit->base_rent, 0, ',', '.') }} VNĐ/tháng
                                                </strong>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="agent-info mt-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                {{ substr($viewing->agent->name ?? 'A', 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $viewing->agent->name ?? 'Agent' }}</div>
                                                <small class="text-muted">Agent</small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($viewing->note)
                                        <div class="viewing-note mt-3">
                                            <small class="text-muted">
                                                <i class="fas fa-sticky-note"></i>
                                                {{ Str::limit($viewing->note, 100) }}
                                            </small>
                                        </div>
                                    @endif
                                    
                                    @if($viewing->result_note)
                                        <div class="result-note mt-3">
                                            <small class="text-info">
                                                <i class="fas fa-info-circle"></i>
                                                {{ Str::limit($viewing->result_note, 100) }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="d-flex gap-2">
                                    <a href="{{ route('viewings.show', $viewing->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                                        <i class="fas fa-eye"></i>
                                        Chi tiết
                                    </a>
                                    @if($viewing->status === 'requested' || $viewing->status === 'confirmed')
                                        <button class="btn btn-outline-danger btn-sm" onclick="cancelViewing({{ $viewing->id }})">
                                            <i class="fas fa-times"></i>
                                            Hủy
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $viewings->links() }}
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có lịch đặt nào</h5>
                        <p class="text-muted">Bạn chưa đặt lịch xem phòng nào. Hãy tìm kiếm bất động sản phù hợp và đặt lịch xem.</p>
                        <a href="{{ route('property.index') }}" class="btn btn-primary">
                            <i class="fas fa-search"></i>
                            Tìm bất động sản
                        </a>
                    </div>
                </div>
            @endif
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

.viewing-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: 1px solid #e9ecef;
}

.viewing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.viewing-time {
    font-weight: 600;
    color: #007bff;
    font-size: 0.9rem;
}

.viewing-title {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.viewing-location {
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.unit-info {
    margin-bottom: 10px;
}

.unit-price {
    font-size: 0.9rem;
}

.agent-info {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 6px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 0.875rem;
}

.viewing-note {
    padding: 8px;
    background: #fff3cd;
    border-radius: 4px;
    border-left: 3px solid #ffc107;
}

.result-note {
    padding: 8px;
    background: #d1ecf1;
    border-radius: 4px;
    border-left: 3px solid #17a2b8;
}

.card-footer {
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
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
let viewingToCancel = null;

function cancelViewing(viewingId) {
    viewingToCancel = viewingId;
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

function confirmCancel() {
    if (!viewingToCancel) return;
    
    fetch(`/viewings/${viewingToCancel}/cancel`, {
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
                location.reload();
            }, 1500);
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    });
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('cancelModal'));
    modal.hide();
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
</script>
@endpush
