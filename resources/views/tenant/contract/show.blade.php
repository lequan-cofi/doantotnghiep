@extends('layouts.app')

@section('title', 'Chi tiết hợp đồng')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/contracts.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/user/contracts-show.css') }}?v={{ time() }}">
<style>
.service-item {
    display: block;
    font-size: 0.9em;
    color: #555;
    margin-bottom: 4px;
    padding: 2px 0;
}
.service-item strong {
    color: #333;
}
.status-draft {
    background-color: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/contracts-show.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="contract-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.contracts.index') }}">Hợp đồng</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
            </ol>
        </nav>

        <!-- Contract Header -->
        <div class="contract-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="contract-title-section">
                        <h1 class="contract-title">{{ $contract->unit->property->name }}</h1>
                        <div class="contract-meta">
                            <span class="contract-number">Mã hợp đồng: {{ $contract->contract_no ?? 'HD' . str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <span class="contract-status-badge {{ $isExpired ? 'expired' : ($isExpiring ? 'expiring' : 'active') }}">
                                @if($isExpired)
                                    <i class="fas fa-times-circle"></i> Đã hết hạn
                                @elseif($isExpiring)
                                    <i class="fas fa-exclamation-triangle"></i> Sắp hết hạn
                                @else
                                    <i class="fas fa-check-circle"></i> Đang hiệu lực
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="contract-actions">
                        <button class="btn btn-outline-success" onclick="downloadContract('{{ $contract->id }}')">
                            <i class="fas fa-download me-1"></i>Tải PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="printContract()">
                            <i class="fas fa-print me-1"></i>In hợp đồng
                        </button>
                        @if(!$isExpired)
                            <button class="btn {{ $isExpiring ? 'btn-warning' : 'btn-outline-warning' }}" onclick="renewContract('{{ $contract->id }}')">
                                <i class="fas fa-refresh me-1"></i>Gia hạn
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Contract Information -->
        <div class="contract-info-section">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-lg-6 mb-4">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Mã hợp đồng:</span>
                                <span class="info-value">{{ $contract->contract_no ?? 'HD' . str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày ký:</span>
                                <span class="info-value">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y H:i') : 'Chưa ký' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày bắt đầu:</span>
                                <span class="info-value">{{ $contract->start_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày kết thúc:</span>
                                <span class="info-value {{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '') }}">{{ $contract->end_date->format('d/m/Y') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Thời gian còn lại:</span>
                                <span class="info-value {{ $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : 'text-success') }}">
                                    @if($isExpired)
                                        Đã hết hạn
                                    @elseif($remainingDays < 30)
                                        Còn {{ $remainingDays }} ngày
                                    @else
                                        Còn {{ floor($remainingDays / 30) }} tháng
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Information -->
                <div class="col-lg-6 mb-4">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-home me-2"></i>Thông tin phòng
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Tên phòng:</span>
                                <span class="info-value">
                                    {{ $contract->unit->property->name }}
                                    @if($contract->unit->code)
                                        - {{ $contract->unit->code }}
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Địa chỉ:</span>
                                <div class="info-value">
                                    @php
                                        $locationAddress = null;
                                        $location2025Address = null;
                                        
                                        if ($contract->unit->property->location) {
                                            $addressParts = [];
                                            if ($contract->unit->property->location->street) $addressParts[] = $contract->unit->property->location->street;
                                            if ($contract->unit->property->location->ward) $addressParts[] = $contract->unit->property->location->ward;
                                            if ($contract->unit->property->location->district) $addressParts[] = $contract->unit->property->location->district;
                                            if ($contract->unit->property->location->city) $addressParts[] = $contract->unit->property->location->city;
                                            if ($contract->unit->property->location->country && $contract->unit->property->location->country !== 'Vietnam') $addressParts[] = $contract->unit->property->location->country;
                                            $locationAddress = !empty($addressParts) ? implode(', ', $addressParts) : null;
                                        }
                                        
                                        if ($contract->unit->property->location2025) {
                                            $addressParts2025 = [];
                                            if ($contract->unit->property->location2025->street) $addressParts2025[] = $contract->unit->property->location2025->street;
                                            if ($contract->unit->property->location2025->ward) $addressParts2025[] = $contract->unit->property->location2025->ward;
                                            if ($contract->unit->property->location2025->city) $addressParts2025[] = $contract->unit->property->location2025->city;
                                            if ($contract->unit->property->location2025->country && $contract->unit->property->location2025->country !== 'Vietnam') $addressParts2025[] = $contract->unit->property->location2025->country;
                                            $location2025Address = !empty($addressParts2025) ? implode(', ', $addressParts2025) : null;
                                        }
                                    @endphp
                                    @if($locationAddress)
                                        <div class="address-item">
                                            <span class="address-label">Địa chỉ cũ:</span>
                                            <span class="address-value">{{ $locationAddress }}</span>
                                        </div>
                                    @endif
                                    @if($location2025Address)
                                        <div class="address-item">
                                            <span class="address-label">Địa chỉ mới:</span>
                                            <span class="address-value">{{ $location2025Address }}</span>
                                        </div>
                                    @endif
                                    @if(!$locationAddress && !$location2025Address)
                                        <span class="address-value">Địa chỉ chưa cập nhật</span>
                                    @endif
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Loại phòng:</span>
                                <span class="info-value">{{ $contract->unit->property->propertyType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Diện tích:</span>
                                <span class="info-value">{{ $contract->unit->area_m2 ? $contract->unit->area_m2 . ' m²' : 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tầng:</span>
                                <span class="info-value">{{ $contract->unit->floor ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Information -->
                <div class="col-lg-6 mb-4">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-money-bill-wave me-2"></i>Thông tin tài chính
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Giá thuê:</span>
                                <span class="info-value price">{{ number_format($contract->rent_amount) }} VNĐ/tháng</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Tiền cọc:</span>
                                <span class="info-value price">{{ number_format($contract->deposit_amount) }} VNĐ</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Chu kỳ thanh toán:</span>
                                <span class="info-value">{{ $contract->lease_payment_cycle ?? 'Hàng tháng' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày thanh toán:</span>
                                <span class="info-value">{{ $contract->lease_payment_day ?? $contract->billing_day }} hàng tháng</span>
                            </div>
                            @if($contract->leaseServices && $contract->leaseServices->count() > 0)
                                <div class="info-item">
                                    <span class="info-label">Dịch vụ đi kèm:</span>
                                    <div class="info-value">
                                        @foreach($contract->leaseServices as $leaseService)
                                            <div class="service-item">
                                                <strong>{{ $leaseService->service->name ?? 'N/A' }}:</strong> 
                                                {{ number_format($leaseService->price) }} VNĐ
                                                @if($leaseService->service->unit)
                                                    / {{ $leaseService->service->unit }}
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="col-lg-6 mb-4">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-users me-2"></i>Thông tin liên hệ
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Chủ nhà/Agent:</span>
                                <span class="info-value">
                                    @if($contract->agent)
                                        {{ $contract->agent->full_name ?? $contract->agent->name }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value">
                                    @if($contract->agent && $contract->agent->phone)
                                        <a href="tel:{{ $contract->agent->phone }}">{{ $contract->agent->phone }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">
                                    @if($contract->agent && $contract->agent->email)
                                        <a href="mailto:{{ $contract->agent->email }}">{{ $contract->agent->email }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Người thuê:</span>
                                <span class="info-value">{{ $contract->tenant->full_name ?? $contract->tenant->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meter Readings Section -->
        <div class="meter-readings-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-tachometer-alt me-2"></i>Chỉ số công tơ điện/nước
                </h2>
            </div>

            <!-- Meter Readings Tabs -->
            <ul class="nav nav-tabs" id="meterTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                        <i class="fas fa-chart-line me-1"></i>Tóm tắt gần nhất
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                        <i class="fas fa-history me-1"></i>Lịch sử đầy đủ
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="meterTabContent">
                <!-- Summary Tab -->
                <div class="tab-pane fade show active" id="summary" role="tabpanel">
                    <div class="meter-summary">
                        @forelse($meterReadingsSummary as $serviceName => $readings)
                            <div class="meter-service-group">
                                <h4 class="service-name">{{ $serviceName }}</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped meter-table">
                                        <thead>
                                            <tr>
                                                <th>Ngày ghi</th>
                                                <th>Chỉ số cũ</th>
                                                <th>Chỉ số mới</th>
                                                <th>Tiêu thụ</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($readings->filter(function($reading) use ($contract) {
                                                return $reading->reading_date >= $contract->start_date;
                                            }) as $reading)
                                                @php
                                                    // Tìm previous reading trong thời gian hợp đồng
                                                    $previousReading = $reading->meter->readings()
                                                        ->where('reading_date', '<', $reading->reading_date)
                                                        ->where('reading_date', '>=', $contract->start_date)
                                                        ->latest('reading_date')
                                                        ->first();
                                                    
                                                    // Nếu không có previous reading trong thời gian hợp đồng, 
                                                    // đây là số đo đầu tiên -> thành tiền = 0
                                                    if (!$previousReading) {
                                                        $previousReading = $reading->meter->readings()
                                                            ->where('reading_date', '<', $contract->start_date)
                                                            ->latest('reading_date')
                                                            ->first();
                                                        $usage = 0; // Số đo đầu tiên = 0
                                                    } else {
                                                        // Có previous reading trong thời gian hợp đồng -> tính bình thường
                                                        $usage = max(0, $reading->value - $previousReading->value);
                                                    }
                                                    
                                                    $price = $contract->leaseServices->where('service_id', $reading->meter->service_id)->first()->price ?? 0;
                                                    $total = $usage * $price;
                                                @endphp
                                                <tr>
                                                    <td>{{ $reading->reading_date->format('d/m/Y') }}</td>
                                                    <td>{{ $previousReading ? number_format($previousReading->value, 3) : '0.000' }}</td>
                                                    <td>{{ number_format($reading->value, 3) }}</td>
                                                    <td>{{ number_format($usage, 3) }}</td>
                                                    <td>{{ number_format($price) }} VNĐ</td>
                                                    <td class="price">{{ number_format($total) }} VNĐ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Chưa có dữ liệu chỉ số công tơ</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- History Tab -->
                <div class="tab-pane fade" id="history" role="tabpanel">
                    <div class="meter-history">
                        @forelse($meterReadingsHistory as $serviceName => $readings)
                            <div class="meter-service-group">
                                <h4 class="service-name">{{ $serviceName }}</h4>
                                <div class="table-responsive">
                                    <table class="table table-striped meter-table">
                                        <thead>
                                            <tr>
                                                <th>Ngày ghi</th>
                                                <th>Chỉ số cũ</th>
                                                <th>Chỉ số mới</th>
                                                <th>Tiêu thụ</th>
                                                <th>Đơn giá</th>
                                                <th>Thành tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($readings->filter(function($reading) use ($contract) {
                                                return $reading->reading_date >= $contract->start_date;
                                            }) as $reading)
                                                @php
                                                    // Tìm previous reading trong thời gian hợp đồng
                                                    $previousReading = $reading->meter->readings()
                                                        ->where('reading_date', '<', $reading->reading_date)
                                                        ->where('reading_date', '>=', $contract->start_date)
                                                        ->latest('reading_date')
                                                        ->first();
                                                    
                                                    // Nếu không có previous reading trong thời gian hợp đồng, 
                                                    // đây là số đo đầu tiên -> thành tiền = 0
                                                    if (!$previousReading) {
                                                        $previousReading = $reading->meter->readings()
                                                            ->where('reading_date', '<', $contract->start_date)
                                                            ->latest('reading_date')
                                                            ->first();
                                                        $usage = 0; // Số đo đầu tiên = 0
                                                    } else {
                                                        // Có previous reading trong thời gian hợp đồng -> tính bình thường
                                                        $usage = max(0, $reading->value - $previousReading->value);
                                                    }
                                                    
                                                    $price = $contract->leaseServices->where('service_id', $reading->meter->service_id)->first()->price ?? 0;
                                                    $total = $usage * $price;
                                                @endphp
                                                <tr>
                                                    <td>{{ $reading->reading_date->format('d/m/Y') }}</td>
                                                    <td>{{ $previousReading ? number_format($previousReading->value, 3) : '0.000' }}</td>
                                                    <td>{{ number_format($reading->value, 3) }}</td>
                                                    <td>{{ number_format($usage, 3) }}</td>
                                                    <td>{{ number_format($price) }} VNĐ</td>
                                                    <td class="price">{{ number_format($total) }} VNĐ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="empty-state">
                                <i class="fas fa-tachometer-alt"></i>
                                <p>Chưa có dữ liệu chỉ số công tơ</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices Section -->
        <div class="invoices-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-file-invoice me-2"></i>Hóa đơn
                </h2>
            </div>

            <!-- Invoice Filters -->
            <div class="invoice-filters">
                <div class="filter-tabs">
                    <button class="filter-tab active" data-status="all">Tất cả</button>
                    <button class="filter-tab" data-status="draft">Nháp</button>
                    <button class="filter-tab" data-status="issued">Chưa trả</button>
                    <button class="filter-tab" data-status="paid">Đã trả</button>
                    <button class="filter-tab" data-status="overdue">Quá hạn</button>
                    <button class="filter-tab" data-status="cancelled">Đã hủy</button>
                </div>
            </div>

            <!-- Invoices Table -->
            <div class="table-responsive">
                <table class="table table-striped invoices-table">
                    <thead>
                        <tr>
                            <th>Mã hóa đơn</th>
                            <th>Ngày phát hành</th>
                            <th>Ngày đến hạn</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no ?? 'HD' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</td>
                                <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                <td>{{ $invoice->due_date->format('d/m/Y') }}</td>
                                <td class="price">{{ number_format($invoice->total_amount) }} VNĐ</td>
                                <td>
                                    <span class="status-badge status-{{ $invoice->status }}">
                                        @switch($invoice->status)
                                            @case('draft')
                                                <i class="fas fa-edit"></i> Nháp
                                                @break
                                            @case('issued')
                                                <i class="fas fa-clock"></i> Chưa trả
                                                @break
                                            @case('paid')
                                                <i class="fas fa-check"></i> Đã trả
                                                @break
                                            @case('overdue')
                                                <i class="fas fa-exclamation-triangle"></i> Quá hạn
                                                @break
                                            @case('cancelled')
                                                <i class="fas fa-times"></i> Đã hủy
                                                @break
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewInvoice('{{ $invoice->id }}')">
                                        <i class="fas fa-eye"></i> Xem
                                    </button>
                                    @if($invoice->status === 'issued')
                                        <button class="btn btn-sm btn-success" onclick="payInvoice('{{ $invoice->id }}')">
                                            <i class="fas fa-credit-card"></i> Thanh toán
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-file-invoice"></i>
                                        <p>Chưa có hóa đơn nào</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Invoices Pagination -->
            @if($invoices->hasPages())
                <div class="pagination-section">
                    {{ $invoices->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Renewal Modal -->
<div class="modal fade" id="renewalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Gia hạn hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="renewal-info">
                    <p>Bạn có muốn gia hạn hợp đồng này không?</p>
                    <div class="form-group">
                        <label for="renewalPeriod">Thời gian gia hạn:</label>
                        <select class="form-control" id="renewalPeriod">
                            <option value="6">6 tháng</option>
                            <option value="12" selected>12 tháng</option>
                            <option value="24">24 tháng</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="renewalNote">Ghi chú (tùy chọn):</label>
                        <textarea class="form-control" id="renewalNote" rows="3" placeholder="Ghi chú cho việc gia hạn..."></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning" onclick="confirmRenewal()">
                    <i class="fas fa-refresh me-1"></i>Xác nhận gia hạn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Download Progress Modal -->
<div class="modal fade" id="downloadModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="download-icon">
                    <i class="fas fa-download"></i>
                </div>
                <h4 class="mt-3">Đang tạo file PDF...</h4>
                <p>Vui lòng chờ trong giây lát</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="downloadProgress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
