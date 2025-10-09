@extends('layouts.auth')
@section('title', 'Đăng nhập')
@section('content')
    <div class="container">
        <div class="login-container mx-auto">
            <div class="row g-0">
                <!-- Left Side - Illustration -->
                <div class="col-lg-5">
                    <div class="login-left h-100">
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

                <!-- Right Side - Login Form -->
                <div class="col-lg-7">
                    <div class="login-right">
                        <button class="close-btn" onclick="window.history.back()">
                            <i class="fas fa-times"></i>
                        </button>

                        <div class="brand-logo">
                            <i class="fas fa-home me-2"></i>
                            PhongTro24h
                        </div>

                        <div class="welcome-text">
                            <h2>Xin chào bạn</h2>
                            <h3>Đăng nhập để tiếp tục</h3>
                        </div>

                        <form id="loginForm" method="POST" action="{{ route('login.store') }}">
                            @csrf
                            <div class="form-group">
                                <i class="fas fa-user input-icon"></i>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Email" required>
                                <x-form.error for="email" />
                            </div>

                            <div class="form-group">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password" class="form-control" id="password" placeholder="Mật khẩu" required>
                                <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
                                <x-form.error for="password" />
                            </div>

                            <div class="remember-forgot">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Nhớ tài khoản</label>
                                </div>
                                <a href="#" class="forgot-link">Quên mật khẩu?</a>
                            </div>

                            <button type="submit" class="btn btn-login">
                                Đăng nhập
                            </button>
                        </form>

                        <div class="divider">
                            <span>Hoặc</span>
                        </div>

                        <a href="#" class="social-btn apple-btn">
                            <i class="fab fa-apple"></i>
                            Đăng nhập với Apple
                        </a>

                        <a href="#" class="social-btn google-btn">
                            <i class="fab fa-google"></i>
                            Đăng nhập với Google
                        </a>

                        <div class="terms-text">
                            Bằng việc tiếp tục, bạn đồng ý với 
                            <a href="#">Điều khoản sử dụng</a>, 
                            <a href="#">Chính sách bảo mật</a>, 
                            <a href="#">Quy chế</a> của chúng tôi.
                        </div>

                        <div class="signup-link">
                            Chưa là thành viên? <a href="{{ route('register') }}">Đăng ký tại đây</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection