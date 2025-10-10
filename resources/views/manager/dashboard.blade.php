@extends('layouts.manager_dashboard')

@section('title', 'Quản lý môi giới - Dashboard')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Dashboard Quản lý Môi giới</h1>
                <p>Tổng quan hoạt động kinh doanh và hiệu suất</p>
            </div>
            <div class="header-actions">
                <button onclick="clearDashboardCache()" class="btn btn-outline-secondary me-2" title="Làm mới dữ liệu">
                    <i class="fas fa-sync-alt"></i>
                    Làm mới
                </button>
                <a href="{{ route('manager.properties.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Thêm BĐS mới
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <!-- Key Performance Stats -->
        <div class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-header">
                    <span class="stat-title">BĐS Quản lý</span>
                    <i class="fas fa-building stat-icon"></i>
                </div>
                <div class="stat-value">{{ $dashboardData['stats']['properties_count'] }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Tổng tài sản</span>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Tỷ lệ lấp đầy</span>
                    <i class="fas fa-chart-pie stat-icon"></i>
                </div>
                <div class="stat-value">{{ $dashboardData['stats']['occupancy_rate'] }}%</div>
                <div class="stat-footer">
                    <span class="stat-label">{{ $dashboardData['stats']['occupied_units'] }}/{{ $dashboardData['stats']['total_units'] }} phòng</span>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-header">
                    <span class="stat-title">Lịch xem phòng</span>
                    <i class="fas fa-calendar-check stat-icon"></i>
                </div>
                <div class="stat-value">{{ $dashboardData['stats']['upcoming_viewings'] }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Lịch hẹn sắp tới</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-header">
                    <span class="stat-title">Tỷ lệ chuyển đổi</span>
                    <i class="fas fa-percentage stat-icon"></i>
                </div>
                <div class="stat-value">{{ $dashboardData['stats']['conversion_rate'] }}%</div>
                <div class="stat-footer">
                    <span class="stat-label">{{ $dashboardData['stats']['converted_leads'] }}/{{ $dashboardData['stats']['total_leads'] }} leads</span>
                </div>
            </div>
        </div>

        <!-- Revenue & Commission Stats -->
        <div class="revenue-stats">
            <div class="stat-card-large revenue">
                <div class="stat-header">
                    <div>
                        <h3>Doanh thu tháng này</h3>
                        <p class="text-muted">Tổng thu từ hóa đơn</p>
                    </div>
                    <i class="fas fa-dollar-sign stat-icon-large"></i>
                </div>
                <div class="stat-value-large">{{ number_format($dashboardData['revenue']['monthly_revenue'] / 1000000, 1) }}M</div>
                <div class="stat-trend {{ $dashboardData['revenue']['revenue_growth'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $dashboardData['revenue']['revenue_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    <span>{{ $dashboardData['revenue']['revenue_growth'] >= 0 ? '+' : '' }}{{ $dashboardData['revenue']['revenue_growth'] }}% so với tháng trước</span>
                </div>
            </div>

            <div class="stat-card-large commission">
                <div class="stat-header">
                    <div>
                        <h3>Hoa hồng tháng này</h3>
                        <p class="text-muted">Tổng hoa hồng dự kiến</p>
                    </div>
                    <i class="fas fa-hand-holding-usd stat-icon-large"></i>
                </div>
                <div class="stat-value-large">{{ number_format($dashboardData['revenue']['monthly_commission'] / 1000000, 1) }}M</div>
                <div class="stat-trend {{ $dashboardData['revenue']['commission_growth'] >= 0 ? 'up' : 'down' }}">
                    <i class="fas fa-arrow-{{ $dashboardData['revenue']['commission_growth'] >= 0 ? 'up' : 'down' }}"></i>
                    <span>{{ $dashboardData['revenue']['commission_growth'] >= 0 ? '+' : '' }}{{ $dashboardData['revenue']['commission_growth'] }}% so với tháng trước</span>
                </div>
            </div>

            <div class="stat-card-large pending">
                <div class="stat-header">
                    <div>
                        <h3>Cần xử lý</h3>
                        <p class="text-muted">Hóa đơn & tickets</p>
                    </div>
                    <i class="fas fa-exclamation-circle stat-icon-large"></i>
                </div>
                <div class="stat-value-large">{{ $dashboardData['revenue']['pending_invoices'] + $dashboardData['revenue']['open_tickets'] }}</div>
                <div class="stat-details">
                    <span>{{ $dashboardData['revenue']['pending_invoices'] }} hóa đơn, {{ $dashboardData['revenue']['open_tickets'] }} tickets</span>
                </div>
            </div>
        </div>
        
        <!-- Main Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Left Column: Charts & Data -->
            <div class="chart-section">
                <!-- Revenue Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-line"></i> Biểu đồ doanh thu 6 tháng</h3>
                        <div class="card-actions">
                            <button class="btn-icon" title="Xuất báo cáo">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-content">
                        <canvas id="revenueChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Occupancy & Availability -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-home"></i> Tình trạng phòng</h3>
                    </div>
                    <div class="card-content">
                        <div class="occupancy-grid">
                            <div class="occupancy-item available">
                                <div class="occupancy-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $dashboardData['occupancy']['available'] }}</div>
                                    <div class="occupancy-label">Trống</div>
                                </div>
                            </div>
                            <div class="occupancy-item occupied">
                                <div class="occupancy-icon"><i class="fas fa-users"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $dashboardData['occupancy']['occupied'] }}</div>
                                    <div class="occupancy-label">Đã thuê</div>
                                </div>
                            </div>
                            <div class="occupancy-item reserved">
                                <div class="occupancy-icon"><i class="fas fa-clock"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $dashboardData['occupancy']['reserved'] }}</div>
                                    <div class="occupancy-label">Đặt cọc</div>
                                </div>
                            </div>
                            <div class="occupancy-item maintenance">
                                <div class="occupancy-icon"><i class="fas fa-tools"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $dashboardData['occupancy']['maintenance'] }}</div>
                                    <div class="occupancy-label">Bảo trì</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Performers -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-trophy"></i> Top CTV/Nhân viên</h3>
                    </div>
                    <div class="card-content">
                        <div class="top-agents-list">
                            @forelse ($dashboardData['topPerformers'] as $index => $agent)
                            <div class="agent-item">
                                <div class="agent-rank rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                                <div class="agent-avatar">{{ substr($agent->full_name, 0, 1) }}</div>
                                <div class="agent-info">
                                    <div class="agent-name">{{ $agent->full_name }}</div>
                                    <div class="agent-stats">{{ $agent->deals }} giao dịch</div>
                                </div>
                                <div class="agent-commission">{{ number_format($agent->total_commission / 1000000, 1) }}M</div>
                            </div>
                            @empty
                            <p class="text-muted">Chưa có dữ liệu</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Sidebar: Quick Actions & Alerts -->
            <div class="right-sidebar">
                <!-- Quick Actions -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bolt"></i> Thao tác nhanh</h3>
                    </div>
                    <div class="card-content">
                        <a href="{{ route('manager.properties.create') }}" class="quick-action-btn">
                            <i class="fas fa-building"></i>
                            <span>Thêm BĐS mới</span>
                        </a>
                        <a href="{{ route('manager.users.create') }}" class="quick-action-btn">
                            <i class="fas fa-user-plus"></i>
                            <span>Thêm tài khoản</span>
                        </a>
                        <a href="{{ route('manager.staff.index') }}" class="quick-action-btn">
                            <i class="fas fa-user-tie"></i>
                            <span>Gán nhân viên</span>
                        </a>
                        <a href="{{ route('manager.revenue-reports.index') }}" class="quick-action-btn">
                            <i class="fas fa-chart-bar"></i>
                            <span>Xem báo cáo</span>
                        </a>
                    </div>
                </div>

                <!-- Urgent Tasks -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-bell"></i> Cần xử lý ngay</h3>
                    </div>
                    <div class="card-content">
                        <div class="alert-item urgent">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div class="alert-content">
                                <div class="alert-title">Hóa đơn quá hạn</div>
                                <div class="alert-value">{{ $dashboardData['urgentTasks']['overdue_invoices'] }} hóa đơn</div>
                            </div>
                            <a href="{{ route('manager.invoices.index') }}" class="alert-action">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="alert-item warning">
                            <i class="fas fa-file-contract"></i>
                            <div class="alert-content">
                                <div class="alert-title">HĐ sắp hết hạn</div>
                                <div class="alert-value">{{ $dashboardData['urgentTasks']['expiring_leases'] }} hợp đồng</div>
                            </div>
                            <a href="{{ route('manager.leases.index') }}" class="alert-action">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="alert-item info">
                            <i class="fas fa-calendar"></i>
                            <div class="alert-content">
                                <div class="alert-title">Lịch hẹn chờ duyệt</div>
                                <div class="alert-value">{{ $dashboardData['urgentTasks']['pending_viewings'] }} lịch</div>
                            </div>
                            <a href="#" class="alert-action">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activities -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Hoạt động gần đây</h3>
                    </div>
                    <div class="card-content">
                        <div class="activity-list">
                            @forelse ($dashboardData['recentActivities'] as $activity)
                            <div class="activity-item">
                                <div class="activity-avatar">{{ substr($activity->full_name ?? 'U', 0, 1) }}</div>
                                <div class="activity-details">
                                    <div class="activity-header">
                                        <span class="activity-user">{{ $activity->full_name ?? 'Unknown' }}</span>
                                        <span class="badge badge-primary">{{ $activity->action }}</span>
                                    </div>
                                    <div class="activity-action">{{ $activity->entity_type }} #{{ $activity->entity_id }}</div>
                                    <div class="activity-time">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</div>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted">Chưa có hoạt động</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Section: Detailed Analytics -->
        <div class="analytics-section">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Phân tích chi tiết</h3>
                    <div class="card-actions">
                        <select class="form-select form-select-sm" id="analyticsTimeRange">
                            <option value="7">7 ngày</option>
                            <option value="30" selected>30 ngày</option>
                            <option value="90">90 ngày</option>
                        </select>
                    </div>
                </div>
                <div class="card-content">
                    <div class="analytics-grid">
                        <div class="analytics-item">
                            <div class="analytics-icon leads">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Leads mới</div>
                                <div class="analytics-value">{{ $dashboardData['analytics']['new_leads'] }}</div>
                                <div class="analytics-trend">+12% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon viewings">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Lượt xem phòng</div>
                                <div class="analytics-value">{{ $dashboardData['analytics']['total_viewings'] }}</div>
                                <div class="analytics-trend">+8% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon contracts">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Hợp đồng ký mới</div>
                                <div class="analytics-value">{{ $dashboardData['analytics']['new_leases'] }}</div>
                                <div class="analytics-trend">+5% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon deposits">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Đặt cọc mới</div>
                                <div class="analytics-value">{{ $dashboardData['analytics']['new_deposits'] }}</div>
                                <div class="analytics-trend">+3% vs tháng trước</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
