@extends('layouts.agent_dashboard')

@section('title', 'Quản lý hóa đơn')

@push('styles')
<style>
    #invoicesTable {
        width: 100%;
        border-collapse: collapse;
    }
    
    #invoicesTable th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        padding: 12px 8px;
        text-align: left;
        font-weight: 600;
        color: #495057;
    }
    
    #invoicesTable td {
        padding: 12px 8px;
        border-bottom: 1px solid #dee2e6;
        vertical-align: middle;
    }
    
    #invoicesTable tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .table-responsive {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice text-primary"></i>
                Quản lý hóa đơn
            </h1>
            <p class="text-muted mb-0">Quản lý các hóa đơn thuê phòng/căn hộ</p>
        </div>
        <div>
            <a href="{{ route('agent.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo hóa đơn mới
            </a>
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
                                Tổng hóa đơn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-300"></i>
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
                                Đã phát hành
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['issued']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-paper-plane fa-2x text-gray-300"></i>
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
                                Đã thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['paid']) }}
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
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Quá hạn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['overdue']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Summary -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Tổng giá trị hóa đơn
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_amount'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Đã thu
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['paid_amount'], 0, ',', '.') }} VND
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter"></i> Bộ lọc
            </h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.invoices.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Tìm kiếm</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Số hóa đơn, tên khách hàng, tài sản...">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Tất cả</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                        <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Đã phát hành</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="invoice_type" class="form-label">Loại hóa đơn</label>
                    <select class="form-control" id="invoice_type" name="invoice_type">
                        <option value="">Tất cả</option>
                        <option value="lease" {{ request('invoice_type') == 'lease' ? 'selected' : '' }}>Hợp đồng thuê</option>
                        <option value="booking" {{ request('invoice_type') == 'booking' ? 'selected' : '' }}>Đặt cọc</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="lease_id" class="form-label">Hợp đồng</label>
                    <select class="form-control" id="lease_id" name="lease_id">
                        <option value="">Tất cả hợp đồng</option>
                        @foreach($managedLeases as $lease)
                            <option value="{{ $lease->id }}" {{ request('lease_id') == $lease->id ? 'selected' : '' }}>
                                {{ $lease->unit->property->name ?? 'N/A' }} - {{ $lease->unit->code ?? 'N/A' }} ({{ $lease->tenant->full_name ?? 'Chưa có khách hàng' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Từ ngày</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Đến ngày</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Lọc
                    </button>
                    <a href="{{ route('agent.invoices.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Xóa bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Danh sách hóa đơn
            </h6>
        </div>
        <div class="card-body">
            @if($invoices->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="invoicesTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Số hóa đơn</th>
                                <th>Loại</th>
                                <th>Khách hàng</th>
                                <th>Tài sản</th>
                                <th>Ngày phát hành</th>
                                <th>Hạn thanh toán</th>
                                <th>Số tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                                <tr>
                                    <td>
                                        <strong>{{ $invoice->invoice_no }}</strong>
                                    </td>
                                    <td>
                                        @if($invoice->lease)
                                            <span class="badge badge-primary">Hợp đồng thuê</span>
                                            @if($invoice->is_auto_created)
                                                <br><small class="text-info"><i class="fas fa-robot"></i> Tự động</small>
                                            @endif
                                        @elseif($invoice->bookingDeposit)
                                            <span class="badge badge-info">Đặt cọc</span>
                                            @if($invoice->is_auto_created)
                                                <br><small class="text-info"><i class="fas fa-robot"></i> Tự động</small>
                                            @endif
                                        @else
                                            <span class="badge badge-secondary">Khác</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            @if($invoice->lease)
                                                <strong>{{ $invoice->lease->tenant->full_name ?? 'Chưa có khách hàng' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->lease->tenant->phone ?? 'N/A' }}</small>
                                            @elseif($invoice->bookingDeposit)
                                                @if($invoice->bookingDeposit->tenantUser)
                                                    <strong>{{ $invoice->bookingDeposit->tenantUser->full_name ?? 'Chưa có khách hàng' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $invoice->bookingDeposit->tenantUser->phone ?? 'N/A' }}</small>
                                                @elseif($invoice->bookingDeposit->lead)
                                                    <strong>{{ $invoice->bookingDeposit->lead->full_name ?? 'Chưa có khách hàng' }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $invoice->bookingDeposit->lead->phone ?? 'N/A' }}</small>
                                                @else
                                                    <strong>Chưa có khách hàng</strong>
                                                @endif
                                            @else
                                                <strong>Chưa có khách hàng</strong>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            @if($invoice->lease)
                                                <strong>{{ $invoice->lease->unit->property->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->lease->unit->code ?? 'N/A' }}</small>
                                            @elseif($invoice->bookingDeposit)
                                                <strong>{{ $invoice->bookingDeposit->unit->property->name ?? 'N/A' }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $invoice->bookingDeposit->unit->code ?? 'N/A' }}</small>
                                            @else
                                                <strong>N/A</strong>
                                            @endif
                                        </div>
                                    </td>
                                    <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="{{ $invoice->due_date < now() && $invoice->status != 'paid' ? 'text-danger' : '' }}">
                                            {{ $invoice->due_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                    <td>
                                        @switch($invoice->status)
                                            @case('draft')
                                                <span class="badge badge-secondary">Nháp</span>
                                                @break
                                            @case('issued')
                                                <span class="badge badge-warning">Đã phát hành</span>
                                                @break
                                            @case('paid')
                                                <span class="badge badge-success">Đã thanh toán</span>
                                                @break
                                            @case('overdue')
                                                <span class="badge badge-danger">Quá hạn</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-dark">Đã hủy</span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.invoices.show', $invoice->id) }}" 
                                               class="btn btn-sm btn-info" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($invoice->status == 'draft')
                                                <a href="{{ route('agent.invoices.edit', $invoice->id) }}" 
                                                   class="btn btn-sm btn-warning btn-edit-invoice" 
                                                   title="Chỉnh sửa"
                                                   data-invoice-id="{{ $invoice->id }}"
                                                   data-is-auto-created="{{ $invoice->is_auto_created ? 'true' : 'false' }}"
                                                   data-auto-source="{{ $invoice->is_auto_created ? ($invoice->booking_deposit_id ? 'booking_deposit' : 'lease') : 'manual' }}"
                                                   data-invoice-no="{{ $invoice->invoice_no }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="{{ route('agent.invoices.issue', $invoice->id) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-success btn-issue" title="Phát hành">
                                                        <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('agent.invoices.destroy', $invoice->id) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @elseif($invoice->status == 'issued')
                                                <form method="POST" action="{{ route('agent.invoices.cancel', $invoice->id) }}" 
                                                      style="display: inline;">
                                                    @csrf
                                                    <button type="button" class="btn btn-sm btn-danger btn-cancel" title="Hủy">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $invoices->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">Chưa có hóa đơn nào</h5>
                    <p class="text-muted">Bắt đầu tạo hóa đơn đầu tiên của bạn.</p>
                    <a href="{{ route('agent.invoices.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo hóa đơn mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize table sorting (simple implementation)
    // Note: DataTables removed to avoid dependency issues
    initializeTableSorting();

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

    // Show info about auto-created invoices in the list
    @php
        $autoCreatedCount = $invoices->where('is_auto_created', true)->count();
    @endphp
    
    @if($autoCreatedCount > 0)
        setTimeout(() => {
            Notify.info(`Có ${autoCreatedCount} hóa đơn được tạo tự động trong danh sách. Những hóa đơn này có thể có hạn chế chỉnh sửa.`, 'Thông tin hóa đơn tự động');
        }, 1500);
    @endif

    // Enhanced delete confirmation
    $(document).on('click', '.btn-delete', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const invoiceNo = $(this).closest('tr').find('td:first strong').text();
        
        console.log('Delete button clicked for invoice:', invoiceNo);
        Notify.confirmDelete(`hóa đơn ${invoiceNo}`, function() {
            form.submit();
        });
    });

    // Enhanced issue confirmation
    $(document).on('click', '.btn-issue', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const invoiceNo = $(this).closest('tr').find('td:first strong').text();
        
        console.log('Issue button clicked for invoice:', invoiceNo);
        Notify.confirm({
            title: 'Phát hành hóa đơn',
            message: `Bạn có chắc chắn muốn phát hành hóa đơn ${invoiceNo}?`,
            details: 'Hóa đơn sẽ chuyển sang trạng thái "Đã phát hành" và không thể chỉnh sửa.',
            type: 'info',
            confirmText: 'Phát hành',
            onConfirm: function() {
                form.submit();
            }
        });
    });

    // Enhanced cancel confirmation
    $(document).on('click', '.btn-cancel', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const invoiceNo = $(this).closest('tr').find('td:first strong').text();
        
        console.log('Cancel button clicked for invoice:', invoiceNo);
        Notify.confirm({
            title: 'Hủy hóa đơn',
            message: `Bạn có chắc chắn muốn hủy hóa đơn ${invoiceNo}?`,
            details: 'Hóa đơn sẽ chuyển sang trạng thái "Đã hủy".',
            type: 'warning',
            confirmText: 'Hủy hóa đơn',
            onConfirm: function() {
                form.submit();
            }
        });
    });

    // Enhanced edit button with auto-created invoice notifications
    $(document).on('click', '.btn-edit-invoice', function(e) {
        const isAutoCreated = $(this).data('is-auto-created') === 'true';
        const autoSource = $(this).data('auto-source');
        const invoiceNo = $(this).data('invoice-no');
        
        if (isAutoCreated) {
            e.preventDefault(); // Prevent immediate navigation
            
            let message = '';
            let title = '';
            
            if (autoSource === 'booking_deposit') {
                title = 'Chỉnh sửa hóa đơn đặt cọc tự động';
                message = `Hóa đơn ${invoiceNo} được tạo tự động từ đặt cọc. Một số thông tin có thể bị hạn chế chỉnh sửa vì được liên kết với đặt cọc. Thay đổi đặt cọc sẽ tự động cập nhật hóa đơn.`;
            } else if (autoSource === 'lease') {
                title = 'Chỉnh sửa hóa đơn hợp đồng thuê tự động';
                message = `Hóa đơn ${invoiceNo} được tạo tự động từ hợp đồng thuê. Một số thông tin có thể bị hạn chế chỉnh sửa vì được liên kết với hợp đồng. Thay đổi hợp đồng sẽ tự động cập nhật hóa đơn.`;
            }
            
            Notify.warning(message, title);
            
            // Navigate after a short delay to allow user to read the notification
            setTimeout(() => {
                window.location.href = $(this).attr('href');
            }, 2000);
        }
        // If not auto-created, allow normal navigation (no preventDefault)
    });

    // Filter change notifications
    $('#status, #invoice_type, #lease_id').on('change', function() {
        const filterName = $(this).find('option:selected').text();
        if (filterName && filterName !== 'Tất cả' && filterName !== 'Tất cả hợp đồng') {
            Notify.info(`Đã lọc theo: ${filterName}`, 'Bộ lọc');
        }
    });

    // Search notifications
    $('#search').on('keyup', function() {
        const searchTerm = $(this).val();
        if (searchTerm.length > 2) {
            // Debounce search
            clearTimeout(window.searchTimeout);
            window.searchTimeout = setTimeout(() => {
                Notify.info(`Đang tìm kiếm: "${searchTerm}"`, 'Tìm kiếm');
            }, 500);
        }
    });

    // Simple table sorting function
    function initializeTableSorting() {
        const table = document.getElementById('invoicesTable');
        if (!table) return;

        const headers = table.querySelectorAll('th');
        headers.forEach((header, index) => {
            // Skip the last column (actions)
            if (index === headers.length - 1) return;
            
            header.style.cursor = 'pointer';
            header.style.userSelect = 'none';
            header.innerHTML += ' <i class="fas fa-sort text-muted"></i>';
            
            header.addEventListener('click', () => {
                sortTable(table, index);
            });
        });

        // Mặc định sắp xếp theo ID giảm dần (dữ liệu đã được sắp xếp từ server)
        // Chỉ cần cập nhật icon để hiển thị trạng thái sắp xếp hiện tại
        setTimeout(() => {
            // Cập nhật icon cho cột đầu tiên để hiển thị đang sắp xếp giảm dần
            const firstHeader = headers[0];
            const icon = firstHeader.querySelector('i');
            if (icon) {
                icon.className = 'fas fa-sort-down text-primary';
            }
        }, 100);
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
});
</script>
@endpush
