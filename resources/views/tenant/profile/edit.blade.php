@extends('layouts.app')

@section('title', 'Chỉnh sửa hồ sơ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/profile-edit.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="profile-edit-header mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="profile-edit-title">
                    <i class="fas fa-user-edit me-3"></i>Chỉnh sửa hồ sơ cá nhân
                </h1>
                <p class="profile-edit-subtitle">Cập nhật thông tin tài khoản và bảo mật</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('tenant.profile') }}" class="btn btn-outline-secondary btn-modern me-2">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary btn-modern">
                    <i class="fas fa-tachometer-alt me-2"></i>Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if($errors->any())
        <div class="alert alert-danger alert-modern alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-exclamation-circle me-3"></i>
                <div>
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('tenant.profile.update') }}" id="profileEditForm">
                @csrf
                @method('PUT')

                <!-- Basic Information -->
                <div class="modern-card mb-4">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user me-3"></i>
                            <h5 class="mb-0">Thông tin cơ bản</h5>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name" class="form-label-modern">
                                    <i class="fas fa-user me-2"></i>Họ và tên <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control-modern @error('full_name') is-invalid @enderror" 
                                       id="full_name" 
                                       name="full_name" 
                                       value="{{ old('full_name', $user->full_name) }}" 
                                       required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label-modern">
                                    <i class="fas fa-phone me-2"></i>Số điện thoại
                                </label>
                                <input type="text" 
                                       class="form-control-modern @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="Nhập số điện thoại">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="email" class="form-label-modern">
                                    <i class="fas fa-envelope me-2"></i>Email <span class="text-danger">*</span>
                                </label>
                                <input type="email" 
                                       class="form-control-modern @error('email') is-invalid @enderror" 
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
                </div>

                <!-- KYC Information -->
                <div class="modern-card mb-4">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-id-card me-3"></i>
                            <h5 class="mb-0">Thông tin KYC (Know Your Customer)</h5>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <div class="alert alert-info alert-modern">
                            <div class="alert-content">
                                <i class="fas fa-info-circle me-3"></i>
                                <span>Thông tin KYC giúp xác thực danh tính và tăng độ tin cậy cho tài khoản của bạn.</span>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="dob" class="form-label-modern">
                                    <i class="fas fa-birthday-cake me-2"></i>Ngày sinh
                                </label>
                                <input type="date" 
                                       class="form-control-modern @error('dob') is-invalid @enderror" 
                                       id="dob" 
                                       name="dob" 
                                       value="{{ old('dob', $userProfile?->dob?->format('Y-m-d')) }}"
                                       max="{{ date('Y-m-d', strtotime('-1 day')) }}">
                                @error('dob')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="gender" class="form-label-modern">
                                    <i class="fas fa-venus-mars me-2"></i>Giới tính
                                </label>
                                <select class="form-control-modern @error('gender') is-invalid @enderror" 
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
                            
                            <div class="form-group">
                                <label for="id_number" class="form-label-modern">
                                    <i class="fas fa-id-card me-2"></i>Số CMND/CCCD
                                </label>
                                <input type="text" 
                                       class="form-control-modern @error('id_number') is-invalid @enderror" 
                                       id="id_number" 
                                       name="id_number" 
                                       value="{{ old('id_number', $userProfile?->id_number) }}"
                                       placeholder="Nhập số CMND/CCCD">
                                @error('id_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="id_issued_at" class="form-label-modern">
                                    <i class="fas fa-calendar-alt me-2"></i>Ngày cấp CMND/CCCD
                                </label>
                                <input type="date" 
                                       class="form-control-modern @error('id_issued_at') is-invalid @enderror" 
                                       id="id_issued_at" 
                                       name="id_issued_at" 
                                       value="{{ old('id_issued_at', $userProfile?->id_issued_at?->format('Y-m-d')) }}"
                                       max="{{ date('Y-m-d') }}">
                                @error('id_issued_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="address" class="form-label-modern">
                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ thường trú
                                </label>
                                <textarea class="form-control-modern @error('address') is-invalid @enderror" 
                                          id="address" 
                                          name="address" 
                                          rows="3"
                                          placeholder="Nhập địa chỉ thường trú">{{ old('address', $userProfile?->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="note" class="form-label-modern">
                                    <i class="fas fa-sticky-note me-2"></i>Ghi chú
                                </label>
                                <textarea class="form-control-modern @error('note') is-invalid @enderror" 
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

                <!-- Password Change -->
                <div class="modern-card mb-4">
                    <div class="card-header-modern">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-lock me-3"></i>
                            <h5 class="mb-0">Thay đổi mật khẩu</h5>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <div class="alert alert-info alert-modern">
                            <div class="alert-content">
                                <i class="fas fa-info-circle me-3"></i>
                                <span>Để thay đổi mật khẩu, vui lòng nhập mật khẩu hiện tại và mật khẩu mới.</span>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="current_password" class="form-label-modern">
                                    <i class="fas fa-key me-2"></i>Mật khẩu hiện tại
                                </label>
                                <input type="password" 
                                       class="form-control-modern @error('current_password') is-invalid @enderror" 
                                       id="current_password" 
                                       name="current_password"
                                       placeholder="Nhập mật khẩu hiện tại">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group">
                                <label for="password" class="form-label-modern">
                                    <i class="fas fa-lock me-2"></i>Mật khẩu mới
                                </label>
                                <input type="password" 
                                       class="form-control-modern @error('password') is-invalid @enderror" 
                                       id="password" 
                                       name="password"
                                       placeholder="Nhập mật khẩu mới">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="password_confirmation" class="form-label-modern">
                                    <i class="fas fa-check-circle me-2"></i>Xác nhận mật khẩu mới
                                </label>
                                <input type="password" 
                                       class="form-control-modern" 
                                       id="password_confirmation" 
                                       name="password_confirmation"
                                       placeholder="Nhập lại mật khẩu mới">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="modern-card">
                    <div class="card-body-modern">
                        <div class="form-actions">
                            <a href="{{ route('tenant.profile') }}" class="btn btn-outline-secondary btn-modern">
                                <i class="fas fa-times me-2"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary btn-modern">
                                <i class="fas fa-save me-2"></i>Cập nhật thông tin
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="modern-card profile-edit-sidebar">
                <div class="card-body-modern text-center">
                    <div class="profile-avatar">
                        <img class="avatar-img" 
                             src="https://ui-avatars.com/api/?name={{ urlencode($user->full_name ?? 'User') }}&background=ff6b35&color=fff&size=120" 
                             alt="avatar">
                        <div class="avatar-status online"></div>
                    </div>
                    <h5 class="profile-name">{{ $user->full_name ?? 'User' }}</h5>
                    <div class="profile-email">{{ $user->email ?? 'user@example.com' }}</div>

                    <div class="profile-actions">
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-primary btn-modern w-100 mb-3">
                            <i class="fas fa-tachometer-alt me-2"></i>Vào Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-modern w-100 logout-btn">
                                <i class="fas fa-sign-out-alt me-2"></i>Đăng xuất
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Help Card -->
            <div class="modern-card mt-4">
                <div class="card-header-modern">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-question-circle me-3"></i>
                        <h6 class="mb-0">Hướng dẫn</h6>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="help-list">
                        <div class="help-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <span>Cập nhật thông tin cơ bản</span>
                        </div>
                        <div class="help-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <span>Thay đổi mật khẩu (tùy chọn)</span>
                        </div>
                        <div class="help-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <span>Email phải là duy nhất</span>
                        </div>
                        <div class="help-item">
                            <i class="fas fa-check text-success me-2"></i>
                            <span>Mật khẩu tối thiểu 8 ký tự</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/user/profile-edit.js') }}?v={{ time() }}"></script>
@endpush
@endsection
