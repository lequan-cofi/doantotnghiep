@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết báo cáo doanh thu')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-list-alt text-primary"></i>
                Chi tiết báo cáo doanh thu
            </h1>
            <p class="text-muted mb-0">Danh sách chi tiết các giao dịch doanh thu</p>
        </div>
        <div>
            <a href="{{ route('manager.revenue-reports.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <button class="btn btn-success" onclick="exportDetailReport()">
                <i class="fas fa-download"></i> Xuất Excel
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('manager.revenue-reports.detail') }}">
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
                            <label for="type">Loại giao dịch</label>
                            <select name="type" id="type" class="form-control">
                                <option value="all" {{ $type == 'all' ? 'selected' : '' }}>Tất cả</option>
                                <option value="rental" {{ $type == 'rental' ? 'selected' : '' }}>Cho thuê</option>
                                <option value="sale" {{ $type == 'sale' ? 'selected' : '' }}>Bán</option>
                                <option value="commission" {{ $type == 'commission' ? 'selected' : '' }}>Hoa hồng</option>
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

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng giao dịch
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $detailedData->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-contract fa-2x text-gray-300"></i>
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
                                Tổng giá trị
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($detailedData->sum(function($lease) { return $lease->rent_amount + $lease->deposit_amount; })) }} VND
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đã thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $detailedData->where('status', 'active')->count() }}
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
                                Chờ thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $detailedData->where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Data Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Chi tiết giao dịch</h6>
        </div>
        <div class="card-body">
            @if($detailedData->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ngày tạo</th>
                                <th>Loại</th>
                                <th>Bất động sản</th>
                                <th>Nhân viên</th>
                                <th>Khách hàng</th>
                                <th>Giá trị</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($detailedData as $lease)
                                <tr>
                                    <td>{{ $lease->id }}</td>
                                    <td>{{ $lease->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge bg-primary">Cho thuê</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $lease->unit->property->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $lease->unit->property->address ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $lease->agent->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $lease->agent->email ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $lease->tenant->name ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $lease->tenant->email ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong class="text-primary">
                                            {{ number_format($lease->rent_amount + $lease->deposit_amount) }} VND
                                        </strong>
                                    </td>
                                    <td>
                                        @switch($lease->status)
                                            @case('active')
                                                <span class="badge bg-success">Hoạt động</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-info">Hoàn thành</span>
                                                @break
                                            @case('pending')
                                                <span class="badge bg-warning">Chờ xử lý</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Đã hủy</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $lease->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @if($lease->invoices && $lease->invoices->count() > 0)
                                            @php
                                                $totalPaid = $lease->invoices->sum(function($invoice) {
                                                    return $invoice->payments->where('status', 'completed')->sum('amount');
                                                });
                                                $totalAmount = $lease->invoices->sum('total_amount');
                                                $percentage = $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0;
                                            @endphp
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}" 
                                                     role="progressbar" style="width: {{ $percentage }}%">
                                                    {{ $percentage }}%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($totalPaid) }}/{{ number_format($totalAmount) }} VND
                                            </small>
                                        @else
                                            <span class="badge bg-secondary">Chưa có hóa đơn</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('manager.leases.show', $lease->id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($lease->status !== 'cancelled')
                                                <a href="{{ route('manager.leases.edit', $lease->id) }}" 
                                                   class="btn btn-sm btn-warning" title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có dữ liệu</h5>
                    <p class="text-muted">Không tìm thấy giao dịch nào trong khoảng thời gian đã chọn.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/notifications.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/manager/revenue-reports.css') }}">
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="{{ asset('assets/js/manager/revenue-reports.js') }}"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#dataTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json"
        },
        "pageLength": 25,
        "order": [[ 1, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [9] }
        ]
    });
});

function resetFilter() {
    document.getElementById('start_date').value = '';
    document.getElementById('end_date').value = '';
    document.getElementById('type').value = 'all';
    document.querySelector('form').submit();
}

function exportDetailReport() {
    Notify.info('Chức năng xuất Excel đang được phát triển', 'Thông báo');
}

// Initialize notification system
if (typeof window.Notify === 'undefined') {
    window.Notify = new NotificationSystem();
}
</script>
@endpush
