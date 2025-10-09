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
                <div class="stat-value">{{ DB::table('properties')->count() }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Tổng tài sản</span>
                </div>
            </div>
            
            <div class="stat-card success">
                <div class="stat-header">
                    <span class="stat-title">Tỷ lệ lấp đầy</span>
                    <i class="fas fa-chart-pie stat-icon"></i>
                </div>
                @php
                    $totalUnits = DB::table('units')->count();
                    $occupiedUnits = DB::table('units')->where('status', 'occupied')->count();
                    $occupancyRate = $totalUnits > 0 ? round(($occupiedUnits / $totalUnits) * 100, 1) : 0;
                @endphp
                <div class="stat-value">{{ $occupancyRate }}%</div>
                <div class="stat-footer">
                    <span class="stat-label">{{ $occupiedUnits }}/{{ $totalUnits }} phòng</span>
                </div>
            </div>
            
            <div class="stat-card warning">
                <div class="stat-header">
                    <span class="stat-title">Lịch xem phòng</span>
                    <i class="fas fa-calendar-check stat-icon"></i>
                </div>
                @php
                    $upcomingViewings = DB::table('viewings')
                        ->where('schedule_at', '>=', now())
                        ->where('status', 'confirmed')
                        ->count();
                @endphp
                <div class="stat-value">{{ $upcomingViewings }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Lịch hẹn sắp tới</span>
                </div>
            </div>
            
            <div class="stat-card info">
                <div class="stat-header">
                    <span class="stat-title">Tỷ lệ chuyển đổi</span>
                    <i class="fas fa-percentage stat-icon"></i>
                </div>
                @php
                    $totalLeads = DB::table('leads')->count();
                    $convertedLeads = DB::table('leads')->where('status', 'converted')->count();
                    $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;
                @endphp
                <div class="stat-value">{{ $conversionRate }}%</div>
                <div class="stat-footer">
                    <span class="stat-label">{{ $convertedLeads }}/{{ $totalLeads }} leads</span>
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
                @php
                    $monthlyRevenue = DB::table('invoices')
                        ->where('status', 'paid')
                        ->whereYear('issue_date', now()->year)
                        ->whereMonth('issue_date', now()->month)
                        ->sum('total_amount');
                @endphp
                <div class="stat-value-large">{{ number_format($monthlyRevenue / 1000000, 1) }}M</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+15% so với tháng trước</span>
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
                @php
                    $monthlyCommission = DB::table('commission_events')
                        ->whereYear('occurred_at', now()->year)
                        ->whereMonth('occurred_at', now()->month)
                        ->sum('commission_total');
                @endphp
                <div class="stat-value-large">{{ number_format($monthlyCommission / 1000000, 1) }}M</div>
                <div class="stat-trend up">
                    <i class="fas fa-arrow-up"></i>
                    <span>+8% so với tháng trước</span>
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
                @php
                    $pendingInvoices = DB::table('invoices')->whereIn('status', ['issued','overdue'])->count();
                    $openTickets = DB::table('tickets')->whereIn('status', ['open','in_progress'])->count();
                @endphp
                <div class="stat-value-large">{{ $pendingInvoices + $openTickets }}</div>
                <div class="stat-details">
                    <span>{{ $pendingInvoices }} hóa đơn, {{ $openTickets }} tickets</span>
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
                            @php
                                $unitsByStatus = DB::table('units')
                                    ->select('status', DB::raw('count(*) as count'))
                                    ->groupBy('status')
                                    ->pluck('count', 'status');
                            @endphp
                            <div class="occupancy-item available">
                                <div class="occupancy-icon"><i class="fas fa-check-circle"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $unitsByStatus['available'] ?? 0 }}</div>
                                    <div class="occupancy-label">Trống</div>
                                </div>
                            </div>
                            <div class="occupancy-item occupied">
                                <div class="occupancy-icon"><i class="fas fa-users"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $unitsByStatus['occupied'] ?? 0 }}</div>
                                    <div class="occupancy-label">Đã thuê</div>
                                </div>
                            </div>
                            <div class="occupancy-item reserved">
                                <div class="occupancy-icon"><i class="fas fa-clock"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $unitsByStatus['reserved'] ?? 0 }}</div>
                                    <div class="occupancy-label">Đặt cọc</div>
                                </div>
                            </div>
                            <div class="occupancy-item maintenance">
                                <div class="occupancy-icon"><i class="fas fa-tools"></i></div>
                                <div class="occupancy-info">
                                    <div class="occupancy-value">{{ $unitsByStatus['maintenance'] ?? 0 }}</div>
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
                        @php
                            $topAgents = DB::table('commission_event_splits')
                                ->join('users', 'users.id', '=', 'commission_event_splits.user_id')
                                ->select('users.id', 'users.full_name', DB::raw('SUM(commission_event_splits.amount) as total_commission'), DB::raw('COUNT(*) as deals'))
                                ->whereYear('commission_event_splits.created_at', now()->year)
                                ->whereMonth('commission_event_splits.created_at', now()->month)
                                ->groupBy('users.id', 'users.full_name')
                                ->orderByDesc('total_commission')
                                ->limit(5)
                                ->get();
                        @endphp
                        <div class="top-agents-list">
                            @forelse ($topAgents as $index => $agent)
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
                        <a href="{{ route('manager.reports.revenue') }}" class="quick-action-btn">
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
                        @php
                            $overdueInvoices = DB::table('invoices')->where('status', 'overdue')->count();
                            $expiringLeases = DB::table('leases')
                                ->where('end_date', '<=', now()->addDays(30))
                                ->where('status', 'active')
                                ->count();
                            $pendingViewings = DB::table('viewings')
                                ->where('status', 'requested')
                                ->count();
                        @endphp
                        <div class="alert-item urgent">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div class="alert-content">
                                <div class="alert-title">Hóa đơn quá hạn</div>
                                <div class="alert-value">{{ $overdueInvoices }} hóa đơn</div>
                            </div>
                            <a href="{{ route('manager.invoices.index') }}" class="alert-action">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="alert-item warning">
                            <i class="fas fa-file-contract"></i>
                            <div class="alert-content">
                                <div class="alert-title">HĐ sắp hết hạn</div>
                                <div class="alert-value">{{ $expiringLeases }} hợp đồng</div>
                            </div>
                            <a href="{{ route('manager.leases.index') }}" class="alert-action">
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>

                        <div class="alert-item info">
                            <i class="fas fa-calendar"></i>
                            <div class="alert-content">
                                <div class="alert-title">Lịch hẹn chờ duyệt</div>
                                <div class="alert-value">{{ $pendingViewings }} lịch</div>
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
                        @php
                            $recentActivities = DB::table('audit_logs')
                                ->join('users', 'users.id', '=', 'audit_logs.actor_id')
                                ->orderBy('audit_logs.created_at', 'desc')
                                ->limit(5)
                                ->select('audit_logs.*', 'users.full_name')
                                ->get();
                        @endphp
                        <div class="activity-list">
                            @forelse ($recentActivities as $activity)
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
                                @php
                                    $newLeads = DB::table('leads')
                                        ->where('created_at', '>=', now()->subDays(30))
                                        ->count();
                                @endphp
                                <div class="analytics-value">{{ $newLeads }}</div>
                                <div class="analytics-trend">+12% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon viewings">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Lượt xem phòng</div>
                                @php
                                    $totalViewings = DB::table('viewings')
                                        ->where('created_at', '>=', now()->subDays(30))
                                        ->count();
                                @endphp
                                <div class="analytics-value">{{ $totalViewings }}</div>
                                <div class="analytics-trend">+8% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon contracts">
                                <i class="fas fa-file-signature"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Hợp đồng ký mới</div>
                                @php
                                    $newLeases = DB::table('leases')
                                        ->where('created_at', '>=', now()->subDays(30))
                                        ->count();
                                @endphp
                                <div class="analytics-value">{{ $newLeases }}</div>
                                <div class="analytics-trend">+5% vs tháng trước</div>
                            </div>
                        </div>

                        <div class="analytics-item">
                            <div class="analytics-icon deposits">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="analytics-info">
                                <div class="analytics-label">Đặt cọc mới</div>
                                @php
                                    $newDeposits = DB::table('booking_deposits')
                                        ->where('created_at', '>=', now()->subDays(30))
                                        ->where('payment_status', 'paid')
                                        ->count();
                                @endphp
                                <div class="analytics-value">{{ $newDeposits }}</div>
                                <div class="analytics-trend">+3% vs tháng trước</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection
