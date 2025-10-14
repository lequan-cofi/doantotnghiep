@extends('layouts.app')

@section('title', 'Hóa đơn của tôi')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/invoices.css') }}?v={{ time() }}">
<style>
.address-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.address-item {
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.address-label {
    font-size: 0.8em;
    color: #666;
    font-weight: 500;
}
.address-value {
    font-size: 0.9em;
    color: #333;
}
.unit-code {
    font-size: 0.9em;
    color: #666;
    font-weight: normal;
}
.service-item {
    display: block;
    font-size: 0.85em;
    color: #555;
    margin-bottom: 2px;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/invoices.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="invoices-container">
    <div class="container">
        <!-- Page Header -->
        <div class="invoices-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Hóa đơn của tôi</h1>
                            <p class="page-subtitle">Quản lý và theo dõi các hóa đơn thanh toán</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                        </a>
                        <button class="btn btn-primary ms-2" onclick="exportInvoices()">
                            <i class="fas fa-download me-2"></i>Xuất Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card paid">
                        <div class="stat-icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['paid'] }}</h3>
                            <p>Đã thanh toán</p>
                            <div class="stat-amount">{{ number_format($stats['paid_amount'] / 1000000, 1) }}M VNĐ</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending'] }}</h3>
                            <p>Chờ thanh toán</p>
                            <div class="stat-amount">{{ number_format($stats['pending_amount'] / 1000000, 1) }}M VNĐ</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card overdue">
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['overdue'] }}</h3>
                            <p>Quá hạn</p>
                            <div class="stat-amount">{{ number_format($stats['overdue_amount'] / 1000000, 1) }}M VNĐ</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-calculator"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['total'] }}</h3>
                            <p>Tổng hóa đơn</p>
                            <div class="stat-amount">{{ number_format($stats['total_amount'] / 1000000, 1) }}M VNĐ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="filter-section">
            <form method="GET" id="filterForm">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm theo mã hóa đơn..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" name="month" id="monthFilter">
                            <option value="">Tất cả tháng</option>
                            @for($i = 0; $i < 12; $i++)
                                @php
                                    $date = \Carbon\Carbon::now()->subMonths($i);
                                    $value = $date->format('Y-m');
                                    $label = $date->format('m/Y');
                                @endphp
                                <option value="{{ $value }}" {{ request('month') == $value ? 'selected' : '' }}>
                                    Tháng {{ $label }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="filter-tabs">
                            <button type="button" class="filter-tab {{ request('status', 'all') == 'all' ? 'active' : '' }}" data-status="all">Tất cả</button>
                            <button type="button" class="filter-tab {{ request('status') == 'paid' ? 'active' : '' }}" data-status="paid">Đã thanh toán</button>
                            <button type="button" class="filter-tab {{ request('status') == 'pending' ? 'active' : '' }}" data-status="pending">Chờ thanh toán</button>
                            <button type="button" class="filter-tab {{ request('status') == 'overdue' ? 'active' : '' }}" data-status="overdue">Quá hạn</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">
            </form>
        </div>

        <!-- Invoices List -->
        <div class="invoices-list">
            @forelse($invoices as $invoice)
                @php
                    $now = \Carbon\Carbon::now();
                    $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                    $isOverdue = $invoice->status === 'issued' && $dueDate < $now;
                    
                    if ($invoice->status === 'paid') {
                        $status = 'paid';
                        $statusText = 'Đã thanh toán';
                        $statusIcon = 'fas fa-check-circle';
                        $statusClass = 'paid';
                    } elseif ($isOverdue) {
                        $status = 'overdue';
                        $statusText = 'Quá hạn';
                        $statusIcon = 'fas fa-exclamation-triangle';
                        $statusClass = 'overdue';
                    } elseif ($invoice->status === 'issued') {
                        $status = 'pending';
                        $statusText = 'Chờ thanh toán';
                        $statusIcon = 'fas fa-clock';
                        $statusClass = 'pending';
                    } elseif ($invoice->status === 'draft') {
                        $status = 'draft';
                        $statusText = 'Nháp';
                        $statusIcon = 'fas fa-edit';
                        $statusClass = 'draft';
                    } else {
                        $status = 'cancelled';
                        $statusText = 'Đã hủy';
                        $statusIcon = 'fas fa-times';
                        $statusClass = 'cancelled';
                    }
                @endphp
                
                <div class="invoice-card" data-status="{{ $status }}">
                    <div class="invoice-status {{ $statusClass }}">
                        <i class="{{ $statusIcon }}"></i>
                        <span>{{ $statusText }}</span>
                    </div>
                    <div class="invoice-content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="invoice-info">
                                    <div class="invoice-id">{{ $invoice->invoice_no ?? 'HD' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</div>
                                    <div class="invoice-date">Ngày tạo: {{ $invoice->issue_date->format('d/m/Y') }}</div>
                                    <div class="due-date {{ $isOverdue ? 'overdue' : '' }}">
                                        @if($invoice->status === 'paid')
                                            Đã thanh toán: {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y') : 'N/A' }}
                                        @else
                                            Hạn thanh toán: {{ $invoice->due_date->format('d/m/Y') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="property-info">
                                    <h4 class="property-name">
                                        {{ $invoice->lease->unit->property->name }}
                                        @if($invoice->lease->unit->code)
                                            <span class="unit-code">- {{ $invoice->lease->unit->code }}</span>
                                        @endif
                                    </h4>
                                    <p class="property-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        @php
                                            $locationAddress = null;
                                            $location2025Address = null;
                                            
                                            if ($invoice->lease->unit->property->location) {
                                                $addressParts = [];
                                                if ($invoice->lease->unit->property->location->street) $addressParts[] = $invoice->lease->unit->property->location->street;
                                                if ($invoice->lease->unit->property->location->ward) $addressParts[] = $invoice->lease->unit->property->location->ward;
                                                if ($invoice->lease->unit->property->location->district) $addressParts[] = $invoice->lease->unit->property->location->district;
                                                if ($invoice->lease->unit->property->location->city) $addressParts[] = $invoice->lease->unit->property->location->city;
                                                if ($invoice->lease->unit->property->location->country && $invoice->lease->unit->property->location->country !== 'Vietnam') $addressParts[] = $invoice->lease->unit->property->location->country;
                                                $locationAddress = !empty($addressParts) ? implode(', ', $addressParts) : null;
                                            }
                                            
                                            if ($invoice->lease->unit->property->location2025) {
                                                $addressParts2025 = [];
                                                if ($invoice->lease->unit->property->location2025->street) $addressParts2025[] = $invoice->lease->unit->property->location2025->street;
                                                if ($invoice->lease->unit->property->location2025->ward) $addressParts2025[] = $invoice->lease->unit->property->location2025->ward;
                                                if ($invoice->lease->unit->property->location2025->city) $addressParts2025[] = $invoice->lease->unit->property->location2025->city;
                                                if ($invoice->lease->unit->property->location2025->country && $invoice->lease->unit->property->location2025->country !== 'Vietnam') $addressParts2025[] = $invoice->lease->unit->property->location2025->country;
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
                                    </p>
                                    <div class="invoice-period">
                                        <i class="fas fa-calendar"></i>
                                        Tháng {{ $invoice->issue_date->format('m/Y') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="amount-info">
                                    <div class="amount-breakdown">
                                        @foreach($invoice->items as $item)
                                            <div class="breakdown-item">
                                                <span>{{ $item->description }}:</span>
                                                <span>{{ number_format($item->amount) }} VNĐ</span>
                                            </div>
                                        @endforeach
                                        <div class="breakdown-total">
                                            <span>Tổng cộng:</span>
                                            <span>{{ number_format($invoice->total_amount) }} VNĐ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="payment-method">
                                    <div class="method-label">Phương thức</div>
                                    <div class="method-value">
                                        @if($invoice->payment_method)
                                            <div class="method-icon {{ $invoice->payment_method }}">
                                                @switch($invoice->payment_method)
                                                    @case('momo')
                                                        <img src="https://developers.momo.vn/v3/assets/images/logo.png" alt="MoMo" style="width: 20px;">
                                                        <span>MoMo</span>
                                                        @break
                                                    @case('bank')
                                                        <i class="fas fa-university"></i>
                                                        <span>Chuyển khoản</span>
                                                        @break
                                                    @case('vnpay')
                                                        <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418196384.png" alt="VNPay" style="width: 20px;">
                                                        <span>VNPay</span>
                                                        @break
                                                    @case('zalopay')
                                                        <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" style="width: 20px;">
                                                        <span>ZaloPay</span>
                                                        @break
                                                @endswitch
                                            </div>
                                        @else
                                            Chưa chọn
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="invoice-actions">
                        @if($invoice->status === 'issued')
                            <button class="btn {{ $isOverdue ? 'btn-danger' : 'btn-primary' }} btn-sm" onclick="payInvoice('{{ $invoice->id }}')">
                                <i class="fas fa-credit-card me-1"></i>{{ $isOverdue ? 'Thanh toán ngay' : 'Thanh toán' }}
                            </button>
                        @endif
                        <a href="{{ route('tenant.invoices.show', $invoice->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadInvoice('{{ $invoice->id }}')">
                            <i class="fas fa-download me-1"></i>Tải PDF
                        </button>
                        @if($invoice->status === 'paid')
                            <button class="btn btn-outline-info btn-sm" onclick="viewReceipt('{{ $invoice->id }}')">
                                <i class="fas fa-receipt me-1"></i>Biên lai
                            </button>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h3>Không có hóa đơn nào</h3>
                    <p>Bạn chưa có hóa đơn nào. Hãy kiểm tra lại sau!</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($invoices->hasPages())
            {{ $invoices->appends(request()->query())->links('vendor.pagination.custom') }}
        @endif
    </div>
</div>

<!-- Payment Method Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chọn phương thức thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="invoice-summary mb-4">
                    <h6>Thông tin hóa đơn</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="summary-item">
                                <span class="label">Mã hóa đơn:</span>
                                <span class="value" id="paymentInvoiceId">-</span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Phòng:</span>
                                <span class="value" id="paymentProperty">-</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-item">
                                <span class="label">Kỳ thanh toán:</span>
                                <span class="value" id="paymentPeriod">-</span>
                            </div>
                            <div class="summary-item total">
                                <span class="label">Tổng tiền:</span>
                                <span class="value" id="paymentAmount">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="payment-methods">
                    <h6>Chọn phương thức thanh toán</h6>
                    <div class="method-options">
                        <div class="method-option" data-method="momo">
                            <div class="method-icon">
                                <img src="https://developers.momo.vn/v3/assets/images/logo.png" alt="MoMo" style="width: 40px;">
                            </div>
                            <div class="method-info">
                                <h6>Ví MoMo</h6>
                                <p>Thanh toán nhanh chóng với ví điện tử</p>
                            </div>
                            <div class="method-select">
                                <input type="radio" name="payment_method" value="momo" id="method_momo">
                                <label for="method_momo"></label>
                            </div>
                        </div>

                        <div class="method-option" data-method="bank">
                            <div class="method-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <div class="method-info">
                                <h6>Chuyển khoản ngân hàng</h6>
                                <p>Chuyển tiền qua tài khoản ngân hàng</p>
                            </div>
                            <div class="method-select">
                                <input type="radio" name="payment_method" value="bank" id="method_bank">
                                <label for="method_bank"></label>
                            </div>
                        </div>

                        <div class="method-option" data-method="vnpay">
                            <div class="method-icon">
                                <img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418196384.png" alt="VNPay" style="width: 40px;">
                            </div>
                            <div class="method-info">
                                <h6>VNPay</h6>
                                <p>Cổng thanh toán trực tuyến VNPay</p>
                            </div>
                            <div class="method-select">
                                <input type="radio" name="payment_method" value="vnpay" id="method_vnpay">
                                <label for="method_vnpay"></label>
                            </div>
                        </div>

                        <div class="method-option" data-method="zalopay">
                            <div class="method-icon">
                                <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" style="width: 40px;">
                            </div>
                            <div class="method-info">
                                <h6>ZaloPay</h6>
                                <p>Thanh toán an toàn với ZaloPay</p>
                            </div>
                            <div class="method-select">
                                <input type="radio" name="payment_method" value="zalopay" id="method_zalopay">
                                <label for="method_zalopay"></label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="processPayment()" disabled id="confirmPaymentBtn">
                    <i class="fas fa-credit-card me-1"></i>Xác nhận thanh toán
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Detail Modal -->
<div class="modal fade" id="invoiceDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hóa đơn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="invoice-detail-content" id="invoiceDetailContent">
                    <!-- Invoice details will be loaded here -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Đang tải thông tin hóa đơn...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" onclick="downloadCurrentInvoice()">
                    <i class="fas fa-download me-1"></i>Tải PDF
                </button>
                <button type="button" class="btn btn-primary" onclick="printInvoice()">
                    <i class="fas fa-print me-1"></i>In hóa đơn
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Processing Modal -->
<div class="modal fade" id="processingModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="processing-icon">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h4 class="mt-3">Đang xử lý thanh toán...</h4>
                <p>Vui lòng không đóng trang này</p>
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" style="width: 0%" id="paymentProgress"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
