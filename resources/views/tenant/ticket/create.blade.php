@extends('layouts.app')

@section('title', 'Tạo ticket mới')

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
    TicketModule.initCreate();
});
</script>
@endpush

@section('content')
<div class="ticket-create-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.tickets.index') }}">Tickets</a></li>
                <li class="breadcrumb-item active">Tạo mới</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center">
                <div class="header-content">
                    <div class="header-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div>
                        <h1 class="page-title">Tạo Ticket Mới</h1>
                        <p class="page-subtitle">Báo cáo sự cố hoặc yêu cầu sửa chữa cho phòng thuê của bạn</p>
                    </div>
                </div>
                <a href="{{ route('tenant.tickets.index') }}" class="btn btn-outline-secondary">
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
                        <form method="POST" action="{{ route('tenant.tickets.store') }}" id="ticketForm">
                            @csrf
                            
                            <!-- Basic Information Section -->
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Thông tin cơ bản
                            </div>

                            <!-- Lease Selection -->
                            <div class="form-group">
                                <label for="lease_id" class="form-label required">
                                    Hợp đồng
                                </label>
                                <select class="form-select @error('lease_id') is-invalid @enderror" 
                                        id="lease_id" name="lease_id" required>
                                    <option value="">-- Chọn hợp đồng --</option>
                                    @foreach($leases as $lease)
                                        <option value="{{ $lease->id }}" 
                                                data-unit-id="{{ $lease->unit_id }}"
                                                data-unit-code="{{ $lease->unit->code ?? '' }}"
                                                data-property-name="{{ $lease->unit->property->name ?? '' }}"
                                                {{ old('lease_id') == $lease->id ? 'selected' : '' }}>
                                            {{ $lease->unit->property->name ?? 'N/A' }} - Phòng {{ $lease->unit->code ?? 'N/A' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('lease_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Chọn hợp đồng thuê của bạn để xác định phòng cần sửa chữa
                                </small>
                            </div>

                            <!-- Hidden Unit ID -->
                            <input type="hidden" id="unit_id" name="unit_id" value="{{ old('unit_id') }}">

                            <!-- Unit Info Display -->
                            <div id="unitInfo" class="unit-info-card d-none">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-home me-2 text-primary"></i>
                                    <strong>Thông tin phòng đã chọn:</strong>
                                </div>
                                <div id="unitInfoContent"></div>
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
                                       value="{{ old('title') }}" 
                                       placeholder="VD: Vòi nước bị hỏng, Điện bị cúp, Cửa không khóa được..." 
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
                                        id="priority" name="priority" required>
                                    <option value="">-- Chọn độ ưu tiên --</option>
                                    <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>
                                        🟢 Thấp - Không cấp bách
                                    </option>
                                    <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>
                                        🟡 Trung bình - Cần xử lý sớm
                                    </option>
                                    <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>
                                        🟠 Cao - Ảnh hưởng sinh hoạt
                                    </option>
                                    <option value="urgent" {{ old('priority') == 'urgent' ? 'selected' : '' }}>
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
                                          placeholder="Mô tả chi tiết về sự cố hoặc yêu cầu sửa chữa. Ví dụ:&#10;- Thời gian xảy ra sự cố&#10;- Mức độ nghiêm trọng&#10;- Các thiết bị bị ảnh hưởng&#10;- Yêu cầu xử lý cụ thể..." 
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    Mô tả càng chi tiết càng giúp chúng tôi xử lý nhanh hơn
                                </small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex gap-3 justify-content-end">
                                <a href="{{ route('tenant.tickets.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Gửi Ticket
                                </button>
                            </div>
                        </form>
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
                                <li>Chọn hợp đồng để tự động xác định phòng</li>
                                <li>Mô tả rõ ràng vấn đề để dễ xử lý</li>
                                <li>Ticket sẽ được gửi đến bộ phận quản lý</li>
                                <li>Bạn có thể theo dõi tiến độ trong danh sách</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Tips Card -->
                <div class="sidebar-card">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Mẹo hay</h6>
                    </div>
                    <div class="card-body">
                        <div class="help-item">
                            <h6><i class="fas fa-camera me-2"></i>Chụp ảnh:</h6>
                            <p class="small mb-0">Nếu có thể, hãy chụp ảnh sự cố để mô tả rõ hơn</p>
                        </div>
                        
                        <div class="help-item">
                            <h6><i class="fas fa-clock me-2"></i>Thời gian:</h6>
                            <p class="small mb-0">Ghi rõ thời gian xảy ra sự cố nếu biết</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection