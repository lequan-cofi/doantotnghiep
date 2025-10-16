@extends('layouts.app')

@section('title', 'Chỉnh sửa ticket #' . $ticket->id)

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
    TicketModule.initEdit({{ $ticket->id }}, '{{ $ticket->status }}');
});
</script>
@endpush

@section('content')
<div class="ticket-edit-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.tickets.show', $ticket->id) }}">#{{ $ticket->id }}</a></li>
                <li class="breadcrumb-item active">Chỉnh sửa</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div>
                        <h1 class="page-title">Chỉnh sửa Ticket #{{ $ticket->id }}</h1>
                        <p class="page-subtitle">Cập nhật thông tin ticket của bạn</p>
                    </div>
                </div>
                <a href="{{ route('tenant.tickets.show', $ticket->id) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
        </div>

        <!-- Success Messages -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <!-- Status Warning -->
        @if($ticket->status !== 'open')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Lưu ý:</strong> Ticket này đang ở trạng thái 
            <span class="status-badge status-{{ $ticket->status }}">{{ $ticket->status_label }}</span>. 
            Chỉ có thể chỉnh sửa ticket khi đang ở trạng thái "Đang mở".
        </div>
        @endif

        <!-- Error Messages -->
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Có lỗi xảy ra:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <!-- Main Form Card -->
                <div class="form-card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('tenant.tickets.update', $ticket->id) }}" id="ticketForm" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information Section -->
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Thông tin cơ bản
                            </div>

                            <!-- Current Lease Info (Read-only) -->
                            <div class="form-group">
                                <label class="form-label">Hợp đồng / Phòng</label>
                                <input type="text" class="form-control" 
                                       value="{{ $ticket->lease->unit->property->name ?? 'N/A' }} - Phòng {{ $ticket->lease->unit->code ?? 'N/A' }}" 
                                       readonly disabled>
                                <small class="form-text text-muted">
                                    <i class="fas fa-lock me-1"></i>
                                    Không thể thay đổi hợp đồng sau khi đã tạo ticket
                                </small>
                            </div>

                            <!-- Title -->
                            <div class="form-group">
                                <label for="title" class="form-label required">
                                    Tiêu đề
                                </label>
                                <input type="text" 
                                       class="form-control @error('title') is-invalid @enderror" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title', $ticket->title) }}" 
                                       placeholder="VD: Vòi nước bị hỏng, Điện bị cúp..."
                                       {{ $ticket->status !== 'open' ? 'readonly' : '' }}
                                       required>
                                @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Priority -->
                            <div class="form-group">
                                <label for="priority" class="form-label required">
                                    Độ ưu tiên
                                </label>
                                <select class="form-select @error('priority') is-invalid @enderror" 
                                        id="priority" 
                                        name="priority"
                                        {{ $ticket->status !== 'open' ? 'disabled' : '' }}
                                        required>
                                    <option value="">-- Chọn độ ưu tiên --</option>
                                    <option value="low" {{ old('priority', $ticket->priority) == 'low' ? 'selected' : '' }}>
                                        🟢 Thấp - Không cấp bách
                                    </option>
                                    <option value="medium" {{ old('priority', $ticket->priority) == 'medium' ? 'selected' : '' }}>
                                        🟡 Trung bình - Cần xử lý sớm
                                    </option>
                                    <option value="high" {{ old('priority', $ticket->priority) == 'high' ? 'selected' : '' }}>
                                        🟠 Cao - Ảnh hưởng sinh hoạt
                                    </option>
                                    <option value="urgent" {{ old('priority', $ticket->priority) == 'urgent' ? 'selected' : '' }}>
                                        🔴 Khẩn cấp - Cần xử lý ngay
                                    </option>
                                </select>
                                @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Description Section -->
                            <div class="section-title">
                                <i class="fas fa-align-left"></i>
                                Mô tả chi tiết
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description" class="form-label required">
                                    Mô tả sự cố/yêu cầu
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                          id="description" 
                                          name="description" 
                                          rows="6" 
                                          placeholder="Mô tả chi tiết về sự cố hoặc yêu cầu sửa chữa..."
                                          {{ $ticket->status !== 'open' ? 'readonly' : '' }}
                                          required>{{ old('description', $ticket->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Mô tả càng chi tiết càng giúp chúng tôi xử lý nhanh hơn
                                </small>
                            </div>

                            <!-- Image Upload Section -->
                            <div class="section-title">
                                <i class="fas fa-image"></i>
                                Hình ảnh đính kèm
                            </div>

                            <div class="form-group">
                                <label for="image" class="form-label">
                                    Hình ảnh minh họa
                                </label>
                                
                                @if($ticket->image)
                                    <div class="mb-3">
                                        <img src="{{ $ticket->image_url }}" alt="Current image" class="img-thumbnail" style="max-width: 200px; max-height: 200px;">
                                        <div class="form-text">Ảnh hiện tại</div>
                                    </div>
                                @endif
                                
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*" {{ $ticket->status !== 'open' ? 'disabled' : '' }}>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Định dạng: JPEG, PNG, JPG, GIF. Kích thước tối đa: 2MB. Để trống nếu không muốn thay đổi ảnh.
                                </small>
                            </div>

                            <!-- Submit Buttons -->
                            @if($ticket->status === 'open')
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('tenant.tickets.show', $ticket->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-save me-2"></i>Lưu Thay Đổi
                                </button>
                            </div>
                            @else
                            <div class="alert alert-info mb-0">
                                <i class="fas fa-lock me-2"></i>
                                Ticket này không thể chỉnh sửa vì đã chuyển sang trạng thái 
                                <span class="status-badge status-{{ $ticket->status }}">{{ $ticket->status_label }}</span>.
                            </div>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Ticket Info Card -->
                <div class="form-card">
                    <div class="card-header bg-light">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Thông tin ticket</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Trạng thái:</strong> 
                                    <span class="status-badge status-{{ $ticket->status }}">
                                        {{ $ticket->status_label }}
                                    </span>
                                </p>
                                <p class="mb-2"><strong>Ngày tạo:</strong> {{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-2"><strong>Người tạo:</strong> {{ $ticket->createdBy->name ?? 'N/A' }}</p>
                                <p class="mb-2"><strong>Được gán cho:</strong> {{ $ticket->assignedTo->name ?? 'Chưa gán' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Help Card -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-question-circle me-2"></i>Hướng dẫn</h6>
                    </div>
                    <div class="card-body">
                        <div class="help-item">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Độ ưu tiên:</h6>
                            <ul class="help-list">
                                <li><strong>🟢 Thấp:</strong> Sự cố nhỏ, không ảnh hưởng sinh hoạt</li>
                                <li><strong>🟡 Trung bình:</strong> Sự cố thông thường cần sửa chữa</li>
                                <li><strong>🟠 Cao:</strong> Sự cố ảnh hưởng sinh hoạt hàng ngày</li>
                                <li><strong>🔴 Khẩn cấp:</strong> Sự cố nguy hiểm, cần xử lý ngay</li>
                            </ul>
                        </div>
                        
                        <div class="help-item">
                            <h6><i class="fas fa-info-circle me-2"></i>Lưu ý:</h6>
                            <ul class="help-list">
                                <li>Chỉ có thể sửa khi ticket đang "Đang mở"</li>
                                <li>Không thể thay đổi hợp đồng/phòng</li>
                                <li>Cập nhật rõ ràng để dễ xử lý</li>
                                <li>Thay đổi sẽ được ghi nhận trong lịch sử</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- History Card -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-history me-2"></i>Lịch sử</h6>
                    </div>
                    <div class="card-body">
                        <div class="help-item">
                            <h6><i class="fas fa-calendar-plus me-2"></i>Tạo:</h6>
                            <p class="small mb-2">{{ $ticket->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div class="help-item">
                            <h6><i class="fas fa-calendar-edit me-2"></i>Cập nhật:</h6>
                            <p class="small mb-0">{{ $ticket->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection