@extends('layouts.app')

@section('title', 'Dashboard - Quản lý cá nhân')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/dashboard.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/dashboard.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="dashboard-container">
    <div class="container-fluid">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="welcome-section">
                        <div class="welcome-avatar">
                            <img src="https://ui-avatars.com/api/?name=Nguyen+Van+A&background=ff6b35&color=fff&size=60" alt="Avatar">
                        </div>
                        <div class="welcome-text">
                            <h1 class="welcome-title">Xin chào, Nguyễn Văn A!</h1>
                            <p class="welcome-subtitle">Chào mừng bạn quay lại. Hôm nay là {{ date('d/m/Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="quick-actions">
                        <a href="{{ route('home') }}" class="btn btn-outline-primary">
                            <i class="fas fa-search me-2"></i>Tìm phòng
                        </a>
                        <a href="{{ route('tenant.profile') }}" class="btn btn-outline-secondary ms-2">
                            <i class="fas fa-user me-2"></i>Hồ sơ cá nhân
                        </a>
                        <div class="dropdown d-inline-block ms-2">
                            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-plus me-2"></i>Thao tác
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('tenant.booking', 1) }}"><i class="fas fa-calendar me-2"></i>Đặt lịch xem phòng</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-file-contract me-2"></i>Gia hạn hợp đồng</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-tools me-2"></i>Yêu cầu sửa chữa</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-star me-2"></i>Đánh giá phòng</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Overview -->
        <div class="stats-overview">
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card appointments">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">5</div>
                            <div class="stat-label">Lịch hẹn</div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>+2 tuần này</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card contracts">
                        <div class="stat-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">2</div>
                            <div class="stat-label">Hợp đồng</div>
                            <div class="stat-trend stable">
                                <i class="fas fa-minus"></i>
                                <span>Không đổi</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card invoices">
                        <div class="stat-icon">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">3</div>
                            <div class="stat-label">Hóa đơn</div>
                            <div class="stat-trend down">
                                <i class="fas fa-arrow-down"></i>
                                <span>-1 tháng này</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="stat-card notifications">
                        <div class="stat-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-number">12</div>
                            <div class="stat-label">Thông báo</div>
                            <div class="stat-trend up">
                                <i class="fas fa-arrow-up"></i>
                                <span>+5 hôm nay</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Dashboard Content -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-xl-8 col-lg-7 mb-4">
                <!-- Quick Access Cards -->
                <div class="quick-access-section">
                    <h3 class="section-title">
                        <i class="fas fa-tachometer-alt me-2"></i>
                        Truy cập nhanh
                    </h3>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.appointments') }}" class="quick-access-card">
                                <div class="card-icon appointments">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Lịch hẹn</h4>
                                    <p>Quản lý lịch xem phòng</p>
                                    <div class="card-badge">5 lịch hẹn</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.profile') }}" class="quick-access-card">
                                <div class="card-icon">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Hồ sơ cá nhân</h4>
                                    <p>Xem/Cập nhật thông tin</p>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.contracts.index') }}" class="quick-access-card">
                                <div class="card-icon contracts">
                                    <i class="fas fa-file-signature"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Hợp đồng</h4>
                                    <p>Xem hợp đồng thuê nhà</p>
                                    <div class="card-badge">2 hợp đồng</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.invoices.index') }}" class="quick-access-card">
                                <div class="card-icon invoices">
                                    <i class="fas fa-receipt"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Hóa đơn</h4>
                                    <p>Thanh toán & lịch sử</p>
                                    <div class="card-badge">3 hóa đơn</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.notifications') }}" class="quick-access-card">
                                <div class="card-icon notifications">
                                    <i class="fas fa-bell"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Thông báo</h4>
                                    <p>Tin nhắn & cập nhật</p>
                                    <div class="card-badge">12 mới</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.maintenance') }}" class="quick-access-card">
                                <div class="card-icon maintenance">
                                    <i class="fas fa-wrench"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Sửa chữa</h4>
                                    <p>Yêu cầu bảo trì phòng</p>
                                    <div class="card-badge">1 đang xử lý</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="{{ route('tenant.reviews') }}" class="quick-access-card">
                                <div class="card-icon reviews">
                                    <i class="fas fa-star"></i>
                                </div>
                                <div class="card-content">
                                    <h4>Đánh giá</h4>
                                    <p>Đánh giá phòng trọ</p>
                                    <div class="card-badge">2 chưa đánh giá</div>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-arrow-right"></i>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="recent-activity-section">
                    <div class="section-header">
                        <h3 class="section-title">
                            <i class="fas fa-history me-2"></i>
                            Hoạt động gần đây
                        </h3>
                        <a href="#" class="view-all-link">Xem tất cả</a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon success">
                                <i class="fas fa-check"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Đặt cọc thành công</div>
                                <div class="activity-description">Bạn đã đặt cọc thành công cho phòng trọ Cầu Giấy</div>
                                <div class="activity-time">2 giờ trước</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon info">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Lịch hẹn được xác nhận</div>
                                <div class="activity-description">Chủ nhà đã xác nhận lịch hẹn xem phòng ngày 28/12</div>
                                <div class="activity-time">5 giờ trước</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon warning">
                                <i class="fas fa-exclamation"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Hóa đơn sắp đến hạn</div>
                                <div class="activity-description">Hóa đơn tiền phòng tháng 12 sẽ đến hạn vào ngày 30/12</div>
                                <div class="activity-time">1 ngày trước</div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon info">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">Yêu cầu đánh giá</div>
                                <div class="activity-description">Bạn có thể đánh giá phòng trọ Homestay Hạnh Đào</div>
                                <div class="activity-time">2 ngày trước</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-xl-4 col-lg-5 mb-4">
                <!-- Current Rental -->
                <div class="current-rental-section">
                    <h3 class="section-title">
                        <i class="fas fa-home me-2"></i>
                        Phòng hiện tại
                    </h3>
                    <div class="rental-card">
                        <div class="rental-image">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="Phòng trọ">
                            <div class="rental-status active">Đang thuê</div>
                        </div>
                        <div class="rental-info">
                            <h4 class="rental-title">Phòng trọ cao cấp Cầu Giấy</h4>
                            <p class="rental-address">
                                <i class="fas fa-map-marker-alt"></i>
                                123 Đường Cầu Giấy, Hà Nội
                            </p>
                            <div class="rental-details">
                                <div class="detail-item">
                                    <span class="label">Giá thuê:</span>
                                    <span class="value">2.500.000 VNĐ/tháng</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Ngày thuê:</span>
                                    <span class="value">01/12/2023</span>
                                </div>
                                <div class="detail-item">
                                    <span class="label">Hết hạn:</span>
                                    <span class="value">01/12/2024</span>
                                </div>
                            </div>
                            <div class="rental-actions">
                                <a href="{{ route('tenant.contracts.index') }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-file-contract me-1"></i>Xem hợp đồng
                                </a>
                                <button class="btn btn-outline-success btn-sm" onclick="showComingSoon('Gia hạn')">
                                    <i class="fas fa-refresh me-1"></i>Gia hạn
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events -->
                <div class="upcoming-events-section">
                    <h3 class="section-title">
                        <i class="fas fa-clock me-2"></i>
                        Sự kiện sắp tới
                    </h3>
                    <div class="events-list">
                        <div class="event-item urgent">
                            <div class="event-date">
                                <div class="day">30</div>
                                <div class="month">T12</div>
                            </div>
                            <div class="event-content">
                                <div class="event-title">Hóa đơn tiền phòng</div>
                                <div class="event-description">Đến hạn thanh toán</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    2 ngày nữa
                                </div>
                            </div>
                            <div class="event-action">
                                <button class="btn btn-sm btn-danger" onclick="showComingSoon('Thanh toán')">
                                    <i class="fas fa-credit-card"></i>
                                </button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <div class="day">28</div>
                                <div class="month">T12</div>
                            </div>
                            <div class="event-content">
                                <div class="event-title">Lịch xem phòng</div>
                                <div class="event-description">Chung cư mini Mạnh Hà</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    14:00 - 16:00
                                </div>
                            </div>
                            <div class="event-action">
                                <button class="btn btn-sm btn-outline-primary" onclick="goToAppointments()">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="event-item">
                            <div class="event-date">
                                <div class="day">01</div>
                                <div class="month">T01</div>
                            </div>
                            <div class="event-content">
                                <div class="event-title">Gia hạn hợp đồng</div>
                                <div class="event-description">Hợp đồng sắp hết hạn</div>
                                <div class="event-time">
                                    <i class="fas fa-clock"></i>
                                    1 tuần nữa
                                </div>
                            </div>
                            <div class="event-action">
                                <button class="btn btn-sm btn-outline-warning" onclick="showComingSoon('Gia hạn hợp đồng')">
                                    <i class="fas fa-file-contract"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="quick-stats-section">
                    <h3 class="section-title">
                        <i class="fas fa-chart-pie me-2"></i>
                        Thống kê nhanh
                    </h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">15</div>
                                <div class="stat-label">Lịch hẹn đã hoàn thành</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">4.8</div>
                                <div class="stat-label">Đánh giá trung bình</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-money-bill"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">25M</div>
                                <div class="stat-label">Tổng đã thanh toán</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">3</div>
                                <div class="stat-label">Phòng đã thuê</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Coming Soon Modal -->
<div class="modal fade" id="comingSoonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="coming-soon-icon">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4 class="mt-3">Tính năng sắp ra mắt!</h4>
                <p id="comingSoonMessage">Chức năng này đang được phát triển và sẽ có mặt trong phiên bản tiếp theo.</p>
                <div class="coming-soon-features">
                    <div class="feature-item">
                        <i class="fas fa-check text-success"></i>
                        <span>Giao diện thân thiện</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check text-success"></i>
                        <span>Tính năng đầy đủ</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check text-success"></i>
                        <span>Bảo mật cao</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Đã hiểu</button>
            </div>
        </div>
    </div>
</div>
@endsection
