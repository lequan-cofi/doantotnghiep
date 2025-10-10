@extends('layouts.superadmin')

@section('title', 'Super Admin Dashboard - SaaS Platform')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active" aria-current="page">
            <i class="fas fa-tachometer-alt me-1"></i>
            Dashboard
        </li>
    </ol>
</nav>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-crown text-warning me-2"></i>
                Super Admin Dashboard
            </h1>
            <p class="text-muted mb-0">Tổng quan toàn bộ hệ thống SaaS Platform</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="clearSuperAdminCache()" class="btn btn-outline-primary">
                <i class="fas fa-sync-alt me-1"></i>
                Làm mới dữ liệu
            </button>
        </div>
    </div>

    <!-- Primary SaaS Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổ chức
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $dashboardData['totalOrganizations'] ?? 0 }}
                            </div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +{{ $dashboardData['newOrganizationsThisMonth'] ?? 0 }} tháng này
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-building fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Người dùng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $dashboardData['totalUsers'] ?? 0 }}
                            </div>
                            <div class="text-xs text-success">
                                <i class="fas fa-arrow-up"></i> +{{ $dashboardData['newUsersThisMonth'] ?? 0 }} tháng này
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                MRR (Doanh thu tháng)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['monthlyRecurringRevenue'] ?? 0) }}đ
                            </div>
                            <div class="text-xs {{ ($dashboardData['mrrGrowthRate'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="fas fa-arrow-{{ ($dashboardData['mrrGrowthRate'] ?? 0) >= 0 ? 'up' : 'down' }}"></i> 
                                {{ abs($dashboardData['mrrGrowthRate'] ?? 0) }}% so với tháng trước
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Churn Rate
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['churnRate'] ?? 0, 1) }}%
                            </div>
                            <div class="text-xs {{ ($dashboardData['churnRate'] ?? 0) <= 5 ? 'text-success' : 'text-danger' }}">
                                {{ ($dashboardData['churnRate'] ?? 0) <= 5 ? 'Tốt' : 'Cần cải thiện' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary SaaS Metrics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                ARPU (Doanh thu/khách)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['averageRevenuePerUser'] ?? 0) }}đ
                            </div>
                            <div class="text-xs text-muted">
                                Trung bình/tháng
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">
                                LTV (Giá trị khách hàng)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['customerLifetimeValue'] ?? 0) }}đ
                            </div>
                            <div class="text-xs text-muted">
                                Trung bình/khách hàng
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-gem fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                CAC (Chi phí thu khách)
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['customerAcquisitionCost'] ?? 0) }}đ
                            </div>
                            <div class="text-xs text-muted">
                                Chi phí/khách hàng mới
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-light shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-light text-uppercase mb-1">
                                LTV/CAC Ratio
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($dashboardData['ltvCacRatio'] ?? 0, 1) }}:1
                            </div>
                            <div class="text-xs {{ ($dashboardData['ltvCacRatio'] ?? 0) >= 3 ? 'text-success' : 'text-warning' }}">
                                {{ ($dashboardData['ltvCacRatio'] ?? 0) >= 3 ? 'Tốt' : 'Cần cải thiện' }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-balance-scale fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- MRR Growth Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">MRR Growth & Churn Rate</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="mrrChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Organizations Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Phân bố tổ chức</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="organizationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional SaaS Charts -->
    <div class="row">
        <!-- ARPU vs CAC Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">ARPU vs CAC Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="arpuCacChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Growth Chart -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Growth & Retention</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="userGrowthChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Health & Performance -->
    <div class="row mb-4">
        <!-- System Health -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Health</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Server Status</span>
                            <span class="badge badge-success">Online</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Database</span>
                            <span class="badge badge-success">Healthy</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">API Response Time</span>
                            <span class="text-xs text-success">{{ $dashboardData['apiResponseTime'] ?? 0 }}ms</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Uptime</span>
                            <span class="text-xs text-muted">{{ $dashboardData['systemUptime'] ?? '99.9%' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Active Sessions</span>
                            <span class="text-xs text-primary">{{ $dashboardData['activeSessions'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Page Load Time</span>
                            <span class="text-xs text-success">{{ $dashboardData['pageLoadTime'] ?? 0 }}ms</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Memory Usage</span>
                            <span class="text-xs text-warning">{{ $dashboardData['memoryUsage'] ?? 0 }}%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">CPU Usage</span>
                            <span class="text-xs text-info">{{ $dashboardData['cpuUsage'] ?? 0 }}%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Business Metrics -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Business Health</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Conversion Rate</span>
                            <span class="text-xs text-success">{{ number_format($dashboardData['conversionRate'] ?? 0, 1) }}%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Support Tickets</span>
                            <span class="text-xs text-warning">{{ $dashboardData['openSupportTickets'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Feature Requests</span>
                            <span class="text-xs text-info">{{ $dashboardData['featureRequests'] ?? 0 }}</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-xs">Customer Satisfaction</span>
                            <span class="text-xs text-success">{{ number_format($dashboardData['customerSatisfaction'] ?? 0, 1) }}/5</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities & Top Organizations -->
    <div class="row">
        <!-- Recent Activities -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Hoạt động gần đây</h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['recentActivities'] ?? [] as $activity)
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-{{ $activity->action_type === 'created' ? 'plus' : ($activity->action_type === 'updated' ? 'edit' : 'trash') }} text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">{{ $activity->created_at->diffForHumans() }}</div>
                            <div class="text-xs">{{ $activity->description ?? 'Hoạt động hệ thống' }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-inbox fa-2x text-gray-300 mb-2"></i>
                        <p class="text-muted">Không có hoạt động nào</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Top Organizations -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tổ chức hàng đầu</h6>
                </div>
                <div class="card-body">
                    @forelse($dashboardData['topOrganizations'] ?? [] as $org)
                    <div class="d-flex align-items-center mb-3">
                        <div class="mr-3">
                            <div class="icon-circle bg-success">
                                <span class="text-white font-weight-bold">{{ strtoupper(substr($org->name, 0, 1)) }}</span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-xs font-weight-bold text-gray-800">{{ $org->name }}</div>
                            <div class="text-xs text-gray-500">
                                <span class="badge badge-primary">{{ $org->users_count ?? 0 }} users</span>
                                <span class="badge badge-success">{{ $org->properties_count ?? 0 }} properties</span>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i class="fas fa-building fa-2x text-gray-300 mb-2"></i>
                        <p class="text-muted">Không có tổ chức nào</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-secondary {
    border-left: 0.25rem solid #858796 !important;
}

.border-left-dark {
    border-left: 0.25rem solid #5a5c69 !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.border-left-light {
    border-left: 0.25rem solid #f8f9fc !important;
}

.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-area {
    position: relative;
    height: 10rem;
    width: 100%;
}

.chart-pie {
    position: relative;
    height: 15rem;
    width: 100%;
}

@media (max-width: 768px) {
    .chart-area {
        height: 8rem;
    }
    
    .chart-pie {
        height: 12rem;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Initialize Charts
document.addEventListener('DOMContentLoaded', function() {
    // MRR Growth Chart
    const mrrCtx = document.getElementById('mrrChart');
    if (mrrCtx) {
        new Chart(mrrCtx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'MRR (VNĐ)',
                    data: [15000000, 18000000, 22000000, 25000000, 28000000, 32000000],
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Churn Rate (%)',
                    data: [5.2, 4.8, 4.5, 4.2, 3.9, 3.6],
                    borderColor: '#e74a3b',
                    backgroundColor: 'rgba(231, 74, 59, 0.1)',
                    tension: 0.4,
                    fill: false,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        max: 10,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    }
    
    // Organizations Chart
    const orgCtx = document.getElementById('organizationsChart');
    if (orgCtx) {
        new Chart(orgCtx, {
            type: 'doughnut',
            data: {
                labels: ['Hoạt động', 'Tạm dừng', 'Mới tạo'],
                datasets: [{
                    data: [{{ $dashboardData['activeOrganizations'] ?? 0 }}, {{ $dashboardData['inactiveOrganizations'] ?? 0 }}, {{ $dashboardData['newOrganizations'] ?? 0 }}],
                    backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    // ARPU vs CAC Chart
    const arpuCacCtx = document.getElementById('arpuCacChart');
    if (arpuCacCtx) {
        new Chart(arpuCacCtx, {
            type: 'bar',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'ARPU (VNĐ)',
                    data: [250000, 280000, 320000, 350000, 380000, 420000],
                    backgroundColor: 'rgba(28, 200, 138, 0.8)',
                    borderColor: '#1cc88a',
                    borderWidth: 1
                }, {
                    label: 'CAC (VNĐ)',
                    data: [150000, 140000, 130000, 125000, 120000, 115000],
                    backgroundColor: 'rgba(231, 74, 59, 0.8)',
                    borderColor: '#e74a3b',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('vi-VN') + 'đ';
                            }
                        }
                    }
                }
            }
        });
    }

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx) {
        new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6'],
                datasets: [{
                    label: 'New Users',
                    data: [120, 150, 180, 200, 220, 250],
                    borderColor: '#36b9cc',
                    backgroundColor: 'rgba(54, 185, 204, 0.1)',
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Retained Users',
                    data: [95, 120, 145, 160, 175, 190],
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.4,
                    fill: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endpush