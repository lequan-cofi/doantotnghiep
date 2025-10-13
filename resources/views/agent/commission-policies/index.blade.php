@extends('layouts.agent_dashboard')

@section('title', 'Chính sách hoa hồng')

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
                <i class="fas fa-percentage text-primary"></i>
                Chính sách hoa hồng
            </h1>
            <p class="text-muted mb-0">Theo dõi các chính sách hoa hồng và sự kiện hoa hồng của bạn</p>
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
                                Tổng chính sách
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_policies']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-cogs fa-2x text-gray-300"></i>
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
                                Chính sách hoạt động
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_policies']) }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
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
    </div>

    <!-- Commission Policies Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i>
                Danh sách chính sách hoa hồng
            </h6>
        </div>
        <div class="card-body">
            <!-- Debug Information -->
            {{-- <div class="alert alert-info">
                <strong>Debug Info:</strong><br>
                Total Policies: {{ $policies->count() }}<br>
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
                <strong>Sample Policies Data:</strong><br>
                @if($policies->count() > 0)
                    @foreach($policies->take(3) as $policy)
                        Policy ID: {{ $policy->id }}, Title: {{ $policy->title }}, Active: {{ $policy->active ? 'Yes' : 'No' }}<br>
                    @endforeach
                @else
                    No policies found
                @endif
                <br><br>
                <button class="btn btn-sm btn-warning" onclick="testDatabase()">Test Database Connection</button>
                <small class="text-muted">(Note: Test route not available - check console for debug info)</small>
            </div>
             --}}
            @if($policies->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Mã chính sách</th>
                                <th>Tên chính sách</th>
                                <th>Sự kiện kích hoạt</th>
                                <th>Loại tính toán</th>
                                <th>Giá trị</th>
                                <th>Cơ sở tính</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($policies as $policy)
                            <tr>
                                <td>
                                    <span class="badge badge-secondary">{{ $policy->code ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <strong>{{ $policy->title }}</strong>
                                </td>
                                <td>
                                    @switch($policy->trigger_event)
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
                                            <span class="badge badge-dark">
                                                <i class="fas fa-bullhorn"></i> Đăng tin
                                            </span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $policy->trigger_event }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @switch($policy->calc_type)
                                        @case('percent')
                                            <span class="badge badge-success">Phần trăm</span>
                                            @break
                                        @case('flat')
                                            <span class="badge badge-info">Số tiền cố định</span>
                                            @break
                                        @case('tiered')
                                            <span class="badge badge-warning">Bậc thang</span>
                                            @break
                                        @default
                                            <span class="badge badge-secondary">{{ $policy->calc_type }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($policy->calc_type === 'percent')
                                        <strong>{{ $policy->percent_value }}%</strong>
                                    @elseif($policy->calc_type === 'flat')
                                        <strong>{{ number_format($policy->flat_amount) }}đ</strong>
                                    @else
                                        <span class="text-muted">Bậc thang</span>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->basis === 'cash')
                                        <span class="badge badge-success">Tiền mặt</span>
                                    @else
                                        <span class="badge badge-info">Dồn tích</span>
                                    @endif
                                </td>
                                <td>
                                    @if($policy->active)
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> Hoạt động
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> Tạm dừng
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $policy->created_at->format('d/m/Y H:i') }}
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('agent.commission-policies.show', $policy->id) }}" 
                                           class="btn btn-sm btn-primary" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Soft Delete Button -->
                                        <form action="{{ route('agent.commission-policies.destroy', $policy->id) }}" 
                                              method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-soft-delete" 
                                                    title="Xóa chính sách" data-policy-id="{{ $policy->id }}">
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
            @else
                <div class="text-center py-5">
                    <i class="fas fa-percentage fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Chưa có chính sách hoa hồng nào</h5>
                    <p class="text-muted">Hiện tại chưa có chính sách hoa hồng nào được thiết lập cho tổ chức của bạn.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Recent Commission Events -->
    @if($stats['total_events'] > 0)
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-history"></i>
                Sự kiện hoa hồng gần đây
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Ngày</th>
                            <th>Chính sách</th>
                            <th>Sự kiện</th>
                            <th>Số tiền gốc</th>
                            <th>Hoa hồng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $recentEvents = \App\Models\CommissionEvent::where('organization_id', auth()->user()->organization_id)
                                ->where('agent_id', auth()->user()->id)
                                ->with('policy')
                                ->orderBy('occurred_at', 'desc')
                                ->limit(10)
                                ->get();
                        @endphp
                        @foreach($recentEvents as $event)
                        <tr>
                            <td>
                                <small>{{ $event->occurred_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                <strong>{{ $event->policy->title ?? 'N/A' }}</strong>
                            </td>
                            <td>
                                @switch($event->trigger_event)
                                    @case('deposit_paid')
                                        <span class="badge badge-info">Đặt cọc</span>
                                        @break
                                    @case('lease_signed')
                                        <span class="badge badge-success">Ký hợp đồng</span>
                                        @break
                                    @case('invoice_paid')
                                        <span class="badge badge-primary">Thanh toán hóa đơn</span>
                                        @break
                                    @case('viewing_done')
                                        <span class="badge badge-warning">Xem phòng</span>
                                        @break
                                    @case('listing_published')
                                        <span class="badge badge-dark">Đăng tin</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $event->trigger_event }}</span>
                                @endswitch
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
                                        <span class="badge badge-warning">Chờ duyệt</span>
                                        @break
                                    @case('approved')
                                        <span class="badge badge-info">Đã duyệt</span>
                                        @break
                                    @case('paid')
                                        <span class="badge badge-success">Đã thanh toán</span>
                                        @break
                                    @case('reversed')
                                        <span class="badge badge-danger">Đã hoàn</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge badge-secondary">Đã hủy</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $event->status }}</span>
                                @endswitch
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('agent.commission-events.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list"></i> Xem tất cả sự kiện hoa hồng
                </a>
            </div>
        </div>
    </div>
    @endif
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
        const policyId = $(this).data('policy-id');
        
        Notify.confirmDelete(`chính sách hoa hồng #${policyId}`, function() {
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
    const totalPolicies = {{ $policies->count() }};
    if (totalPolicies > 0) {
        Notify.info(`Hiển thị ${totalPolicies} chính sách hoa hồng`, 'Danh sách chính sách');
    }
});

// Test database function removed - route no longer exists
</script>
@endpush
