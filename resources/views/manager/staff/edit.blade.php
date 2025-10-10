@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa nhân viên')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Chỉnh sửa nhân viên</h1>
                <p class="text-muted">Cập nhật thông tin nhân viên: {{ $staff->full_name }}</p>
            </div>
            <div>
                <a href="{{ route('manager.staff.show', $staff->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
                <a href="{{ route('manager.staff.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <form action="{{ route('manager.staff.update', $staff->id) }}" method="POST" id="editStaffForm">
            @csrf
            @method('PUT')
            
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-8">
                    <!-- Basic Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin cơ bản</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" name="full_name" class="form-control @error('full_name') is-invalid @enderror" value="{{ old('full_name', $staff->full_name) }}" required>
                                    @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $staff->email) }}" required>
                                    @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Số điện thoại</label>
                                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $staff->phone) }}">
                                    @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mật khẩu mới</label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                                    @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Để trống nếu không muốn thay đổi mật khẩu</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                                    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn vai trò --</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id', $staff->organizationRoles->first()?->id) == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" class="form-select" required>
                                        <option value="1" {{ old('status', $staff->status) == 1 ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ old('status', $staff->status) == 0 ? 'selected' : '' }}>Tạm ngưng</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Property Assignment -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-building"></i> Gắn bất động sản</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Chọn bất động sản quản lý</label>
                                <select name="properties[]" id="propertySelect" class="form-select" multiple>
                                    @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ in_array($property->id, old('properties', $assignedPropertyIds)) ? 'selected' : '' }}>
                                        {{ $property->name }} @if($property->location) - {{ $property->location->city }} @endif
                                    </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Giữ Ctrl để chọn nhiều bất động sản</small>
                            </div>

                            <div id="selectedProperties" class="mt-3">
                                <!-- Selected properties will be displayed here -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-4">
                    <!-- Quick Actions -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-cogs"></i> Thao tác</h5>
                        </div>
                        <div class="card-body">
                            <button type="submit" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-save"></i> Cập nhật nhân viên
                            </button>
                            <a href="{{ route('manager.staff.show', $staff->id) }}" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                            <a href="{{ route('manager.staff.index') }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times"></i> Hủy bỏ
                            </a>
                        </div>
                    </div>

                    <!-- Current Info -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-secondary text-white">
                            <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <small class="text-muted">Ngày tạo:</small>
                                <div>{{ $staff->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">Cập nhật lần cuối:</small>
                                <div>{{ $staff->updated_at->format('d/m/Y H:i') }}</div>
                            </div>
                            <div class="mb-2">
                                <small class="text-muted">BĐS đang quản lý:</small>
                                <div><span class="badge bg-primary">{{ $staff->assignedProperties->count() }} BĐS</span></div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Card -->
                    <div class="card shadow-sm border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lưu ý</h6>
                        </div>
                        <div class="card-body">
                            <ul class="small mb-0">
                                <li>Gắn BĐS mới sẽ thay thế danh sách cũ</li>
                                <li>Thay đổi vai trò có thể ảnh hưởng quyền hạn</li>
                                <li>Mật khẩu để trống = giữ nguyên</li>
                                <li>Quản lý hợp đồng lương tại mục riêng</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--multiple {
    min-height: 100px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for property selection
    $('#propertySelect').select2({
        placeholder: 'Chọn bất động sản...',
        allowClear: true,
        width: '100%'
    });

    // Handle form submission
    document.getElementById('editStaffForm').addEventListener('submit', function(e) {
        e.preventDefault();

        if (window.Preloader) {
            window.Preloader.show();
        }

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json().catch(() => ({ success: false, message: 'Lỗi phản hồi từ server' })))
        .then(data => {
            if (window.Preloader) {
                window.Preloader.hide();
            }

            if (data.success || data.redirect) {
                Notify.success('Cập nhật nhân viên thành công!');
                setTimeout(() => {
                    window.location.href = data.redirect || '{{ route("manager.staff.show", $staff->id) }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể cập nhật nhân viên. Vui lòng kiểm tra lại thông tin.');
            }
        })
        .catch(error => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
            console.error('Error:', error);
            Notify.error('Đã xảy ra lỗi khi cập nhật nhân viên. Vui lòng thử lại.');
        });
    });

    // Display selected properties
    $('#propertySelect').on('change', function() {
        const selectedOptions = $(this).find(':selected');
        const container = document.getElementById('selectedProperties');
        
        if (selectedOptions.length > 0) {
            let html = '<div class="alert alert-info"><strong>Đã chọn:</strong><ul class="mb-0 mt-2">';
            selectedOptions.each(function() {
                html += `<li>${$(this).text()}</li>`;
            });
            html += '</ul></div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '';
        }
    });

    // Initialize selected properties display
    $('#propertySelect').trigger('change');
});
</script>
@endpush
@endsection

