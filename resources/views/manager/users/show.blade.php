@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết người dùng')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-user me-2"></i>Chi tiết người dùng
                        </h1>
                        <p class="text-muted mb-0">Thông tin chi tiết về tài khoản: {{ $user->full_name }}</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('manager.users.edit', $user->id) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i>Sửa
                        </a>
                        <a href="{{ route('manager.users.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Details -->
        <div class="row">
            <!-- User Profile Card -->
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <div class="avatar-xl bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                            <i class="fas fa-user text-white fa-3x"></i>
                        </div>
                        <h4 class="mb-1">{{ $user->full_name }}</h4>
                        <p class="text-muted mb-2">{{ $user->email }}</p>
                        @if($user->phone)
                            <p class="text-muted mb-3">{{ $user->phone }}</p>
                        @endif
                        
                        <!-- Status Badge -->
                        <div class="mb-3">
                            @if($user->status)
                                <span class="badge bg-success fs-6">Hoạt động</span>
                            @else
                                <span class="badge bg-warning fs-6">Tạm ngưng</span>
                            @endif
                        </div>

                        <!-- Role Badges -->
                        <div class="mb-3">
                            <h6>Vai trò:</h6>
                            @if($user->userRoles->count() > 0)
                                @foreach($user->userRoles as $role)
                                    <span class="badge bg-info me-1">{{ $role->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">Chưa có vai trò</span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('manager.users.edit', $user->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i>Chỉnh sửa
                            </a>
                            @if($user->id !== auth()->id())
                                <button class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->full_name }}')">
                                    <i class="fas fa-trash me-1"></i>Xóa tài khoản
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Information -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin chi tiết
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ID người dùng:</label>
                                    <div class="p-2 bg-light rounded">
                                        <span class="badge bg-secondary">#{{ $user->id }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái:</label>
                                    <div class="p-2 bg-light rounded">
                                        @if($user->status)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-warning">Tạm ngưng</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Họ và tên:</label>
                                    <div class="p-2 bg-light rounded">{{ $user->full_name }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Email:</label>
                                    <div class="p-2 bg-light rounded">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Số điện thoại:</label>
                                    <div class="p-2 bg-light rounded">
                                        {{ $user->phone ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Vai trò:</label>
                                    <div class="p-2 bg-light rounded">
                                        @if($user->userRoles->count() > 0)
                                            @foreach($user->userRoles as $role)
                                                <span class="badge bg-info me-1">{{ $role->name }}</span>
                                            @endforeach
                                        @else
                                            <span class="text-muted">Chưa có vai trò</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ngày tạo:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $user->created_at->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Cập nhật cuối:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $user->updated_at->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->last_login_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Đăng nhập cuối:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-sign-in-alt me-1"></i>
                                        {{ $user->last_login_at->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($user->deleted_at)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ngày xóa:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-trash me-1"></i>
                                        {{ $user->deleted_at->format('d/m/Y H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- KYC Information -->
                @if($user->userProfile)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-id-card me-2"></i>Thông tin KYC (Know Your Customer)
                        </h6>
                        <span class="badge {{ $user->userProfile->isKycComplete() ? 'bg-success' : 'bg-warning' }}">
                            {{ $user->userProfile->getKycCompletionPercentage() }}% hoàn thành
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ngày sinh:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-calendar me-1"></i>
                                        {{ $user->userProfile->formatted_dob ?? 'Chưa cập nhật' }}
                                        @if($user->userProfile->dob)
                                            <small class="text-muted">({{ $user->userProfile->age }} tuổi)</small>
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Giới tính:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-{{ $user->userProfile->gender == 'male' ? 'mars' : ($user->userProfile->gender == 'female' ? 'venus' : 'genderless') }} me-1"></i>
                                        {{ $user->userProfile->gender_text }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Số CMND/CCCD:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-id-card me-1"></i>
                                        {{ $user->userProfile->id_number ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ngày cấp CMND/CCCD:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-calendar-check me-1"></i>
                                        {{ $user->userProfile->formatted_id_issued_at ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Địa chỉ thường trú:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        {{ $user->userProfile->address ?? 'Chưa cập nhật' }}
                                    </div>
                                </div>
                                @if($user->userProfile->note)
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ghi chú:</label>
                                    <div class="p-2 bg-light rounded">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        {{ $user->userProfile->note }}
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        @if(!$user->userProfile->isKycComplete())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Thông tin KYC chưa đầy đủ:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach($user->userProfile->getMissingKycFields() as $field)
                                    <li>{{ $field }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Thông tin KYC đã hoàn thành!</strong> Tài khoản đã được xác thực đầy đủ.
                        </div>
                        @endif
                    </div>
                </div>
                @else
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-id-card me-2"></i>Thông tin KYC (Know Your Customer)
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="text-muted">
                            <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                            <h5>Chưa có thông tin KYC</h5>
                            <p>Người dùng chưa cập nhật thông tin xác thực danh tính.</p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Role Details -->
                @if($user->userRoles->count() > 0)
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-user-tag me-2"></i>Chi tiết vai trò
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($user->userRoles as $role)
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">{{ $role->name }}</h6>
                                    <small class="text-muted">Mã vai trò: {{ $role->key_code }}</small>
                                </div>
                                <span class="badge bg-info">{{ $role->name }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
function deleteUser(id, name) {
    Notify.confirmDelete(`người dùng "${name}"`, () => {
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            Notify.error('Lỗi bảo mật: Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.', 'Lỗi bảo mật!');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }

        fetch(`/manager/users/${id}`, {
            method: 'DELETE',
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
                Notify.success(data.message, 'Đã xóa!');
                setTimeout(() => {
                    window.location.href = '{{ route("manager.users.index") }}';
                }, 1000);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể xóa người dùng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
}
</script>
@endpush
@endsection
