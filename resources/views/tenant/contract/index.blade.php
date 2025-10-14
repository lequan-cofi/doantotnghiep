@extends('layouts.app')

@section('title', 'Hợp đồng của tôi')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/contracts.css') }}?v={{ time() }}">
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
<script src="{{ asset('assets/js/user/contracts.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="contracts-container">
    <div class="container">
        <!-- Page Header -->
        <div class="contracts-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Hợp đồng của tôi</h1>
                            <p class="page-subtitle">Quản lý và theo dõi các hợp đồng thuê nhà</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card active">
                        <div class="stat-icon">
                            <i class="fas fa-file-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['active'] }}</h3>
                            <p>Đang hiệu lực</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card expiring">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['expiring'] }}</h3>
                            <p>Sắp hết hạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card expired">
                        <div class="stat-icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['expired'] }}</h3>
                            <p>Đã hết hạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-folder"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['total'] }}</h3>
                            <p>Tổng hợp đồng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="filter-section">
            <form method="GET" id="filterForm">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Tìm kiếm theo tên phòng, địa chỉ..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="filter-tabs">
                            <button type="button" class="filter-tab {{ request('status', 'all') == 'all' ? 'active' : '' }}" data-status="all">Tất cả</button>
                            <button type="button" class="filter-tab {{ request('status') == 'active' ? 'active' : '' }}" data-status="active">Đang hiệu lực</button>
                            <button type="button" class="filter-tab {{ request('status') == 'expiring' ? 'active' : '' }}" data-status="expiring">Sắp hết hạn</button>
                            <button type="button" class="filter-tab {{ request('status') == 'expired' ? 'active' : '' }}" data-status="expired">Đã hết hạn</button>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="status" id="statusInput" value="{{ request('status', 'all') }}">
            </form>
        </div>

        <!-- Contracts List -->
        <div class="contracts-list">
            @forelse($contracts as $contract)
                @php
                    $now = \Carbon\Carbon::now();
                    $endDate = \Carbon\Carbon::parse($contract->end_date);
                    $remainingDays = $now->diffInDays($endDate, false);
                    $isExpired = $endDate < $now;
                    $isExpiring = !$isExpired && $remainingDays <= 30;
                    
                    if ($isExpired) {
                        $status = 'expired';
                        $statusText = 'Đã hết hạn';
                        $statusIcon = 'fas fa-times-circle';
                        $statusClass = 'expired';
                    } elseif ($isExpiring) {
                        $status = 'expiring';
                        $statusText = 'Sắp hết hạn';
                        $statusIcon = 'fas fa-exclamation-triangle';
                        $statusClass = 'expiring';
                    } else {
                        $status = 'active';
                        $statusText = 'Đang hiệu lực';
                        $statusIcon = 'fas fa-check-circle';
                        $statusClass = 'active';
                    }
                    
                    // Calculate remaining time text
                    if ($isExpired) {
                        $remainingText = 'Đã hết hạn';
                    } elseif ($remainingDays < 30) {
                        $remainingText = "Còn " . abs(round($remainingDays)) . " ngày";
                    } else {
                        $remainingMonths = floor(abs($remainingDays) / 30);
                        $remainingText = "Còn {$remainingMonths} tháng";
                    }
                @endphp
                
                <div class="contract-card" data-status="{{ $status }}">
                    <div class="contract-status {{ $statusClass }}">
                        <i class="{{ $statusIcon }}"></i>
                        <span>{{ $statusText }}</span>
                    </div>
                    <div class="contract-content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="property-image">
                                    @if($contract->unit->property->images && count($contract->unit->property->images) > 0)
                                        <img src="{{ Storage::url($contract->unit->property->images[0]) }}" alt="{{ $contract->unit->property->name }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="{{ $contract->unit->property->name }}">
                                    @endif
                                    <div class="contract-type">
                                        <span class="badge {{ $statusClass }}">Thuê phòng</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="contract-info">
                                    <h4 class="contract-title">
                                        {{ $contract->unit->property->name }}
                                        @if($contract->unit->code)
                                            <span class="unit-code">- {{ $contract->unit->code }}</span>
                                        @endif
                                    </h4>
                                    <div class="property-address">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <div class="address-info">
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
                                    <div class="contract-details">
                                        <div class="detail-item">
                                            <span class="label">Mã hợp đồng:</span>
                                            <span class="value">{{ $contract->contract_no ?? 'HD' . str_pad($contract->id, 6, '0', STR_PAD_LEFT) }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Ngày ký:</span>
                                            <span class="value">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : 'Chưa ký' }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Ngày bắt đầu:</span>
                                            <span class="value">{{ $contract->start_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Ngày kết thúc:</span>
                                            <span class="value">{{ $contract->end_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Chủ nhà:</span>
                                            <span class="value">
                                                @if($contract->agent)
                                                    {{ $contract->agent->full_name ?? $contract->agent->name }} - {{ $contract->agent->phone ?? 'N/A' }}
                                                @else
                                                    N/A
                                                @endif
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="label">Giá thuê:</span>
                                            <span class="value price">{{ number_format($contract->rent_amount) }} VNĐ/tháng</span>
                                        </div>
                                        @if($contract->leaseServices && $contract->leaseServices->count() > 0)
                                            <div class="detail-item">
                                                <span class="label">Dịch vụ đi kèm:</span>
                                                <div class="value">
                                                    @foreach($contract->leaseServices as $leaseService)
                                                        <span class="service-item">
                                                            {{ $leaseService->service->name ?? 'N/A' }}: {{ number_format($leaseService->price) }} VNĐ
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="contract-dates">
                                    <div class="date-item">
                                        <div class="date-label">Ngày ký</div>
                                        <div class="date-value">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y') : 'Chưa ký' }}</div>
                                    </div>
                                    <div class="date-item">
                                        <div class="date-label">Ngày bắt đầu</div>
                                        <div class="date-value">{{ $contract->start_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="date-item {{ $isExpired ? 'expired' : ($isExpiring ? 'urgent' : '') }}">
                                        <div class="date-label">Ngày kết thúc</div>
                                        <div class="date-value">{{ $contract->end_date->format('d/m/Y') }}</div>
                                    </div>
                                    <div class="remaining-time {{ $isExpired ? 'expired' : ($isExpiring ? 'urgent' : '') }}">
                                        <i class="{{ $isExpired ? 'fas fa-times-circle' : ($isExpiring ? 'fas fa-exclamation-circle' : 'fas fa-calendar-alt') }}"></i>
                                        <span>{{ $remainingText }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="contract-actions">
                        <a href="{{ route('tenant.contracts.show', $contract->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="downloadContract('{{ $contract->id }}')">
                            <i class="fas fa-download me-1"></i>Tải PDF
                        </button>
                        @if(!$isExpired)
                            <button class="btn {{ $isExpiring ? 'btn-warning' : 'btn-outline-warning' }} btn-sm" onclick="renewContract('{{ $contract->id }}')">
                                <i class="fas fa-refresh me-1"></i>{{ $isExpiring ? 'Gia hạn ngay' : 'Gia hạn' }}
                            </button>
                        @endif
                        @if($contract->agent && $contract->agent->phone)
                            <a href="tel:{{ $contract->agent->phone }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-phone me-1"></i>Liên hệ
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-file-contract"></i>
                    </div>
                    <h3>Không có hợp đồng nào</h3>
                    <p>Bạn chưa có hợp đồng thuê nhà nào. Hãy tìm kiếm và thuê phòng mới!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Tìm phòng ngay
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($contracts->hasPages())
            <div class="pagination-section">
                <nav aria-label="Contracts pagination">
                    {{ $contracts->appends(request()->query())->links() }}
                </nav>
            </div>
        @endif
    </div>
</div>

<!-- Contract Detail Modal -->
<div class="modal fade" id="contractDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết hợp đồng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="contract-detail-content" id="contractDetailContent">
                    <!-- Contract details will be loaded here -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Đang tải thông tin hợp đồng...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-success" onclick="downloadCurrentContract()">
                    <i class="fas fa-download me-1"></i>Tải PDF
                </button>
                <button type="button" class="btn btn-primary" onclick="printContract()">
                    <i class="fas fa-print me-1"></i>In hợp đồng
                </button>
            </div>
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
