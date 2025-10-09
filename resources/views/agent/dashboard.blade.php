@extends('layouts.agent_dashboad')

@section('title', 'Bảng điều khiển')

@section('content')
<!-- Main Content -->
<main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-content">
                    <div class="header-info">
                        <h1>Tổng quan hệ thống</h1>
                        <p>Quản lý trang web cho thuê phòng trọ</p>
                    </div>
                    <button class="btn btn-primary">
                        <i class="fas fa-plus"></i>
                        Thêm bài đăng mới
                    </button>
                </div>
            </header>
            
            <!-- Dashboard Content -->
            <div class="content" id="content">
                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Tổng số phòng</span>
                            <i class="fas fa-building stat-icon"></i>
                        </div>
                        <div class="stat-value">1,234</div>
                        <div class="stat-change positive">+12% so với tháng trước</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Người dùng hoạt động</span>
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                        <div class="stat-value">567</div>
                        <div class="stat-change positive">+8% so với tháng trước</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Bài đăng trong tháng</span>
                            <i class="fas fa-file-text stat-icon"></i>
                        </div>
                        <div class="stat-value">89</div>
                        <div class="stat-change positive">+23% so với tháng trước</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-header">
                            <span class="stat-title">Doanh thu tháng này</span>
                            <i class="fas fa-dollar-sign stat-icon"></i>
                        </div>
                        <div class="stat-value">45.2M VNĐ</div>
                        <div class="stat-change positive">+15% so với tháng trước</div>
                    </div>
                </div>
                
                <!-- Charts and Activities -->
                <div class="dashboard-grid">
                    <!-- Revenue Chart -->
                    <div class="chart-section">
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-chart-line"></i> Biểu đồ doanh thu 6 tháng gần đây</h3>
                            </div>
                            <div class="card-content">
                                <canvas id="revenueChart" width="400" height="200"></canvas>
                            </div>
                        </div>
                        
                        <!-- Popular Listings -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-eye"></i> Bài đăng được xem nhiều nhất</h3>
                            </div>
                            <div class="card-content">
                                <div class="listing-item">
                                    <div class="listing-info">
                                        <div class="listing-title">Phòng trọ 2PN giá rẻ tại Quận 1</div>
                                        <div class="listing-views">1,234 lượt xem</div>
                                    </div>
                                    <div class="listing-price">5.5M VNĐ/tháng</div>
                                </div>
                                
                                <div class="listing-item">
                                    <div class="listing-info">
                                        <div class="listing-title">Căn hộ mini full nội thất Quận 7</div>
                                        <div class="listing-views">987 lượt xem</div>
                                    </div>
                                    <div class="listing-price">7.2M VNĐ/tháng</div>
                                </div>
                                
                                <div class="listing-item">
                                    <div class="listing-info">
                                        <div class="listing-title">Nhà nguyên căn 3PN Bình Thạnh</div>
                                        <div class="listing-views">876 lượt xem</div>
                                    </div>
                                    <div class="listing-price">12M VNĐ/tháng</div>
                                </div>
                                
                                <div class="listing-item">
                                    <div class="listing-info">
                                        <div class="listing-title">Phòng trọ sinh viên gần ĐH Bách Khoa</div>
                                        <div class="listing-views">654 lượt xem</div>
                                    </div>
                                    <div class="listing-price">3.8M VNĐ/tháng</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Sidebar -->
                    <div class="right-sidebar">
                        <!-- Recent Activity -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Hoạt động gần đây</h3>
                            </div>
                            <div class="card-content">
                                <div class="activity-item">
                                    <div class="activity-avatar">N</div>
                                    <div class="activity-details">
                                        <div class="activity-header">
                                            <span class="activity-user">Nguyễn Văn A</span>
                                            <span class="badge badge-primary">Bài đăng</span>
                                        </div>
                                        <div class="activity-action">đã đăng bài mới <strong>Phòng trọ 2PN tại Quận 1</strong></div>
                                        <div class="activity-time">5 phút trước</div>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-avatar">T</div>
                                    <div class="activity-details">
                                        <div class="activity-header">
                                            <span class="activity-user">Trần Thị B</span>
                                            <span class="badge badge-secondary">Liên hệ</span>
                                        </div>
                                        <div class="activity-action">đã liên hệ về <strong>Căn hộ mini Quận 7</strong></div>
                                        <div class="activity-time">15 phút trước</div>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-avatar">L</div>
                                    <div class="activity-details">
                                        <div class="activity-header">
                                            <span class="activity-user">Lê Văn C</span>
                                            <span class="badge badge-success">Thanh toán</span>
                                        </div>
                                        <div class="activity-action">đã thanh toán <strong>Gói VIP 30 ngày</strong></div>
                                        <div class="activity-time">1 giờ trước</div>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-avatar">P</div>
                                    <div class="activity-details">
                                        <div class="activity-header">
                                            <span class="activity-user">Phạm Thị D</span>
                                            <span class="badge badge-info">Đăng ký</span>
                                        </div>
                                        <div class="activity-action">đã đăng ký tài khoản</div>
                                        <div class="activity-time">2 giờ trước</div>
                                    </div>
                                </div>
                                
                                <div class="activity-item">
                                    <div class="activity-avatar">H</div>
                                    <div class="activity-details">
                                        <div class="activity-header">
                                            <span class="activity-user">Hoàng Văn E</span>
                                            <span class="badge badge-warning">Cập nhật</span>
                                        </div>
                                        <div class="activity-action">đã cập nhật thông tin <strong>Phòng trọ Bình Thạnh</strong></div>
                                        <div class="activity-time">3 giờ trước</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Quick Actions -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-bolt"></i> Thao tác nhanh</h3>
                            </div>
                            <div class="card-content">
                                <button class="quick-action-btn">
                                    <i class="fas fa-building"></i>
                                    Thêm phòng mới
                                </button>
                                <button class="quick-action-btn">
                                    <i class="fas fa-users"></i>
                                    Quản lý người dùng
                                </button>
                                <button class="quick-action-btn">
                                    <i class="fas fa-file-text"></i>
                                    Duyệt bài đăng
                                </button>
                                <button class="quick-action-btn">
                                    <i class="fas fa-comments"></i>
                                    Tin nhắn mới
                                </button>
                            </div>
                        </div>
                        
                        <!-- System Status -->
                        <div class="card">
                            <div class="card-header">
                                <h3>Trạng thái hệ thống</h3>
                            </div>
                            <div class="card-content">
                                <div class="status-item">
                                    <span class="status-label">Server</span>
                                    <div class="status-indicator">
                                        <div class="status-dot status-success"></div>
                                        <span class="status-text">Hoạt động bình thường</span>
                                    </div>
                                </div>
                                
                                <div class="status-item">
                                    <span class="status-label">Database</span>
                                    <div class="status-indicator">
                                        <div class="status-dot status-success"></div>
                                        <span class="status-text">Kết nối ổn định</span>
                                    </div>
                                </div>
                                
                                <div class="status-item">
                                    <span class="status-label">API Response</span>
                                    <div class="status-indicator">
                                        <div class="status-dot status-warning"></div>
                                        <span class="status-text">Phản hồi chậm</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
@endsection


