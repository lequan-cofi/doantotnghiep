@extends('layouts.agent_dashboard')

@section('title', 'Danh sách lịch hẹn')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-calendar-alt me-2"></i>Danh sách lịch hẹn
                    </h1>
                    <p class="text-muted mb-0">Quản lý tất cả lịch hẹn xem phòng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('agent.viewings.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Tên khách, SĐT, email...">
                        </div>
                        <div class="col-md-3">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Tất cả trạng thái</option>
                                <option value="requested" {{ request('status') == 'requested' ? 'selected' : '' }}>Chờ xác nhận</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Hoàn thành</option>
                                <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>Không đến</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="property_id" class="form-label">Bất động sản</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">Tất cả BĐS</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ request('property_id') == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Viewings Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Danh sách lịch hẹn
                        <span class="badge bg-primary ms-2">{{ $viewings->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($viewings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-viewings table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Khách hàng</th>
                                        <th>Loại</th>
                                        <th>Bất động sản</th>
                                        <th>Phòng</th>
                                        <th>Thời gian hẹn</th>
                                        <th>Trạng thái</th>
                                        <th>Agent</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($viewings as $viewing)
                                        <tr>
                                            <td>
                                                <span class="text-muted">#{{ $viewing->id }}</span>
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
                                                                @if($viewing->lead_email)
                                                                    • {{ $viewing->lead_email }}
                                                                @endif
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
                                                <div>
                                                    <strong>{{ $viewing->schedule_at->format('d/m/Y') }}</strong>
                                                </div>
                                                <small class="text-muted">{{ $viewing->schedule_at->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $viewing->status_badge_class }}">
                                                    {{ $viewing->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($viewing->agent)
                                                    <div>
                                                        <strong>{{ $viewing->agent->full_name }}</strong>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.viewings.show', $viewing->id) }}" 
                                                       class="btn btn-outline-primary btn-action" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('agent.viewings.edit', $viewing->id) }}" 
                                                       class="btn btn-outline-warning btn-action" title="Sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    @if(in_array($viewing->status, ['requested', 'confirmed']))
                                                        <button type="button" 
                                                                class="btn btn-outline-danger btn-action" 
                                                                title="Xóa"
                                                                onclick="confirmDelete({{ $viewing->id }}, '{{ $viewing->customer_name }}')">
                                                            <i class="fas fa-trash"></i>
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
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có lịch hẹn nào</h5>
                            <p class="text-muted">Bắt đầu tạo lịch hẹn đầu tiên của bạn</p>
                            <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                            </a>
                        </div>
                    @endif
                </div>
            </div>
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
