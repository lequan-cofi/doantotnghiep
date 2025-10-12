@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết Lead')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-user me-2"></i>{{ $lead->name }}
                    </h1>
                    <p class="text-muted mb-0">Chi tiết khách hàng tiềm năng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                    <a href="{{ route('agent.leads.edit', $lead->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                    @if($lead->status != 'converted')
                        <a href="{{ route('agent.leads.create-lease', $lead->id) }}" class="btn btn-success">
                            <i class="fas fa-file-contract me-1"></i>Tạo hợp đồng
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Lead Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Thông tin Lead
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Tên khách hàng</label>
                                <p class="mb-0 fw-bold">{{ $lead->name }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Số điện thoại</label>
                                <p class="mb-0">{{ $lead->phone }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Email</label>
                                <p class="mb-0">{{ $lead->email ?? 'N/A' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Thành phố mong muốn</label>
                                <p class="mb-0">{{ $lead->desired_city ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Nguồn</label>
                                <p class="mb-0">
                                    <span class="badge bg-info">{{ ucfirst($lead->source) }}</span>
                                </p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Trạng thái</label>
                                <p class="mb-0">
                                    @switch($lead->status)
                                        @case('new')
                                            <span class="badge bg-primary">Mới</span>
                                            @break
                                        @case('contacted')
                                            <span class="badge bg-info">Đã liên hệ</span>
                                            @break
                                        @case('qualified')
                                            <span class="badge bg-warning">Đủ điều kiện</span>
                                            @break
                                        @case('proposal')
                                            <span class="badge bg-secondary">Đề xuất</span>
                                            @break
                                        @case('negotiation')
                                            <span class="badge bg-dark">Đàm phán</span>
                                            @break
                                        @case('converted')
                                            <span class="badge bg-success">Đã chuyển đổi</span>
                                            @break
                                        @case('lost')
                                            <span class="badge bg-danger">Mất khách</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $lead->status }}</span>
                                    @endswitch
                                </p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngân sách</label>
                                <p class="mb-0">
                                    @if($lead->budget_min && $lead->budget_max)
                                        {{ number_format($lead->budget_min) }}đ - {{ number_format($lead->budget_max) }}đ
                                    @elseif($lead->budget_min)
                                        Từ {{ number_format($lead->budget_min) }}đ
                                    @elseif($lead->budget_max)
                                        Đến {{ number_format($lead->budget_max) }}đ
                                    @else
                                        <span class="text-muted">Chưa xác định</span>
                                    @endif
                                </p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày tạo</label>
                                <p class="mb-0">{{ $lead->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($lead->note)
                        <hr>
                        <div class="info-item">
                            <label class="form-label text-muted small">Ghi chú</label>
                            <p class="mb-0">{{ $lead->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Viewings -->
            @if($viewings->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-eye me-2"></i>Lịch xem phòng ({{ $viewings->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Phòng</th>
                                        <th>Ngày xem</th>
                                        <th>Trạng thái</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewings as $viewing)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $viewing->unit->code ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $viewing->unit->property->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $viewing->schedule_at ? $viewing->schedule_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>
                                                @switch($viewing->status)
                                                    @case('scheduled')
                                                        <span class="badge bg-primary">Đã lên lịch</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Hoàn thành</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-danger">Đã hủy</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $viewing->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $viewing->notes ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Booking Deposits -->
            @if($bookingDeposits->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>Đặt cọc ({{ $bookingDeposits->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Phòng</th>
                                        <th>Số tiền</th>
                                        <th>Ngày đặt</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bookingDeposits as $deposit)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $deposit->unit->code ?? 'N/A' }}</div>
                                                    <small class="text-muted">{{ $deposit->unit->property->name ?? 'N/A' }}</small>
                                                </div>
                                            </td>
                                            <td class="fw-bold text-success">{{ number_format($deposit->amount) }}đ</td>
                                            <td>{{ $deposit->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @switch($deposit->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Chờ xử lý</span>
                                                        @break
                                                    @case('confirmed')
                                                        <span class="badge bg-success">Đã xác nhận</span>
                                                        @break
                                                    @case('refunded')
                                                        <span class="badge bg-info">Đã hoàn</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $deposit->status }}</span>
                                                @endswitch
                                            </td>
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
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.leads.edit', $lead->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa Lead
                        </a>
                        @if($lead->status != 'converted')
                            <a href="{{ route('agent.leads.create-lease', $lead->id) }}" class="btn btn-success">
                                <i class="fas fa-file-contract me-1"></i>Tạo hợp đồng
                            </a>
                        @endif
                        <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>Danh sách Leads
                        </a>
                    </div>
                </div>
            </div>

            <!-- Status Update -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-sync-alt me-2"></i>Cập nhật trạng thái
                    </h5>
                </div>
                <div class="card-body">
                    <form id="statusUpdateForm">
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái mới</label>
                            <select class="form-select" id="status" name="status">
                                <option value="new" {{ $lead->status == 'new' ? 'selected' : '' }}>Mới</option>
                                <option value="contacted" {{ $lead->status == 'contacted' ? 'selected' : '' }}>Đã liên hệ</option>
                                <option value="qualified" {{ $lead->status == 'qualified' ? 'selected' : '' }}>Đủ điều kiện</option>
                                <option value="proposal" {{ $lead->status == 'proposal' ? 'selected' : '' }}>Đề xuất</option>
                                <option value="negotiation" {{ $lead->status == 'negotiation' ? 'selected' : '' }}>Đàm phán</option>
                                <option value="converted" {{ $lead->status == 'converted' ? 'selected' : '' }}>Đã chuyển đổi</option>
                                <option value="lost" {{ $lead->status == 'lost' ? 'selected' : '' }}>Mất khách</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i>Cập nhật
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status update form
    document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(`/agent/leads/{{ $lead->id }}/status`, {
            method: 'PUT',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Cập nhật trạng thái');
                } else {
                    alert(data.message);
                }
                location.reload();
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi cập nhật');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi cập nhật trạng thái', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi cập nhật trạng thái');
            }
        });
    });
});
</script>
@endpush
