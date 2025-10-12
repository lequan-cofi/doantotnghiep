@extends('layouts.agent_dashboard')

@section('title', 'Thêm Người dùng mới')

@section('content')
<div class="content">
    <div class="content-header">
        <h1 class="content-title">Thêm Người dùng mới</h1>
        <p class="content-subtitle">Tạo tài khoản người dùng mới với quyền truy cập hệ thống</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin người dùng</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.tenants.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Họ và tên *</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại *</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">Vai trò *</label>
                                <select class="form-select @error('role_id') is-invalid @enderror" 
                                        id="role_id" name="role_id" required>
                                    <option value="">Chọn vai trò</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agent.tenants.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo người dùng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hướng dẫn</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> Lưu ý quan trọng:</h6>
                        <ul class="mb-0">
                            <li>Người dùng sẽ được tạo tài khoản đăng nhập hệ thống</li>
                            <li>Tự động gán vào tổ chức của bạn</li>
                            <li>Có thể tạo hợp đồng thuê nhà</li>
                            <li>Có thể quản lý thông tin và vai trò</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Vai trò người dùng</h5>
                </div>
                <div class="card-body">
                    @foreach($roles as $role)
                        <div class="mb-2">
                            <strong>{{ $role->name }}</strong>
                            <br>
                            <small class="text-muted">{{ $role->key_code }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
