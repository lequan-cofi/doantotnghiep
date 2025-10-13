@extends('layouts.agent_dashboard')

@section('title', 'Lịch hẹn hôm nay')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-calendar-day me-2"></i>Lịch hẹn hôm nay
                    </h1>
                    <p class="text-muted mb-0">Danh sách lịch hẹn xem phòng trong ngày {{ now()->format('d/m/Y') }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                    </a>
                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Tất cả lịch hẹn
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Viewings -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-day me-2"></i>Lịch hẹn hôm nay
                        <span class="badge bg-primary ms-2">{{ $viewings->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($viewings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-viewings table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Thời gian</th>
                                        <th>Khách hàng</th>
                                        <th>Loại</th>
                                        <th>Bất động sản</th>
                                        <th>Phòng</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewings as $viewing)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $viewing->schedule_at->format('H:i') }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $viewing->schedule_at->format('d/m/Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="customer-info">
                                                    <div class="customer-avatar">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="customer-details">
                                                        <div class="customer-name">{{ $viewing->customer_name }}</div>
                                                        <div class="customer-meta">
                                                            @if($viewing->tenant)
                                                                {{ $viewing->tenant->email }}
                                                            @else
                                                                {{ $viewing->lead_phone }}
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="customer-type-badge {{ $viewing->customer_type }}">
                                                    <i class="fas {{ $viewing->customer_type === 'tenant' ? 'fa-user' : 'fa-user-plus' }}"></i>
                                                    {{ $viewing->customer_type_text }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $viewing->property->name }}</strong>
                                                </div>
                                            </td>
                                            <td>
                                                @if($viewing->unit)
                                                    <span class="badge bg-light text-dark">
                                                        {{ $viewing->unit->code }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $viewing->status_badge_class }}">
                                                    {{ $viewing->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.viewings.show', $viewing->id) }}" 
                                                       class="btn btn-outline-primary btn-action" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($viewing->status === 'requested')
                                                        <form action="{{ route('agent.viewings.confirm', $viewing->id) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-outline-success btn-action" title="Xác nhận">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    @elseif($viewing->status === 'confirmed')
                                                        <button type="button" class="btn btn-outline-primary btn-action" 
                                                                title="Đánh dấu hoàn thành" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#markDoneModal{{ $viewing->id }}">
                                                            <i class="fas fa-check-circle"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có lịch hẹn nào hôm nay</h5>
                            <p class="text-muted">Bạn có thể tạo lịch hẹn mới hoặc xem tất cả lịch hẹn</p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                                </a>
                                <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list me-1"></i>Tất cả lịch hẹn
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mark Done Modals -->
@foreach($viewings as $viewing)
    @if($viewing->status === 'confirmed')
        <div class="modal fade" id="markDoneModal{{ $viewing->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="{{ route('agent.viewings.mark-done', $viewing->id) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title">Đánh dấu hoàn thành - {{ $viewing->customer_name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="result_note{{ $viewing->id }}" class="form-label">Kết quả buổi xem</label>
                                <textarea class="form-control" id="result_note{{ $viewing->id }}" name="result_note" rows="4" 
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
    @endif
@endforeach
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="{{ asset('assets/js/agent/viewings.js') }}"></script>
@endpush

@push('styles')
<link href="{{ asset('assets/css/notifications.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/agent/viewings.css') }}" rel="stylesheet">
@endpush
