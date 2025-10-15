@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/profile.css') }}?v={{ time() }}">
@endpush

@section('content')
<div class="container py-4">
    <!-- Header Section -->
    <div class="profile-header mb-5">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="profile-title">
                    <i class="fas fa-user-circle me-3"></i>Hồ sơ cá nhân
                </h1>
                <p class="profile-subtitle">Quản lý thông tin tài khoản và bảo mật</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('tenant.profile.edit') }}" class="btn btn-primary btn-modern me-2">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa
                </a>
                <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary btn-modern">
                    <i class="fas fa-tachometer-alt me-2"></i>Về Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- Notifications -->
    @if(session('success'))
        <div class="alert alert-success alert-modern alert-dismissible fade show" role="alert">
            <div class="alert-content">
                <i class="fas fa-check-circle me-3"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

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
            <!-- Account Information Card -->
            <div class="modern-card mb-4">
                <div class="card-header-modern">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user me-3"></i>
                        <h5 class="mb-0">Thông tin tài khoản</h5>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="info-grid">
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-user me-2"></i>Họ và tên
                            </label>
                            <div class="info-value">{{ auth()->user()?->full_name ?? 'Chưa cập nhật' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-envelope me-2"></i>Email
                            </label>
                            <div class="info-value">{{ auth()->user()?->email ?? 'Chưa cập nhật' }}</div>
                        </div>
                        <div class="info-item">
                            <label class="info-label">
                                <i class="fas fa-phone me-2"></i>Số điện thoại
                            </label>
                            <div class="info-value">{{ auth()->user()?->phone ?? 'Chưa cập nhật' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- KYC Information -->
            @if($userProfile)
                <div class="modern-card mb-4">
                    <div class="card-header-modern">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-id-card me-3"></i>
                                <h5 class="mb-0">Thông tin KYC</h5>
                            </div>
                            <div class="kyc-progress">
                                <div class="progress-circle {{ $userProfile->isKycComplete() ? 'complete' : 'incomplete' }}">
                                    <span class="progress-text">{{ $userProfile->getKycCompletionPercentage() }}%</span>
                                </div>
                                <span class="progress-label">{{ $userProfile->isKycComplete() ? 'Hoàn thành' : 'Chưa hoàn thành' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body-modern">
                        <div class="info-grid">
                            <div class="info-item">
                                <label class="info-label">
                                    <i class="fas fa-birthday-cake me-2"></i>Ngày sinh
                                </label>
                                <div class="info-value">{{ $userProfile->formatted_dob ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="info-item">
                                <label class="info-label">
                                    <i class="fas fa-venus-mars me-2"></i>Giới tính
                                </label>
                                <div class="info-value">{{ $userProfile->gender_text ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="info-item">
                                <label class="info-label">
                                    <i class="fas fa-id-card me-2"></i>Số CMND/CCCD
                                </label>
                                <div class="info-value">{{ $userProfile->id_number ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="info-item">
                                <label class="info-label">
                                    <i class="fas fa-calendar-alt me-2"></i>Ngày cấp CMND/CCCD
                                </label>
                                <div class="info-value">{{ $userProfile->formatted_id_issued_at ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="info-item full-width">
                                <label class="info-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Địa chỉ thường trú
                                </label>
                                <div class="info-value">{{ $userProfile->address ?? 'Chưa cập nhật' }}</div>
                            </div>
                            @if($userProfile->note)
                                <div class="info-item full-width">
                                    <label class="info-label">
                                        <i class="fas fa-sticky-note me-2"></i>Ghi chú
                                    </label>
                                    <div class="info-value">{{ $userProfile->note }}</div>
                                </div>
                            @endif
                        </div>
                        
                        @if(!$userProfile->isKycComplete())
                            <div class="alert alert-warning alert-modern mt-4">
                                <div class="alert-content">
                                    <i class="fas fa-exclamation-triangle me-3"></i>
                                    <div>
                                        <strong>Thông tin KYC chưa đầy đủ:</strong>
                                        <ul class="mb-0 mt-2">
                                            @foreach($userProfile->getMissingKycFields() as $field)
                                                <li>{{ $field }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Security Card -->
            <div class="modern-card">
                <div class="card-header-modern">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-shield-alt me-3"></i>
                        <h5 class="mb-0">Bảo mật</h5>
                    </div>
                </div>
                <div class="card-body-modern">
                    <div class="security-actions">
                        <a href="{{ route('tenant.profile.edit') }}" class="btn btn-outline-secondary btn-modern">
                            <i class="fas fa-key me-2"></i>Đổi mật khẩu (Trong trang chỉnh sửa)
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="modern-card profile-sidebar">
                <div class="card-body-modern text-center">
                    <div class="profile-avatar">
                        <img class="avatar-img" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->full_name ?? 'User') }}&background=ff6b35&color=fff&size=120" alt="avatar">
                        <div class="avatar-status online"></div>
                    </div>
                    <h5 class="profile-name">{{ auth()->user()?->full_name ?? 'User' }}</h5>
                    <div class="profile-email">{{ auth()->user()?->email ?? 'user@example.com' }}</div>
                    
                    <div class="profile-stats">
                        <div class="stat-item">
                            <div class="stat-number">{{ $userProfile ? $userProfile->getKycCompletionPercentage() : 0 }}%</div>
                            <div class="stat-label">KYC hoàn thành</div>
                        </div>
                    </div>

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
        </div>
    </div>
</div>

@push('scripts')
<script src="{{ asset('assets/js/user/profile.js') }}?v={{ time() }}"></script>
@endpush
@endsection


