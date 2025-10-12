@extends('layouts.agent_dashboard')

@section('title', 'Quản lý Người dùng')

@section('content')
<div class="content">
    <div class="content-header">
        <h1 class="content-title">Quản lý Người dùng</h1>
        <p class="content-subtitle">Quản lý người dùng cùng tổ chức với bạn</p>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('agent.tenants.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm người dùng mới
                    </a>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.tenants.index', ['type' => 'all']) }}" 
                       class="btn btn-outline-secondary {{ $type === 'all' ? 'active' : '' }}">
                        Tất cả
                    </a>
                    <a href="{{ route('agent.tenants.index', ['type' => 'with_leases']) }}" 
                       class="btn btn-outline-secondary {{ $type === 'with_leases' ? 'active' : '' }}">
                        Có hợp đồng
                    </a>
                    <a href="{{ route('agent.tenants.index', ['type' => 'without_leases']) }}" 
                       class="btn btn-outline-secondary {{ $type === 'without_leases' ? 'active' : '' }}">
                        Chưa có hợp đồng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('agent.tenants.index') }}" class="row g-3">
                        <div class="col-md-8">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Tìm kiếm theo tên, số điện thoại, email..." 
                                   value="{{ $search }}">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>Tất cả</option>
                                <option value="with_leases" {{ $type === 'with_leases' ? 'selected' : '' }}>Có hợp đồng</option>
                                <option value="without_leases" {{ $type === 'without_leases' ? 'selected' : '' }}>Chưa có hợp đồng</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    @if($users->count() > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            Danh sách người dùng 
                            @if($type === 'with_leases')
                                ({{ $usersWithLeases->count() }} có hợp đồng)
                            @elseif($type === 'without_leases')
                                ({{ $usersWithoutLeases->count() }} chưa có hợp đồng)
                            @else
                                ({{ $users->count() }} tổng cộng)
                            @endif
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Loại</th>
                                        <th>Tên</th>
                                        <th>Số điện thoại</th>
                                        <th>Email</th>
                                        <th>Hợp đồng</th>
                                        <th>Vai trò</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                        <tr>
                                            <td>
                                                @if($user->leasesAsTenant->count() > 0)
                                                    <span class="badge bg-success">Có hợp đồng</span>
                                                @else
                                                    <span class="badge bg-warning">Chưa có hợp đồng</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $user->full_name }}</strong>
                                            </td>
                                            <td>{{ $user->phone }}</td>
                                            <td>{{ $user->email ?? 'N/A' }}</td>
                                            <td>
                                                @if($user->leasesAsTenant->count() > 0)
                                                    <span class="badge bg-info">
                                                        {{ $user->leasesAsTenant->count() }} hợp đồng
                                                    </span>
                                                @else
                                                    <span class="text-muted">Chưa có</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->organizationRoles->count() > 0)
                                                    <span class="badge bg-primary">
                                                        {{ $user->organizationRoles->first()->name }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Chưa gán</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($user->status == 1)
                                                    <span class="badge bg-success">Hoạt động</span>
                                                @else
                                                    <span class="badge bg-secondary">Không hoạt động</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.tenants.show', $user->id) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('agent.tenants.edit', $user->id) }}" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('agent.tenants.destroy', $user->id) }}" 
                                                          method="POST" class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa người dùng này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có người dùng nào</h5>
                        <p class="text-muted">Bắt đầu bằng cách thêm người dùng mới vào tổ chức.</p>
                        <a href="{{ route('agent.tenants.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Thêm người dùng đầu tiên
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
