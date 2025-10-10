@extends('layouts.manager_dashboard')

@section('title', 'Quản lý nhân viên')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Quản lý nhân viên</h1>
                <p class="text-muted">Quản lý nhân viên, lương, hoa hồng và bất động sản được gắn</p>
                @if(auth()->user()->organizations()->first())
                <small class="text-info">
                    <i class="fas fa-building"></i> 
                    Tổ chức: {{ auth()->user()->organizations()->first()->name }}
                </small>
                @endif
            </div>
            <a href="{{ route('manager.staff.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Thêm nhân viên mới
            </a>
        </div>

        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <form method="GET" action="{{ route('manager.staff.index') }}">
                            <div class="row g-3">
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
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                @if($staff->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th width="20%">Nhân viên</th>
                                <th width="15%">Vai trò</th>
                                <th width="15%">Lương cơ bản</th>
                                <th width="10%">BĐS quản lý</th>
                                <th width="10%">Hoa hồng</th>
                                <th width="10%">Trạng thái</th>
                                <th width="10%">Ngày tạo</th>
                                <th width="5%">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($staff as $member)
                            <tr>
                                <td>{{ $loop->iteration + ($staff->currentPage() - 1) * $staff->perPage() }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-2">
                                            {{ strtoupper(substr($member->full_name ?? 'N', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong>{{ $member->full_name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $member->email }}</small>
                                            @if($member->phone)
                                            <br>
                                            <small class="text-muted"><i class="fas fa-phone fa-xs"></i> {{ $member->phone }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @foreach($member->organizationRoles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>
                                    @php
                                        $activeSalary = $member->salaryContracts()->where('status', 'active')->latest('effective_from')->first();
                                    @endphp
                                    @if($activeSalary)
                                    <strong class="text-success">{{ number_format($activeSalary->base_salary, 0, ',', '.') }} VNĐ</strong>
                                    <br>
                                    <small class="text-muted">Kỳ lương: {{ $activeSalary->pay_day }}/tháng</small>
                                    @else
                                    <span class="text-muted">Chưa thiết lập</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary">{{ $member->assignedProperties->count() }} BĐS</span>
                                </td>
                                <td>
                                    @php
                                        $totalCommission = DB::table('commission_event_splits')
                                            ->where('user_id', $member->id)
                                            ->where('status', 'paid')
                                            ->sum('amount');
                                    @endphp
                                    @if($totalCommission > 0)
                                    <strong class="text-warning">{{ number_format($totalCommission, 0, ',', '.') }} VNĐ</strong>
                                    @else
                                    <span class="text-muted">0 VNĐ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($member->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $member->created_at->format('d/m/Y') }}</small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('manager.staff.show', $member->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('manager.staff.edit', $member->id) }}" class="btn btn-sm btn-outline-primary" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteStaff({{ $member->id }}, '{{ $member->full_name }}')" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $staff->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có nhân viên nào.</p>
                    <a href="{{ route('manager.staff.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm nhân viên đầu tiên
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</main>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}

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
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2
    $('.select2').select2({
        placeholder: function() {
            return $(this).data('placeholder') || 'Chọn...';
        },
        allowClear: true,
        width: '100%'
    });
});

function deleteStaff(id, name) {
    Notify.confirmDelete(`nhân viên "${name}"`, () => {
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }

        fetch(`/manager/staff/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (window.Preloader) {
                window.Preloader.hide();
            }

            if (data.success) {
                Notify.success('Xóa nhân viên thành công!');
                setTimeout(() => location.reload(), 1500);
            } else {
                Notify.error(data.message || 'Không thể xóa nhân viên. Vui lòng thử lại.');
            }
        })
        .catch(error => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
            console.error('Error:', error);
            Notify.error('Đã xảy ra lỗi khi xóa nhân viên. Vui lòng kiểm tra kết nối và thử lại.');
        });
    });
}
</script>
@endpush
@endsection

