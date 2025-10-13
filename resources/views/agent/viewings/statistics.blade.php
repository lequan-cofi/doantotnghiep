@extends('layouts.agent_dashboard')

@section('title', 'Thống kê lịch hẹn')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê lịch hẹn
                    </h1>
                    <p class="text-muted mb-0">Phân tích và báo cáo về lịch hẹn xem phòng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                    </a>
                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-number">{{ $stats['total_viewings'] }}</div>
                <div class="stat-label">Tổng lịch hẹn</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-number">{{ $stats['confirmed_viewings'] }}</div>
                <div class="stat-label">Đã xác nhận</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-number">{{ $stats['done_viewings'] }}</div>
                <div class="stat-label">Hoàn thành</div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-number">{{ $stats['today_viewings'] }}</div>
                <div class="stat-label">Hôm nay</div>
            </div>
        </div>
    </div>

    <!-- Additional Statistics -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                    <h5 class="mb-1">{{ $stats['requested_viewings'] }}</h5>
                    <p class="text-muted mb-0">Chờ xác nhận</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-times fa-2x text-danger mb-2"></i>
                    <h5 class="mb-1">{{ $stats['cancelled_viewings'] }}</h5>
                    <p class="text-muted mb-0">Đã hủy</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                    <h5 class="mb-1">{{ $stats['this_week_viewings'] }}</h5>
                    <p class="text-muted mb-0">Tuần này</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>Lịch hẹn theo ngày trong tháng
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="100"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Type Analysis -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-users me-2"></i>Phân tích nguồn khách
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="customerTypeChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Trạng thái lịch hẹn
                    </h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Viewings -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Lịch hẹn gần đây
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if($monthlyStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-viewings table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Số lượng</th>
                                        <th>Biểu đồ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($monthlyStats as $stat)
                                        <tr>
                                            <td>
                                                <strong>{{ \Carbon\Carbon::parse($stat->date)->format('d/m/Y') }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary">{{ $stat->count }}</span>
                                            </td>
                                            <td>
                                                <div class="progress" style="height: 20px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ ($stat->count / $monthlyStats->max('count')) * 100 }}%"
                                                         aria-valuenow="{{ $stat->count }}" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="{{ $monthlyStats->max('count') }}">
                                                        {{ $stat->count }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có dữ liệu thống kê</h5>
                            <p class="text-muted">Tạo lịch hẹn đầu tiên để xem thống kê</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/agent/viewings.js') }}"></script>
<script>
    // Monthly Chart
    const monthlyData = @json($monthlyStats);
    const monthlyLabels = monthlyData.map(item => {
        const date = new Date(item.date);
        return date.getDate() + '/' + (date.getMonth() + 1);
    });
    const monthlyCounts = monthlyData.map(item => item.count);

    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Số lịch hẹn',
                data: monthlyCounts,
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Customer Type Chart
    const customerTypeCtx = document.getElementById('customerTypeChart').getContext('2d');
    new Chart(customerTypeCtx, {
        type: 'doughnut',
        data: {
            labels: ['Lead', 'Khách thuê'],
            datasets: [{
                data: [{{ $stats['total_viewings'] - ($stats['total_viewings'] * 0.3) }}, {{ $stats['total_viewings'] * 0.3 }}],
                backgroundColor: ['#ffc107', '#0dcaf0'],
                borderWidth: 0
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

    // Status Chart
    const statusCtx = document.getElementById('statusChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'pie',
        data: {
            labels: ['Chờ xác nhận', 'Đã xác nhận', 'Hoàn thành', 'Đã hủy'],
            datasets: [{
                data: [
                    {{ $stats['requested_viewings'] }},
                    {{ $stats['confirmed_viewings'] }},
                    {{ $stats['done_viewings'] }},
                    {{ $stats['cancelled_viewings'] }}
                ],
                backgroundColor: ['#ffc107', '#0dcaf0', '#198754', '#dc3545'],
                borderWidth: 0
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
</script>
@endpush

@push('styles')
<link href="{{ asset('assets/css/notifications.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/agent/viewings.css') }}" rel="stylesheet">
@endpush
