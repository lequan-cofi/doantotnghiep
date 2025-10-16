@extends('layouts.app')

@section('title', 'Ticket của tôi')

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
    TicketModule.initIndex();
});
</script>
@endpush

@section('content')
<div class="tickets-container">
    <div class="container">
        <!-- Page Header -->
        <div class="tickets-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Ticket của tôi</h1>
                            <p class="page-subtitle">Quản lý và theo dõi các yêu cầu sửa chữa/bảo trì</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('tenant.tickets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo ticket mới
                    </a>
                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary ms-2">
                        <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats['open'] }}</div>
                            <div class="stat-label">Đang mở</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats['in_progress'] }}</div>
                            <div class="stat-label">Đang xử lý</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats['resolved'] }}</div>
                            <div class="stat-label">Đã giải quyết</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">{{ $stats['total'] }}</div>
                            <div class="stat-label">Tổng cộng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
            <div class="row">
                <div class="col-md-12">
                    <form method="GET" action="{{ route('tenant.tickets.index') }}" class="filters-form">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search" class="form-label">Tìm kiếm</label>
                                    <input type="text" class="form-control" id="search" name="search" 
                                           value="{{ request('search') }}" placeholder="Tìm theo tiêu đề, mô tả, ID...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="status" class="form-label">Trạng thái</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Tất cả</option>
                                        <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Đang mở</option>
                                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Đang xử lý</option>
                                        <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Đã giải quyết</option>
                                        <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Đã đóng</option>
                                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="priority" class="form-label">Độ ưu tiên</label>
                                    <select class="form-select" id="priority" name="priority">
                                        <option value="all" {{ request('priority') == 'all' ? 'selected' : '' }}>Tất cả</option>
                                        <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Thấp</option>
                                        <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Trung bình</option>
                                        <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Cao</option>
                                        <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-search me-2"></i>Lọc
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tickets List -->
        <div class="tickets-list">
            @if($tickets->count() > 0)
                <div class="row">
                    @foreach($tickets as $ticket)
                        <div class="col-md-12 mb-4">
                            <div class="ticket-card">
                                <div class="ticket-header">
                                    <div class="ticket-info">
                                        <div class="ticket-title">
                                            <h5 class="mb-1">
                                                <a href="{{ route('tenant.tickets.show', $ticket->id) }}" class="text-decoration-none">
                                                    {{ $ticket->title }}
                                                </a>
                                            </h5>
                                            <div class="ticket-meta">
                                                <span class="ticket-id">#{{ $ticket->id }}</span>
                                                <span class="ticket-date">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    {{ $ticket->created_at->format('d/m/Y H:i') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ticket-badges">
                                        <span class="priority-badge priority-{{ $ticket->priority }}">
                                            {{ $ticket->priority_label }}
                                        </span>
                                        <span class="status-badge status-{{ $ticket->status }}">
                                            {{ $ticket->status_label }}
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="ticket-body">
                                    <div class="ticket-description">
                                        <p class="mb-2">{{ Str::limit($ticket->description, 150) }}</p>
                                        @if($ticket->image)
                                            <div class="mt-2">
                                                <i class="fas fa-image text-info" title="Có hình ảnh đính kèm"></i>
                                                <small class="text-muted ms-1">Có hình ảnh</small>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    <div class="ticket-details">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label>Địa chỉ:</label>
                                                    <div class="address-info">
                                                        <div class="address-item">
                                                            <span class="address-label">Tòa nhà:</span>
                                                            <span class="address-value">{{ $ticket->property_name ?: 'Chưa xác định' }}</span>
                                                        </div>
                                                        <div class="address-item">
                                                            <span class="address-label">Địa chỉ:</span>
                                                            <span class="address-value">
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
                                                        <div class="address-item">
                                                            <span class="address-label">Phòng:</span>
                                                            <span class="address-value">{{ $ticket->unit_name ?: 'Chưa xác định' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label>Người xử lý:</label>
                                                    <span>{{ $ticket->assigned_to_name ?: 'Chưa phân công' }}</span>
                                                </div>
                                                <div class="detail-item">
                                                    <label>Cập nhật cuối:</label>
                                                    <span>{{ $ticket->updated_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="ticket-actions">
                                    <a href="{{ route('tenant.tickets.show', $ticket->id) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                                    </a>
                                    @if(in_array($ticket->status, ['open', 'in_progress']))
                                        <a href="{{ route('tenant.tickets.edit', $ticket->id) }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit me-1"></i>Chỉnh sửa
                                        </a>
                                        <form method="POST" action="{{ route('tenant.tickets.destroy', $ticket->id) }}" 
                                              class="d-inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-warning btn-sm">
                                                <i class="fas fa-ban me-1"></i>Hủy
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="pagination-section">
                    {{ $tickets->appends(request()->query())->links('vendor.pagination.custom') }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <h3>Chưa có ticket nào</h3>
                    <p>Bạn chưa tạo ticket nào. Hãy tạo ticket đầu tiên để báo cáo sự cố hoặc yêu cầu sửa chữa.</p>
                    <a href="{{ route('tenant.tickets.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Tạo ticket đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection