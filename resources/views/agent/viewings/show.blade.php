@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết lịch hẹn')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-calendar-check me-2"></i>Chi tiết lịch hẹn
                    </h1>
                    <p class="text-muted mb-0">Thông tin chi tiết lịch hẹn xem phòng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                    <a href="{{ route('agent.viewings.edit', $viewing->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Sửa
                    </a>
                    @if(in_array($viewing->status, ['requested', 'confirmed']))
                        <button type="button" class="btn btn-danger" 
                                onclick="confirmDelete({{ $viewing->id }}, '{{ $viewing->customer_name }}')">
                            <i class="fas fa-trash me-1"></i>Xóa
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer Information -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Thông tin khách hàng
                    </h5>
                </div>
                <div class="card-body">
                    @if($viewing->tenant)
                        <!-- Tenant Information -->
                        <div class="text-center mb-3">
                            <div class="customer-avatar mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-user"></i>
                            </div>
                            <h6 class="mb-1">{{ $viewing->tenant->full_name }}</h6>
                            <span class="customer-type-badge tenant">
                                <i class="fas fa-user"></i>
                                Khách thuê
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <strong>Email:</strong><br>
                                <a href="mailto:{{ $viewing->tenant->email }}">{{ $viewing->tenant->email }}</a>
                            </div>
                            <div class="col-12 mb-2">
                                <strong>Số điện thoại:</strong><br>
                                <a href="tel:{{ $viewing->tenant->phone }}">{{ $viewing->tenant->phone }}</a>
                            </div>
                            @if($viewing->tenant->created_at)
                                <div class="col-12">
                                    <strong>Tham gia:</strong><br>
                                    {{ $viewing->tenant->created_at->format('d/m/Y') }}
                                </div>
                            @endif
                        </div>
                    @else
                        <!-- Lead Information -->
                        <div class="text-center mb-3">
                            <div class="customer-avatar mx-auto mb-2" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <h6 class="mb-1">{{ $viewing->lead_name }}</h6>
                            <span class="customer-type-badge lead">
                                <i class="fas fa-user-plus"></i>
                                Lead
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <strong>Số điện thoại:</strong><br>
                                <a href="tel:{{ $viewing->lead_phone }}">{{ $viewing->lead_phone }}</a>
                            </div>
                            @if($viewing->lead_email)
                                <div class="col-12 mb-2">
                                    <strong>Email:</strong><br>
                                    <a href="mailto:{{ $viewing->lead_email }}">{{ $viewing->lead_email }}</a>
                                </div>
                            @endif
                            @if($viewing->lead)
                                <div class="col-12">
                                    <strong>Lead ID:</strong><br>
                                    #{{ $viewing->lead->id }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Viewing Details -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>Chi tiết lịch hẹn
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>ID lịch hẹn:</strong><br>
                            <span class="text-muted">#{{ $viewing->id }}</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Trạng thái:</strong><br>
                            <span class="badge {{ $viewing->status_badge_class }}">
                                {{ $viewing->status_text }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Thời gian hẹn:</strong><br>
                            <i class="fas fa-calendar-alt me-1"></i>
                            {{ $viewing->schedule_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Agent phụ trách:</strong><br>
                            @if($viewing->agent)
                                <i class="fas fa-user-tie me-1"></i>
                                {{ $viewing->agent->full_name }}
                            @else
                                <span class="text-muted">Chưa phân công</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Bất động sản:</strong><br>
                            <i class="fas fa-building me-1"></i>
                            {{ $viewing->property->name }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phòng:</strong><br>
                            @if($viewing->unit)
                                <i class="fas fa-door-open me-1"></i>
                                {{ $viewing->unit->code }}
                            @else
                                <span class="text-muted">Chưa chọn phòng</span>
                            @endif
                        </div>
                        @if($viewing->note)
                            <div class="col-12 mb-3">
                                <strong>Ghi chú:</strong><br>
                                <div class="bg-light p-3 rounded">
                                    {{ $viewing->note }}
                                </div>
                            </div>
                        @endif
                        @if($viewing->result_note)
                            <div class="col-12 mb-3">
                                <strong>Kết quả buổi xem:</strong><br>
                                <div class="bg-light p-3 rounded">
                                    {{ $viewing->result_note }}
                                </div>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <strong>Tạo lúc:</strong><br>
                            <i class="fas fa-clock me-1"></i>
                            {{ $viewing->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Cập nhật lần cuối:</strong><br>
                            <i class="fas fa-edit me-1"></i>
                            {{ $viewing->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    @if($viewing->status === 'requested')
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i>Thao tác
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <form action="{{ route('agent.viewings.confirm', $viewing->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>Xác nhận lịch hẹn
                                </button>
                            </form>
                            <form action="{{ route('agent.viewings.cancel', $viewing->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning" 
                                        onclick="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')">
                                    <i class="fas fa-times me-1"></i>Hủy lịch hẹn
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif($viewing->status === 'confirmed')
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tasks me-2"></i>Thao tác
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#markDoneModal">
                                <i class="fas fa-check-circle me-1"></i>Đánh dấu hoàn thành
                            </button>
                            <form action="{{ route('agent.viewings.cancel', $viewing->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-warning" 
                                        onclick="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn này?')">
                                    <i class="fas fa-times me-1"></i>Hủy lịch hẹn
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Mark Done Modal -->
<div class="modal fade" id="markDoneModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('agent.viewings.mark-done', $viewing->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Đánh dấu hoàn thành</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="result_note" class="form-label">Kết quả buổi xem</label>
                        <textarea class="form-control" id="result_note" name="result_note" rows="4" 
                                  placeholder="Ghi chú về kết quả buổi xem phòng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Hoàn thành</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="{{ asset('assets/js/agent/viewings.js') }}"></script>
@endpush

@push('styles')
<link href="{{ asset('assets/css/notifications.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/agent/viewings.css') }}" rel="stylesheet">
@endpush
