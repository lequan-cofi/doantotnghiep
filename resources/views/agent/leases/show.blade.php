@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết hợp đồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-file-contract me-2"></i>{{ $lease->contract_no ?? 'Hợp đồng #' . $lease->id }}
                    </h1>
                    <p class="text-muted mb-0">Chi tiết hợp đồng thuê phòng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leases.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                    <a href="{{ route('agent.leases.edit', $lease->id) }}" class="btn btn-outline-warning">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Lease Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-contract me-2"></i>Thông tin hợp đồng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Mã hợp đồng</label>
                                <p class="mb-0 fw-bold">{{ $lease->contract_no ?? 'N/A' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày bắt đầu</label>
                                <p class="mb-0">{{ $lease->start_date ? $lease->start_date->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày kết thúc</label>
                                <p class="mb-0">{{ $lease->end_date ? $lease->end_date->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày ký</label>
                                <p class="mb-0">{{ $lease->signed_at ? $lease->signed_at->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Tiền thuê/tháng</label>
                                <p class="mb-0 fw-bold text-success fs-5">{{ number_format($lease->rent_amount) }}đ</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Tiền cọc</label>
                                <p class="mb-0 fw-bold">{{ $lease->deposit_amount ? number_format($lease->deposit_amount) . 'đ' : 'Không có' }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày tạo hóa đơn</label>
                                <p class="mb-0">Ngày {{ $lease->billing_day }}</p>
                            </div>
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Trạng thái</label>
                                <p class="mb-0">
                                    @switch($lease->status)
                                        @case('draft')
                                            <span class="badge bg-secondary">Nháp</span>
                                            @break
                                        @case('active')
                                            <span class="badge bg-success">Hoạt động</span>
                                            @break
                                        @case('terminated')
                                            <span class="badge bg-danger">Đã chấm dứt</span>
                                            @break
                                        @case('expired')
                                            <span class="badge bg-warning">Hết hạn</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $lease->status }}</span>
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Cycle Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Chu kỳ thanh toán
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editPaymentCycleModal">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Chu kỳ thanh toán</label>
                                <div>
                                    @if($lease->lease_payment_cycle)
                                        @switch($lease->lease_payment_cycle)
                                            @case('monthly')
                                                <span class="badge bg-primary fs-6">Hàng tháng</span>
                                                @break
                                            @case('quarterly')
                                                <span class="badge bg-info fs-6">Hàng quý</span>
                                                @break
                                            @case('yearly')
                                                <span class="badge bg-success fs-6">Hàng năm</span>
                                                @break
                                            @case('custom')
                                                <span class="badge bg-warning fs-6">
                                                    {{ $lease->lease_custom_months ? $lease->lease_custom_months . ' tháng' : 'Tùy chỉnh' }}
                                                </span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary fs-6">{{ $lease->lease_payment_cycle }}</span>
                                        @endswitch
                                    @else
                                        <span class="text-muted">Chưa thiết lập</span>
                                    @endif
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Hạn thanh toán</label>
                                <div>
                                    @if($lease->lease_payment_day)
                                        <strong>Ngày {{ $lease->lease_payment_day }}</strong>
                                    @else
                                        <span class="text-muted">Chưa thiết lập</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ngày tạo hóa đơn (cũ)</label>
                                <div>
                                    <span class="text-muted">Ngày {{ $lease->billing_day }} hàng tháng</span>
                                    <br><small class="text-muted">Thông tin cũ, có thể được thay thế bởi chu kỳ thanh toán mới</small>
                                </div>
                            </div>

                            @if($lease->lease_payment_notes)
                            <div class="info-item mb-3">
                                <label class="form-label text-muted small">Ghi chú chu kỳ thanh toán</label>
                                <div>
                                    <div class="bg-light p-3 rounded">
                                        {{ $lease->lease_payment_notes }}
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tenant Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user me-2"></i>Thông tin khách thuê
                        @if($lease->isFromLead())
                            <span class="badge bg-warning ms-2">Từ Lead</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $tenantInfo = $lease->getTenantInfo();
                    @endphp
                    
                    @if($tenantInfo)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Họ và tên</label>
                                    <p class="mb-0 fw-bold">{{ $tenantInfo['name'] }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Số điện thoại</label>
                                    <p class="mb-0">{{ $tenantInfo['phone'] }}</p>
                                </div>
                                <div class="info-item mb-3">
                                    <label class="form-label text-muted small">Email</label>
                                    <p class="mb-0">{{ $tenantInfo['email'] }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if($tenantInfo['type'] === 'user')
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Ngày tạo tài khoản</label>
                                        <p class="mb-0">{{ $lease->tenant->created_at ? $lease->tenant->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Trạng thái tài khoản</label>
                                        <p class="mb-0">
                                            @if($lease->tenant->email_verified_at)
                                                <span class="badge bg-success">Đã xác thực</span>
                                            @else
                                                <span class="badge bg-warning">Chưa xác thực</span>
                                            @endif
                                        </p>
                                    </div>
                                @else
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Nguồn</label>
                                        <p class="mb-0">
                                            <span class="badge bg-info">Lead - Chưa có tài khoản</span>
                                        </p>
                                    </div>
                                    <div class="info-item mb-3">
                                        <label class="form-label text-muted small">Trạng thái Lead</label>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $lease->lead->status === 'converted' ? 'success' : 'warning' }}">
                                                {{ ucfirst($lease->lead->status) }}
                                            </span>
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        @if($lease->isFromLead())
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Lưu ý:</strong> Hợp đồng này được tạo từ lead. Khi khách hàng tạo tài khoản, bạn có thể gắn tài khoản vào hợp đồng này.
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Không có thông tin khách thuê.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Lease Residents -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Người ở cùng
                    </h5>
                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addResidentModal">
                        <i class="fas fa-plus me-1"></i>Thêm người ở cùng
                    </button>
                </div>
                <div class="card-body">
                    @if($lease->residents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Họ và tên</th>
                                        <th>Số điện thoại</th>
                                        <th>CMND/CCCD</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="residents-table-body">
                                    @foreach($lease->residents as $resident)
                                        <tr data-resident-id="{{ $resident->id }}">
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $resident->name }}</div>
                                                    @if($resident->user)
                                                        <small class="text-success">
                                                            <i class="fas fa-user-check me-1"></i>Có tài khoản: {{ $resident->user->email }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-times me-1"></i>Chưa có tài khoản
                                                        </small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>{{ $resident->phone ?? 'N/A' }}</td>
                                            <td>{{ $resident->id_number ?? 'N/A' }}</td>
                                            <td>{{ $resident->note ?? 'N/A' }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <button type="button" class="btn btn-outline-warning btn-sm edit-resident" 
                                                            data-resident-id="{{ $resident->id }}"
                                                            data-name="{{ $resident->name }}"
                                                            data-phone="{{ $resident->phone }}"
                                                            data-id-number="{{ $resident->id_number }}"
                                                            data-note="{{ $resident->note }}"
                                                            data-user-id="{{ $resident->user_id }}"
                                                            data-user-name="{{ $resident->user ? $resident->user->full_name . ' (' . $resident->user->phone . ')' : '' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm delete-resident" 
                                                            data-resident-id="{{ $resident->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Chưa có người ở cùng nào</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Services -->
            @if($lease->leaseServices->count() > 0)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>Dịch vụ bổ sung
                        </h5>
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
                                    @foreach($lease->leaseServices as $leaseService)
                                        <tr>
                                            <td>{{ $leaseService->service->name ?? 'N/A' }}</td>
                                            <td class="fw-bold text-success">{{ number_format($leaseService->price) }}đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Invoices Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-file-invoice me-2"></i>Hóa đơn
                    </h5>
                    <a href="{{ route('agent.invoices.index', ['lease_id' => $lease->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Xem tất cả
                    </a>
                </div>
                <div class="card-body">
                    @if($lease->invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Mã hóa đơn</th>
                                        <th>Ngày phát hành</th>
                                        <th>Hạn thanh toán</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lease->invoices->take(5) as $invoice)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $invoice->invoice_no ?? 'N/A' }}</div>
                                                <small class="text-muted">#{{ $invoice->id }}</small>
                                            </td>
                                            <td>{{ $invoice->issue_date ? $invoice->issue_date->format('d/m/Y') : 'N/A' }}</td>
                                            <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : 'N/A' }}</td>
                                            <td class="fw-bold text-success">{{ number_format($invoice->total_amount) }}đ</td>
                                            <td>
                                                @switch($invoice->status)
                                                    @case('draft')
                                                        <span class="badge bg-secondary">Nháp</span>
                                                        @break
                                                    @case('issued')
                                                        <span class="badge bg-primary">Đã phát hành</span>
                                                        @break
                                                    @case('paid')
                                                        <span class="badge bg-success">Đã thanh toán</span>
                                                        @break
                                                    @case('overdue')
                                                        <span class="badge bg-danger">Quá hạn</span>
                                                        @break
                                                    @case('cancelled')
                                                        <span class="badge bg-warning">Đã hủy</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $invoice->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('agent.invoices.show', $invoice->id) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($lease->invoices->count() > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('agent.invoices.index', ['lease_id' => $lease->id]) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>Xem tất cả {{ $lease->invoices->count() }} hóa đơn
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-file-invoice fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Chưa có hóa đơn nào</p>
                            <small class="text-muted">Hóa đơn sẽ được tạo tự động hoặc thủ công</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Meters Section -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Công tơ và số liệu
                    </h5>
                    <a href="{{ route('agent.meters.index', ['unit_id' => $lease->unit_id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-list me-1"></i>Quản lý công tơ
                    </a>
                </div>
                <div class="card-body">
                    @if($meters->count() > 0)
                        @foreach($meters as $meter)
                            <div class="meter-item mb-4 p-3 border rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="fas fa-tachometer-alt me-1"></i>
                                            {{ $meter->service->name ?? 'Dịch vụ không xác định' }}
                                        </h6>
                                        <small class="text-muted">
                                            Số seri: {{ $meter->serial_no ?? 'N/A' }} | 
                                            Lắp đặt: {{ $meter->installed_at ? $meter->installed_at->format('d/m/Y') : 'N/A' }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{ $meter->status ? 'success' : 'danger' }}">
                                        {{ $meter->status ? 'Hoạt động' : 'Ngừng hoạt động' }}
                                    </span>
                                </div>
                                
                                @if($meter->readings->count() > 0)
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Ngày đọc</th>
                                                    <th>Chỉ số</th>
                                                    <th>Người đọc</th>
                                                    <th>Ghi chú</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($meter->readings->take(5) as $reading)
                                                    <tr>
                                                        <td>{{ $reading->reading_date->format('d/m/Y') }}</td>
                                                        <td class="fw-bold">{{ number_format($reading->value, 3) }}</td>
                                                        <td>{{ $reading->takenBy->full_name ?? 'N/A' }}</td>
                                                        <td>{{ $reading->note ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($meter->readings->count() > 5)
                                        <div class="text-center mt-2">
                                            <small class="text-muted">
                                                Hiển thị 5 chỉ số gần nhất. 
                                                <a href="{{ route('agent.meters.show', $meter->id) }}">Xem tất cả</a>
                                            </small>
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-2">
                                        <i class="fas fa-exclamation-triangle text-warning me-1"></i>
                                        <span class="text-muted">Chưa có số liệu đọc công tơ</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-tachometer-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Chưa có công tơ nào</p>
                            <small class="text-muted">Công tơ cần được lắp đặt cho phòng này</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Property Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-building me-2"></i>Bất động sản
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Tên bất động sản</label>
                        <p class="mb-0 fw-bold">{{ $lease->unit->property->name ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Địa chỉ cũ</label>
                        <p class="mb-0">{{ $lease->unit->property->old_address ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Địa chỉ mới (2025)</label>
                        <p class="mb-0">{{ $lease->unit->property->new_address ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Chủ sở hữu</label>
                        <p class="mb-0">{{ $lease->unit->property->owner_name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Unit Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin phòng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Mã phòng</label>
                        <p class="mb-0 fw-bold">{{ $lease->unit->code ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Tầng</label>
                        <p class="mb-0">{{ $lease->unit->floor ?? 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Diện tích</label>
                        <p class="mb-0">{{ $lease->unit->area_m2 ? $lease->unit->area_m2 . ' m²' : 'N/A' }}</p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Loại phòng</label>
                        <p class="mb-0">
                            @switch($lease->unit->unit_type)
                                @case('room')
                                    <span class="badge bg-primary">Phòng</span>
                                    @break
                                @case('apartment')
                                    <span class="badge bg-info">Căn hộ</span>
                                    @break
                                @case('dorm')
                                    <span class="badge bg-warning">Ký túc xá</span>
                                    @break
                                @case('shared')
                                    <span class="badge bg-secondary">Chung</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                    <div class="info-item mb-3">
                        <label class="form-label text-muted small">Số người tối đa</label>
                        <p class="mb-0">{{ $lease->unit->max_occupancy ?? 'N/A' }} người</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Thao tác nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.leases.edit', $lease->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit me-1"></i>Chỉnh sửa hợp đồng
                        </a>
                        <a href="{{ route('agent.units.show', $lease->unit->id) }}" class="btn btn-outline-info">
                            <i class="fas fa-door-open me-1"></i>Xem chi tiết phòng
                        </a>
                        <a href="{{ route('agent.rented.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>Danh sách phòng thuê
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Resident Modal -->
<div class="modal fade" id="addResidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm người ở cùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addResidentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tìm người dùng đã có tài khoản (tùy chọn)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="resident_user_search_input" placeholder="Gõ tên, SĐT hoặc email để tìm kiếm..." readonly>
                            <button type="button" class="btn btn-outline-primary" id="searchUserBtn">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <input type="hidden" id="resident_user_id" name="user_id">
                        <div class="form-text">Tìm kiếm người dùng tenant từ tổ chức của bạn và tổ chức mặc định. Để trống để xem tất cả.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="resident_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="resident_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="resident_phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="resident_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="resident_id_number" class="form-label">CMND/CCCD</label>
                        <input type="text" class="form-control" id="resident_id_number" name="id_number">
                    </div>
                    <div class="mb-3">
                        <label for="resident_note" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="resident_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Resident Modal -->
<div class="modal fade" id="editResidentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa người ở cùng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editResidentForm">
                <input type="hidden" id="edit_resident_id" name="resident_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tìm người dùng đã có tài khoản (tùy chọn)</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="edit_resident_user_search_input" placeholder="Gõ tên, SĐT hoặc email để tìm kiếm..." readonly>
                            <button type="button" class="btn btn-outline-primary" id="editSearchUserBtn">
                                <i class="fas fa-search"></i> Tìm kiếm
                            </button>
                        </div>
                        <input type="hidden" id="edit_resident_user_id" name="user_id">
                        <div class="form-text">Tìm kiếm người dùng tenant từ tổ chức của bạn và tổ chức mặc định. Để trống để xem tất cả.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_resident_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_resident_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_resident_phone" class="form-label">Số điện thoại</label>
                        <input type="text" class="form-control" id="edit_resident_phone" name="phone">
                    </div>
                    <div class="mb-3">
                        <label for="edit_resident_id_number" class="form-label">CMND/CCCD</label>
                        <input type="text" class="form-control" id="edit_resident_id_number" name="id_number">
                    </div>
                    <div class="mb-3">
                        <label for="edit_resident_note" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="edit_resident_note" name="note" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-warning">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- User Search Modal -->
<div class="modal fade" id="userSearchModal" tabindex="-1" aria-labelledby="userSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userSearchModalLabel">
                    <i class="fas fa-users me-2"></i>Tìm kiếm người dùng
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="userSearchQuery" class="form-label">Tìm kiếm</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="userSearchQuery" placeholder="Gõ tên, số điện thoại hoặc email...">
                        <button type="button" class="btn btn-primary" id="performUserSearch">
                            <i class="fas fa-search"></i> Tìm kiếm
                        </button>
                    </div>
                    <div class="form-text">Tìm kiếm người dùng tenant từ tổ chức của bạn và tổ chức mặc định. Để trống để xem tất cả.</div>
                </div>
                
                <div id="userSearchResults" class="mt-3">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <p>Nhập từ khóa tìm kiếm hoặc để trống để xem tất cả người dùng tenant</p>
                        <small>Chỉ hiển thị người dùng có role "tenant" trong tổ chức của bạn và tổ chức mặc định</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const leaseId = {{ $lease->id }};
    
    // User search functionality
    let currentSearchContext = 'add'; // 'add' or 'edit'
    
    // Initialize user search when modal is shown
    document.getElementById('addResidentModal').addEventListener('shown.bs.modal', function() {
        currentSearchContext = 'add';
        // Clear previous search
        document.getElementById('resident_user_search_input').value = '';
        document.getElementById('resident_user_id').value = '';
    });
    
    // Initialize edit user search when modal is shown
    document.getElementById('editResidentModal').addEventListener('shown.bs.modal', function() {
        currentSearchContext = 'edit';
        // Clear previous search
        document.getElementById('edit_resident_user_search_input').value = '';
        document.getElementById('edit_resident_user_id').value = '';
    });
    
    // Search user button click handlers
    document.getElementById('searchUserBtn').addEventListener('click', function() {
        currentSearchContext = 'add';
        openUserSearchModal();
    });
    
    document.getElementById('editSearchUserBtn').addEventListener('click', function() {
        currentSearchContext = 'edit';
        openUserSearchModal();
    });
    
    // User search modal functionality
    document.getElementById('performUserSearch').addEventListener('click', function() {
        performUserSearch();
    });
    
    // Enter key in search input
    document.getElementById('userSearchQuery').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            performUserSearch();
        }
    });
    
    function openUserSearchModal() {
        // Clear previous search
        document.getElementById('userSearchQuery').value = '';
        document.getElementById('userSearchResults').innerHTML = `
            <div class="text-center text-muted py-4">
                <i class="fas fa-users fa-2x mb-2"></i>
                <p>Nhập từ khóa tìm kiếm hoặc để trống để xem tất cả người dùng tenant</p>
                <small>Chỉ hiển thị người dùng có role "tenant" trong tổ chức của bạn và tổ chức mặc định</small>
            </div>
        `;
        
        // Show modal
        new bootstrap.Modal(document.getElementById('userSearchModal')).show();
    }
    
    function performUserSearch() {
        const query = document.getElementById('userSearchQuery').value.trim();
        
        // Allow empty query to show all tenant users
        if (query.length > 0 && query.length < 2) {
            alert('Vui lòng nhập ít nhất 2 ký tự để tìm kiếm hoặc để trống để xem tất cả');
            return;
        }
        
        // Show loading
        document.getElementById('userSearchResults').innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tìm kiếm...</span>
                </div>
                <p class="mt-2">Đang tìm kiếm người dùng...</p>
            </div>
        `;
        
        // Perform search
        fetch(`/agent/api/leases/search-users?q=${encodeURIComponent(query)}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(users => {
                displayUserSearchResults(users);
            })
            .catch(error => {
                console.error('Error searching users:', error);
                document.getElementById('userSearchResults').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Có lỗi xảy ra khi tìm kiếm: ${error.message}
                    </div>
                `;
            });
    }
    
    function displayUserSearchResults(users) {
        const resultsContainer = document.getElementById('userSearchResults');
        
        if (!Array.isArray(users) || users.length === 0) {
            resultsContainer.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="fas fa-user-times fa-2x mb-2"></i>
                    <p>Không tìm thấy người dùng tenant nào</p>
                    <small>Chỉ hiển thị người dùng có role "tenant" trong tổ chức của bạn và tổ chức mặc định</small>
                </div>
            `;
            return;
        }
        
        let html = '<div class="list-group">';
        users.forEach(user => {
            html += `
                <div class="list-group-item list-group-item-action user-search-item" 
                     data-user-id="${user.id}" 
                     data-user-data='${JSON.stringify(user)}'
                     style="cursor: pointer;">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${user.name}</h6>
                        <small class="text-muted">${user.phone}</small>
                    </div>
                    <p class="mb-1">${user.email}</p>
                    <small class="text-muted">${user.organizations}</small>
                </div>
            `;
        });
        html += '</div>';
        
        resultsContainer.innerHTML = html;
        
        // Add click handlers
        document.querySelectorAll('.user-search-item').forEach(item => {
            item.addEventListener('click', function() {
                selectUser(JSON.parse(this.dataset.userData));
            });
        });
    }
    
    function selectUser(userData) {
        if (currentSearchContext === 'add') {
            document.getElementById('resident_user_search_input').value = userData.text;
            document.getElementById('resident_user_id').value = userData.id;
            autoFillUserData(userData);
        } else {
            document.getElementById('edit_resident_user_search_input').value = userData.text;
            document.getElementById('edit_resident_user_id').value = userData.id;
            autoFillEditUserData(userData);
        }
        
        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('userSearchModal')).hide();
    }
    
    function autoFillUserData(userData) {
        document.getElementById('resident_name').value = userData.name || '';
        document.getElementById('resident_phone').value = userData.phone || '';
        document.getElementById('resident_id_number').value = userData.id_number || '';
    }
    
    function autoFillEditUserData(userData) {
        document.getElementById('edit_resident_name').value = userData.name || '';
        document.getElementById('edit_resident_phone').value = userData.phone || '';
        document.getElementById('edit_resident_id_number').value = userData.id_number || '';
    }
    
    // Add resident
    document.getElementById('addResidentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(`/agent/leases/${leaseId}/residents`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Thêm thành công');
                } else {
                    alert(data.message);
                }
                location.reload();
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi thêm');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi thêm người ở cùng', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi thêm người ở cùng');
            }
        });
    });
    
        // Edit resident
        document.addEventListener('click', function(e) {
            if (e.target.closest('.edit-resident')) {
                const button = e.target.closest('.edit-resident');
                const residentId = button.dataset.residentId;
                
                document.getElementById('edit_resident_id').value = residentId;
                document.getElementById('edit_resident_name').value = button.dataset.name;
                document.getElementById('edit_resident_phone').value = button.dataset.phone || '';
                document.getElementById('edit_resident_id_number').value = button.dataset.idNumber || '';
                document.getElementById('edit_resident_note').value = button.dataset.note || '';
                
                // Set user selection if exists
                if (button.dataset.userId && button.dataset.userName) {
                    document.getElementById('edit_resident_user_search_input').value = button.dataset.userName;
                    document.getElementById('edit_resident_user_id').value = button.dataset.userId;
                } else {
                    document.getElementById('edit_resident_user_search_input').value = '';
                    document.getElementById('edit_resident_user_id').value = '';
                }
                
                new bootstrap.Modal(document.getElementById('editResidentModal')).show();
            }
        });
    
    // Update resident
    document.getElementById('editResidentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const residentId = document.getElementById('edit_resident_id').value;
        const formData = new FormData(this);
        
        fetch(`/agent/leases/${leaseId}/residents/${residentId}`, {
            method: 'PUT',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Cập nhật thành công');
                } else {
                    alert(data.message);
                }
                location.reload();
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi cập nhật');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi cập nhật thông tin người ở cùng', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi cập nhật thông tin người ở cùng');
            }
        });
    });
    
    // Delete resident
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-resident')) {
            const button = e.target.closest('.delete-resident');
            const residentId = button.dataset.residentId;
            
            // Show confirmation dialog
            if (typeof Notify !== 'undefined') {
                Notify.confirmDelete('người ở cùng này', () => {
                    // User confirmed deletion
                    deleteResident(residentId);
                });
            } else {
                // Fallback to browser confirm
                if (confirm('Bạn có chắc chắn muốn xóa người ở cùng này?')) {
                    deleteResident(residentId);
                }
            }
        }
    });

    // Delete resident function
    function deleteResident(residentId) {
        fetch(`/agent/leases/${leaseId}/residents/${residentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success notification
                if (typeof Notify !== 'undefined') {
                    Notify.success(data.message, 'Xóa thành công');
                } else {
                    alert(data.message);
                }
                location.reload();
            } else {
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error(data.message, 'Lỗi xóa');
                } else {
                    alert('Lỗi: ' + data.message);
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error notification
            if (typeof Notify !== 'undefined') {
                Notify.error('Có lỗi xảy ra khi xóa người ở cùng', 'Lỗi hệ thống');
            } else {
                alert('Có lỗi xảy ra khi xóa người ở cùng');
            }
        });
    }
});
</script>

<!-- Edit Payment Cycle Modal -->
<div class="modal fade" id="editPaymentCycleModal" tabindex="-1" aria-labelledby="editPaymentCycleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentCycleModalLabel">Chỉnh sửa chu kỳ thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPaymentCycleForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_lease_payment_cycle" class="form-label">Chu kỳ thanh toán</label>
                        <select class="form-select" id="edit_lease_payment_cycle" name="lease_payment_cycle">
                            <option value="">-- Chọn chu kỳ --</option>
                            <option value="monthly" {{ $lease->lease_payment_cycle == 'monthly' ? 'selected' : '' }}>Hàng tháng</option>
                            <option value="quarterly" {{ $lease->lease_payment_cycle == 'quarterly' ? 'selected' : '' }}>Hàng quý</option>
                            <option value="yearly" {{ $lease->lease_payment_cycle == 'yearly' ? 'selected' : '' }}>Hàng năm</option>
                            <option value="custom" {{ $lease->lease_payment_cycle == 'custom' ? 'selected' : '' }}>Tùy chỉnh (nhập số tháng)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="edit_lease_custom_months_field" style="display: {{ $lease->lease_payment_cycle == 'custom' ? 'block' : 'none' }};">
                        <label for="edit_lease_custom_months" class="form-label">Số tháng tùy chỉnh</label>
                        <input type="number" class="form-control" id="edit_lease_custom_months" name="lease_custom_months" 
                               value="{{ $lease->lease_custom_months }}" min="1" max="60" 
                               placeholder="Nhập số tháng (1-60)">
                        <div class="form-text">Số tháng cho chu kỳ thanh toán tùy chỉnh (1-60)</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_lease_payment_day" class="form-label">Hạn thanh toán</label>
                        <select class="form-select" id="edit_lease_payment_day" name="lease_payment_day">
                            @for($i = 1; $i <= 31; $i++)
                                <option value="{{ $i }}" {{ $i == $lease->lease_payment_day ? 'selected' : '' }}>
                                    Ngày {{ $i }}
                                </option>
                            @endfor
                        </select>
                        <div class="form-text">Ngày hạn thanh toán trong chu kỳ</div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_lease_payment_notes" class="form-label">Ghi chú chu kỳ thanh toán</label>
                        <textarea class="form-control" id="edit_lease_payment_notes" name="lease_payment_notes" rows="3" 
                                  placeholder="Ghi chú về chu kỳ thanh toán...">{{ $lease->lease_payment_notes }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Payment cycle change handler for modal
document.getElementById('edit_lease_payment_cycle').addEventListener('change', function() {
    const customMonthsField = document.getElementById('edit_lease_custom_months_field');
    if (this.value === 'custom') {
        customMonthsField.style.display = 'block';
    } else {
        customMonthsField.style.display = 'none';
    }
});

// Edit payment cycle form submission
document.getElementById('editPaymentCycleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch(`{{ route('agent.leases.update', $lease->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            ...data,
            _method: 'PUT',
            // Include other required fields with current values
            unit_id: {{ $lease->unit_id }},
            tenant_id: {{ $lease->tenant_id }},
            start_date: '{{ $lease->start_date }}',
            end_date: '{{ $lease->end_date }}',
            rent_amount: '{{ number_format($lease->rent_amount, 0, ',', '.') }}',
            deposit_amount: '{{ number_format($lease->deposit_amount, 0, ',', '.') }}',
            billing_day: {{ $lease->billing_day }},
            status: '{{ $lease->status }}',
            contract_no: '{{ $lease->contract_no }}',
            signed_at: '{{ $lease->signed_at }}'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success || response.ok) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('editPaymentCycleModal'));
            modal.hide();
            
            // Reload page to show updated data
            location.reload();
        } else {
            alert('Có lỗi xảy ra khi cập nhật chu kỳ thanh toán');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật chu kỳ thanh toán');
    });
});
</script>
@endpush
