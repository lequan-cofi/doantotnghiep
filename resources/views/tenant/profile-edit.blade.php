@extends('layouts.app')

@section('title', 'Chỉnh sửa hồ sơ')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Chỉnh sửa hồ sơ cá nhân</h1>
        <div>
            <a href="{{ route('tenant.profile') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
            <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-tachometer-alt me-1"></i>Về Dashboard
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('tenant.profile.update') }}">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>Thông tin cơ bản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">
                                        Họ và tên <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" 
                                           name="full_name" 
                                           value="{{ old('full_name', $user->full_name) }}" 
                                           required>
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="text" 
                                           class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" 
                                           name="phone" 
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="Nhập số điện thoại">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">
                                Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email', $user->email) }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- KYC Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-id-card me-2"></i>Thông tin KYC (Know Your Customer)
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Thông tin KYC giúp xác thực danh tính và tăng độ tin cậy cho tài khoản của bạn.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="dob" class="form-label">Ngày sinh</label>
                                    <input type="date" 
                                           class="form-control @error('dob') is-invalid @enderror" 
                                           id="dob" 
                                           name="dob" 
                                           value="{{ old('dob', $userProfile?->dob?->format('Y-m-d')) }}"
                                           max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Giới tính</label>
                                    <select class="form-select @error('gender') is-invalid @enderror" 
                                            id="gender" 
                                            name="gender">
                                        <option value="">Chọn giới tính</option>
                                        <option value="male" {{ old('gender', $userProfile?->gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                        <option value="female" {{ old('gender', $userProfile?->gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                        <option value="other" {{ old('gender', $userProfile?->gender) == 'other' ? 'selected' : '' }}>Khác</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="id_number" class="form-label">Số CMND/CCCD</label>
                                    <input type="text" 
                                           class="form-control @error('id_number') is-invalid @enderror" 
                                           id="id_number" 
                                           name="id_number" 
                                           value="{{ old('id_number', $userProfile?->id_number) }}"
                                           placeholder="Nhập số CMND/CCCD">
                                    @error('id_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_issued_at" class="form-label">Ngày cấp CMND/CCCD</label>
                                    <input type="date" 
                                           class="form-control @error('id_issued_at') is-invalid @enderror" 
                                           id="id_issued_at" 
                                           name="id_issued_at" 
                                           value="{{ old('id_issued_at', $userProfile?->id_issued_at?->format('Y-m-d')) }}"
                                           max="{{ date('Y-m-d') }}">
                                    @error('id_issued_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Địa chỉ thường trú</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" 
                                              name="address" 
                                              rows="3"
                                              placeholder="Nhập địa chỉ thường trú">{{ old('address', $userProfile?->address) }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="mb-3">
                                    <label for="note" class="form-label">Ghi chú</label>
                                    <textarea class="form-control @error('note') is-invalid @enderror" 
                                              id="note" 
                                              name="note" 
                                              rows="2"
                                              placeholder="Ghi chú thêm (tùy chọn)">{{ old('note', $userProfile?->note) }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Password Change -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lock me-2"></i>Thay đổi mật khẩu
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Để thay đổi mật khẩu, vui lòng nhập mật khẩu hiện tại và mật khẩu mới.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại</label>
                                    <input type="password" 
                                           class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" 
                                           name="current_password"
                                           placeholder="Nhập mật khẩu hiện tại">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Mật khẩu mới</label>
                                    <input type="password" 
                                           class="form-control @error('password') is-invalid @enderror" 
                                           id="password" 
                                           name="password"
                                           placeholder="Nhập mật khẩu mới">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Xác nhận mật khẩu mới</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password_confirmation" 
                                   name="password_confirmation"
                                   placeholder="Nhập lại mật khẩu mới">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('tenant.profile') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Cập nhật thông tin
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img class="rounded-circle mb-3" 
                         src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name ?? 'User') }}&background=ff6b35&color=fff&size=96" 
                         alt="avatar">
                    <h5 class="mb-0">{{ $user->full_name }}</h5>
                    <div class="text-muted small">{{ $user->email }}</div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-tachometer-alt me-1"></i>Vào Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>Hướng dẫn
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Cập nhật thông tin cơ bản
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Thay đổi mật khẩu (tùy chọn)
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            Email phải là duy nhất
                        </li>
                        <li>
                            <i class="fas fa-check text-success me-2"></i>
                            Mật khẩu tối thiểu 8 ký tự
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-radius: 8px;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 8px 8px 0 0 !important;
}

.form-control:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.alert {
    border: none;
    border-radius: 8px;
}

.rounded-circle {
    border: 3px solid #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
</style>
@endpush
