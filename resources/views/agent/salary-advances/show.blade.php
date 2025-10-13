@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết đơn ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết đơn ứng lương</h1>
            <p class="mb-0">Thông tin chi tiết đơn ứng lương #{{ $salaryAdvance->id }}</p>
        </div>
        <div class="d-flex gap-2">
            @if($salaryAdvance->canBeDeleted())
                <a href="{{ route('agent.salary-advances.edit', $salaryAdvance->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $salaryAdvance->id }})">
                    <i class="fas fa-trash"></i> Xóa
                </button>
            @endif
            <a href="{{ route('agent.salary-advances.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin đơn ứng lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID đơn:</label>
                                <p class="form-control-plaintext">{{ $salaryAdvance->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-{{ $salaryAdvance->status_color }}">
                                        {{ $salaryAdvance->status_label }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số tiền ứng:</label>
                                <p class="form-control-plaintext">
                                    <strong class="text-success fs-5">{{ number_format($salaryAdvance->amount, 0, ',', '.') }} {{ $salaryAdvance->currency }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số tiền còn lại:</label>
                                <p class="form-control-plaintext">
                                    <strong class="text-warning">{{ number_format($salaryAdvance->remaining_amount, 0, ',', '.') }} {{ $salaryAdvance->currency }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày ứng lương:</label>
                                <p class="form-control-plaintext">{{ $salaryAdvance->advance_date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày hoàn trả dự kiến:</label>
                                <p class="form-control-plaintext">{{ $salaryAdvance->expected_repayment_date->format('d/m/Y') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phương thức hoàn trả:</label>
                                <p class="form-control-plaintext">{{ $salaryAdvance->repayment_method_label }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if($salaryAdvance->installment_months)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Số tháng trả góp:</label>
                                    <p class="form-control-plaintext">{{ $salaryAdvance->installment_months }} tháng</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($salaryAdvance->monthly_deduction)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Số tiền trừ hàng tháng:</label>
                                    <p class="form-control-plaintext">
                                        <strong>{{ number_format($salaryAdvance->monthly_deduction, 0, ',', '.') }} {{ $salaryAdvance->currency }}</strong>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lý do ứng lương:</label>
                        <p class="form-control-plaintext">{{ $salaryAdvance->reason }}</p>
                    </div>

                    @if($salaryAdvance->note)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú:</label>
                            <p class="form-control-plaintext">{{ $salaryAdvance->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Repayment History -->
            @if($salaryAdvance->repaid_amount > 0)
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lịch sử hoàn trả</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Đã hoàn trả</h6>
                                    <h4 class="text-success mb-0">
                                        {{ number_format($salaryAdvance->repaid_amount, 0, ',', '.') }} {{ $salaryAdvance->currency }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Còn lại</h6>
                                    <h4 class="text-warning mb-0">
                                        {{ number_format($salaryAdvance->remaining_amount, 0, ',', '.') }} {{ $salaryAdvance->currency }}
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <h6 class="mb-1">Tỷ lệ hoàn trả</h6>
                                    <h4 class="mb-0">
                                        {{ number_format(($salaryAdvance->repaid_amount / $salaryAdvance->amount) * 100, 1) }}%
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Trạng thái</h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Đơn được tạo</h6>
                                <p class="timeline-text">{{ $salaryAdvance->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($salaryAdvance->approved_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-success"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đã được duyệt</h6>
                                    <p class="timeline-text">{{ $salaryAdvance->approved_at->format('d/m/Y H:i') }}</p>
                                    @if($salaryAdvance->approver)
                                        <small class="text-muted">Bởi: {{ $salaryAdvance->approver->full_name }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($salaryAdvance->rejected_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-danger"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đã bị từ chối</h6>
                                    <p class="timeline-text">{{ $salaryAdvance->rejected_at->format('d/m/Y H:i') }}</p>
                                    @if($salaryAdvance->rejector)
                                        <small class="text-muted">Bởi: {{ $salaryAdvance->rejector->full_name }}</small>
                                    @endif
                                    @if($salaryAdvance->rejection_reason)
                                        <small class="text-muted">Lý do: {{ $salaryAdvance->rejection_reason }}</small>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($salaryAdvance->status === 'repaid')
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="timeline-title">Đã hoàn trả đầy đủ</h6>
                                    <p class="timeline-text">{{ $salaryAdvance->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($salaryAdvance->canBeDeleted())
                            <a href="{{ route('agent.salary-advances.edit', $salaryAdvance->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <button type="button" class="btn btn-danger" onclick="confirmDelete({{ $salaryAdvance->id }})">
                                <i class="fas fa-trash"></i> Xóa
                            </button>
                        @else
                            <button type="button" class="btn btn-secondary" disabled>
                                <i class="fas fa-lock"></i> Không thể chỉnh sửa
                            </button>
                        @endif
                        <a href="{{ route('agent.salary-advances.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Danh sách đơn ứng lương
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Form (Hidden) -->
<form id="delete-form-{{ $salaryAdvance->id }}" action="{{ route('agent.salary-advances.destroy', $salaryAdvance->id) }}" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@push('scripts')
<script>
    // Confirm delete function
    function confirmDelete(id) {
        notify.confirmDelete('đơn ứng lương này', function() {
            document.getElementById('delete-form-' + id).submit();
        });
    }

    // Show notifications
    @if(session('success'))
        notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        notify.error('{{ session('error') }}');
    @endif
</script>
@endpush

@push('styles')
<style>
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
    background: #e3e6f0;
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
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #e3e6f0;
}

.timeline-content {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #4e73df;
}

.timeline-title {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
    color: #5a5c69;
}

.timeline-text {
    margin: 0;
    font-size: 13px;
    color: #858796;
}
</style>
@endpush
