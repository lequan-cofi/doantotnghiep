<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-building"></i>
                <span class="logo-text">Agent Panel</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-page="dashboard">
                <i class="fas fa-home"></i>
                <span>Tổng quan</span>
            </a>
            
            <div class="nav-group" data-group="rooms">
                <a href="#" class="nav-item has-submenu nav-parent" data-page="rooms">
                    <i class="fas fa-building"></i>
                    <span>Quản lý phòng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="submenu-item" data-page="rooms-list">
                        <i class="fas fa-list"></i>
                        <span>Danh sách phòng</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="rooms-create">
                        <i class="fas fa-plus"></i>
                        <span>Thêm phòng mới</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="rooms-categories">
                        <i class="fas fa-tags"></i>
                        <span>Loại phòng</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="users">
                <a href="#" class="nav-item has-submenu nav-parent" data-page="users">
                    <i class="fas fa-users"></i>
                    <span>Quản lý người dùng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="submenu-item" data-page="users-list">
                        <i class="fas fa-list"></i>
                        <span>Danh sách người dùng</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="users-tenants">
                        <i class="fas fa-user"></i>
                        <span>Người thuê</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="users-owners">
                        <i class="fas fa-user-tie"></i>
                        <span>Chủ nhà</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="posts">
                <a href="#" class="nav-item has-submenu nav-parent" data-page="posts">
                    <i class="fas fa-file-text"></i>
                    <span>Bài đăng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="submenu-item" data-page="posts-list">
                        <i class="fas fa-list"></i>
                        <span>Danh sách bài đăng</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="posts-pending">
                        <i class="fas fa-clock"></i>
                        <span>Chờ duyệt</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="posts-news">
                        <i class="fas fa-newspaper"></i>
                        <span>Tin tức</span>
                    </a>
                </div>
            </div>
            
            <a href="#" class="nav-item" data-page="revenue">
                <i class="fas fa-dollar-sign"></i>
                <span>Doanh thu</span>
            </a>
            
            <a href="#" class="nav-item" data-page="analytics">
                <i class="fas fa-chart-bar"></i>
                <span>Thống kê</span>
            </a>
            
            <a href="#" class="nav-item" data-page="appointments">
                <i class="fas fa-calendar"></i>
                <span>Lịch hẹn</span>
            </a>
            
            <a href="#" class="nav-item" data-page="messages">
                <i class="fas fa-comments"></i>
                <span>Tin nhắn</span>
            </a>
            
            <a href="#" class="nav-item" data-page="notifications">
                <i class="fas fa-bell"></i>
                <span>Thông báo</span>
            </a>
            
            <div class="nav-group" data-group="settings">
                <a href="#" class="nav-item has-submenu nav-parent" data-page="settings">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="#" class="submenu-item" data-page="settings-general">
                        <i class="fas fa-sliders-h"></i>
                        <span>Cài đặt chung</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="settings-payment">
                        <i class="fas fa-credit-card"></i>
                        <span>Thanh toán</span>
                    </a>
                    <a href="#" class="submenu-item" data-page="settings-email">
                        <i class="fas fa-envelope"></i>
                        <span>Email</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <a href="{{ route('agent.profile') }}">
                        <i class="fas fa-user-shield"></i>
                    </a>
                </div>
                <div class="user-details">
                    <div class="user-name">Agent</div>
                    <div class="user-email">agent@example.com</div>
                </div>
            </div>
        </div>
    </aside>
