<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-user-tie"></i>
                <span class="logo-text">Agent Panel</span>
            </div>
            {{-- <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button> --}}
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('agent.dashboard') }}" class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            
           
             <a href="{{ route('agent.properties.index') }}" class="nav-item {{ request()->routeIs('agent.properties.*') ? 'active' : '' }}">
                    <i class="fas fa-building"></i>
                    <span>Bất động sản</span>
                  
            </a>
               
         
            
            <div class="nav-group" data-group="units">
                <a href="#" class="nav-item has-submenu nav-parent {{ request()->routeIs('agent.units.*') || request()->routeIs('agent.rented.*') ? 'active' : '' }}">
                    <i class="fas fa-door-open"></i>
                    <span>Phòng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('agent.units.index') }}" class="submenu-item">
                        <i class="fas fa-list"></i>
                        <span>Danh sách phòng</span>
                    </a>
                    <a href="{{ route('agent.units.create') }}" class="submenu-item">
                        <i class="fas fa-plus"></i>
                        <span>Thêm phòng mới</span>
                    </a>
                    <a href="{{ route('agent.rented.index') }}" class="submenu-item">
                        <i class="fas fa-home"></i>
                        <span>Phòng đã cho thuê</span>
                    </a>
                </div>
            </div>
            
            <a href="{{ route('agent.units.index') }}" class="nav-item {{ request()->routeIs('agent.units.*') ? 'active' : '' }}">
                <i class="fas fa-door-open"></i>
                <span>Quản lý phòng</span>
            </a>
            
            <a href="{{ route('agent.rented.index') }}" class="nav-item">
                <i class="fas fa-home"></i>
                <span>Đã cho thuê</span>
            </a>
            
            <a href="{{ route('agent.leases.index') }}" class="nav-item">
                <i class="fas fa-file-contract"></i>
                <span>Hợp đồng</span>
            </a>
            
            <a href="{{ route('agent.booking-deposits.index') }}" class="nav-item {{ request()->routeIs('agent.booking-deposits.*') ? 'active' : '' }}">
                <i class="fas fa-hand-holding-usd"></i>
                <span>Đặt cọc</span>
            </a>
            <a href="{{ route('agent.invoices.index') }}" class="nav-item {{ request()->routeIs('agent.invoices.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice"></i>
                <span>Hóa đơn</span>
            </a>
            <a href="{{ route('agent.leads.index') }}" class="nav-item">
                <i class="fas fa-users"></i>
                <span>Leads</span>
            </a>
            
            <a href="{{ route('agent.tenants.index') }}" class="nav-item {{ request()->routeIs('agent.tenants.*') ? 'active' : '' }}">
                <i class="fas fa-user-friends"></i>
                <span>Người dùng</span>
            </a>
            <div class="nav-group" data-group="viewings">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Lịch hẹn</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('agent.viewings.create') }}" class="submenu-item">
                        <i class="fas fa-plus"></i>
                        <span>Tạo lịch hẹn</span>
                    </a>
                    <a href="{{ route('agent.viewings.index') }}" class="submenu-item">
                        <i class="fas fa-list"></i>
                        <span>Tất cả lịch hẹn</span>
                    </a>
                    <a href="{{ route('agent.viewings.today') }}" class="submenu-item">
                        <i class="fas fa-calendar-day"></i>
                        <span>Lịch hôm nay</span>
                    </a>
                    <a href="{{ route('agent.viewings.calendar') }}" class="submenu-item">
                        <i class="fas fa-calendar"></i>
                        <span>Lịch tổng quan</span>
                    </a>
                    <a href="{{ route('agent.viewings.statistics') }}" class="submenu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Thống kê</span>
                    </a>
                </div>
            </div>
            <a href="{{ route('agent.tickets.index') }}" class="nav-item">
                <i class="fas fa-ticket-alt"></i>
                <span>Ticket</span>
            </a>
            <a href="{{ route('agent.meters.index') }}" class="nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Công tơ đo</span>
            </a>
            
            <div class="nav-group" data-group="payroll">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Lương</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('agent.salary-contracts.index') }}" class="submenu-item">
                        <i class="fas fa-file-contract"></i>
                        <span>Hợp đồng lương</span>
                    </a>
                    <a href="{{ route('agent.payroll-cycles.index') }}" class="submenu-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Kỳ lương</span>
                    </a>
                    <a href="{{ route('agent.payslips.index') }}" class="submenu-item">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Phiếu lương</span>
                    </a>
                    <a href="{{ route('agent.salary-advances.index') }}" class="submenu-item">
                        <i class="fas fa-hand-holding-usd"></i>
                        <span>Ứng lương</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="commission">
                <a href="#" class="nav-item has-submenu nav-parent">
                    <i class="fas fa-percentage"></i>
                    <span>Hoa hồng</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('agent.commission-policies.index') }}" class="submenu-item">
                        <i class="fas fa-cogs"></i>
                        <span>Chính sách hoa hồng</span>
                    </a>
                    <a href="{{ route('agent.commission-events.index') }}" class="submenu-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Sự kiện hoa hồng</span>
                    </a>
                </div>
            </div>
            
            <div class="nav-group" data-group="salary">
                <a href="#" class="nav-item has-submenu nav-parent {{ request()->routeIs('agent.salary-*') || request()->routeIs('agent.payroll-*') || request()->routeIs('agent.payslips.*') ? 'active' : '' }}">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Quản lý lương</span>
                    <i class="fas fa-chevron-down submenu-arrow"></i>
                </a>
                <div class="submenu">
                    <a href="{{ route('agent.salary-contracts.index') }}" class="submenu-item">
                        <i class="fas fa-file-signature"></i>
                        <span>Hợp đồng lương</span>
                    </a>
                    <a href="{{ route('agent.payroll-cycles.index') }}" class="submenu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Kỳ lương</span>
                    </a>
                    <a href="{{ route('agent.payslips.index') }}" class="submenu-item">
                        <i class="fas fa-receipt"></i>
                        <span>Phiếu lương</span>
                    </a>
                    <a href="{{ route('agent.salary-advances.index') }}" class="submenu-item">
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
                    <a href="{{ route('agent.revenue-reports.index') }}" class="submenu-item">
                        <i class="fas fa-chart-bar"></i>
                        <span>Báo cáo doanh thu</span>
                    </a>
                    <a href="{{ route('agent.reports.payments') }}" class="submenu-item">
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
                    <a href="{{ route('agent.profile') }}" class="submenu-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                    <a href="{{ route('agent.settings.general') }}" class="submenu-item">
                        <i class="fas fa-sliders-h"></i>
                        <span>Cài đặt chung</span>
                    </a>
                </div>
            </div>
        </nav>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar">
                    <a href="{{ route('agent.profile') }}">
                        <i class="fas fa-user-tie"></i>
                    </a>
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()?->full_name ?? 'Agent' }}</div>
                    <div class="user-email">{{ auth()->user()?->email ?? '' }}</div>
                </div>
            </div>
        </div>
    </aside>