@extends('layouts.app')

@section('title', 'Trung tâm thông báo')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/notifications.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/notifications.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="notifications-container">
    <div class="container">
        <!-- Page Header -->
        <div class="notifications-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Trung tâm thông báo</h1>
                            <p class="page-subtitle">Theo dõi tất cả thông báo và cập nhật quan trọng</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                        </a>
                        <button class="btn btn-outline-primary ms-2" onclick="markAllAsRead()">
                            <i class="fas fa-check-double me-2"></i>Đánh dấu đã đọc
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card payment">
                        <div class="stat-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="stat-content">
                            <h3>3</h3>
                            <p>Thanh toán</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card contract">
                        <div class="stat-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1</h3>
                            <p>Hợp đồng</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card appointment">
                        <div class="stat-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2</h3>
                            <p>Lịch hẹn</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card review">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1</h3>
                            <p>Đánh giá</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card maintenance">
                        <div class="stat-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2</h3>
                            <p>Sửa chữa</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 col-6 mb-4">
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-bell"></i>
                        </div>
                        <div class="stat-content">
                            <h3>9</h3>
                            <p>Tổng cộng</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm thông báo..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="typeFilter">
                        <option value="">Tất cả loại</option>
                        <option value="payment">💳 Thanh toán</option>
                        <option value="contract">📄 Hợp đồng</option>
                        <option value="appointment">📅 Lịch hẹn</option>
                        <option value="review">⭐ Đánh giá</option>
                        <option value="maintenance">🔧 Sửa chữa</option>
                        <option value="system">⚙️ Hệ thống</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-status="all">Tất cả</button>
                        <button class="filter-tab" data-status="unread">Chưa đọc</button>
                        <button class="filter-tab" data-status="read">Đã đọc</button>
                        <button class="filter-tab" data-status="important">Quan trọng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="notifications-list">
            <!-- Payment Reminder - Urgent -->
            <div class="notification-card unread important" data-status="unread" data-type="payment">
                <div class="notification-icon payment urgent">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Hóa đơn quá hạn thanh toán</h4>
                        <div class="notification-time">2 giờ trước</div>
                    </div>
                    <p class="notification-message">
                        Hóa đơn <strong>HD2023001</strong> cho phòng trọ Cầu Giấy đã quá hạn thanh toán. 
                        Vui lòng thanh toán ngay để tránh bị tính phí phạt.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-money-bill"></i>
                            Số tiền: 2.500.000 VNĐ
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-calendar-times"></i>
                            Quá hạn: 3 ngày
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('invoices') }}" class="btn btn-danger btn-sm">
                            <i class="fas fa-credit-card me-1"></i>Thanh toán ngay
                        </a>
                        <button class="btn btn-outline-primary btn-sm" onclick="viewInvoiceDetail('HD2023001')">
                            <i class="fas fa-eye me-1"></i>Xem chi tiết
                        </button>
                    </div>
                </div>
                <div class="notification-status">
                    <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Contract Renewal Reminder -->
            <div class="notification-card unread" data-status="unread" data-type="contract">
                <div class="notification-icon contract">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Hợp đồng sắp hết hạn</h4>
                        <div class="notification-time">1 ngày trước</div>
                    </div>
                    <p class="notification-message">
                        Hợp đồng <strong>HD2022002</strong> cho chung cư mini Mạnh Hà sẽ hết hạn trong 7 ngày. 
                        Hãy liên hệ chủ nhà để gia hạn hoặc tìm phòng mới.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-calendar"></i>
                            Hết hạn: 01/01/2024
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-phone"></i>
                            Chủ nhà: 0912 345 678
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('contracts') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-refresh me-1"></i>Gia hạn ngay
                        </a>
                        <a href="tel:0912345678" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>Gọi chủ nhà
                        </a>
                    </div>
                </div>
                <div class="notification-status">
                    <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Appointment Confirmation -->
            <div class="notification-card read" data-status="read" data-type="appointment">
                <div class="notification-icon appointment">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Lịch hẹn được xác nhận</h4>
                        <div class="notification-time">5 giờ trước</div>
                    </div>
                    <p class="notification-message">
                        Chủ nhà đã xác nhận lịch hẹn xem phòng <strong>Homestay Hạnh Đào</strong>. 
                        Vui lòng có mặt đúng giờ hẹn.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-clock"></i>
                            Thời gian: 28/12/2023, 14:00 - 16:00
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-user"></i>
                            Liên hệ: Anh Nam - 0901 234 567
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('appointments') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-calendar me-1"></i>Xem lịch hẹn
                        </a>
                        <a href="tel:0901234567" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i>Gọi điện
                        </a>
                    </div>
                </div>
            </div>

            <!-- Review Interaction -->
            <div class="notification-card unread" data-status="unread" data-type="review">
                <div class="notification-icon review">
                    <i class="fas fa-reply"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Chủ nhà phản hồi đánh giá</h4>
                        <div class="notification-time">3 giờ trước</div>
                    </div>
                    <p class="notification-message">
                        <strong>Chị Lan</strong> đã phản hồi đánh giá của bạn về phòng trọ Mạnh Hà. 
                        Hãy xem phản hồi và cảm ơn chủ nhà.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-star"></i>
                            Đánh giá: 4/5 sao
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-comment"></i>
                            Có phản hồi mới
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('reviews') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Xem phản hồi
                        </a>
                        <button class="btn btn-outline-success btn-sm" onclick="thankForReply('review2')">
                            <i class="fas fa-heart me-1"></i>Cảm ơn
                        </button>
                    </div>
                </div>
                <div class="notification-status">
                    <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Payment Success -->
            <div class="notification-card read" data-status="read" data-type="payment">
                <div class="notification-icon payment success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Thanh toán thành công</h4>
                        <div class="notification-time">1 ngày trước</div>
                    </div>
                    <p class="notification-message">
                        Bạn đã thanh toán thành công hóa đơn <strong>HD2023003</strong> cho phòng trọ Cầu Giấy 
                        qua ví MoMo với số tiền 2.680.000 VNĐ.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-money-bill"></i>
                            Số tiền: 2.680.000 VNĐ
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-mobile-alt"></i>
                            Qua: MoMo
                        </span>
                    </div>
                    <div class="notification-actions">
                        <button class="btn btn-outline-success btn-sm" onclick="downloadReceipt('HD2023003')">
                            <i class="fas fa-download me-1"></i>Tải biên lai
                        </button>
                    </div>
                </div>
            </div>

            <!-- Maintenance Update -->
            <div class="notification-card unread" data-status="unread" data-type="maintenance">
                <div class="notification-icon maintenance">
                    <i class="fas fa-tools"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Yêu cầu sửa chữa đã hoàn thành</h4>
                        <div class="notification-time">4 giờ trước</div>
                    </div>
                    <p class="notification-message">
                        Kỹ thuật viên <strong>Anh Minh</strong> đã hoàn thành sửa chữa vòi nước rò rỉ tại phòng Cầu Giấy. 
                        Hãy kiểm tra và đánh giá dịch vụ.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-wrench"></i>
                            Mã YC: YC001
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-user"></i>
                            KTV: Anh Minh
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('maintenance') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-star me-1"></i>Đánh giá KTV
                        </a>
                        <a href="tel:0987654321" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i>Gọi KTV
                        </a>
                    </div>
                </div>
                <div class="notification-status">
                    <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Appointment Reminder -->
            <div class="notification-card unread important" data-status="unread" data-type="appointment">
                <div class="notification-icon appointment">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Nhắc nhở lịch hẹn</h4>
                        <div class="notification-time">6 giờ trước</div>
                    </div>
                    <p class="notification-message">
                        Bạn có lịch hẹn xem phòng <strong>Homestay Hạnh Đào</strong> vào ngày mai (28/12/2023) 
                        lúc 14:00. Đừng quên chuẩn bị và có mặt đúng giờ.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-map-marker-alt"></i>
                            Địa chỉ: 789 Đường Hạnh Đào, Hà Nội
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-clock"></i>
                            14:00 - 16:00, 28/12/2023
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('appointments') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-calendar me-1"></i>Xem lịch hẹn
                        </a>
                        <button class="btn btn-outline-warning btn-sm" onclick="setReminder('appointment1')">
                            <i class="fas fa-bell me-1"></i>Đặt nhắc nhở
                        </button>
                    </div>
                </div>
                <div class="notification-status">
                    <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Review Request -->
            <div class="notification-card read" data-status="read" data-type="review">
                <div class="notification-icon review">
                    <i class="fas fa-star"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Yêu cầu đánh giá phòng trọ</h4>
                        <div class="notification-time">2 ngày trước</div>
                    </div>
                    <p class="notification-message">
                        Bạn đã hoàn thành lịch hẹn xem phòng <strong>Phòng trọ cao cấp Cầu Giấy</strong>. 
                        Hãy chia sẻ trải nghiệm để giúp người khác.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-calendar-check"></i>
                            Đã xem: 25/12/2023
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-edit"></i>
                            Chưa đánh giá
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('reviews') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-star me-1"></i>Viết đánh giá
                        </a>
                    </div>
                </div>
            </div>

            <!-- Maintenance Assignment -->
            <div class="notification-card read" data-status="read" data-type="maintenance">
                <div class="notification-icon maintenance">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-header">
                        <h4 class="notification-title">Kỹ thuật viên được phân công</h4>
                        <div class="notification-time">3 ngày trước</div>
                    </div>
                    <p class="notification-message">
                        <strong>Anh Cường</strong> đã được phân công xử lý yêu cầu sửa chữa ổ cắm điện 
                        tại Homestay Hạnh Đào. KTV sẽ liên hệ với bạn sớm.
                    </p>
                    <div class="notification-details">
                        <span class="detail-item">
                            <i class="fas fa-wrench"></i>
                            Mã YC: YC002
                        </span>
                        <span class="detail-item">
                            <i class="fas fa-user"></i>
                            KTV: Anh Cường - 0903 456 789
                        </span>
                    </div>
                    <div class="notification-actions">
                        <a href="{{ route('maintenance') }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>Theo dõi
                        </a>
                        <a href="tel:0903456789" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-phone me-1"></i>Gọi KTV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h3>Không có thông báo nào</h3>
                <p>Không tìm thấy thông báo nào phù hợp với bộ lọc hiện tại.</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Notifications pagination">
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
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Notification Settings Modal -->
<div class="modal fade" id="settingsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cài đặt thông báo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="settings-section">
                    <h6>Loại thông báo</h6>
                    <div class="setting-item">
                        <label class="setting-toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                            Nhắc nhở thanh toán
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                            Nhắc nhở hợp đồng
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                            Cập nhật lịch hẹn
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                            Tương tác đánh giá
                        </label>
                    </div>
                    <div class="setting-item">
                        <label class="setting-toggle">
                            <input type="checkbox" checked>
                            <span class="toggle-slider"></span>
                            Cập nhật sửa chữa
                        </label>
                    </div>
                </div>
                
                <div class="settings-section">
                    <h6>Thời gian nhắc nhở</h6>
                    <div class="setting-item">
                        <label>Nhắc thanh toán trước:</label>
                        <select class="form-select">
                            <option value="1">1 ngày</option>
                            <option value="3" selected>3 ngày</option>
                            <option value="7">7 ngày</option>
                        </select>
                    </div>
                    <div class="setting-item">
                        <label>Nhắc hợp đồng trước:</label>
                        <select class="form-select">
                            <option value="7">7 ngày</option>
                            <option value="14" selected>14 ngày</option>
                            <option value="30">30 ngày</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveSettings()">
                    <i class="fas fa-save me-1"></i>Lưu cài đặt
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
