@extends('layouts.manager_dashboard')

@section('title', 'Báo cáo doanh thu')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary"></i>
                Báo cáo doanh thu
            </h1>
            <p class="text-muted mb-0">Phân tích và thống kê doanh thu bất động sản</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="exportReport()">
                <i class="fas fa-download"></i> Xuất báo cáo
            </button>
            <a href="{{ route('manager.revenue-reports.detail') }}" class="btn btn-info">
                <i class="fas fa-list"></i> Xem chi tiết
            </a>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc thời gian</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.revenue-reports.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="start_date">Từ ngày</label>
                            <input type="date" name="start_date" id="start_date" class="form-control" 
                                   value="{{ $startDate }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="end_date">Đến ngày</label>
                            <input type="date" name="end_date" id="end_date" class="form-control" 
                                   value="{{ $endDate }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="period">Chu kỳ</label>
                            <select name="period" id="period" class="form-control">
                                <option value="daily" {{ $period == 'daily' ? 'selected' : '' }}>Hàng ngày</option>
                                <option value="weekly" {{ $period == 'weekly' ? 'selected' : '' }}>Hàng tuần</option>
                                <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Hàng tháng</option>
                                <option value="yearly" {{ $period == 'yearly' ? 'selected' : '' }}>Hàng năm</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Lọc
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="resetFilter()">
                                    <i class="fas fa-times"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Revenue Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng doanh thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenueData['total_revenue']) }} VND
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
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenueData['payment_revenue']) }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                                Chờ thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenueData['pending_revenue']) }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                Hoa hồng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($revenueData['commission_revenue']) }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Xu hướng doanh thu</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" 
                             aria-labelledby="dropdownMenuLink">
                            <div class="dropdown-header">Tùy chọn:</div>
                            <a class="dropdown-item" href="#" onclick="changeChartType('line')">Biểu đồ đường</a>
                            <a class="dropdown-item" href="#" onclick="changeChartType('bar')">Biểu đồ cột</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue by Type Chart -->
        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Doanh thu theo loại</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4 pb-2">
                        <canvas id="revenueByTypeChart"></canvas>
                    </div>
                    <div class="mt-4 text-center small">
                        @foreach($revenueData['revenue_by_type'] as $type => $amount)
                            <span class="mr-2">
                                <i class="fas fa-circle text-{{ $type == 'rental' ? 'primary' : 'success' }}"></i>
                                {{ $type == 'rental' ? 'Cho thuê' : 'Bán' }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Row -->
    <div class="row mb-4">
        <!-- Property Statistics -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê bất động sản</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ $propertyStats['total_properties'] }}</div>
                                <div class="text-muted">Tổng BĐS</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-success">{{ $propertyStats['active_properties'] }}</div>
                                <div class="text-muted">Đang hoạt động</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-info">{{ $propertyStats['rented_properties'] }}</div>
                                <div class="text-muted">Đã cho thuê</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-warning">{{ $propertyStats['occupancy_rate'] }}%</div>
                                <div class="text-muted">Tỷ lệ lấp đầy</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Statistics -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê hoa hồng</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-primary">{{ number_format($commissionStats['total_commission']) }}</div>
                                <div class="text-muted">Tổng hoa hồng</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-success">{{ number_format($commissionStats['paid_commission']) }}</div>
                                <div class="text-muted">Đã trả</div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-warning">{{ number_format($commissionStats['pending_commission']) }}</div>
                                <div class="text-muted">Chờ trả</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <div class="h4 text-info">
                                    {{ $commissionStats['total_commission'] > 0 ? 
                                        round(($commissionStats['paid_commission'] / $commissionStats['total_commission']) * 100, 1) : 0 }}%
                                </div>
                                <div class="text-muted">Tỷ lệ trả</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top nhân viên xuất sắc</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Hạng</th>
                                    <th>Nhân viên</th>
                                    <th>Hợp đồng thuê</th>
                                    <th>Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topPerformers as $index => $performer)
                                    <tr>
                                        <td>
                                            @if($index == 0)
                                                <i class="fas fa-trophy text-warning"></i>
                                            @elseif($index == 1)
                                                <i class="fas fa-medal text-secondary"></i>
                                            @elseif($index == 2)
                                                <i class="fas fa-award text-warning"></i>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $performer->full_name ?? 'N/A' }}</td>
                                        <td>{{ $performer->leases_count }}</td>
                                        <td>{{ number_format($performer->total_revenue) }} VND</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Occupancy Rates -->
        <div class="col-xl-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tỷ lệ lấp đầy theo BĐS</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>BĐS</th>
                                    <th>Loại</th>
                                    <th>Đã thuê</th>
                                    <th>Tỷ lệ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($occupancyRates->take(10) as $occupancy)
                                    <tr>
                                        <td>{{ $occupancy['property_name'] }}</td>
                                        <td>
                                            <span class="badge bg-{{ $occupancy['property_type'] == 'apartment' ? 'primary' : 'success' }}">
                                                {{ $occupancy['property_type'] == 'apartment' ? 'Chung cư' : 'Nhà trọ' }}
                                            </span>
                                        </td>
                                        <td>{{ $occupancy['occupied_units'] }}/{{ $occupancy['total_units'] }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $occupancy['occupancy_rate'] >= 80 ? 'success' : ($occupancy['occupancy_rate'] >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" style="width: {{ $occupancy['occupancy_rate'] }}%">
                                                    {{ $occupancy['occupancy_rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/notifications.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/manager/revenue-reports.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/manager/revenue-reports.js') }}"></script>
<script>
// Revenue Trend Chart
const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
const revenueTrendChart = new Chart(revenueTrendCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($revenueTrends->pluck('period')) !!},
        datasets: [{
            label: 'Doanh thu',
            data: {!! json_encode($revenueTrends->pluck('revenue')) !!},
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return new Intl.NumberFormat('vi-VN').format(value) + ' VND';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' VND';
                    }
                }
            }
        }
    }
});

// Revenue by Type Chart
const revenueByTypeCtx = document.getElementById('revenueByTypeChart').getContext('2d');
const revenueByTypeChart = new Chart(revenueByTypeCtx, {
    type: 'doughnut',
    data: {
        labels: ['Cho thuê', 'Bán'],
        datasets: [{
            data: [
                {{ $revenueData['revenue_by_type']->get('rental', 0) }},
                {{ $revenueData['revenue_by_type']->get('sale', 0) }}
            ],
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)'
            ],
            borderColor: [
                'rgba(54, 162, 235, 1)',
                'rgba(255, 99, 132, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                        return context.label + ': ' + new Intl.NumberFormat('vi-VN').format(context.parsed) + ' VND (' + percentage + '%)';
                    }
                }
            }
        }
    }
});

// Functions
function changeChartType(type) {
    revenueTrendChart.config.type = type;
    revenueTrendChart.update();
}

function resetFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('period').value = 'monthly';
    document.querySelector('form').submit();
}

function exportReport() {
    Notify.info('Chức năng xuất báo cáo đang được phát triển', 'Thông báo');
}

// Initialize notification system
if (typeof window.Notify === 'undefined') {
    window.Notify = new NotificationSystem();
}
</script>
@endpush
