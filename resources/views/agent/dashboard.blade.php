@extends('layouts.agent_dashboard')

@section('title', 'Agent Dashboard')

@section('content')
<div class="content">
    <div class="content-header">
        <h1 class="content-title">Dashboard</h1>
        <p class="content-subtitle">Chào mừng, {{ Auth::user()->name ?? 'Agent' }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Tổng BĐS</h6>
                            <h3 class="mb-0">{{ $stats['total_properties'] ?? 0 }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="fas fa-building fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Tổng phòng</h6>
                            <h3 class="mb-0">{{ $stats['total_units'] ?? 0 }}</h3>
                        </div>
                        <div class="text-success">
                            <i class="fas fa-door-open fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Hợp đồng</h6>
                            <h3 class="mb-0">{{ $stats['active_leases'] ?? 0 }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="fas fa-file-contract fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Lịch hẹn</h6>
                            <h3 class="mb-0">{{ $stats['total_viewings'] ?? 0 }}</h3>
                        </div>
                        <div class="text-info">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Hợp đồng gần đây</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentLeases) && $recentLeases->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentLeases as $lease)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $lease->unit->code ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $lease->tenant->full_name ?? 'N/A' }}</small>
                                    </div>
                                    <span class="badge badge-success">{{ $lease->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">Không có hợp đồng nào</p>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Lịch hẹn gần đây</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentViewings) && $recentViewings->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentViewings as $viewing)
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $viewing->unit->code ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $viewing->schedule_at ? $viewing->schedule_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                    </div>
                                    <span class="badge badge-info">{{ $viewing->status }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted text-center">Không có lịch hẹn nào</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Properties Overview -->
    @if(isset($properties) && $properties->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Tổng quan bất động sản</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Tên BĐS</th>
                                    <th>Tổng phòng</th>
                                    <th>Đã thuê</th>
                                    <th>Trống</th>
                                    <th>Tỷ lệ lấp đầy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($properties as $property)
                                    <tr>
                                        <td>{{ $property->name }}</td>
                                        <td>{{ $property->total_units ?? 0 }}</td>
                                        <td>{{ $property->occupied_units ?? 0 }}</td>
                                        <td>{{ $property->available_units ?? 0 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar" style="width: {{ $property->occupancy_rate ?? 0 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $property->occupancy_rate ?? 0 }}%</small>
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
    @endif
</div>
@endsection

@push('styles')
<style>
.progress {
    background-color: #e2e8f0;
    border-radius: 4px;
}

.progress-bar {
    background-color: #2563eb;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.list-group-item {
    border: none;
    border-bottom: 1px solid #e2e8f0;
    padding: 12px 0;
}

.list-group-item:last-child {
    border-bottom: none;
}

.badge {
    font-size: 11px;
    padding: 4px 8px;
}
</style>
@endpush
