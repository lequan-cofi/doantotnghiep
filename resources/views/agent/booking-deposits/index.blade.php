@extends('layouts.agent_dashboard')

@section('title', 'Danh sách đặt cọc')

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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-hand-holding-usd me-2"></i>Danh sách đặt cọc
                    </h1>
                    <p class="text-muted mb-0">Quản lý đặt cọc phòng/căn hộ</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.booking-deposits.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo đặt cọc
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('agent.booking-deposits.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Tìm kiếm</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   value="{{ $search }}" placeholder="Số tham chiếu, tên khách hàng, ghi chú...">
                        </div>
                        <div class="col-md-2">
                            <label for="payment_status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="payment_status" name="payment_status">
                                <option value="">Tất cả</option>
                                <option value="pending" {{ $selectedPaymentStatus === 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                                <option value="paid" {{ $selectedPaymentStatus === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="refunded" {{ $selectedPaymentStatus === 'refunded' ? 'selected' : '' }}>Đã hoàn tiền</option>
                                <option value="expired" {{ $selectedPaymentStatus === 'expired' ? 'selected' : '' }}>Hết hạn</option>
                                <option value="cancelled" {{ $selectedPaymentStatus === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="deposit_type" class="form-label">Loại đặt cọc</label>
                            <select class="form-select" id="deposit_type" name="deposit_type">
                                <option value="">Tất cả</option>
                                <option value="booking" {{ $selectedDepositType === 'booking' ? 'selected' : '' }}>Đặt cọc</option>
                                <option value="security" {{ $selectedDepositType === 'security' ? 'selected' : '' }}>Cọc an toàn</option>
                                <option value="advance" {{ $selectedDepositType === 'advance' ? 'selected' : '' }}>Trả trước</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="property_id" class="form-label">Bất động sản</label>
                            <select class="form-select" id="property_id" name="property_id">
                                <option value="">Tất cả</option>
                                @foreach($properties as $property)
                                    <option value="{{ $property->id }}" {{ $selectedProperty == $property->id ? 'selected' : '' }}>
                                        {{ $property->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search me-1"></i>Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>Kết quả tìm kiếm
                        <span class="badge badge-primary ms-2">{{ $deposits->count() }} đặt cọc</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($deposits->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Số tham chiếu</th>
                                        <th>Khách hàng</th>
                                        <th>Phòng/Căn hộ</th>
                                        <th>Số tiền</th>
                                        <th>Loại</th>
                                        <th>Trạng thái</th>
                                        <th>Giữ chỗ đến</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($deposits as $deposit)
                                        @php
                                            $customerInfo = $deposit->getTenantInfo();
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ route('agent.booking-deposits.show', $deposit->id) }}" 
                                                   class="text-decoration-none fw-bold">
                                                    {{ $deposit->reference_number }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($customerInfo)
                                                    <div>
                                                        <div class="fw-bold">{{ $customerInfo['name'] }}</div>
                                                        <small class="text-muted">{{ $customerInfo['phone'] }}</small>
                                                    </div>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="fw-bold">{{ $deposit->unit->property->name }}</div>
                                                    <small class="text-muted">{{ $deposit->unit->code }} - {{ $deposit->unit->unit_type }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-primary">
                                                    {{ number_format($deposit->amount, 0, ',', '.') }} VNĐ
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ $deposit->getTypeText() }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $deposit->getStatusBadgeClass() }}">
                                                    {{ $deposit->getStatusText() }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $deposit->hold_until->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $deposit->hold_until->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <div>{{ $deposit->created_at->format('d/m/Y') }}</div>
                                                    <small class="text-muted">{{ $deposit->created_at->format('H:i') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('agent.booking-deposits.show', $deposit->id) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($deposit->payment_status === 'pending')
                                                        <a href="{{ route('agent.booking-deposits.edit', $deposit->id) }}" 
                                                           class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @endif
                                                    
                                                    <!-- Soft Delete Button -->
                                                    <form action="{{ route('agent.booking-deposits.destroy', $deposit->id) }}" 
                                                          method="POST" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm btn-outline-danger btn-soft-delete" 
                                                                title="Xóa đặt cọc" data-reference="{{ $deposit->reference_number }}">
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
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có đặt cọc nào</h5>
                            <p class="text-muted">Chưa có đặt cọc nào được tạo hoặc không tìm thấy kết quả phù hợp.</p>
                            <a href="{{ route('agent.booking-deposits.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>Tạo đặt cọc đầu tiên
                            </a>
                        </div>
                    @endif
                </div>
            </div>
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
        const referenceNumber = $(this).data('reference');
        
        Notify.confirmDelete(`đặt cọc ${referenceNumber}`, function() {
            form.submit();
        });
    });

    // Filter change notifications
    $('#payment_status, #deposit_type, #property_id').on('change', function() {
        const filterName = $(this).find('option:selected').text();
        if (filterName && filterName !== 'Tất cả') {
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

    // Auto-refresh page every 60 seconds to check for status changes
    setInterval(function() {
        // Only refresh if there are pending deposits
        const pendingBadges = document.querySelectorAll('.badge-warning');
        if (pendingBadges.length > 0) {
            Notify.info('Đang kiểm tra cập nhật trạng thái...', 'Tự động cập nhật');
            setTimeout(() => {
                location.reload();
            }, 2000);
        }
    }, 60000);

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

    // Format currency inputs
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
        }
        input.value = value;
    }

    // Add currency formatting to any amount inputs
    const amountInputs = document.querySelectorAll('input[name="amount"]');
    amountInputs.forEach(input => {
        input.addEventListener('input', function() {
            formatCurrency(this);
        });
    });

    // Show page info
    const totalDeposits = {{ $deposits->count() }};
    if (totalDeposits > 0) {
        Notify.info(`Hiển thị ${totalDeposits} đặt cọc`, 'Danh sách đặt cọc');
    }
});
</script>
@endpush