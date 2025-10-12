@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết công tơ đo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-tachometer-alt me-2"></i>Công tơ đo #{{ $meter->serial_no }}
                    </h1>
                    <p class="text-muted mb-0">{{ $meter->property->name }} - {{ $meter->unit->code }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('agent.meters.edit', $meter->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                    <a href="{{ route('agent.meter-readings.create', ['meter_id' => $meter->id]) }}" class="btn btn-info">
                        <i class="fas fa-plus me-1"></i>Thêm số liệu
                    </a>
                    <a href="{{ route('agent.meters.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Meter Information -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin công tơ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Số seri:</strong></div>
                        <div class="col-sm-8">{{ $meter->serial_no }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Bất động sản:</strong></div>
                        <div class="col-sm-8">{{ $meter->property->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Phòng:</strong></div>
                        <div class="col-sm-8">{{ $meter->unit->code }} ({{ $meter->unit->unit_type }})</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Dịch vụ:</strong></div>
                        <div class="col-sm-8">
                            {{ $meter->service->name }}
                            <small class="text-muted d-block">({{ $meter->service->key_code }})</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Ngày lắp đặt:</strong></div>
                        <div class="col-sm-8">{{ $meter->installed_at->format('d/m/Y') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                        <div class="col-sm-8">
                            @if($meter->status)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Ngừng hoạt động</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Lease Info -->
            @if($currentLease)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract me-2"></i>Hợp đồng hiện tại
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $tenantInfo = $currentLease->getTenantInfo();
                        @endphp
                        @if($tenantInfo)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Khách thuê:</strong></div>
                                <div class="col-sm-8">{{ $tenantInfo['name'] }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>SĐT:</strong></div>
                                <div class="col-sm-8">{{ $tenantInfo['phone'] }}</div>
                            </div>
                        @endif
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày bắt đầu:</strong></div>
                            <div class="col-sm-8">{{ $currentLease->start_date->format('d/m/Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày kết thúc:</strong></div>
                            <div class="col-sm-8">{{ $currentLease->end_date->format('d/m/Y') }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Meter Readings -->
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Lịch sử số liệu đo
                    </h5>
                    <a href="{{ route('agent.meter-readings.create', ['meter_id' => $meter->id]) }}" 
                       class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i>Thêm số liệu
                    </a>
                </div>
                <div class="card-body">
                    @if($meter->readings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Ngày đo</th>
                                        <th>Số liệu</th>
                                        <th>Lượng sử dụng</th>
                                        <th>Người đo</th>
                                        <th>Ghi chú</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($meter->readings as $index => $reading)
                                        @php
                                            $previousReading = $index < $meter->readings->count() - 1 
                                                ? $meter->readings[$index + 1] 
                                                : null;
                                            $usage = $previousReading ? $reading->value - $previousReading->value : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $reading->reading_date->format('d/m/Y') }}</div>
                                                <small class="text-muted">{{ $reading->reading_date->format('H:i') }}</small>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-primary">
                                                    {{ number_format($reading->value, 3) }}
                                                </div>
                                                <small class="text-muted">{{ $meter->service->unit_label }}</small>
                                            </td>
                                            <td>
                                                @if($usage > 0)
                                                    <div class="fw-bold text-success">
                                                        +{{ number_format($usage, 3) }}
                                                    </div>
                                                    <small class="text-muted">{{ $meter->service->unit_label }}</small>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>{{ $reading->takenBy->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $reading->takenBy->email ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if($reading->note)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;" 
                                                          title="{{ $reading->note }}">
                                                        {{ $reading->note }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.meter-readings.show', $reading->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('agent.meter-readings.edit', $reading->id) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có số liệu đo</h5>
                            <p class="text-muted">Hãy thêm số liệu đo đầu tiên để bắt đầu theo dõi.</p>
                            <a href="{{ route('agent.meter-readings.create', ['meter_id' => $meter->id]) }}" 
                               class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Thêm số liệu đo
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Billing History -->
            @if($billingHistory->count() > 0)
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-receipt me-2"></i>Lịch sử tính tiền
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tháng</th>
                                        <th>Số đầu</th>
                                        <th>Số cuối</th>
                                        <th>Lượng sử dụng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($billingHistory as $billing)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $billing->month)->format('m/Y') }}</div>
                                            </td>
                                            <td>{{ number_format($billing->start_reading, 3) }}</td>
                                            <td>{{ number_format($billing->end_reading, 3) }}</td>
                                            <td>
                                                <div class="fw-bold text-primary">
                                                    {{ number_format($billing->usage, 3) }}
                                                </div>
                                                <small class="text-muted">{{ $meter->service->unit_label }}</small>
                                            </td>
                                            <td>{{ number_format($billing->cost / $billing->usage, 0) }} đ/{{ $meter->service->unit_label }}</td>
                                            <td>
                                                <div class="fw-bold text-success">
                                                    {{ number_format($billing->cost, 0) }} đ
                                                </div>
                                            </td>
                                        </tr>
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

@push('scripts')
<script>
// Auto-refresh page every 5 minutes to show latest readings
setInterval(function() {
    // Only refresh if user is not actively interacting
    if (document.hidden) {
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>
@endpush