function clearDashboardCache() {
    if (typeof Notify !== 'undefined') {
        Notify.confirm(
            'Làm mới dữ liệu',
            'Bạn có chắc chắn muốn làm mới dữ liệu dashboard? Thao tác này sẽ xóa cache và tải lại dữ liệu mới nhất.',
            function() {
                // Show loading
                Notify.toast('Đang làm mới dữ liệu...', 'info');
                
                // Make AJAX request to clear cache
                fetch('{{ route("manager.dashboard.clear-cache") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Notify.toast('Dữ liệu đã được làm mới thành công!', 'success');
                        // Reload page to show updated data
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Notify.toast('Có lỗi xảy ra khi làm mới dữ liệu', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Notify.toast('Có lỗi xảy ra khi làm mới dữ liệu', 'error');
                });
            }
        );
    } else {
        // Fallback if Notify is not available
        if (confirm('Bạn có chắc chắn muốn làm mới dữ liệu dashboard?')) {
            fetch('{{ route("manager.dashboard.clear-cache") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Dữ liệu đã được làm mới thành công!');
                    window.location.reload();
                } else {
                    alert('Có lỗi xảy ra khi làm mới dữ liệu');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi làm mới dữ liệu');
            });
        }
    }
}
</script>
@endpush
@endsection
