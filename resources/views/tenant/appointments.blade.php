@extends('layouts.app')

@section('title', 'Quản lý lịch hẹn')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/appointments.css') }}?v={{ time() }}">
<style>
/* Inline CSS fallback for appointments page */
.appointments-container {
    padding: 30px 0 60px;
    background-color: #f4f4f4;
    min-height: calc(100vh - 120px);
}

.appointments-header {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #ff6b35, #ff8563);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 5px;
}

.page-subtitle {
    color: #6b7280;
    margin: 0;
    font-size: 1rem;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.stat-icon.pending {
    background: linear-gradient(135deg, #f59e0b, #f97316);
}

.stat-icon.confirmed {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-icon.completed {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stat-icon.cancelled {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.appointment-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    margin-bottom: 20px;
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.appointment-status {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 2;
}

.appointment-status.pending {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.appointment-content {
    padding: 25px;
}

.property-image {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    height: 150px;
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.property-info {
    padding-left: 20px;
}

.property-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 10px;
    line-height: 1.3;
}

.appointment-actions {
    padding: 20px 25px;
    border-top: 1px solid #e5e7eb;
    background: rgba(249, 250, 251, 0.5);
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    flex-wrap: wrap;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/appointments.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="appointments-container">
    <div class="container">
        <!-- Header -->
        <div class="appointments-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Quản lý lịch hẹn</h1>
                            <p class="page-subtitle">Theo dõi và quản lý các lịch hẹn xem phòng của bạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Tìm phòng mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>3</h3>
                            <p>Chờ xác nhận</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon confirmed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2</h3>
                            <p>Đã xác nhận</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1</h3>
                            <p>Đã hoàn thành</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>1</h3>
                            <p>Đã hủy</p>
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
                        <button class="filter-tab" data-status="pending">Chờ xác nhận</button>
                        <button class="filter-tab" data-status="confirmed">Đã xác nhận</button>
                        <button class="filter-tab" data-status="completed">Hoàn thành</button>
                        <button class="filter-tab" data-status="cancelled">Đã hủy</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="appointments-list">
            <!-- Appointment Item 1 - Pending -->
            <div class="appointment-card" data-status="pending">
                <div class="appointment-status pending">
                    <i class="fas fa-clock"></i>
                    <span>Chờ xác nhận</span>
                </div>
                <div class="appointment-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="Phòng trọ">
                                <div class="property-badges">
                                    <span class="badge new">Mới</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info">
                                <h4 class="property-title">Phòng trọ cao cấp Cầu Giấy</h4>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội
                                </p>
                                <div class="property-details">
                                    <span class="detail">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                        25m²
                                    </span>
                                    <span class="detail">
                                        <i class="fas fa-users"></i>
                                        2 người
                                    </span>
                                    <span class="detail price">
                                        <i class="fas fa-money-bill-wave"></i>
                                        2.5 triệu/tháng
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="appointment-info">
                                <div class="appointment-time">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <strong>25/12/2023</strong>
                                        <span>09:00 - 11:00</span>
                                    </div>
                                </div>
                                <div class="appointment-contact">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong>Anh Minh</strong>
                                        <span>0987 654 321</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="appointment-actions">
                    <button class="btn btn-outline-danger btn-sm" onclick="cancelAppointment(1)">
                        <i class="fas fa-times"></i>
                        Hủy lịch
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="editAppointment(1)">
                        <i class="fas fa-edit"></i>
                        Chỉnh sửa
                    </button>
                    <a href="tel:0987654321" class="btn btn-success btn-sm">
                        <i class="fas fa-phone"></i>
                        Gọi điện
                    </a>
                </div>
            </div>

            <!-- Appointment Item 2 - Confirmed -->
            <div class="appointment-card" data-status="confirmed">
                <div class="appointment-status confirmed">
                    <i class="fas fa-check-circle"></i>
                    <span>Đã xác nhận</span>
                </div>
                <div class="appointment-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=200&fit=crop" alt="Chung cư mini">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info">
                                <h4 class="property-title">Chung cư mini Mạnh Hà</h4>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="property-details">
                                    <span class="detail">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                        45m²
                                    </span>
                                    <span class="detail">
                                        <i class="fas fa-users"></i>
                                        3 người
                                    </span>
                                    <span class="detail price">
                                        <i class="fas fa-money-bill-wave"></i>
                                        10 triệu/tháng
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="appointment-info">
                                <div class="appointment-time">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <strong>28/12/2023</strong>
                                        <span>14:00 - 16:00</span>
                                    </div>
                                </div>
                                <div class="appointment-contact">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong>Chị Lan</strong>
                                        <span>0912 345 678</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="appointment-actions">
                    <button class="btn btn-outline-warning btn-sm" onclick="markCompleted(2)">
                        <i class="fas fa-check"></i>
                        Đã xem
                    </button>
                    <button class="btn btn-outline-primary btn-sm" onclick="rescheduleAppointment(2)">
                        <i class="fas fa-calendar-alt"></i>
                        Đổi lịch
                    </button>
                    <a href="tel:0912345678" class="btn btn-success btn-sm">
                        <i class="fas fa-phone"></i>
                        Gọi điện
                    </a>
                </div>
            </div>

            <!-- Appointment Item 3 - Completed -->
            <div class="appointment-card" data-status="completed">
                <div class="appointment-status completed">
                    <i class="fas fa-calendar-check"></i>
                    <span>Đã hoàn thành</span>
                </div>
                <div class="appointment-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=200&fit=crop" alt="Homestay">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info">
                                <h4 class="property-title">Homestay Hạnh Đào</h4>
                                <p class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="property-details">
                                    <span class="detail">
                                        <i class="fas fa-expand-arrows-alt"></i>
                                        35m²
                                    </span>
                                    <span class="detail">
                                        <i class="fas fa-users"></i>
                                        2 người
                                    </span>
                                    <span class="detail price">
                                        <i class="fas fa-money-bill-wave"></i>
                                        8 triệu/tháng
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="appointment-info">
                                <div class="appointment-time">
                                    <i class="fas fa-calendar"></i>
                                    <div>
                                        <strong>20/12/2023</strong>
                                        <span>10:00 - 12:00</span>
                                    </div>
                                </div>
                                <div class="appointment-contact">
                                    <i class="fas fa-user"></i>
                                    <div>
                                        <strong>Anh Nam</strong>
                                        <span>0901 234 567</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="appointment-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="rateProperty(3)">
                        <i class="fas fa-star"></i>
                        Đánh giá
                    </button>
                    <a href="{{ route('deposit', 3) }}" class="btn btn-outline-success btn-sm">
                        <i class="fas fa-home"></i>
                        Thuê phòng
                    </a>
                    <a href="tel:0901234567" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-phone"></i>
                        Gọi lại
                    </a>
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
                <h3>Không có lịch hẹn nào</h3>
                <p>Bạn chưa có lịch hẹn xem phòng nào. Hãy tìm kiếm và đặt lịch xem phòng mới!</p>
                <a href="{{ route('home') }}" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Tìm phòng ngay
                </a>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Appointments pagination">
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

<!-- Modals -->
<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hủy lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hủy lịch hẹn này không?</p>
                <div class="mb-3">
                    <label for="cancelReason" class="form-label">Lý do hủy (tùy chọn)</label>
                    <textarea class="form-control" id="cancelReason" rows="3" placeholder="Nhập lý do hủy lịch..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">Xác nhận hủy</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editDate" class="form-label">Ngày hẹn</label>
                            <input type="date" class="form-control" id="editDate" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="editStartTime" class="form-label">Từ giờ</label>
                            <input type="time" class="form-control" id="editStartTime" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="editEndTime" class="form-label">Đến giờ</label>
                            <input type="time" class="form-control" id="editEndTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editNote" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="editNote" rows="3" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đánh giá phòng trọ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rating-section mb-3">
                    <label class="form-label">Đánh giá tổng thể</label>
                    <div class="star-rating">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                        <i class="fas fa-star" data-rating="5"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reviewText" class="form-label">Nhận xét</label>
                    <textarea class="form-control" id="reviewText" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitRating()">Gửi đánh giá</button>
            </div>
        </div>
    </div>
</div>
@endsection
