@extends('layouts.app')

@section('title', 'Chi tiết ticket')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/tenant/tickets.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/tenant/tickets.js') }}?v={{ time() }}"></script>
<script>
// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    TicketModule.initShow({{ $ticket->id }});
});
</script>
@endpush

@section('content')
<div class="ticket-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
            </ol>
        </nav>

        <!-- Ticket Header -->
        <div class="ticket-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="ticket-title">
                            <h1 class="page-title">{{ $ticket->title }}</h1>
                            <div class="ticket-meta">
                                <span class="ticket-id">#{{ $ticket->id }}</span>
                                <span class="ticket-date">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Tạo lúc {{ $ticket->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="ticket-badges">
                        <span class="priority-badge priority-{{ $ticket->priority }}">
                            {{ $ticket->priority_label }}
                        </span>
                        <span class="status-badge status-{{ $ticket->status }}">
                            {{ $ticket->status_label }}
                        </span>
                    </div>
                    <div class="ticket-actions mt-2">
                        <a href="#" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-comments me-1"></i>Chat với Agent
                        </a>
                        @if($ticket->status === 'open')
                            <a href="{{ route('tenant.tickets.edit', $ticket->id) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-edit me-1"></i>Chỉnh sửa
                            </a>
                        @endif
                        <a href="{{ route('tenant.tickets.index') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ticket Content -->
        <div class="ticket-content">
            <div class="row">
                <!-- Main Content -->
                <div class="col-md-8">
                    <!-- Description -->
                    <div class="content-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-align-left me-2"></i>Mô tả chi tiết
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="description-content">
                                {!! nl2br(e($ticket->description)) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Ticket Logs (if any) -->
                    @if($ticket->logs && $ticket->logs->count() > 0)
                        <div class="content-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-history me-2"></i>Lịch sử xử lý
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    @foreach($ticket->logs as $log)
                                        <div class="timeline-item">
                                            <div class="timeline-marker">
                                                {{-- <i class="fas fa-circle"></i> --}}
                                            </div>
                                            <div class="timeline-content">
                                                <div class="timeline-header">
                                                    <span class="timeline-title">{{ $log->action }}</span>
                                                    <span class="timeline-date">{{ $log->created_at ? $log->created_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                                </div>
                                                @if($log->detail)
                                                    <div class="timeline-description">
                                                        {{ $log->detail }}
                                                    </div>
                                                @endif
                                                <div class="timeline-user">
                                                    <i class="fas fa-user me-1"></i>
                                                    {{ $log->actor_name ?: 'Hệ thống' }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="content-card">
                            <div class="card-header">
                                <h5 class="card-title">
                                    <i class="fas fa-history me-2"></i>Lịch sử xử lý
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-3"></i>
                                    <p>Chưa có lịch sử xử lý nào</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="col-md-4">
                    <!-- Ticket Information -->
                    <div class="sidebar-card">
                        <div class="card-header">
                            <h5 class="sidebar-title">
                                <i class="fas fa-info-circle me-2"></i>Thông tin ticket
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="sidebar-content">
                                <div class="info-item">
                                    <label>Trạng thái:</label>
                                    <span class="status-badge status-{{ $ticket->status }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Độ ưu tiên:</label>
                                    <span class="priority-badge priority-{{ $ticket->priority }}">
                                        {{ $ticket->priority_label }}
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Ngày tạo:</label>
                                    <span>{{ $ticket->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Cập nhật cuối:</label>
                                    <span>{{ $ticket->updated_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Người tạo:</label>
                                    <span>{{ $ticket->created_by_name ?: 'Hệ thống' }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Người xử lý:</label>
                                    <span>{{ $ticket->assigned_to_name ?: 'Chưa phân công' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Property Information -->
                    <div class="sidebar-card">
                        <div class="card-header">
                            <h5 class="sidebar-title">
                                <i class="fas fa-home me-2"></i>Thông tin phòng
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="sidebar-content">
                                <div class="info-item">
                                    <label>Tòa nhà:</label>
                                    <span>{{ $ticket->property_name ?: 'Chưa xác định' }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Địa chỉ:</label>
                                    <span>
                                        @if($ticket->location_address)
                                            {{ $ticket->location_address }}
                                            @if($ticket->location2025_address && $ticket->location2025_address != $ticket->location_address)
                                                <br><small class="text-muted">(2025: {{ $ticket->location2025_address }})</small>
                                            @endif
                                        @elseif($ticket->location2025_address)
                                            {{ $ticket->location2025_address }}
                                        @else
                                            Chưa xác định
                                        @endif
                                    </span>
                                </div>
                                <div class="info-item">
                                    <label>Phòng:</label>
                                    <span>{{ $ticket->unit_name ?: 'Chưa xác định' }}</span>
                                </div>
                                <div class="info-item">
                                    <label>Hợp đồng:</label>
                                    <span>{{ $ticket->lease_contract_number ?: 'Chưa liên kết' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if(in_array($ticket->status, ['open', 'in_progress']))
                        <div class="sidebar-card">
                            <div class="card-header">
                                <h5 class="sidebar-title">
                                    <i class="fas fa-tools me-2"></i>Hành động
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="action-buttons">
                                    <a href="{{ route('tenant.tickets.edit', $ticket->id) }}" class="btn btn-primary btn-sm w-100 mb-2">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa ticket
                                    </a>
                                    <form method="POST" action="{{ route('tenant.tickets.destroy', $ticket->id) }}" 
                                          class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                            <i class="fas fa-ban me-2"></i>Hủy ticket
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Status Information -->
                    <div class="sidebar-card">
                        <div class="card-header">
                            <h5 class="sidebar-title">
                                <i class="fas fa-info-circle me-2"></i>Trạng thái
                            </h5>
                        </div>
                        <div class="card-body">
                            @if($ticket->status === 'open')
                                <div class="alert alert-info">
                                    <i class="fas fa-clock me-2"></i>
                                    Ticket đang chờ được xử lý. Bạn có thể chỉnh sửa thông tin.
                                </div>
                            @elseif($ticket->status === 'in_progress')
                                <div class="alert alert-warning">
                                    <i class="fas fa-cog me-2"></i>
                                    Ticket đang được xử lý. Không thể chỉnh sửa.
                                </div>
                            @elseif($ticket->status === 'resolved')
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    Ticket đã được giải quyết. Vui lòng kiểm tra và phản hồi.
                                </div>
                            @elseif($ticket->status === 'closed')
                                <div class="alert alert-secondary">
                                    <i class="fas fa-archive me-2"></i>
                                    Ticket đã được đóng. Không thể chỉnh sửa.
                                </div>
                            @elseif($ticket->status === 'cancelled')
                                <div class="alert alert-danger">
                                    <i class="fas fa-times-circle me-2"></i>
                                    Ticket đã bị hủy. Không thể chỉnh sửa.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection