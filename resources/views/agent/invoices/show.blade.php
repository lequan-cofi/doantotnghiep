@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết hóa đơn')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-file-invoice text-primary"></i>
                Chi tiết hóa đơn
            </h1>
            <p class="text-muted mb-0">Hóa đơn số: <strong>{{ $invoice->invoice_no }}</strong></p>
        </div>
        <div>
            <div class="btn-group" role="group">
                @if($invoice->status == 'draft')
                    <a href="{{ route('agent.invoices.edit', $invoice->id) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <form method="POST" action="{{ route('agent.invoices.issue', $invoice->id) }}" 
                          style="display: inline;">
                        @csrf
                        <button type="button" class="btn btn-success btn-issue">
                            <i class="fas fa-paper-plane"></i> Phát hành
                        </button>
                    </form>
                @elseif($invoice->status == 'issued')
                    <form method="POST" action="{{ route('agent.invoices.cancel', $invoice->id) }}" 
                          style="display: inline;">
                        @csrf
                        <button type="button" class="btn btn-danger btn-cancel">
                            <i class="fas fa-ban"></i> Hủy hóa đơn
                        </button>
                    </form>
                @endif
                <a href="{{ route('agent.invoices.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Invoice Details -->
        <div class="col-lg-8">
            <!-- Invoice Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Thông tin hóa đơn
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Số hóa đơn:</strong></td>
                                    <td>{{ $invoice->invoice_no }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày phát hành:</strong></td>
                                    <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Hạn thanh toán:</strong></td>
                                    <td>
                                        <span class="{{ $invoice->due_date < now() && $invoice->status != 'paid' ? 'text-danger' : '' }}">
                                            {{ $invoice->due_date->format('d/m/Y') }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
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
                                        
                                        @if($invoice->is_auto_created)
                                            <br><small class="text-info">
                                                <i class="fas fa-robot"></i> 
                                                @if($invoice->booking_deposit_id)
                                                    Hóa đơn đặt cọc được tạo tự động
                                                @elseif($invoice->lease_id)
                                                    Hóa đơn hợp đồng thuê được tạo tự động
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Khách hàng:</strong></td>
                                    <td>
                                        @if($invoice->lease)
                                            {{ $invoice->lease->tenant->full_name ?? 'Chưa có khách hàng' }}
                                        @elseif($invoice->bookingDeposit)
                                            @if($invoice->bookingDeposit->tenantUser)
                                                {{ $invoice->bookingDeposit->tenantUser->full_name ?? 'Chưa có khách hàng' }}
                                            @elseif($invoice->bookingDeposit->lead)
                                                {{ $invoice->bookingDeposit->lead->full_name ?? 'Chưa có khách hàng' }}
                                            @else
                                                Chưa có khách hàng
                                            @endif
                                        @else
                                            Chưa có khách hàng
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Số điện thoại:</strong></td>
                                    <td>
                                        @if($invoice->lease)
                                            {{ $invoice->lease->tenant->phone ?? 'N/A' }}
                                        @elseif($invoice->bookingDeposit)
                                            @if($invoice->bookingDeposit->tenantUser)
                                                {{ $invoice->bookingDeposit->tenantUser->phone ?? 'N/A' }}
                                            @elseif($invoice->bookingDeposit->lead)
                                                {{ $invoice->bookingDeposit->lead->phone ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td>
                                        @if($invoice->lease)
                                            {{ $invoice->lease->tenant->email ?? 'N/A' }}
                                        @elseif($invoice->bookingDeposit)
                                            @if($invoice->bookingDeposit->tenantUser)
                                                {{ $invoice->bookingDeposit->tenantUser->email ?? 'N/A' }}
                                            @elseif($invoice->bookingDeposit->lead)
                                                {{ $invoice->bookingDeposit->lead->email ?? 'N/A' }}
                                            @else
                                                N/A
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tài sản:</strong></td>
                                    <td>
                                        @if($invoice->lease)
                                            {{ $invoice->lease->unit->property->name ?? 'N/A' }} - {{ $invoice->lease->unit->code ?? 'N/A' }}
                                        @elseif($invoice->bookingDeposit)
                                            {{ $invoice->bookingDeposit->unit->property->name ?? 'N/A' }} - {{ $invoice->bookingDeposit->unit->code ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($invoice->note)
                        <div class="mt-3">
                            <strong>Ghi chú:</strong>
                            <p class="text-muted">{{ $invoice->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Invoice Items -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-list"></i> Chi tiết hóa đơn
                    </h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Mô tả</th>
                                    <th class="text-center">Số lượng</th>
                                    <th class="text-right">Đơn giá</th>
                                    <th class="text-right">Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 0, ',', '.') }} VND</td>
                                        <td class="text-right">{{ number_format($item->amount, 0, ',', '.') }} VND</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" class="text-right"><strong>Tạm tính:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($invoice->subtotal, 0, ',', '.') }} VND</strong></td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-right">Thuế:</td>
                                        <td class="text-right">{{ number_format($invoice->tax_amount, 0, ',', '.') }} VND</td>
                                    </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                    <tr>
                                        <td colspan="3" class="text-right">Giảm giá:</td>
                                        <td class="text-right">-{{ number_format($invoice->discount_amount, 0, ',', '.') }} VND</td>
                                    </tr>
                                @endif
                                <tr class="table-primary">
                                    <td colspan="3" class="text-right"><strong>Tổng cộng:</strong></td>
                                    <td class="text-right"><strong>{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payments -->
            @if($invoice->payments->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-credit-card"></i> Lịch sử thanh toán
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Ngày thanh toán</th>
                                        <th>Số tiền</th>
                                        <th>Phương thức</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->payment_date->format('d/m/Y H:i') }}</td>
                                            <td class="text-right">{{ number_format($payment->amount, 0, ',', '.') }} VND</td>
                                            <td>{{ $payment->method->name ?? 'N/A' }}</td>
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
        <div class="col-lg-4">
            <!-- Invoice Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calculator"></i> Tổng kết
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">
                            <strong>Tạm tính:</strong>
                        </div>
                        <div class="col-6 text-right">
                            {{ number_format($invoice->subtotal, 0, ',', '.') }} VND
                        </div>
                    </div>
                    
                    @if($invoice->tax_amount > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Thuế:</strong>
                            </div>
                            <div class="col-6 text-right">
                                {{ number_format($invoice->tax_amount, 0, ',', '.') }} VND
                            </div>
                        </div>
                    @endif
                    
                    @if($invoice->discount_amount > 0)
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Giảm giá:</strong>
                            </div>
                            <div class="col-6 text-right">
                                -{{ number_format($invoice->discount_amount, 0, ',', '.') }} VND
                            </div>
                        </div>
                    @endif
                    
                    <hr>
                    
                    <div class="row">
                        <div class="col-6">
                            <strong>Tổng cộng:</strong>
                        </div>
                        <div class="col-6 text-right">
                            <strong class="text-primary">{{ number_format($invoice->total_amount, 0, ',', '.') }} VND</strong>
                        </div>
                    </div>

                    @php
                        $paidAmount = $invoice->payments->sum('amount');
                        $remainingAmount = $invoice->total_amount - $paidAmount;
                    @endphp

                    @if($paidAmount > 0)
                        <hr>
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Đã thanh toán:</strong>
                            </div>
                            <div class="col-6 text-right text-success">
                                {{ number_format($paidAmount, 0, ',', '.') }} VND
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <strong>Còn lại:</strong>
                            </div>
                            <div class="col-6 text-right {{ $remainingAmount > 0 ? 'text-warning' : 'text-success' }}">
                                {{ number_format($remainingAmount, 0, ',', '.') }} VND
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contract/Booking Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-file-contract"></i> 
                        @if($invoice->lease)
                            Thông tin hợp đồng
                        @elseif($invoice->bookingDeposit)
                            Thông tin đặt cọc
                        @else
                            Thông tin liên quan
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if($invoice->lease)
                        <div class="mb-3">
                            <strong>Số hợp đồng:</strong><br>
                            <span class="text-primary">{{ $invoice->lease->contract_no ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Ngày bắt đầu:</strong><br>
                            {{ $invoice->lease->start_date->format('d/m/Y') }}
                        </div>
                        <div class="mb-3">
                            <strong>Ngày kết thúc:</strong><br>
                            {{ $invoice->lease->end_date->format('d/m/Y') }}
                        </div>
                        <div class="mb-3">
                            <strong>Tiền thuê:</strong><br>
                            <span class="text-primary font-weight-bold">{{ number_format($invoice->lease->rent_amount, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="mb-3">
                            <strong>Ngày thanh toán:</strong><br>
                            Ngày {{ $invoice->lease->billing_day }} hàng tháng
                        </div>
                    @elseif($invoice->bookingDeposit)
                        <div class="mb-3">
                            <strong>Số tham chiếu:</strong><br>
                            <span class="text-primary">{{ $invoice->bookingDeposit->reference_number ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-3">
                            <strong>Số tiền đặt cọc:</strong><br>
                            <span class="text-primary font-weight-bold">{{ number_format($invoice->bookingDeposit->amount, 0, ',', '.') }} VND</span>
                        </div>
                        <div class="mb-3">
                            <strong>Loại đặt cọc:</strong><br>
                            @switch($invoice->bookingDeposit->deposit_type)
                                @case('booking')
                                    <span class="badge badge-info">Đặt cọc</span>
                                    @break
                                @case('security')
                                    <span class="badge badge-warning">Cọc an ninh</span>
                                    @break
                                @case('advance')
                                    <span class="badge badge-success">Trả trước</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $invoice->bookingDeposit->deposit_type }}</span>
                            @endswitch
                        </div>
                        <div class="mb-3">
                            <strong>Trạng thái thanh toán:</strong><br>
                            @switch($invoice->bookingDeposit->payment_status)
                                @case('pending')
                                    <span class="badge badge-warning">Chờ thanh toán</span>
                                    @break
                                @case('paid')
                                    <span class="badge badge-success">Đã thanh toán</span>
                                    @break
                                @case('refunded')
                                    <span class="badge badge-info">Đã hoàn tiền</span>
                                    @break
                                @case('expired')
                                    <span class="badge badge-danger">Hết hạn</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge badge-dark">Đã hủy</span>
                                    @break
                                @default
                                    <span class="badge badge-secondary">{{ $invoice->bookingDeposit->payment_status }}</span>
                            @endswitch
                        </div>
                        <div class="mb-3">
                            <strong>Giữ chỗ đến:</strong><br>
                            {{ $invoice->bookingDeposit->hold_until ? $invoice->bookingDeposit->hold_until->format('d/m/Y H:i') : 'N/A' }}
                        </div>
                        @if($invoice->bookingDeposit->paid_at)
                            <div class="mb-3">
                                <strong>Ngày thanh toán:</strong><br>
                                {{ $invoice->bookingDeposit->paid_at->format('d/m/Y H:i') }}
                            </div>
                        @endif
                    @else
                        <div class="mb-3">
                            <p class="text-muted">Không có thông tin hợp đồng hoặc đặt cọc liên quan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i> Thao tác
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($invoice->status == 'draft')
                            <a href="{{ route('agent.invoices.edit', $invoice->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <form method="POST" action="{{ route('agent.invoices.issue', $invoice->id) }}" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn phát hành hóa đơn này?')">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-paper-plane"></i> Phát hành
                                </button>
                            </form>
                        @elseif($invoice->status == 'issued')
                            <form method="POST" action="{{ route('agent.invoices.cancel', $invoice->id) }}" 
                                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy hóa đơn này?')">
                                @csrf
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-ban"></i> Hủy hóa đơn
                                </button>
                            </form>
                        @endif
                        
                        <a href="{{ route('agent.invoices.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại danh sách
                        </a>
                    </div>
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

    // Enhanced action confirmations
    $(document).on('click', '.btn-issue', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const invoiceNo = '{{ $invoice->invoice_no }}';
        
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

    $(document).on('click', '.btn-cancel', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const invoiceNo = '{{ $invoice->invoice_no }}';
        
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

    // Show enhanced invoice type and creation info
    @if($invoice->is_auto_created)
        @if($invoice->booking_deposit_id)
            Notify.info('Hóa đơn đặt cọc được tạo tự động. Hóa đơn này được tạo tự động khi tạo đặt cọc. Bạn có thể chỉnh sửa một số thông tin cơ bản.', 'Hóa đơn đặt cọc tự động');
        @elseif($invoice->lease_id)
            Notify.info('Hóa đơn hợp đồng thuê được tạo tự động. Hóa đơn này được tạo tự động khi tạo hợp đồng thuê. Bao gồm tiền thuê chu kỳ đầu và tiền cọc.', 'Hóa đơn hợp đồng thuê tự động');
        @endif
    @else
        Notify.info('Hóa đơn này được tạo trực tiếp bởi người dùng.', 'Hóa đơn thủ công');
    @endif

    // Status change notifications
    const status = '{{ $invoice->status }}';
    const statusMessages = {
        'draft': 'Hóa đơn đang ở trạng thái nháp',
        'issued': 'Hóa đơn đã được phát hành',
        'paid': 'Hóa đơn đã được thanh toán',
        'overdue': 'Hóa đơn đã quá hạn thanh toán',
        'cancelled': 'Hóa đơn đã bị hủy'
    };

    if (statusMessages[status]) {
        setTimeout(() => {
            Notify.info(statusMessages[status], 'Trạng thái hóa đơn');
        }, 1000);
    }

    // Show editing restrictions for auto-created invoices
    @if($invoice->is_auto_created && $invoice->status === 'draft')
        @if($invoice->booking_deposit_id)
            setTimeout(() => {
                Notify.warning('Một số thông tin có thể bị hạn chế chỉnh sửa vì được liên kết với đặt cọc. Thay đổi đặt cọc sẽ tự động cập nhật hóa đơn.', 'Lưu ý chỉnh sửa hóa đơn đặt cọc tự động');
            }, 2000);
        @elseif($invoice->lease_id)
            setTimeout(() => {
                Notify.warning('Một số thông tin có thể bị hạn chế chỉnh sửa vì được liên kết với hợp đồng. Thay đổi hợp đồng sẽ tự động cập nhật hóa đơn.', 'Lưu ý chỉnh sửa hóa đơn hợp đồng thuê tự động');
            }, 2000);
        @endif
    @endif
});
</script>
@endpush
