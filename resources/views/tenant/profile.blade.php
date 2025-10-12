@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Hồ sơ cá nhân</h1>
        <div>
            <a href="{{ route('tenant.profile.edit') }}" class="btn btn-primary me-2">
                <i class="fas fa-edit me-1"></i>Chỉnh sửa
            </a>
            <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-tachometer-alt me-1"></i>Về Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
            <div class="card mb-4">
                <div class="card-header">Thông tin tài khoản</div>
                <div class="card-body">
                    <form method="POST" action="#">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Họ và tên</label>
                            <input type="text" class="form-control" value="{{ auth()->user()?->full_name }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ auth()->user()?->email }}" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" class="form-control" value="{{ auth()->user()?->phone ?? '' }}" disabled>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KYC Information -->
            @if($userProfile)
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Thông tin KYC</span>
                        <span class="badge {{ $userProfile->isKycComplete() ? 'bg-success' : 'bg-warning' }}">
                            {{ $userProfile->getKycCompletionPercentage() }}% hoàn thành
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Ngày sinh</label>
                                    <p class="mb-0">{{ $userProfile->formatted_dob ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Giới tính</label>
                                    <p class="mb-0">{{ $userProfile->gender_text }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Số CMND/CCCD</label>
                                    <p class="mb-0">{{ $userProfile->id_number ?? 'Chưa cập nhật' }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label text-muted">Ngày cấp CMND/CCCD</label>
                                    <p class="mb-0">{{ $userProfile->formatted_id_issued_at ?? 'Chưa cập nhật' }}</p>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-muted">Địa chỉ thường trú</label>
                                    <p class="mb-0">{{ $userProfile->address ?? 'Chưa cập nhật' }}</p>
                                </div>
                                @if($userProfile->note)
                                    <div class="mb-3">
                                        <label class="form-label text-muted">Ghi chú</label>
                                        <p class="mb-0">{{ $userProfile->note }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if(!$userProfile->isKycComplete())
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Thông tin KYC chưa đầy đủ:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($userProfile->getMissingKycFields() as $field)
                                        <li>{{ $field }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">Bảo mật</div>
                <div class="card-body">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-1"></i>Đổi mật khẩu (sắp ra mắt)
                    </a>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <img class="rounded-circle mb-3" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->full_name ?? 'User') }}&background=ff6b35&color=fff&size=96" alt="avatar">
                    <h5 class="mb-0">{{ auth()->user()?->full_name }}</h5>
                    <div class="text-muted small">{{ auth()->user()?->email }}</div>

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
        </div>
    </div>
</div>
@endsection


