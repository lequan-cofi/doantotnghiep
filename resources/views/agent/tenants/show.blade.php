@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết Người dùng')

@section('content')
<div class="content">
    <div class="content-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="content-title">Chi tiết Người dùng</h1>
                <p class="content-subtitle">{{ $tenant->full_name }}</p>
            </div>
            <div>
                <a href="{{ route('agent.tenants.edit', $tenant->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('agent.tenants.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- User Information -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Thông tin người dùng</h5>
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
        </div>

        <!-- Leases Information -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Hợp đồng thuê nhà</h5>
                </div>
                <div class="card-body">
                    @if($tenant->leasesAsTenant->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã hợp đồng</th>
                                        <th>BĐS</th>
                                        <th>Phòng</th>
                                        <th>Ngày bắt đầu</th>
                                        <th>Ngày kết thúc</th>
                                        <th>Tiền thuê</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenant->leasesAsTenant as $lease)
                                        <tr>
                                            <td>
                                                <strong>{{ $lease->contract_no ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                {{ $lease->unit->property->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $lease->unit->name ?? 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $lease->start_date ? $lease->start_date->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $lease->end_date ? $lease->end_date->format('d/m/Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                <strong>{{ number_format($lease->rent_amount, 0, ',', '.') }} VNĐ</strong>
                                            </td>
                                            <td>
                                                @switch($lease->status)
                                                    @case('active')
                                                        <span class="badge bg-success">Hoạt động</span>
                                                        @break
                                                    @case('expired')
                                                        <span class="badge bg-danger">Hết hạn</span>
                                                        @break
                                                    @case('terminated')
                                                        <span class="badge bg-warning">Chấm dứt</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ ucfirst($lease->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('agent.leases.show', $lease->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có hợp đồng nào</h5>
                            <p class="text-muted">Khách hàng này chưa có hợp đồng thuê nhà.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Residents Information -->
            @if($tenant->leasesAsTenant->where('residents')->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Người ở cùng</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Hợp đồng</th>
                                        <th>Tên</th>
                                        <th>Số điện thoại</th>
                                        <th>CMND/CCCD</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tenant->leasesAsTenant as $lease)
                                        @foreach($lease->residents as $resident)
                                            <tr>
                                                <td>{{ $lease->contract_no ?? 'N/A' }}</td>
                                                <td>
                                                    <strong>{{ $resident->name }}</strong>
                                                    @if($resident->user_id)
                                                        <span class="badge bg-info ms-1">Có tài khoản</span>
                                                    @endif
                                                </td>
                                                <td>{{ $resident->phone }}</td>
                                                <td>{{ $resident->id_number ?? 'N/A' }}</td>
                                                <td>{{ $resident->note ?? 'N/A' }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
