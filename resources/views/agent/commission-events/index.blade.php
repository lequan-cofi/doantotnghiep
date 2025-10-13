@extends('layouts.agent_dashboard')

@section('title', 'Sự kiện hoa hồng')

@push('styles')
<style>
    .table {
        width: 100%;
        border-collapse: collapse;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
        color: #495057;
        cursor: pointer;
        user-select: none;
    }
    
    .table th:hover {
        background-color: #e9ecef;
    }
    
    .table td {
        padding: 12px 8px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    /* Custom badge styles to fix white background issue */
    .badge-success {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    .badge-info {
        background-color: #17a2b8 !important;
        color: white !important;
    }
    
    .badge-warning {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .badge-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    .badge-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }
    
    .badge-primary {
        background-color: #007bff !important;
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary"></i>
                Sự kiện hoa hồng
            </h1>
            <p class="text-muted mb-0">Theo dõi tất cả sự kiện hoa hồng của bạn</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng sự kiện
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_events']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                                Tổng hoa hồng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_commission']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                {{ number_format($stats['paid_commission']) }}đ
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
                                {{ number_format($stats['pending_commission']) }}đ
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

    <!-- Status Statistics -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie"></i>
                        Thống kê theo trạng thái
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-warning">{{ $statusStats['pending'] }}</div>
                                <small class="text-muted">Chờ duyệt</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-info">{{ $statusStats['approved'] }}</div>
                                <small class="text-muted">Đã duyệt</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-success">{{ $statusStats['paid'] }}</div>
                                <small class="text-muted">Đã thanh toán</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-danger">{{ $statusStats['reversed'] }}</div>
                                <small class="text-muted">Đã hoàn</small>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-4 col-6 mb-3">
                            <div class="text-center">
                                <div class="h4 text-secondary">{{ $statusStats['cancelled'] }}</div>
                                <small class="text-muted">Đã hủy</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission Events Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i>
                Danh sách sự kiện hoa hồng
            </h6>
        </div>
        <div class="card-body">
            <!-- Debug Information -->
            {{-- <div class="alert alert-info">
                <strong>Debug Info:</strong><br>
                Total Events: {{ $events->count() }}<br>
                User ID: {{ auth()->user()->id }}<br>
                Organization ID: {{ \DB::table('organization_users')->where('user_id', auth()->user()->id)->where('status', 'active')->value('organization_id') ?? 'NULL' }}<br>
                Stats: {{ json_encode($stats) }}<br>
                <br>
                @if(!\DB::table('organization_users')->where('user_id', auth()->user()->id)->where('status', 'active')->exists())
                    <div class="alert alert-warning">
                        <strong>⚠️ WARNING:</strong> User has no organization_id! This is why no data is showing.
                    </div>
                @endif
                <br>
                <strong>Sample Events Data:</strong><br>
                @if($events->count() > 0)
                    @foreach($events->take(3) as $event)
                        Event ID: {{ $event->id }}, Policy: {{ $event->policy->title ?? 'N/A' }}, Agent: {{ $event->agent->name ?? 'N/A' }}<br>
                    @endforeach
                @else
                    No events found
                @endif
                <br><br>
                <button class="btn btn-sm btn-warning" onclick="testDatabase()">Test Database Connection</button>
                <small class="text-muted">(Note: Test route not available - check console for debug info)</small>
            </div> --}}
            
            @if($events->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Ngày</th>
                                <th>Chính sách</th>
                                <th>Sự kiện</th>
                                <th>Tham chiếu</th>
                                <th>Agent</th>
                                <th>Số tiền gốc</th>
                                <th>Hoa hồng</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                            <tr>
                                <td>
                                    <small>{{ $event->occurred_at->format('d/m/Y H:i') }}</small>
                                </td>
                                <td>
                                    <strong>{{ $event->policy->title ?? 'N/A' }}</strong>
                                    @if($event->policy && $event->policy->code)
                                        <br><small class="text-muted">{{ $event->policy->code }}</small>
                                    @endif
                                </td>
                                <td>
                                    @switch($event->trigger_event)
                                        @case('deposit_paid')
                                            <span class="badge badge-info">
                                                <i class="fas fa-hand-holding-usd"></i> Đặt cọc
                                            </span>
                                            @break
                                        @case('lease_signed')
                                            <span class="badge badge-success">
                                                <i class="fas fa-file-contract"></i> Ký hợp đồng
                                            </span>
                                            @break
                                        @case('invoice_paid')
                                            <span class="badge badge-primary">
                                                <i class="fas fa-file-invoice-dollar"></i> Thanh toán hóa đơn
                                            </span>
                                            @break
                                        @case('viewing_done')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-eye"></i> Xem phòng
                                            </span>
                                            @break
                                        @case('listing_published')
                                            <span class="badge badge-success">
                                                <i class="fas fa-bullhorn"></i> Đăng tin
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $event->trigger_event }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($event->lease)
                                        <small class="text-muted">
                                            Hợp đồng #{{ $event->lease->id }}
                                            @if($event->lease->unit && $event->lease->unit->property)
                                                <br>{{ $event->lease->unit->property->name }}
                                            @endif
                                            @if($event->lease->tenant)
                                                <br><i class="fas fa-user"></i> {{ $event->lease->tenant->name }}
                                            @endif
                                        </small>
                                    @elseif($event->listing)
                                        <small class="text-muted">
                                            Tin đăng #{{ $event->listing->id }}
                                        </small>
                                    @else
                                        <small class="text-muted">
                                            {{ $event->ref_type }} #{{ $event->ref_id }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($event->agent)
                                        <div>
                                            <strong>{{ $event->agent->name ?? 'N/A' }}</strong>
                                            @if($event->agent->email)
                                                <br><small class="text-muted">{{ $event->agent->email }}</small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ number_format($event->amount_base) }}đ</strong>
                                </td>
                                <td>
                                    <strong class="text-success">{{ number_format($event->commission_total) }}đ</strong>
                                </td>
                                <td>
                                    @switch($event->status)
                                        @case('pending')
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock"></i> Chờ duyệt
                                            </span>
                                            @break
                                        @case('approved')
                                            <span class="badge badge-info">
                                                <i class="fas fa-check"></i> Đã duyệt
                                            </span>
                                            @break
                                        @case('paid')
                                            <span class="badge badge-success">
                                                <i class="fas fa-money-bill-wave"></i> Đã thanh toán
                                            </span>
                                            @break
                                        @case('reversed')
                                            <span class="badge badge-danger">
                                                <i class="fas fa-undo"></i> Đã hoàn
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge badge-secondary">
                                                <i class="fas fa-times"></i> Đã hủy
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $event->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('agent.commission-events.show', $event->id) }}" 
                                           class="btn btn-sm btn-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Soft Delete Button -->
                                        <form action="{{ route('agent.commission-events.destroy', $event->id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-soft-delete" 
                                                    title="Xóa sự kiện" data-event-id="{{ $event->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $events->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Chưa có sự kiện hoa hồng nào</h5>
                    <p class="text-muted">Chưa có sự kiện hoa hồng nào được tạo cho bạn.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show notifications from session
    @if(session('success'))
        Notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        Notify.error('{{ session('error') }}');
    @endif

    @if(session('warning'))
        Notify.warning('{{ session('warning') }}');
    @endif

    @if(session('info'))
        Notify.info('{{ session('info') }}');
    @endif

    // Initialize table sorting
    initializeTableSorting();

    // Soft delete confirmation
    $(document).on('click', '.btn-soft-delete', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const eventId = $(this).data('event-id');
        
        Notify.confirmDelete(`sự kiện hoa hồng #${eventId}`, function() {
            form.submit();
        });
    });

    // Simple table sorting function
    function initializeTableSorting() {
        const table = document.querySelector('.table');
        if (!table) return;

        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            // Skip the last column (actions)
            if (index === headers.length - 1) return;
            
            header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
            
            header.addEventListener('click', () => {
                sortTable(table, index);
            });
        });
    }

    function sortTable(table, columnIndex) {
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        const isAscending = table.getAttribute('data-sort-direction') !== 'asc';
        table.setAttribute('data-sort-direction', isAscending ? 'asc' : 'desc');
        
        rows.sort((a, b) => {
            const aText = a.cells[columnIndex].textContent.trim();
            const bText = b.cells[columnIndex].textContent.trim();
            
            // Try to parse as numbers first
            const aNum = parseFloat(aText.replace(/[^\d.-]/g, ''));
            const bNum = parseFloat(bText.replace(/[^\d.-]/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                return isAscending ? aNum - bNum : bNum - aNum;
            }
            
            // Fall back to string comparison
            return isAscending ? 
                aText.localeCompare(bText, 'vi', { numeric: true }) : 
                bText.localeCompare(aText, 'vi', { numeric: true });
        });
        
        // Clear tbody and re-append sorted rows
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort indicators
        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            const icon = header.querySelector('i');
            if (index === columnIndex) {
                icon.className = isAscending ? 'fas fa-sort-up text-primary' : 'fas fa-sort-down text-primary';
            } else {
                icon.className = 'fas fa-sort text-muted';
            }
        });
        
        Notify.info(`Đã sắp xếp theo cột ${headers[columnIndex].textContent.replace(/\s*<i.*<\/i>/, '').trim()}`, 'Sắp xếp');
    }

    // Show page info
    const totalEvents = {{ $events->count() }};
    if (totalEvents > 0) {
        Notify.info(`Hiển thị ${totalEvents} sự kiện hoa hồng`, 'Danh sách sự kiện');
    }
});

// Test database function removed - route no longer exists
</script>
@endpush