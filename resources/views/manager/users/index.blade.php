@extends('layouts.manager_dashboard')

@section('title', 'Quản lý người dùng')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0 text-gray-800">
                            <i class="fas fa-users me-2"></i>Quản lý người dùng
                        </h1>
                        <p class="text-muted mb-0">Quản lý tài khoản người dùng trong hệ thống</p>
                    </div>
                    <a href="{{ route('manager.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Thêm người dùng
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('manager.users.index') }}">
                            <!-- First row -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="form-label">Tìm kiếm</label>
                                    <input type="text" name="search" class="form-control" placeholder="Tên, email, số điện thoại..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Vai trò</label>
                                    <select name="role_id" class="form-select select2" data-placeholder="Chọn vai trò">
                                        <option value="">Tất cả vai trò</option>
                                        @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Trạng thái</label>
                                    <select name="status" class="form-select">
                                        <option value="">Tất cả</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Tạm ngưng</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Từ ngày</label>
                                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Đến ngày</label>
                                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="submit" class="btn btn-outline-primary w-100">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Second row -->
                            <div class="row g-3">
                                <div class="col-md-10 d-flex align-items-end">
                                    <a href="{{ route('manager.users.index') }}" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-times"></i> Xóa bộ lọc
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>Danh sách người dùng
                            <span class="badge bg-primary ms-2">{{ $users->total() }}</span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Thông tin</th>
                                        <th>Vai trò</th>
                                        <th>BĐS được gán</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">#{{ $user->id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $user->full_name }}</h6>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                    @if($user->phone)
                                                        <br><small class="text-muted">{{ $user->phone }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->userRoles->count() > 0)
                                                @foreach($user->userRoles as $role)
                                                    <span class="badge bg-info">{{ $role->name }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">Chưa có vai trò</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->assignedProperties->count() > 0)
                                                <span class="badge bg-success">{{ $user->assignedProperties->count() }} BĐS</span>
                                                <div class="mt-1">
                                                    @foreach($user->assignedProperties->take(2) as $property)
                                                        <small class="d-block text-muted">{{ $property->name }}</small>
                                                    @endforeach
                                                    @if($user->assignedProperties->count() > 2)
                                                        <small class="text-muted">+{{ $user->assignedProperties->count() - 2 }} khác</small>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Chưa gán BĐS</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->status)
                                                <span class="badge bg-success">Hoạt động</span>
                                            @else
                                                <span class="badge bg-warning">Tạm ngưng</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $user->created_at->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('manager.users.show', $user->id) }}" class="btn btn-outline-info" title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('manager.users.edit', $user->id) }}" class="btn btn-outline-primary" title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->id !== auth()->id())
                                                    <button class="btn btn-outline-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->full_name }}')" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-3x mb-3 text-muted"></i>
                                            <br>Chưa có người dùng nào
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</main>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
    padding-left: 12px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
// Initialize Select2 for searchable dropdowns
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 on all select elements with select2 class
    $('.select2').select2({
        placeholder: function() {
            return $(this).data('placeholder') || 'Chọn...';
        },
        allowClear: true,
        width: '100%'
    });

    // Note: Location filters removed since users don't have location data
});

function deleteUser(id, name) {
    Notify.confirmDelete(`người dùng "${name}"`, () => {
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            Notify.error('Lỗi bảo mật: Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.', 'Lỗi bảo mật!');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }

        fetch(`/manager/users/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Đã xóa!');
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể xóa người dùng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
}
</script>
@endpush
@endsection
