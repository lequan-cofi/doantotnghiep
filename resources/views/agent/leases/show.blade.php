@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết hợp đồng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>{{ $lease->contract_no }}</h1>
                <p>
                    <i class="fas fa-user text-primary"></i> {{ $lease->tenant ? $lease->tenant->full_name : 'Chưa gán' }} - 
                    <i class="fas fa-building text-secondary"></i> {{ $lease->unit->property->name }}
                </p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.leases.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('agent.leases.edit', $lease->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Lease Information -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hợp đồng</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Mã hợp đồng</label>
                                <p class="mb-0">{{ $lease->contract_no }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Trạng thái</label>
                                <p class="mb-0">
                                    @switch($lease->status)
                                        @case('draft')
                                            <span class="badge bg-secondary fs-6">Nháp</span>
                                            @break
                                        @case('active')
                                            <span class="badge bg-success fs-6">Hoạt động</span>
                                            @break
                                        @case('terminated')
                                            <span class="badge bg-danger fs-6">Chấm dứt</span>
                                            @break
                                        @case('expired')
                                            <span class="badge bg-warning fs-6">Hết hạn</span>
                                            @break
                                    @endswitch
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày bắt đầu</label>
                                <p class="mb-0">{{ $lease->start_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày kết thúc</label>
                                <p class="mb-0">{{ $lease->end_date->format('d/m/Y') }}</p>
                            </div>
                            @if($lease->signed_at)
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày ký</label>
                                <p class="mb-0">{{ $lease->signed_at->format('d/m/Y H:i') }}</p>
                            </div>
                            @endif
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày tạo</label>
                                <p class="mb-0">{{ $lease->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave"></i> Thông tin tài chính</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Giá thuê</label>
                                <p class="mb-0 h5 text-success">
                                    {{ number_format($lease->rent_amount, 0, ',', '.') }} VNĐ
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Tiền cọc</label>
                                <p class="mb-0 h5 text-warning">
                                    {{ number_format($lease->deposit_amount, 0, ',', '.') }} VNĐ
                                </p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Ngày thanh toán</label>
                                <p class="mb-0">Ngày {{ $lease->billing_day }} hàng tháng</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Services -->
                @if($lease->leaseServices->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-concierge-bell"></i> Dịch vụ kèm theo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Giá</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lease->leaseServices as $service)
                                    <tr>
                                        <td>{{ $service->service->name }}</td>
                                        <td class="text-success fw-bold">
                                            {{ number_format($service->price, 0, ',', '.') }} VNĐ
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Residents -->
                @if($lease->residents->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-users"></i> Cư dân kèm theo</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Tên</th>
                                        <th>Số điện thoại</th>
                                        <th>CMND/CCCD</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lease->residents as $resident)
                                    <tr>
                                        <td>{{ $resident->name }}</td>
                                        <td>{{ $resident->phone ?? '-' }}</td>
                                        <td>{{ $resident->id_number ?? '-' }}</td>
                                        <td>{{ $resident->note ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Tenant Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fas fa-user"></i> Thông tin khách thuê</h6>
                    </div>
                    <div class="card-body">
                        @if($lease->tenant)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Họ và tên</label>
                                <p class="mb-0">{{ $lease->tenant->full_name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Email</label>
                                <p class="mb-0">{{ $lease->tenant->email }}</p>
                            </div>
                            @if($lease->tenant->phone)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Số điện thoại</label>
                                <p class="mb-0">{{ $lease->tenant->phone }}</p>
                            </div>
                            @endif
                        @else
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-user-slash fa-2x mb-2"></i>
                                <p class="mb-0">Chưa gán khách thuê</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Unit Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0"><i class="fas fa-door-open"></i> Thông tin phòng</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mã phòng</label>
                            <p class="mb-0">{{ $lease->unit->code }}</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Loại phòng</label>
                            <p class="mb-0">
                                @switch($lease->unit->unit_type)
                                    @case('room')
                                        <span class="badge bg-info">Phòng trọ</span>
                                        @break
                                    @case('apartment')
                                        <span class="badge bg-primary">Căn hộ</span>
                                        @break
                                    @case('dorm')
                                        <span class="badge bg-warning">Ký túc xá</span>
                                        @break
                                    @case('shared')
                                        <span class="badge bg-secondary">Phòng chung</span>
                                        @break
                                @endswitch
                            </p>
                        </div>
                        @if($lease->unit->area_m2)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Diện tích</label>
                            <p class="mb-0">{{ $lease->unit->area_m2 }} m²</p>
                        </div>
                        @endif
                        @if($lease->unit->floor)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tầng</label>
                            <p class="mb-0">Tầng {{ $lease->unit->floor }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Property Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-building"></i> Thông tin bất động sản</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tên bất động sản</label>
                            <p class="mb-0">{{ $lease->unit->property->name }}</p>
                        </div>
                        @if($lease->unit->property->owner)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Chủ trọ</label>
                            <p class="mb-0">
                                <i class="fas fa-user text-primary"></i> {{ $lease->unit->property->owner->full_name }}
                            </p>
                        </div>
                        @endif
                        @if($lease->unit->property->location2025)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Địa chỉ</label>
                            <p class="mb-0 small">
                                {{ $lease->unit->property->location2025->street }}, {{ $lease->unit->property->location2025->ward }}, {{ $lease->unit->property->location2025->city }}
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-cogs"></i> Thao tác</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('agent.leases.edit', $lease->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa hợp đồng
                            </a>
                            <form action="{{ route('agent.leases.destroy', $lease->id) }}" method="POST" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn xóa hợp đồng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-trash"></i> Xóa hợp đồng
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('styles')
<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-1px);
}

.badge.fs-6 {
    font-size: 0.875rem !important;
}
</style>
@endpush
