@extends('layouts.auth')

@section('title', 'Đăng ký')

@section('content')
    <div class="container d-flex justify-content-center">
        <div class="register-container mx-auto">
            <div class="row g-0">
                <!-- Left Side - Illustration -->
                <div class="col-lg-5">
                    <div class="register-left h-100">
                        <div class="illustration">
                            <div class="sofa-illustration">
                                <div class="person"></div>
                                <div class="laptop"></div>
                                <div class="papers">
                                    <div class="paper"></div>
                                    <div class="paper"></div>
                                    <div class="paper"></div>
                                </div>
                            </div>
                        </div>
                        <h3>Tìm phòng trọ</h3>
                        <p>PhongTro24h.com dẫn lối</p>
                    </div>
                </div>

                <!-- Right Side - Register Form -->
                <div class="col-lg-7">
                    <div class="register-right">
                        <button class="close-btn" onclick="window.history.back()">
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="brand-logo">
                            <i class="fas fa-home me-2"></i>
                            PhongTro24h
                        </div>

                        <div class="welcome-text">
                            <h2>Xin chào bạn</h2>
                            <h3>Đăng ký tài khoản mới</h3>
                        </div>

                        <form id="registerForm" method="POST" action="{{ route('register.store') }}">
                            @csrf
                            <div class="form-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" name="full_name" value="{{ old('full_name') }}" class="form-control" placeholder="Họ và tên" required>
                                <x-form.error for="full_name" />
                            </div>

                            <div class="form-group">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required>
                                <x-form.error for="email" />
                            </div>

                            <div class="form-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-control" placeholder="Mật khẩu" required>
                                <x-form.error for="password" />
                            </div>

                            <div class="form-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
                            </div>

                            <button type="submit" class="btn btn-register">
                                Hoàn tất đăng ký
                            </button>
                        </form>

                        <div class="divider step-1-content">
                            <span>Hoặc</span>
                        </div>

                        <div class="step-1-content">
                            <a href="#" class="social-btn apple-btn">
                                <i class="fab fa-apple"></i>
                                Đăng nhập với Apple
                            </a>

                            <a href="#" class="social-btn google-btn">
                                <i class="fab fa-google"></i>
                                Đăng nhập với Google
                            </a>
                        </div>

                        <div class="terms-text">
                            Bằng việc tiếp tục, bạn đồng ý với 
                            <a href="#">Điều khoản sử dụng</a>, 
                            <a href="#">Chính sách bảo mật</a>, 
                            <a href="#">Quy chế</a> của chúng tôi.
                        </div>

                        <div class="login-link">
                            Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập tại đây</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection