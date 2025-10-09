@extends('layouts.manager_dashboard')

@section('title', 'Sửa Loại Bất động sản')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Sửa Loại Bất động sản</h1>
                <p>Cập nhật thông tin loại bất động sản</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.property-types.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="propertyTypeForm" method="POST" action="{{ route('manager.property-types.update', $propertyType->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <!-- Basic Info -->
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Mã Code <span class="text-danger">*</span></label>
                            <input type="text" name="key_code" class="form-control" value="{{ $propertyType->key_code }}" required>
                            <small class="form-text text-muted">Mã định danh duy nhất cho loại bất động sản (không dấu, viết thường)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tên loại BĐS <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ $propertyType->name }}" required>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Icon Font Awesome</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-icons"></i></span>
                                <input type="text" name="icon" id="iconInput" class="form-control" value="{{ $propertyType->icon }}" placeholder="vd: fas fa-building, fas fa-home">
                                <span class="input-group-text" id="iconPreview">
                                    @if ($propertyType->icon)
                                        <i class="{{ $propertyType->icon }}"></i>
                                    @else
                                        <i class="fas fa-building"></i>
                                    @endif
                                </span>
                            </div>
                            <small class="form-text text-muted">Class icon Font Awesome (tùy chọn)</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái hoạt động</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $propertyType->status == 1 ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ $propertyType->status == 0 ? 'selected' : '' }}>Tạm ngưng</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label class="form-label">Mô tả</label>
                            <textarea name="description" class="form-control" rows="3">{{ $propertyType->description }}</textarea>
                        </div>
                    </div>

                    <!-- Status -->
                    <h5 class="mb-3"><i class="fas fa-toggle-on"></i> Trạng thái</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái hoạt động</label>
                            <select name="status" class="form-select">
                                <option value="1" {{ $propertyType->status == 1 ? 'selected' : '' }}>Hoạt động</option>
                                <option value="0" {{ $propertyType->status == 0 ? 'selected' : '' }}>Tạm ngưng</option>
                            </select>
                        </div>
                    </div>

                    <!-- Property Count Info -->
                    @if ($propertyType->properties_count > 0)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> Loại bất động sản này đang được sử dụng bởi {{ $propertyType->properties_count }} bất động sản.
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('manager.property-types.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Cập nhật
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('propertyTypeForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }

        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        
        if (!csrfToken) {
            console.error('CSRF token not found');
            showAlert('error', 'Lỗi bảo mật: Không tìm thấy CSRF token');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Thành công!');
                // Redirect to index after success
                setTimeout(() => {
                    window.location.href = '{{ route("manager.property-types.index") }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra khi cập nhật loại bất động sản: ' + error.message, 'Lỗi hệ thống!');
        })
        .finally(() => {
            // Hide preloader
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
});

function showAlert(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Insert alert at the top of content
    const content = document.getElementById('content');
    content.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        const alert = content.querySelector('.alert');
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    }, 5000);
}

// Icon preview
document.getElementById('iconInput').addEventListener('input', function() {
    const iconPreview = document.getElementById('iconPreview');
    const iconValue = this.value.trim();
    if (iconValue) {
        iconPreview.innerHTML = `<i class="${iconValue}"></i>`;
    } else {
        iconPreview.innerHTML = '<i class="fas fa-building"></i>';
    }
});
</script>
@endpush
@endsection
