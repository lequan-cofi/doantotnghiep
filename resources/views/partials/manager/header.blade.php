<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
                <span class="logo-text">Manager Panel</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('manager.dashboard') }}" class="nav-item {{ request()->routeIs('manager.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tổng quan</span>
            </a>
            
            <div class="nav-group" data-group="properties">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-building"></i>
                    <span>Bất động sản</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.properties.index') }}" class="submenu-item">
                        <i class="fas fa-list"></i>
                        <span>Danh sách BĐS</span>
                    </a>
                        <a href="{{ route('manager.properties.create') }}" class="submenu-item">
                        <i class="fas fa-plus"></i>
                        <span>Thêm BĐS mới</span>
                    </a>
                    <a href="{{ route('manager.property-types.index') }}" class="submenu-item">
                        <i class="fas fa-tags"></i>
                        <span>Loại BĐS</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="users">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-users"></i>
                    <span>Người dùng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.users.index') }}" class="submenu-item">
                        <i class="fas fa-list"></i>
                        <span>Tất cả người dùng</span>
                    </a>
                    <a href="{{ route('manager.staff.index') }}" class="submenu-item">
                        <i class="fas fa-user-tie"></i>
                        <span>CTV/Nhân viên</span>
                    </a>
                    <a href="{{ route('manager.users.create') }}" class="submenu-item">
                        <i class="fas fa-plus"></i>
                        <span>Thêm tài khoản</span>
                    </a>
                </div>
            </div>
            
            <a href="{{ route('manager.leases.index') }}" class="nav-item">
                <i class="fas fa-file-contract"></i>
                <span>Hợp đồng</span>
            </a>
            
            <a href="{{ route('manager.invoices.index') }}" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Hóa đơn</span>
            </a>
            
            <a href="{{ route('manager.tickets.index') }}" class="nav-item">
                <i class="fas fa-tools"></i>
                <span>Tickets</span>
            </a>
            <div class="nav-group" data-group="commission">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-percentage"></i>
                    <span>Hoa hồng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.commission-policies.index') }}" class="submenu-item">
                        <i class="fas fa-cogs"></i>
                        <span>Chính sách hoa hồng</span>
                    </a>
                    <a href="{{ route('manager.commission-events.index') }}" class="submenu-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Sự kiện hoa hồng</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="payroll">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Lương</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.salary-contracts.index') }}" class="submenu-item">
                        <i class="fas fa-file-contract"></i>
                        <span>Hợp đồng lương</span>
                    </a>
                    <a href="{{ route('manager.payroll-cycles.index') }}" class="submenu-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Kỳ lương</span>
                    </a>
                    <a href="{{ route('manager.payroll-payslips.index') }}" class="submenu-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Phiếu lương</span>
                    </a>
                    <a href="{{ route('manager.salary-advances.index') }}" class="submenu-item">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Ứng lương</span>
                    </a>
                </div>
            </div>
            
            
            <div class="nav-group" data-group="reports">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-chart-line"></i>
                    <span>Báo cáo</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.revenue-reports.index') }}" class="submenu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Báo cáo doanh thu</span>
                    </a>
                    <a href="{{ route('manager.reports.payments') }}" class="submenu-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Thanh toán</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="settings">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-cog"></i>
                    <span>Cài đặt</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('manager.profile') }}" class="submenu-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                    <a href="{{ route('manager.settings.general') }}" class="submenu-item">
                        <i class="fas fa-sliders-h"></i>
                        <span>Cài đặt chung</span>
                    </a>
                    <a href="{{ route('manager.payment-cycle-settings.index') }}" class="submenu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Chu kỳ thanh toán</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <a href="{{ route('manager.profile') }}">
                        <i class="fas fa-user-shield"></i>
                    </a>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()?->full_name ?? 'Manager' }}</div>
                    <div class="user-email">{{ auth()->user()?->email ?? '' }}</div>
                </div>
            </div>
        </div>
    </aside>

