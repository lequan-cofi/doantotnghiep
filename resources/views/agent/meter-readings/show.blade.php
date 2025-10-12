@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết số liệu đo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-chart-line me-2"></i>Số liệu đo #{{ $meterReading->id }}
                    </h1>
                    <p class="text-muted mb-0">{{ $meterReading->meter->property->name }} - {{ $meterReading->meter->unit->code }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('agent.meter-readings.edit', $meterReading->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </a>
                    <a href="{{ route('agent.meter-readings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Reading Information -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin số liệu đo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Ngày đo:</strong></div>
                        <div class="col-sm-8">
                            <div class="fw-bold">{{ $meterReading->reading_date->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $meterReading->reading_date->format('H:i:s') }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Số liệu:</strong></div>
                        <div class="col-sm-8">
                            <div class="fw-bold text-primary fs-4">
                                {{ number_format($meterReading->value, 3) }}
                            </div>
                            <small class="text-muted">{{ $meterReading->meter->service->unit_label }}</small>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Lượng sử dụng:</strong></div>
                        <div class="col-sm-8">
                            @if($usage > 0)
                                <div class="fw-bold text-success fs-5">
                                    +{{ number_format($usage, 3) }}
                                </div>
                                <small class="text-muted">{{ $meterReading->meter->service->unit_label }}</small>
                            @else
                                <span class="text-muted">Không có dữ liệu trước đó</span>
                            @endif
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Người đo:</strong></div>
                        <div class="col-sm-8">
                            <div>{{ $meterReading->takenBy->name ?? 'N/A' }}</div>
                            <small class="text-muted">{{ $meterReading->takenBy->email ?? '' }}</small>
                        </div>
                    </div>
                    @if($meterReading->note)
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ghi chú:</strong></div>
                            <div class="col-sm-8">{{ $meterReading->note }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Meter Information -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Thông tin công tơ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Số seri:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->serial_no }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Bất động sản:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->property->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Phòng:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->unit->code }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Dịch vụ:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->service->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                        <div class="col-sm-8">
                            @if($meterReading->meter->status)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Ngừng hoạt động</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Information -->
            @if($usage > 0 && $servicePrice > 0)
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calculator me-2"></i>Tính toán hóa đơn
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Lượng sử dụng:</strong></div>
                            <div class="col-sm-8">{{ number_format($usage, 3) }} {{ $meterReading->meter->service->unit_label }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Đơn giá:</strong></div>
                            <div class="col-sm-8">{{ number_format($servicePrice, 0) }} đ/{{ $meterReading->meter->service->unit_label }}</div>
                        </div>
                        <hr>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Thành tiền:</strong></div>
                            <div class="col-sm-8">
                                <div class="fw-bold text-success fs-5">
                                    {{ number_format($cost, 0) }} đ
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Reading Details and Navigation -->
        <div class="col-lg-8">
            <!-- Image Display -->
            @if($meterReading->image_url)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-image me-2"></i>Hình ảnh công tơ
                        </h5>
                    </div>
                    <div class="card-body text-center">
                        <img src="{{ Storage::url($meterReading->image_url) }}" 
                             class="img-fluid rounded shadow" 
                             style="max-height: 400px;" 
                             alt="Hình ảnh công tơ">
                        <div class="mt-2">
                            <a href="{{ Storage::url($meterReading->image_url) }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-external-link-alt me-1"></i>Xem ảnh gốc
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Reading Navigation -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-arrows-alt-h me-2"></i>Điều hướng số liệu
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($previousReading)
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-left me-2"></i>Số liệu trước
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Ngày:</strong></div>
                                            <div class="col-sm-8">{{ $previousReading->reading_date->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Số liệu:</strong></div>
                                            <div class="col-sm-8">
                                                <div class="fw-bold text-primary">
                                                    {{ number_format($previousReading->value, 3) }}
                                                </div>
                                                <small class="text-muted">{{ $meterReading->meter->service->unit_label }}</small>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Người đo:</strong></div>
                                            <div class="col-sm-8">{{ $previousReading->takenBy->name ?? 'N/A' }}</div>
                                        </div>
                                        <a href="{{ route('agent.meter-readings.show', $previousReading->id) }}" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-left me-2"></i>Số liệu trước
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Đây là số liệu đo đầu tiên</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-6">
                            @if($nextReading)
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-right me-2"></i>Số liệu sau
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Ngày:</strong></div>
                                            <div class="col-sm-8">{{ $nextReading->reading_date->format('d/m/Y') }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Số liệu:</strong></div>
                                            <div class="col-sm-8">
                                                <div class="fw-bold text-success">
                                                    {{ number_format($nextReading->value, 3) }}
                                                </div>
                                                <small class="text-muted">{{ $meterReading->meter->service->unit_label }}</small>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-sm-4"><strong>Người đo:</strong></div>
                                            <div class="col-sm-8">{{ $nextReading->takenBy->name ?? 'N/A' }}</div>
                                        </div>
                                        <a href="{{ route('agent.meter-readings.show', $nextReading->id) }}" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="card border-secondary">
                                    <div class="card-header bg-secondary text-white">
                                        <h6 class="mb-0">
                                            <i class="fas fa-arrow-right me-2"></i>Số liệu sau
                                        </h6>
                                    </div>
                                    <div class="card-body text-center">
                                        <i class="fas fa-info-circle fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">Đây là số liệu đo mới nhất</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Usage Chart (if multiple readings) -->
            @if($previousReading && $nextReading)
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-chart-line me-2"></i>Biểu đồ sử dụng
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Trước</h6>
                                    <div class="fw-bold text-primary fs-4">
                                        {{ number_format($previousReading->value, 3) }}
                                    </div>
                                    <small class="text-muted">{{ $previousReading->reading_date->format('d/m/Y') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 bg-light">
                                    <h6 class="text-muted">Hiện tại</h6>
                                    <div class="fw-bold text-success fs-4">
                                        {{ number_format($meterReading->value, 3) }}
                                    </div>
                                    <small class="text-muted">{{ $meterReading->reading_date->format('d/m/Y') }}</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3">
                                    <h6 class="text-muted">Sau</h6>
                                    <div class="fw-bold text-info fs-4">
                                        {{ number_format($nextReading->value, 3) }}
                                    </div>
                                    <small class="text-muted">{{ $nextReading->reading_date->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>
                        
                        @if($usage > 0)
                            <div class="mt-3 text-center">
                                <div class="alert alert-success">
                                    <h6 class="mb-1">
                                        <i class="fas fa-arrow-up me-2"></i>Lượng sử dụng trong kỳ
                                    </h6>
                                    <div class="fw-bold fs-4">
                                        +{{ number_format($usage, 3) }} {{ $meterReading->meter->service->unit_label }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Auto-refresh page every 5 minutes to show latest data
setInterval(function() {
    // Only refresh if user is not actively interacting
    if (document.hidden) {
        window.location.reload();
    }
}, 300000); // 5 minutes
</script>
@endpush
