@extends('layouts.manager_dashboard')

@section('title', 'Sửa thông tin người dùng')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-user-edit me-2"></i>Sửa thông tin người dùng
                        </h1>
                        <p class="text-muted mb-0">Cập nhật thông tin tài khoản: {{ $user->full_name }}</p>
                    </div>
                    <a href="{{ route('manager.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Thông tin người dùng
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="userForm" action="{{ route('manager.users.update', $user->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="full_name" name="full_name" 
                                               value="{{ old('full_name', $user->full_name) }}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="{{ old('email', $user->email) }}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input type="text" class="form-control" id="phone" name="phone" 
                                               value="{{ old('phone', $user->phone) }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Mật khẩu mới</label>
                                        <input type="password" class="form-control" id="password" name="password">
                                        <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu</div>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="role_id" class="form-label">Vai trò <span class="text-danger">*</span></label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <option value="">Chọn vai trò</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" 
                                                        {{ old('role_id', $user->userRoles->first()?->id) == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="status" class="form-label">Trạng thái</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="1" {{ old('status', $user->status) == '1' ? 'selected' : '' }}>Hoạt động</option>
                                            <option value="0" {{ old('status', $user->status) == '0' ? 'selected' : '' }}>Tạm ngưng</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('manager.users.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- User Info Card -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin hiện tại
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                <i class="fas fa-user text-white fa-2x"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $user->full_name }}</h6>
                                <small class="text-muted">{{ $user->email }}</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6>Vai trò hiện tại:</h6>
                            @if($user->userRoles->count() > 0)
                                @foreach($user->userRoles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Chưa có vai trò</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <h6>Trạng thái:</h6>
                            @if($user->status)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-warning">Tạm ngưng</span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <h6>Thông tin khác:</h6>
                            <ul class="list-unstyled mb-0">
                                <li><small class="text-muted">ID: #{{ $user->id }}</small></li>
                                <li><small class="text-muted">Tạo lúc: {{ $user->created_at->format('d/m/Y H:i') }}</small></li>
                                @if($user->last_login_at)
                                    <li><small class="text-muted">Đăng nhập cuối: {{ $user->last_login_at->format('d/m/Y H:i') }}</small></li>
                                @endif
                            </ul>
                        </div>

                        @if($user->id === auth()->id())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Bạn đang chỉnh sửa tài khoản của chính mình
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('userForm');
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
            Notify.error('Lỗi bảo mật: Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.', 'Lỗi bảo mật!');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
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
                setTimeout(() => {
                    window.location.href = '{{ route("manager.users.index") }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể cập nhật người dùng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
});
</script>
@endpush
@endsection
