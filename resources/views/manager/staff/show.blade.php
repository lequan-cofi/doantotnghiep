@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết nhân viên')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Chi tiết nhân viên</h1>
                <p class="text-muted">Thông tin chi tiết, lương, hoa hồng và hiệu suất</p>
            </div>
            <div>
                <a href="{{ route('manager.staff.edit', $staff->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('manager.staff.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-md-8">
                <!-- Basic Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-user"></i> Thông tin cơ bản</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Họ và tên</label>
                                <div class="fw-bold">{{ $staff->full_name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Email</label>
                                <div class="fw-bold">{{ $staff->email }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Số điện thoại</label>
                                <div class="fw-bold">{{ $staff->phone ?? 'Chưa cập nhật' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Tổ chức</label>
                                <div>
                                    @if($staff->organizations->count() > 0)
                                        @foreach($staff->organizations as $org)
                                            <span class="badge bg-primary">{{ $org->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Chưa được gắn tổ chức</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Vai trò</label>
                                <div>
                                    @foreach($staff->organizationRoles as $role)
                                    <span class="badge bg-info">{{ $role->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Trạng thái</label>
                                <div>
                                    @if($staff->status)
                                    <span class="badge bg-success">Hoạt động</span>
                                    @else
                                    <span class="badge bg-secondary">Tạm ngưng</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Ngày tạo</label>
                                <div class="fw-bold">{{ $staff->created_at->format('d/m/Y H:i') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Salary Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Hợp đồng lương</h5>
                        <button class="btn btn-sm btn-light" onclick="loadSalaryHistory()">
                            <i class="fas fa-history"></i> Lịch sử
                        </button>
                    </div>
                    <div class="card-body">
                        @php
                            $activeSalary = $staff->salaryContracts()->where('status', 'active')->latest('effective_from')->first();
                        @endphp
                        @if($activeSalary)
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Lương cơ bản</label>
                                <div class="h4 text-success mb-0">{{ number_format($activeSalary->base_salary, 0, ',', '.') }} VNĐ</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Tổ chức</label>
                                <div class="fw-bold">{{ $activeSalary->organization->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="text-muted small">Ngày trả lương</label>
                                <div class="fw-bold">Ngày {{ $activeSalary->pay_day }} hàng tháng</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Hiệu lực từ</label>
                                <div>{{ $activeSalary->effective_from->format('d/m/Y') }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Hiệu lực đến</label>
                                <div>{{ $activeSalary->effective_to ? $activeSalary->effective_to->format('d/m/Y') : 'Không giới hạn' }}</div>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i> Chưa có hợp đồng lương nào được thiết lập.
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Salary History (Last 12 months) -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-warning">
                        <h5 class="mb-0"><i class="fas fa-file-invoice-dollar"></i> Lịch sử lương (12 tháng gần nhất)</h5>
                    </div>
                    <div class="card-body">
                        @if($salaryHistory && $salaryHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-hover">
                                <thead>
                                    <tr>
                                        <th>Kỳ lương</th>
                                        <th>Lương gộp</th>
                                        <th>Khấu trừ</th>
                                        <th>Thực nhận</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày trả</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryHistory as $salary)
                                    <tr>
                                        <td>{{ $salary->period_month }}</td>
                                        <td class="text-success">{{ number_format($salary->gross_amount, 0, ',', '.') }} VNĐ</td>
                                        <td class="text-danger">{{ number_format($salary->deduction_amount, 0, ',', '.') }} VNĐ</td>
                                        <td class="fw-bold text-primary">{{ number_format($salary->net_amount, 0, ',', '.') }} VNĐ</td>
                                        <td>
                                            @if($salary->status == 'paid')
                                            <span class="badge bg-success">Đã trả</span>
                                            @elseif($salary->status == 'approved')
                                            <span class="badge bg-info">Đã duyệt</span>
                                            @else
                                            <span class="badge bg-secondary">Nháp</span>
                                            @endif
                                        </td>
                                        <td>{{ $salary->paid_at ? \Carbon\Carbon::parse($salary->paid_at)->format('d/m/Y') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Chưa có lịch sử lương</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Commission Statistics -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-chart-line"></i> Thống kê hoa hồng</h5>
                        <button class="btn btn-sm btn-light" onclick="loadCommissionDetails()">
                            <i class="fas fa-list"></i> Chi tiết
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            @php
                                $totalPending = $commissionStats->where('status', 'pending')->first();
                                $totalBooked = $commissionStats->where('status', 'booked')->first();
                                $totalPaid = $commissionStats->where('status', 'paid')->first();
                            @endphp
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="text-warning h2">{{ number_format($totalPending->total_amount ?? 0, 0, ',', '.') }} VNĐ</div>
                                    <div class="text-muted">Đang chờ ({{ $totalPending->count ?? 0 }})</div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="text-info h2">{{ number_format($totalBooked->total_amount ?? 0, 0, ',', '.') }} VNĐ</div>
                                    <div class="text-muted">Đã ghi nhận ({{ $totalBooked->count ?? 0 }})</div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3">
                                    <div class="text-success h2">{{ number_format($totalPaid->total_amount ?? 0, 0, ',', '.') }} VNĐ</div>
                                    <div class="text-muted">Đã trả ({{ $totalPaid->count ?? 0 }})</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Properties -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-building"></i> Bất động sản đang quản lý ({{ $staff->assignedProperties->count() }})</h5>
                    </div>
                    <div class="card-body">
                        @if($staff->assignedProperties->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Tên BĐS</th>
                                        <th>Loại</th>
                                        <th>Địa chỉ</th>
                                        <th>Tổng phòng</th>
                                        <th>Ngày gắn</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staff->assignedProperties as $property)
                                    <tr>
                                        <td><strong>{{ $property->name }}</strong></td>
                                        <td>{{ $property->propertyType->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($property->location)
                                            {{ $property->location->city }}
                                            @else
                                            N/A
                                            @endif
                                        </td>
                                        <td><span class="badge bg-info">{{ $property->total_rooms }} phòng</span></td>
                                        <td>{{ $property->pivot->assigned_at ? \Carbon\Carbon::parse($property->pivot->assigned_at)->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            <a href="{{ route('manager.properties.show', $property->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="text-center py-3 text-muted">
                            <i class="fas fa-building fa-2x mb-2"></i>
                            <p>Chưa có bất động sản nào được gắn</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê tổng quan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-building text-primary"></i> BĐS quản lý</span>
                                <strong class="h4 mb-0">{{ $staff->assignedProperties->count() }}</strong>
                            </div>
                        </div>
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-contract text-success"></i> Hợp đồng</span>
                                <strong class="h4 mb-0">{{ $staff->commissionEvents->count() }}</strong>
                            </div>
                        </div>
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-money-bill-wave text-warning"></i> Tổng HH</span>
                                @php
                                    $totalCommission = $commissionStats->sum('total_amount');
                                @endphp
                                <strong class="h5 mb-0">{{ number_format($totalCommission, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Organization Info -->
                @if($staff->organizationUsers->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-sitemap"></i> Tổ chức</h6>
                    </div>
                    <div class="card-body">
                        @foreach($staff->organizationUsers as $orgUser)
                        <div class="mb-2">
                            <strong>{{ $orgUser->organization->name }}</strong>
                            <br>
                            <small class="text-muted">
                                Vai trò: {{ $orgUser->role->name ?? 'N/A' }}
                            </small>
                            <br>
                            <span class="badge {{ $orgUser->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                {{ $orgUser->status }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-cogs"></i> Thao tác</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('manager.staff.edit', $staff->id) }}" class="btn btn-warning w-100 mb-2">
                            <i class="fas fa-edit"></i> Chỉnh sửa thông tin
                        </a>
                        <button type="button" class="btn btn-danger w-100" onclick="deleteStaff({{ $staff->id }}, '{{ $staff->full_name }}')">
                            <i class="fas fa-trash"></i> Xóa nhân viên
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
function loadSalaryHistory() {
    Notify.info('Đang tải lịch sử lương...');
    
    fetch(`/manager/staff/{{ $staff->id }}/salary-contracts`)
        .then(response => response.json())
        .then(data => {
            console.log('Salary contracts:', data);
            Notify.success('Đã tải lịch sử lương!');
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể tải lịch sử lương');
        });
}

function loadCommissionDetails() {
    Notify.info('Đang tải chi tiết hoa hồng...');
    
    fetch(`/manager/staff/{{ $staff->id }}/commission-events`)
        .then(response => response.json())
        .then(data => {
            console.log('Commission events:', data);
            Notify.success('Đã tải chi tiết hoa hồng!');
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể tải chi tiết hoa hồng');
        });
}

function deleteStaff(id, name) {
    Notify.confirmDelete(`nhân viên "${name}"`, () => {
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
                setTimeout(() => {
                    window.location.href = '{{ route("manager.staff.index") }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Không thể xóa nhân viên');
            }
        })
        .catch(error => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
            console.error('Error:', error);
            Notify.error('Đã xảy ra lỗi khi xóa nhân viên');
        });
    });
}
</script>
@endpush
@endsection

