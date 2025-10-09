@extends('layouts.agent_dashboad')

@section('title', 'Hồ sơ Agent')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Hồ sơ cá nhân</h1>
                <p>Thông tin tài khoản agent</p>
            </div>
            <a href="{{ route('agent.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </div>
    </header>

    <div class="content" id="content">
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Thông tin tài khoản</h3>
                </div>
                <div class="card-content">
                    <div class="row">
                        <div class="col-md-8">
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
                        </div>
                        <div class="col-md-4 text-center">
                            <img class="rounded-circle mb-3" src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->full_name ?? 'Agent') }}&background=0d6efd&color=fff&size=96" alt="avatar">
                            <div class="mt-3">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="fas fa-sign-out-alt me-1"></i>Đăng xuất
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-shield-alt"></i> Bảo mật</h3>
                </div>
                <div class="card-content">
                    <button class="btn btn-outline-secondary">
                        <i class="fas fa-key me-1"></i>Đổi mật khẩu (sắp ra mắt)
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection


