@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Hóa đơn')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chi tiết Hóa đơn</h1>
                <p>Thông tin chi tiết hóa đơn #{{ $invoice->invoice_no ?? $invoice->id }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="{{ route('manager.invoices.edit', $invoice->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-edit"></i>
                    Chỉnh sửa
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row">
            <!-- Invoice Details -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice me-2"></i>
                            Thông tin hóa đơn
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Số hóa đơn:</strong></td>
                                        <td>
                                            @if ($invoice->invoice_no)
                                                <code class="bg-light px-2 py-1 rounded">{{ $invoice->invoice_no }}</code>
                                            @else
                                                <span class="text-muted">Chưa có</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Ngày phát hành:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Hạn thanh toán:</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Trạng thái:</strong></td>
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
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td><strong>Tiền tệ:</strong></td>
                                        <td>{{ $invoice->currency ?? 'VND' }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tổng tiền trước thuế:</strong></td>
                                        <td>{{ number_format($invoice->subtotal, 0, ',', '.') }} VND</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Thuế:</strong></td>
                                        <td>{{ number_format($invoice->tax_amount, 0, ',', '.') }} VND</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Giảm giá:</strong></td>
                                        <td>{{ number_format($invoice->discount_amount, 0, ',', '.') }} VND</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tổng tiền:</strong></td>
                                        <td><strong class="text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        @if ($invoice->note)
                        <div class="mt-3">
                            <strong>Ghi chú:</strong>
                            <p class="text-muted">{{ $invoice->note }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-list me-2"></i>
                            Chi tiết các khoản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Loại</th>
                                        <th>Mô tả</th>
                                        <th>Số lượng</th>
                                        <th>Đơn giá</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($invoice->items as $item)
                                    <tr>
                                        <td>
                                            @php
                                            $typeLabels = [
                                                'rent' => 'Tiền thuê',
                                                'service' => 'Dịch vụ',
                                                'meter' => 'Đồng hồ',
                                                'deposit' => 'Cọc',
                                                'other' => 'Khác'
                                            ];
                                            @endphp
                                            <span class="badge bg-info">{{ $typeLabels[$item->item_type] ?? $item->item_type }}</span>
                                        </td>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ number_format($item->quantity, 3) }}</td>
                                        <td>{{ number_format($item->unit_price, 0, ',', '.') }} VND</td>
                                        <td><strong>{{ number_format($item->amount, 0, ',', '.') }} VND</strong></td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Không có khoản nào</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Payments -->
                @if($invoice->payments->count() > 0)
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-credit-card me-2"></i>
                            Lịch sử thanh toán
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Ngày thanh toán</th>
                                        <th>Số tiền</th>
                                        <th>Phương thức</th>
                                        <th>Trạng thái</th>
                                        <th>Người thanh toán</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y H:i') }}</td>
                                        <td><strong>{{ number_format($payment->amount, 0, ',', '.') }} VND</strong></td>
                                        <td>{{ $payment->method->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                            $statusClass = '';
                                            switch($payment->status) {
                                            case 'pending': $statusClass = 'bg-warning'; break;
                                            case 'success': $statusClass = 'bg-success'; break;
                                            case 'failed': $statusClass = 'bg-danger'; break;
                                            case 'refunded': $statusClass = 'bg-info'; break;
                                            default: $statusClass = 'bg-secondary'; break;
                                            }
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ ucfirst($payment->status) }}</span>
                                        </td>
                                        <td>{{ $payment->payerUser->full_name ?? 'N/A' }}</td>
                                        <td>{{ $payment->note ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Lease Information -->
                @if($invoice->lease)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-contract me-2"></i>
                            Thông tin hợp đồng
                        </h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Số hợp đồng:</strong></td>
                                <td>{{ $invoice->lease->contract_no ?? 'HD#' . $invoice->lease->id }}</td>
                            </tr>
                            <tr>
                                <td><strong>Khách thuê:</strong></td>
                                <td>{{ $invoice->lease->tenant->full_name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Bất động sản:</strong></td>
                                <td>{{ $invoice->lease->unit->property->name ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Phòng:</strong></td>
                                <td>{{ $invoice->lease->unit->code ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <td><strong>Tiền thuê:</strong></td>
                                <td>{{ number_format($invoice->lease->rent_amount, 0, ',', '.') }} VND</td>
                            </tr>
                            <tr>
                                <td><strong>Thời hạn:</strong></td>
                                <td>
                                    {{ \Carbon\Carbon::parse($invoice->lease->start_date)->format('d/m/Y') }} - 
                                    {{ \Carbon\Carbon::parse($invoice->lease->end_date)->format('d/m/Y') }}
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                @endif

                <!-- Actions -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-cogs me-2"></i>
                            Thao tác
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('manager.invoices.edit', $invoice->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit"></i> Chỉnh sửa hóa đơn
                            </a>
                            
                            @if($invoice->status !== 'paid')
                            <button class="btn btn-success" onclick="markAsPaid({{ $invoice->id }})">
                                <i class="fas fa-check"></i> Đánh dấu đã thanh toán
                            </button>
                            @endif
                            
                            @if($invoice->status === 'draft')
                            <button class="btn btn-info" onclick="issueInvoice({{ $invoice->id }})">
                                <i class="fas fa-paper-plane"></i> Phát hành hóa đơn
                            </button>
                            @endif
                            
                            <button class="btn btn-outline-danger" onclick="deleteInvoice({{ $invoice->id }}, '{{ $invoice->invoice_no ?? 'Hóa đơn #' . $invoice->id }}')">
                                <i class="fas fa-trash"></i> Xóa hóa đơn
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
function deleteInvoice(id, name) {
    Notify.confirmDelete(`hóa đơn "${name}"`, function() {
        const loadingToast = Notify.toast({
            title: 'Đang xử lý...',
            message: 'Vui lòng chờ trong giây lát',
            type: 'info',
            duration: 0
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
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Xóa thành công!');
                setTimeout(() => {
                    window.location.href = '{{ route("manager.invoices.index") }}';
                }, 1500);
            } else {
                Notify.error(data.message, 'Không thể xóa hóa đơn');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra khi xóa hóa đơn. Vui lòng thử lại.', 'Lỗi hệ thống');
        });
    });
}

function markAsPaid(id) {
    Notify.confirm('Đánh dấu đã thanh toán', 'Bạn có chắc chắn muốn đánh dấu hóa đơn này là đã thanh toán?', function() {
        // TODO: Implement mark as paid functionality
        Notify.info('Chức năng đang được phát triển', 'Thông báo');
    });
}

function issueInvoice(id) {
    Notify.confirm('Phát hành hóa đơn', 'Bạn có chắc chắn muốn phát hành hóa đơn này?', function() {
        // TODO: Implement issue invoice functionality
        Notify.info('Chức năng đang được phát triển', 'Thông báo');
    });
}
</script>
@endpush
