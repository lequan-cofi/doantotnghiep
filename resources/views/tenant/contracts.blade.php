@extends('layouts.app')

@section('title', 'Hợp đồng của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/contracts.css') }}?v={{ time() }}">
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
                            <h3>2</h3>
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
                            <h3>1</h3>
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
                            <h3>1</h3>
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
                            <h3>4</h3>
                            <p>Tổng hợp đồng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm theo tên phòng, địa chỉ..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-status="all">Tất cả</button>
                        <button class="filter-tab" data-status="active">Đang hiệu lực</button>
                        <button class="filter-tab" data-status="expiring">Sắp hết hạn</button>
                        <button class="filter-tab" data-status="expired">Đã hết hạn</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contracts List -->
        <div class="contracts-list">
            <!-- Contract Item 1 - Active -->
            <div class="contract-card" data-status="active">
                <div class="contract-status active">
                    <i class="fas fa-check-circle"></i>
                    <span>Đang hiệu lực</span>
                </div>
                <div class="contract-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="Phòng trọ">
                                <div class="contract-type">
                                    <span class="badge rental">Thuê phòng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contract-info">
                                <h4 class="contract-title">Hợp đồng thuê phòng trọ Cầu Giấy</h4>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội
                                </p>
                                <div class="contract-details">
                                    <div class="detail-item">
                                        <span class="label">Mã hợp đồng:</span>
                                        <span class="value">HD2023001</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Chủ nhà:</span>
                                        <span class="value">Anh Minh - 0987 654 321</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Giá thuê:</span>
                                        <span class="value price">2.500.000 VNĐ/tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="contract-dates">
                                <div class="date-item">
                                    <div class="date-label">Ngày ký</div>
                                    <div class="date-value">01/12/2023</div>
                                </div>
                                <div class="date-item">
                                    <div class="date-label">Ngày hết hạn</div>
                                    <div class="date-value">01/12/2024</div>
                                </div>
                                <div class="remaining-time">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Còn 11 tháng</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contract-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewContract('HD2023001')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="downloadContract('HD2023001')">
                        <i class="fas fa-download me-1"></i>Tải PDF
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="renewContract('HD2023001')">
                        <i class="fas fa-refresh me-1"></i>Gia hạn
                    </button>
                    <a href="tel:0987654321" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-phone me-1"></i>Liên hệ
                    </a>
                </div>
            </div>

            <!-- Contract Item 2 - Expiring -->
            <div class="contract-card" data-status="expiring">
                <div class="contract-status expiring">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Sắp hết hạn</span>
                </div>
                <div class="contract-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=200&fit=crop" alt="Chung cư mini">
                                <div class="contract-type">
                                    <span class="badge rental">Thuê phòng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contract-info">
                                <h4 class="contract-title">Hợp đồng thuê chung cư mini Mạnh Hà</h4>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="contract-details">
                                    <div class="detail-item">
                                        <span class="label">Mã hợp đồng:</span>
                                        <span class="value">HD2022002</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Chủ nhà:</span>
                                        <span class="value">Chị Lan - 0912 345 678</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Giá thuê:</span>
                                        <span class="value price">10.000.000 VNĐ/tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="contract-dates">
                                <div class="date-item">
                                    <div class="date-label">Ngày ký</div>
                                    <div class="date-value">01/01/2023</div>
                                </div>
                                <div class="date-item urgent">
                                    <div class="date-label">Ngày hết hạn</div>
                                    <div class="date-value">01/01/2024</div>
                                </div>
                                <div class="remaining-time urgent">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <span>Còn 7 ngày</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contract-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewContract('HD2022002')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="downloadContract('HD2022002')">
                        <i class="fas fa-download me-1"></i>Tải PDF
                    </button>
                    <button class="btn btn-warning btn-sm" onclick="renewContract('HD2022002')">
                        <i class="fas fa-refresh me-1"></i>Gia hạn ngay
                    </button>
                    <a href="tel:0912345678" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-phone me-1"></i>Liên hệ
                    </a>
                </div>
            </div>

            <!-- Contract Item 3 - Active -->
            <div class="contract-card" data-status="active">
                <div class="contract-status active">
                    <i class="fas fa-check-circle"></i>
                    <span>Đang hiệu lực</span>
                </div>
                <div class="contract-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=200&fit=crop" alt="Homestay">
                                <div class="contract-type">
                                    <span class="badge rental">Thuê phòng</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contract-info">
                                <h4 class="contract-title">Hợp đồng thuê homestay Hạnh Đào</h4>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="contract-details">
                                    <div class="detail-item">
                                        <span class="label">Mã hợp đồng:</span>
                                        <span class="value">HD2023003</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Chủ nhà:</span>
                                        <span class="value">Anh Nam - 0901 234 567</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Giá thuê:</span>
                                        <span class="value price">8.000.000 VNĐ/tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="contract-dates">
                                <div class="date-item">
                                    <div class="date-label">Ngày ký</div>
                                    <div class="date-value">15/06/2023</div>
                                </div>
                                <div class="date-item">
                                    <div class="date-label">Ngày hết hạn</div>
                                    <div class="date-value">15/06/2024</div>
                                </div>
                                <div class="remaining-time">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Còn 6 tháng</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contract-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewContract('HD2023003')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="downloadContract('HD2023003')">
                        <i class="fas fa-download me-1"></i>Tải PDF
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="renewContract('HD2023003')">
                        <i class="fas fa-refresh me-1"></i>Gia hạn
                    </button>
                    <a href="tel:0901234567" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-phone me-1"></i>Liên hệ
                    </a>
                </div>
            </div>

            <!-- Contract Item 4 - Expired -->
            <div class="contract-card" data-status="expired">
                <div class="contract-status expired">
                    <i class="fas fa-times-circle"></i>
                    <span>Đã hết hạn</span>
                </div>
                <div class="contract-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1571055107559-3e67626fa8be?w=300&h=200&fit=crop" alt="Căn hộ">
                                <div class="contract-type">
                                    <span class="badge expired">Đã hết hạn</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contract-info">
                                <h4 class="contract-title">Hợp đồng thuê căn hộ Thanh Xuân</h4>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    321 Đường Thanh Xuân, Quận Thanh Xuân, Hà Nội
                                </p>
                                <div class="contract-details">
                                    <div class="detail-item">
                                        <span class="label">Mã hợp đồng:</span>
                                        <span class="value">HD2021001</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Chủ nhà:</span>
                                        <span class="value">Cô Hoa - 0903 456 789</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="label">Giá thuê:</span>
                                        <span class="value price">12.000.000 VNĐ/tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="contract-dates">
                                <div class="date-item">
                                    <div class="date-label">Ngày ký</div>
                                    <div class="date-value">01/01/2022</div>
                                </div>
                                <div class="date-item expired">
                                    <div class="date-label">Ngày hết hạn</div>
                                    <div class="date-value">01/01/2023</div>
                                </div>
                                <div class="remaining-time expired">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Đã hết hạn</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="contract-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewContract('HD2021001')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="downloadContract('HD2021001')">
                        <i class="fas fa-download me-1"></i>Tải PDF
                    </button>
                    <span class="text-muted">Hợp đồng đã kết thúc</span>
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <h3>Không có hợp đồng nào</h3>
                <p>Bạn chưa có hợp đồng thuê nhà nào. Hãy tìm kiếm và thuê phòng mới!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Tìm phòng ngay
                </a>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Contracts pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <span class="page-link">Trước</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
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
