@extends('layouts.manager_dashboard')

@section('title', 'Quản lý hóa đơn')

@section('content')
<main class="main-content">
    <div class="container-fluid">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-0">Quản lý hóa đơn</h1>
                <p class="text-muted mb-0">Danh sách tất cả hóa đơn trong hệ thống</p>
            </div>
            <a href="{{ route('manager.invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Tạo hóa đơn mới
            </a>
        </div>

        <!-- Session Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Filters -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('manager.invoices.index') }}">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Tìm kiếm</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Số hóa đơn, tên khách thuê, BĐS..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                                <option value="issued" {{ request('status') == 'issued' ? 'selected' : '' }}>Đã phát hành</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hợp đồng</label>
                            <select name="lease_id" class="form-select">
                                <option value="">Tất cả</option>
                                @foreach($leases as $lease)
                                <option value="{{ $lease->id }}" {{ request('lease_id') == $lease->id ? 'selected' : '' }}>
                                    {{ $lease->contract_no ?? 'HD#' . $lease->id }} - {{ $lease->tenant->full_name ?? 'N/A' }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Từ ngày</label>
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Đến ngày</label>
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('manager.invoices.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Số hóa đơn</th>
                                <th>Hợp đồng</th>
                                <th>Khách thuê</th>
                                <th>Bất động sản</th>
                                <th>Ngày phát hành</th>
                                <th>Hạn thanh toán</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($invoices && $invoices->count() > 0)
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td>{{ $invoice->id }}</td>
                                    <td>
                                        @if ($invoice->invoice_no)
                                        <code class="bg-light px-2 py-1 rounded">{{ $invoice->invoice_no }}</code>
                                        @else
                                        <span class="text-muted">Chưa có</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->lease)
                                        <strong>{{ $invoice->lease->contract_no ?? 'HD#' . $invoice->lease->id }}</strong>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->lease && $invoice->lease->tenant)
                                        <div class="d-flex flex-column">
                                            <strong>{{ $invoice->lease->tenant->full_name }}</strong>
                                            @if ($invoice->lease->tenant->phone)
                                            <br><small class="text-muted">{{ $invoice->lease->tenant->phone }}</small>
                                            @endif
                                        </div>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($invoice->lease && $invoice->lease->unit && $invoice->lease->unit->property)
                                        <strong>{{ $invoice->lease->unit->property->name }}</strong>
                                        @if ($invoice->lease->unit->code)
                                        <br><small class="text-muted">Phòng {{ $invoice->lease->unit->code }}</small>
                                        @endif
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                    <td>
                                        @php
                                        $statusClass = '';
                                        switch($invoice->status) {
                                        case 'draft': $statusClass = 'bg-secondary'; break;
                                        case 'issued': $statusClass = 'bg-info'; break;
                                        case 'paid': $statusClass = 'bg-success'; break;
                                        case 'overdue': $statusClass = 'bg-danger'; break;
                                        case 'cancelled': $statusClass = 'bg-warning'; break;
                                        default: $statusClass = 'bg-secondary'; break;
                                        }
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a href="{{ route('manager.invoices.show', $invoice->id) }}" class="btn btn-outline-info me-2" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('manager.invoices.edit', $invoice->id) }}" class="btn btn-outline-primary me-2" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" onclick="deleteInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no ?? 'Hóa đơn #' . $invoice->id }}')" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-file-invoice fa-3x mb-3 text-muted"></i>
                                        <br>Chưa có hóa đơn nào
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if(isset($invoices) && method_exists($invoices, 'links'))
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
function deleteInvoice(id, name) {
    // Sử dụng notification system
    Notify.confirmDelete(`hóa đơn "${name}"`, function() {
        // Hiển thị loading toast
        const loadingToast = Notify.toast({
            title: 'Đang xử lý...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0 // Không tự động đóng
        });
        
        fetch(`/manager/invoices/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
        })
        .then(response => {
            // Đóng loading toast
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                Notify.success(data.message, 'Xóa thành công!');
                
                // Reload trang sau 1.5 giây
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                // Hiển thị thông báo lỗi
                Notify.error(data.message, 'Không thể xóa hóa đơn');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Hiển thị thông báo lỗi
            Notify.error('Có lỗi xảy ra khi xóa hóa đơn. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
}
</script>
@endpush
