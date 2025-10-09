@extends('layouts.app')

@section('title', 'Hồ sơ cá nhân')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">Hồ sơ cá nhân</h1>
        <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-primary">
            <i class="fas fa-tachometer-alt me-1"></i>Về Dashboard
        </a>
    </div>

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


