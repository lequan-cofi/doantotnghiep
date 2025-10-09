@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Ticket #' . $ticket->id)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Ticket #{{ $ticket->id }}</h1>
            <p class="mb-0">{{ $ticket->title }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('manager.tickets.edit', $ticket->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="{{ route('manager.tickets.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Ticket Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Ticket</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Tiêu đề</h6>
                            <p class="mb-3">{{ $ticket->title }}</p>
                            
                            <h6 class="text-muted">Mô tả</h6>
                            <p class="mb-3">{{ $ticket->description ?: 'Không có mô tả' }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Trạng thái</h6>
                            @php
                                $statusColors = [
                                    'open' => 'success',
                                    'in_progress' => 'warning',
                                    'resolved' => 'info',
                                    'closed' => 'secondary',
                                    'cancelled' => 'danger'
                                ];
                                $statusLabels = [
                                    'open' => 'Mở',
                                    'in_progress' => 'Đang xử lý',
                                    'resolved' => 'Đã giải quyết',
                                    'closed' => 'Đã đóng',
                                    'cancelled' => 'Đã hủy'
                                ];
                            @endphp
                            <p class="mb-3">
                                <span class="badge bg-{{ $statusColors[$ticket->status] }} fs-6">
                                    {{ $statusLabels[$ticket->status] }}
                                </span>
                            </p>
                            
                            <h6 class="text-muted">Độ ưu tiên</h6>
                            @php
                                $priorityColors = [
                                    'low' => 'secondary',
                                    'medium' => 'primary',
                                    'high' => 'warning',
                                    'urgent' => 'danger'
                                ];
                                $priorityLabels = [
                                    'low' => 'Thấp',
                                    'medium' => 'Trung bình',
                                    'high' => 'Cao',
                                    'urgent' => 'Khẩn cấp'
                                ];
                            @endphp
                            <p class="mb-3">
                                <span class="badge bg-{{ $priorityColors[$ticket->priority] }} fs-6">
                                    {{ $priorityLabels[$ticket->priority] }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ticket Logs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Nhật ký Ticket</h6>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addLogModal">
                        <i class="fas fa-plus"></i> Thêm nhật ký
                    </button>
                </div>
                <div class="card-body">
                    @if($ticket->logs->count() > 0)
                        <div class="timeline">
                            @foreach($ticket->logs as $log)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-primary"></div>
                                <div class="timeline-content">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $log->action }}</h6>
                                            <p class="mb-1">{{ $log->detail }}</p>
                                            @if($log->cost_amount > 0)
                                                <div class="alert alert-info py-2 px-3 mb-2">
                                                    <strong>Chi phí:</strong> {{ number_format($log->cost_amount, 0, ',', '.') }} VND
                                                    @if($log->cost_note)
                                                        <br><small>{{ $log->cost_note }}</small>
                                                    @endif
                                                    <br><small><strong>Hạch toán:</strong> 
                                                        @php
                                                            $chargeLabels = [
                                                                'none' => 'Không hạch toán',
                                                                'tenant_deposit' => 'Trừ vào cọc',
                                                                'tenant_invoice' => 'Thêm vào hóa đơn',
                                                                'landlord' => 'Chủ trọ chịu'
                                                            ];
                                                        @endphp
                                                        {{ $chargeLabels[$log->charge_to] }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted">
                                                {{ $log->actor->full_name ?? 'System' }}<br>
                                                {{ $log->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có nhật ký nào</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Ticket Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin liên kết</h6>
                </div>
                <div class="card-body">
                    @if($ticket->unit)
                        <h6 class="text-muted">Phòng</h6>
                        <p class="mb-3">
                            <strong>{{ $ticket->unit->property->name }}</strong><br>
                            Phòng: {{ $ticket->unit->code }}<br>
                            <small class="text-muted">{{ $ticket->unit->area_m2 }}m²</small>
                        </p>
                    @endif

                    @if($ticket->lease)
                        <h6 class="text-muted">Hợp đồng</h6>
                        <p class="mb-3">
                            <strong>{{ $ticket->lease->contract_no ?: 'HD#' . $ticket->lease->id }}</strong><br>
                            Khách thuê: {{ $ticket->lease->tenant->full_name }}<br>
                            <small class="text-muted">
                                {{ $ticket->lease->start_date->format('d/m/Y') }} - 
                                {{ $ticket->lease->end_date->format('d/m/Y') }}
                            </small>
                        </p>
                    @endif

                    <h6 class="text-muted">Người tạo</h6>
                    <p class="mb-3">{{ $ticket->createdBy->full_name ?? 'N/A' }}</p>

                    <h6 class="text-muted">Người phụ trách</h6>
                    <p class="mb-3">{{ $ticket->assignedTo->full_name ?? 'Chưa giao' }}</p>

                    <h6 class="text-muted">Ngày tạo</h6>
                    <p class="mb-3">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>

                    <h6 class="text-muted">Cập nhật cuối</h6>
                    <p class="mb-0">{{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                </div>
            </div>

            {{-- <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($ticket->status == 'open')
                            <button type="button" class="btn btn-warning btn-sm" onclick="updateStatus('in_progress')">
                                <i class="fas fa-play"></i> Bắt đầu xử lý
                            </button>
                        @endif
                        
                        @if($ticket->status == 'in_progress')
                            <button type="button" class="btn btn-info btn-sm" onclick="updateStatus('resolved')">
                                <i class="fas fa-check"></i> Đánh dấu đã giải quyết
                            </button>
                        @endif
                        
                        @if($ticket->status == 'resolved')
                            <button type="button" class="btn btn-secondary btn-sm" onclick="updateStatus('closed')">
                                <i class="fas fa-lock"></i> Đóng ticket
                            </button>
                        @endif
                        
                        @if(in_array($ticket->status, ['open', 'in_progress']))
                            <button type="button" class="btn btn-danger btn-sm" onclick="updateStatus('cancelled')">
                                <i class="fas fa-times"></i> Hủy ticket
                            </button>
                        @endif
                    </div>
                </div>
            </div> --}}
        </div>
    </div>
</div>

<!-- Add Log Modal -->
<div class="modal fade" id="addLogModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm nhật ký</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addLogForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hành động <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="action" required 
                                   placeholder="Ví dụ: Kiểm tra, Sửa chữa, Hoàn thành...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Hạch toán chi phí <span class="text-danger">*</span></label>
                            <select class="form-select" name="charge_to" required>
                                <option value="none">Không hạch toán</option>
                                <option value="tenant_deposit">Trừ vào cọc</option>
                                <option value="tenant_invoice">Thêm vào hóa đơn</option>
                                <option value="landlord">Chủ trọ chịu</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Chi tiết</label>
                            <textarea class="form-control" name="detail" rows="3" 
                                      placeholder="Mô tả chi tiết hành động..."></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Chi phí (VND)</label>
                            <input type="number" class="form-control" name="cost_amount" 
                                   min="0" step="1000" placeholder="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Ghi chú chi phí</label>
                            <input type="text" class="form-control" name="cost_note" 
                                   placeholder="Mô tả chi phí...">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm nhật ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #007bff;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #007bff;
}
</style>
@endpush

@push('scripts')
<script>
// Ensure function is available globally
window.updateStatus = function(newStatus) {
    console.log('updateStatus called with:', newStatus);
    
    const statusLabels = {
        'in_progress': 'Đang xử lý',
        'resolved': 'Đã giải quyết',
        'closed': 'Đã đóng',
        'cancelled': 'Đã hủy'
    };
    
    Notify.confirm(`Bạn có chắc muốn chuyển ticket sang trạng thái "${statusLabels[newStatus]}"?`, function() {
        console.log('User confirmed status change to:', newStatus);
        // Show loading
        const loadingToast = Notify.toast({
            title: 'Đang cập nhật...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
        });
        
        const formData = new FormData();
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        formData.append('_method', 'PUT');
        formData.append('title', {!! json_encode($ticket->title) !!});
        formData.append('description', {!! json_encode($ticket->description) !!});
        formData.append('priority', {!! json_encode($ticket->priority) !!});
        formData.append('status', newStatus);
        formData.append('unit_id', {!! json_encode($ticket->unit_id) !!});
        formData.append('lease_id', {!! json_encode($ticket->lease_id) !!});
        formData.append('assigned_to', {!! json_encode($ticket->assigned_to) !!});
        
        console.log('Sending request to:', `/manager/tickets/{{ $ticket->id }}`);
        
        fetch(`/manager/tickets/{{ $ticket->id }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            
            // Hide loading
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.success) {
                Notify.success(data.message, 'Cập nhật thành công!');
                setTimeout(() => location.reload(), 1500);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra khi cập nhật', 'Lỗi cập nhật');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
};

// Add log form
document.getElementById('addLogForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang thêm...';
    submitBtn.disabled = true;
    
    fetch(`/manager/tickets/{{ $ticket->id }}/logs`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Notify.success(data.message, 'Thêm thành công!');
            setTimeout(() => location.reload(), 1500);
        } else {
            Notify.error(data.message, 'Lỗi thêm nhật ký');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Notify.error('Có lỗi xảy ra. Vui lòng thử lại.', 'Lỗi hệ thống');
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});
</script>
@endpush
