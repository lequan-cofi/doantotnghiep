@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa Người dùng')

@section('content')
<div class="content">
    <div class="content-header">
        <h1 class="content-title">Chỉnh sửa Người dùng</h1>
        <p class="content-subtitle">{{ $tenant->full_name }}</p>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin người dùng</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.tenants.update', $tenant->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Họ và tên *</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name', $tenant->full_name) }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại *</label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $tenant->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $tenant->email) }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Mật khẩu mới</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" placeholder="Để trống nếu không đổi">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Chỉ nhập nếu muốn thay đổi mật khẩu</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role_id" class="form-label">Vai trò *</label>
                                <select class="form-select @error('role_id') is-invalid @enderror" 
                                        id="role_id" name="role_id" required>
                                    <option value="">Chọn vai trò</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" 
                                                {{ old('role_id', $tenant->organizationRoles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('role_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái tài khoản</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_active" 
                                           value="1" {{ old('status', $tenant->status) == 1 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_active">
                                        Hoạt động
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_inactive" 
                                           value="0" {{ old('status', $tenant->status) == 0 ? 'checked' : '' }}>
                                    <label class="form-check-label" for="status_inactive">
                                        Không hoạt động
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('agent.tenants.show', $tenant->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin hiện tại</h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <div class="avatar-lg mx-auto mb-3">
                            <div class="avatar-title rounded-circle bg-primary text-white display-4">
                                {{ strtoupper(substr($tenant->full_name, 0, 1)) }}
                            </div>
                        </div>
                        <h5>{{ $tenant->full_name }}</h5>
                        <p class="text-muted">
                            @if($tenant->organizationRoles->count() > 0)
                                {{ $tenant->organizationRoles->first()->name }}
                            @else
                                Người dùng
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại:</label>
                        <p class="mb-0">{{ $tenant->phone }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email:</label>
                        <p class="mb-0">{{ $tenant->email ?? 'Chưa cập nhật' }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Trạng thái:</label>
                        <p class="mb-0">
                            @if($tenant->status == 1)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Không hoạt động</span>
                            @endif
                        </p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Ngày tạo:</label>
                        <p class="mb-0">{{ $tenant->created_at->format('d/m/Y H:i') }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Lần đăng nhập cuối:</label>
                        <p class="mb-0">
                            @if($tenant->last_login_at)
                                {{ $tenant->last_login_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">Chưa đăng nhập</span>
                            @endif
                        </p>
                    </div>

                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thống kê</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary">{{ $tenant->leasesAsTenant->count() }}</h4>
                            <small class="text-muted">Hợp đồng</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">{{ $tenant->leasesAsTenant->where('status', 'active')->count() }}</h4>
                            <small class="text-muted">Đang hoạt động</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
