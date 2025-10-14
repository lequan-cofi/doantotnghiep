@extends('layouts.app')

@section('title', 'Chi tiết hóa đơn')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/invoices.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/user/invoices-show.css') }}?v={{ time() }}">
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
<script src="{{ asset('assets/js/user/invoices-show.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="invoice-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="breadcrumb-nav">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('tenant.dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('tenant.invoices.index') }}">Hóa đơn</a></li>
                <li class="breadcrumb-item active" aria-current="page">Chi tiết</li>
            </ol>
        </nav>

        <!-- Invoice Header -->
        <div class="invoice-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="invoice-title-section">
                        <h1 class="invoice-title">{{ $invoice->lease->unit->property->name }}</h1>
                        <div class="invoice-meta">
                            <span class="invoice-number">Mã hóa đơn: {{ $invoice->invoice_no ?? 'HD' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
                            <span class="invoice-status-badge {{ $invoice->status }}">
                                @switch($invoice->status)
                                    @case('paid')
                                        <i class="fas fa-check-circle"></i> Đã thanh toán
                                        @break
                                    @case('issued')
                                        @if($isOverdue)
                                            <i class="fas fa-exclamation-triangle"></i> Quá hạn
                                        @else
                                            <i class="fas fa-clock"></i> Chờ thanh toán
                                        @endif
                                        @break
                                    @case('draft')
                                        <i class="fas fa-edit"></i> Nháp
                                        @break
                                    @case('cancelled')
                                        <i class="fas fa-times"></i> Đã hủy
                                        @break
                                @endswitch
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="invoice-actions">
                        @if($invoice->status === 'issued')
                            <button class="btn {{ $isOverdue ? 'btn-danger' : 'btn-primary' }}" onclick="payInvoice('{{ $invoice->id }}')">
                                <i class="fas fa-credit-card me-1"></i>{{ $isOverdue ? 'Thanh toán ngay' : 'Thanh toán' }}
                            </button>
                        @endif
                        <button class="btn btn-outline-success" onclick="downloadInvoice('{{ $invoice->id }}')">
                            <i class="fas fa-download me-1"></i>Tải PDF
                        </button>
                        <button class="btn btn-outline-primary" onclick="printInvoice()">
                            <i class="fas fa-print me-1"></i>In hóa đơn
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Information -->
        <div class="invoice-info-section">
            <div class="row">
                <!-- Basic Information -->
                <div class="col-lg-6 mb-4">
                    <div class="info-card">
                        <h3 class="info-card-title">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                        </h3>
                        <div class="info-content">
                            <div class="info-item">
                                <span class="info-label">Mã hóa đơn:</span>
                                <span class="info-value">{{ $invoice->invoice_no ?? 'HD' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày phát hành:</span>
                                <span class="info-value">{{ $invoice->issue_date->format('d/m/Y H:i') }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Ngày đến hạn:</span>
                                <span class="info-value {{ $isOverdue ? 'text-danger' : '' }}">{{ $invoice->due_date->format('d/m/Y') }}</span>
                            </div>
                            @if($invoice->status === 'paid')
                                <div class="info-item">
                                    <span class="info-label">Ngày thanh toán:</span>
                                    <span class="info-value text-success">{{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : 'N/A' }}</span>
                                </div>
                            @endif
                            <div class="info-item">
                                <span class="info-label">Trạng thái:</span>
                                <span class="info-value">
                                    @switch($invoice->status)
                                        @case('paid')
                                            <span class="badge bg-success">Đã thanh toán</span>
                                            @break
                                        @case('issued')
                                            @if($isOverdue)
                                                <span class="badge bg-danger">Quá hạn</span>
                                            @else
                                                <span class="badge bg-warning">Chờ thanh toán</span>
                                            @endif
                                            @break
                                        @case('draft')
                                            <span class="badge bg-secondary">Nháp</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-dark">Đã hủy</span>
                                            @break
                                    @endswitch
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
                                    {{ $invoice->lease->unit->property->name }}
                                    @if($invoice->lease->unit->code)
                                        - {{ $invoice->lease->unit->code }}
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Địa chỉ:</span>
                                <div class="info-value">
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
                                </div>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Loại phòng:</span>
                                <span class="info-value">{{ $invoice->lease->unit->property->propertyType->name ?? 'N/A' }}</span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Diện tích:</span>
                                <span class="info-value">{{ $invoice->lease->unit->area_m2 ? $invoice->lease->unit->area_m2 . ' m²' : 'N/A' }}</span>
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
                                <span class="info-label">Tổng tiền:</span>
                                <span class="info-value price">{{ number_format($invoice->total_amount) }} VNĐ</span>
                            </div>
                            @if($invoice->status === 'paid')
                                <div class="info-item">
                                    <span class="info-label">Phương thức thanh toán:</span>
                                    <span class="info-value">
                                        @switch($invoice->payment_method)
                                            @case('momo')
                                                <span class="badge bg-primary">MoMo</span>
                                                @break
                                            @case('bank')
                                                <span class="badge bg-info">Chuyển khoản</span>
                                                @break
                                            @case('vnpay')
                                                <span class="badge bg-success">VNPay</span>
                                                @break
                                            @case('zalopay')
                                                <span class="badge bg-warning">ZaloPay</span>
                                                @break
                                            @default
                                                N/A
                                        @endswitch
                                    </span>
                                </div>
                                @if($invoice->payment_reference)
                                    <div class="info-item">
                                        <span class="info-label">Mã giao dịch:</span>
                                        <span class="info-value">{{ $invoice->payment_reference }}</span>
                                    </div>
                                @endif
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
                                    @if($invoice->lease->agent)
                                        {{ $invoice->lease->agent->full_name ?? $invoice->lease->agent->name }}
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Số điện thoại:</span>
                                <span class="info-value">
                                    @if($invoice->lease->agent && $invoice->lease->agent->phone)
                                        <a href="tel:{{ $invoice->lease->agent->phone }}">{{ $invoice->lease->agent->phone }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value">
                                    @if($invoice->lease->agent && $invoice->lease->agent->email)
                                        <a href="mailto:{{ $invoice->lease->agent->email }}">{{ $invoice->lease->agent->email }}</a>
                                    @else
                                        N/A
                                    @endif
                                </span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Người thuê:</span>
                                <span class="info-value">{{ $invoice->lease->tenant->full_name ?? $invoice->lease->tenant->name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoice Items Section -->
        <div class="invoice-items-section">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-list me-2"></i>Chi tiết hóa đơn
                </h2>
            </div>

            <div class="table-responsive">
                <table class="table table-striped invoice-items-table">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Mô tả</th>
                            <th>Số lượng</th>
                            <th>Đơn giá</th>
                            <th>Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->quantity ?? 1 }}</td>
                                <td>{{ number_format($item->unit_price) }} VNĐ</td>
                                <td class="price">{{ number_format($item->amount) }} VNĐ</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="4" class="text-end"><strong>Tổng cộng:</strong></td>
                            <td class="price"><strong>{{ number_format($invoice->total_amount) }} VNĐ</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Payment History Section -->
        @if($invoice->status === 'paid')
            <div class="payment-history-section">
                <div class="section-header">
                    <h2 class="section-title">
                        <i class="fas fa-history me-2"></i>Lịch sử thanh toán
                    </h2>
                </div>

                <div class="payment-timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker success">
                            <i class="fas fa-check"></i>
                        </div>
                        <div class="timeline-content">
                            <h6>Thanh toán thành công</h6>
                            <p class="text-muted">{{ $invoice->paid_at->format('d/m/Y H:i') }}</p>
                            <div class="payment-details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <span class="label">Phương thức:</span>
                                        <span class="value">
                                            @switch($invoice->payment_method)
                                                @case('momo')
                                                    MoMo
                                                    @break
                                                @case('bank')
                                                    Chuyển khoản ngân hàng
                                                    @break
                                                @case('vnpay')
                                                    VNPay
                                                    @break
                                                @case('zalopay')
                                                    ZaloPay
                                                    @break
                                                @default
                                                    N/A
                                            @endswitch
                                        </span>
                                    </div>
                                    @if($invoice->payment_reference)
                                        <div class="col-md-6">
                                            <span class="label">Mã giao dịch:</span>
                                            <span class="value">{{ $invoice->payment_reference }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
                                <span class="value">{{ $invoice->invoice_no ?? 'HD' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <div class="summary-item">
                                <span class="label">Phòng:</span>
                                <span class="value">{{ $invoice->lease->unit->property->name }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-item">
                                <span class="label">Kỳ thanh toán:</span>
                                <span class="value">Tháng {{ $invoice->issue_date->format('m/Y') }}</span>
                            </div>
                            <div class="summary-item total">
                                <span class="label">Tổng tiền:</span>
                                <span class="value">{{ number_format($invoice->total_amount) }} VNĐ</span>
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
