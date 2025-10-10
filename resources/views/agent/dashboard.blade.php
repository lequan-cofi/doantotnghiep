@extends('layouts.agent_dashboard')

@section('title', 'Agent Dashboard')

@section('content')
<main class="main-content">
    <!-- Top Header -->
    <header class="top-header">
        <div class="header-left">
            <button class="mobile-menu-toggle" id="mobileMenuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="breadcrumb">
                <a href="{{ route('agent.dashboard') }}">Dashboard</a>
            </div>
        </div>
        
        <div class="header-right">
            <!-- Notifications -->
            <div class="notification-dropdown">
                <button class="notification-btn" data-bs-toggle="dropdown">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">0</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end notification-menu">
                    <div class="notification-header">
                        <h6>Thông báo</h6>
                        <a href="#" class="view-all">Xem tất cả</a>
                    </div>
                    <div class="notification-list">
                        <div class="notification-item">
                            <div class="notification-content">
                                <p>Chưa có thông báo nào</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Menu -->
            <div class="user-dropdown">
                <button class="user-btn" data-bs-toggle="dropdown">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-info">
                        <span class="user-name">{{ Auth::user()->full_name }}</span>
                        <span class="user-role">Agent</span>
                    </div>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-end user-menu">
                    <a href="{{ route('agent.profile') }}" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Hồ sơ cá nhân</span>
                    </a>
                    <a href="{{ route('agent.dashboard') }}" class="dropdown-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item logout-btn">
                            <i class="fas fa-sign-out-alt"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

            <header class="header">
                <div class="header-content">
                    <div class="header-info">
                <h1>Dashboard Agent</h1>
                <p>Tổng quan bất động sản được gán quản lý</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.properties.index') }}" class="btn btn-primary">
                    <i class="fas fa-building"></i>
                    Xem bất động sản
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
                <div class="stat-value">{{ $assignedProperties->count() }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Tổng tài sản được gán</span>
                </div>
                    </div>
                    
            <div class="stat-card success">
                        <div class="stat-header">
                    <span class="stat-title">Tỷ lệ lấp đầy</span>
                    <i class="fas fa-chart-pie stat-icon"></i>
                        </div>
                <div class="stat-value">{{ $occupancyRate }}%</div>
                <div class="stat-footer">
                    <span class="stat-label">{{ $occupiedUnits }}/{{ $totalUnits }} phòng</span>
                        </div>
                    </div>
                    
            <div class="stat-card warning">
                        <div class="stat-header">
                    <span class="stat-title">Hợp đồng hoạt động</span>
                    <i class="fas fa-file-contract stat-icon"></i>
                        </div>
                <div class="stat-value">{{ $activeLeases }}</div>
                <div class="stat-footer">
                    <span class="stat-label">Đang có hiệu lực</span>
                    </div>
                </div>
                
            <div class="stat-card info">
                <div class="stat-header">
                    <span class="stat-title">Doanh thu tháng</span>
                    <i class="fas fa-money-bill-wave stat-icon"></i>
                </div>
                <div class="stat-value">{{ number_format($monthlyRevenue, 0, ',', '.') }}</div>
                <div class="stat-footer">
                    <span class="stat-label">VNĐ</span>
                            </div>
                            </div>
                        </div>
                        
        <!-- Properties Overview -->
        <div class="dashboard-grid">
            <div class="dashboard-card">
                            <div class="card-header">
                    <h3>Bất động sản được gán</h3>
                    <a href="{{ route('agent.properties.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
                            </div>
                <div class="card-body">
                    @if($assignedProperties->count() > 0)
                        <div class="properties-list">
                            @foreach($assignedProperties->take(5) as $property)
                            <div class="property-item">
                                <div class="property-info">
                                    <h4>{{ $property->name }}</h4>
                                    <p class="property-location">
                                        @if($property->location2025)
                                            <i class="fas fa-map-marker-alt text-primary"></i>
                                            <strong>Mới:</strong> {{ $property->location2025->street }}, {{ $property->location2025->ward }}, {{ $property->location2025->city }}
                                        @elseif($property->location)
                                            <i class="fas fa-map-marker-alt text-secondary"></i>
                                            <strong>Cũ:</strong> {{ $property->location->address }}, {{ $property->location->city }}
                                        @else
                                            <i class="fas fa-map-marker-alt"></i>
                                            Chưa có địa chỉ
                                        @endif
                                    </p>
                                    <div class="property-stats">
                                        <span class="stat">
                                            <i class="fas fa-door-open"></i>
                                            {{ $property->units->count() }} phòng
                                        </span>
                                        <span class="stat">
                                            <i class="fas fa-check-circle text-success"></i>
                                            {{ $property->units->where('status', 'occupied')->count() }} đã thuê
                                        </span>
                                    </div>
                                </div>
                                <div class="property-actions">
                                    <a href="{{ route('agent.properties.show', $property->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    </div>
                                </div>
                            @endforeach
                                    </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-building fa-3x text-muted"></i>
                            <h4>Chưa có bất động sản nào</h4>
                            <p>Bạn chưa được gán quản lý bất động sản nào. Vui lòng liên hệ quản lý để được phân công.</p>
                                </div>
                    @endif
                        </div>
                    </div>
                    
            <div class="dashboard-card">
                            <div class="card-header">
                    <h3>Thống kê nhanh</h3>
                            </div>
                <div class="card-body">
                    <div class="quick-stats">
                        <div class="quick-stat">
                            <div class="quick-stat-icon">
                                <i class="fas fa-home text-primary"></i>
                                        </div>
                            <div class="quick-stat-content">
                                <div class="quick-stat-value">{{ $totalUnits }}</div>
                                <div class="quick-stat-label">Tổng phòng</div>
                                    </div>
                                </div>
                                
                        <div class="quick-stat">
                            <div class="quick-stat-icon">
                                <i class="fas fa-door-open text-success"></i>
                                        </div>
                            <div class="quick-stat-content">
                                <div class="quick-stat-value">{{ $availableUnits }}</div>
                                <div class="quick-stat-label">Phòng trống</div>
                                    </div>
                                </div>
                                
                        <div class="quick-stat">
                            <div class="quick-stat-icon">
                                <i class="fas fa-users text-warning"></i>
                                        </div>
                            <div class="quick-stat-content">
                                <div class="quick-stat-value">{{ $activeLeases }}</div>
                                <div class="quick-stat-label">Hợp đồng</div>
                                    </div>
                                </div>
                                
                        <div class="quick-stat">
                            <div class="quick-stat-icon">
                                <i class="fas fa-chart-line text-info"></i>
                                        </div>
                            <div class="quick-stat-content">
                                <div class="quick-stat-value">{{ $occupancyRate }}%</div>
                                <div class="quick-stat-label">Lấp đầy</div>
                                    </div>
                                </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
        <!-- Recent Activity -->
        <div class="dashboard-card">
                            <div class="card-header">
                <h3>Hoạt động gần đây</h3>
                            </div>
            <div class="card-body">
                <div class="activity-list">
                    @if($recentLeases->count() > 0)
                        @foreach($recentLeases->take(5) as $lease)
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file-contract text-primary"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title">
                                    Hợp đồng mới: {{ $lease->contract_no ?? 'HD' . str_pad($lease->id, 6, '0', STR_PAD_LEFT) }}
                        </div>
                                <div class="activity-description">
                                    {{ $lease->tenant->full_name ?? 'Khách thuê' }} - {{ $lease->unit->code ?? 'Phòng ' . $lease->unit->id }}
                            </div>
                                <div class="activity-time">
                                    {{ $lease->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="empty-state">
                            <i class="fas fa-history fa-2x text-muted"></i>
                            <p>Chưa có hoạt động nào</p>
                        </div>
                    @endif
                        </div>
                    </div>
                </div>
            </div>
        
@endsection

@push('styles')
<style>
.properties-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.property-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.property-info h4 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
}

.property-location {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #64748b;
}

.property-location i {
    margin-right: 6px;
}

.property-stats {
    display: flex;
    gap: 16px;
}

.property-stats .stat {
    font-size: 12px;
    color: #64748b;
}

.property-stats .stat i {
    margin-right: 4px;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.quick-stat {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
}

.quick-stat-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 8px;
    font-size: 18px;
}

.quick-stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.quick-stat-label {
    font-size: 12px;
    color: #64748b;
    margin-top: 2px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 8px;
}

.activity-icon {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    border-radius: 6px;
    font-size: 14px;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.activity-description {
    font-size: 14px;
    color: #64748b;
    margin-bottom: 4px;
}

.activity-time {
    font-size: 12px;
    color: #94a3b8;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
}

.empty-state i {
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-state h4 {
    margin-bottom: 8px;
    color: #1e293b;
}

.empty-state p {
    margin: 0;
    font-size: 14px;
}
</style>
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle functionality
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (mobileMenuToggle && sidebar) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
        });
        
        // Close mobile menu when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                    sidebar.classList.remove('mobile-open');
                }
            }
        });
    }
});
</script>
</main>
