@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa hóa đơn')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit text-primary"></i>
                Chỉnh sửa hóa đơn
            </h1>
            <p class="text-muted mb-0">Hóa đơn số: <strong>{{ $invoice->invoice_no }}</strong></p>
        </div>
        <div>
            <a href="{{ route('agent.invoices.show', $invoice->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    @if($invoice->bookingDeposit)
        <!-- Booking Deposit Invoice - Cannot Edit -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center">
                        <i class="fas fa-info-circle fa-3x text-info mb-3"></i>
                        <h4 class="text-info">Không thể chỉnh sửa hóa đơn đặt cọc</h4>
                        <p class="text-muted">
                            Hóa đơn này được tạo tự động từ đặt cọc và không thể chỉnh sửa thủ công.
                            <br>
                            Để thay đổi thông tin, vui lòng chỉnh sửa thông tin đặt cọc tương ứng.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('agent.booking-deposits.show', $invoice->bookingDeposit->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> Xem thông tin đặt cọc
                            </a>
                            <a href="{{ route('agent.invoices.show', $invoice->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else

    <form method="POST" action="{{ route('agent.invoices.update', $invoice->id) }}" id="invoiceForm">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle"></i> Thông tin cơ bản
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lease_id" class="form-label">Hợp đồng <span class="text-danger">*</span></label>
                                    <select class="form-control @error('lease_id') is-invalid @enderror" 
                                            id="lease_id" name="lease_id" required>
                                        <option value="">Chọn hợp đồng</option>
                                        @foreach($managedLeases as $lease)
                                            <option value="{{ $lease->id }}" 
                                                    {{ (old('lease_id', $invoice->lease_id) == $lease->id) ? 'selected' : '' }}
                                                    data-rent="{{ $lease->rent_amount }}"
                                                    data-tenant="{{ $lease->tenant->full_name ?? 'Chưa có khách hàng' }}"
                                                    data-property="{{ $lease->unit->property->name ?? 'N/A' }}"
                                                    data-unit="{{ $lease->unit->code ?? 'N/A' }}">
                                                {{ $lease->unit->property->name ?? 'N/A' }} - {{ $lease->unit->code ?? 'N/A' }} ({{ $lease->tenant->full_name ?? 'Chưa có khách hàng' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('lease_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="invoice_no" class="form-label">Số hóa đơn</label>
                                    <input type="text" class="form-control" id="invoice_no" 
                                           value="{{ $invoice->invoice_no }}" readonly>
                                    <small class="form-text text-muted">Số hóa đơn không thể thay đổi</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="issue_date" class="form-label">Ngày phát hành <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('issue_date') is-invalid @enderror" 
                                           id="issue_date" name="issue_date" 
                                           value="{{ old('issue_date', $invoice->issue_date->format('Y-m-d')) }}" required>
                                    @error('issue_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="due_date" class="form-label">Hạn thanh toán <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('due_date') is-invalid @enderror" 
                                           id="due_date" name="due_date" 
                                           value="{{ old('due_date', $invoice->due_date->format('Y-m-d')) }}" required>
                                    @error('due_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="3" 
                                      placeholder="Ghi chú cho hóa đơn...">{{ old('note', $invoice->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Invoice Items -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list"></i> Chi tiết hóa đơn
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" id="addItem">
                            <i class="fas fa-plus"></i> Thêm mục
                        </button>
                    </div>
                    <div class="card-body">
                        <div id="invoiceItems">
                            @foreach($invoice->items as $index => $item)
                                <div class="invoice-item row mb-3" data-index="{{ $index }}">
                                    <div class="col-md-5">
                                        <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="items[{{ $index }}][description]" 
                                               value="{{ old("items.{$index}.description", $item->description) }}" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control item-quantity" name="items[{{ $index }}][quantity]" 
                                               value="{{ old("items.{$index}.quantity", $item->quantity) }}" min="0.01" step="0.01" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Đơn giá <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control item-unit-price" name="items[{{ $index }}][unit_price]" 
                                               value="{{ old("items.{$index}.unit_price", $item->unit_price) }}" min="0" step="1000" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Thành tiền</label>
                                        <input type="number" class="form-control item-amount" name="items[{{ $index }}][amount]" 
                                               value="{{ old("items.{$index}.amount", $item->amount) }}" readonly>
                                    </div>
                                    <div class="col-md-1">
                                        <label class="form-label">&nbsp;</label>
                                        <button type="button" class="btn btn-sm btn-danger remove-item">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @error('items')
                            <div class="alert alert-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Lease Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-contract"></i> Thông tin hợp đồng
                        </h6>
                    </div>
                    <div class="card-body" id="leaseInfo">
                        <div class="mb-3">
                            <strong>Khách hàng:</strong><br>
                            {{ $invoice->lease->tenant->full_name ?? 'Chưa có khách hàng' }}<br>
                            <small class="text-muted">{{ $invoice->lease->tenant->phone ?? 'N/A' }}</small>
                        </div>
                        <div class="mb-3">
                            <strong>Tài sản:</strong><br>
                            {{ $invoice->lease->unit->property->name ?? 'N/A' }}<br>
                            <small class="text-muted">Phòng {{ $invoice->lease->unit->code ?? 'N/A' }}</small>
                        </div>
                        <div class="mb-3">
                            <strong>Tiền thuê:</strong><br>
                            <span class="text-primary font-weight-bold">{{ number_format($invoice->lease->rent_amount, 0, ',', '.') }} VND</span>
                        </div>
                    </div>
                </div>

                <!-- Invoice Summary -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator"></i> Tổng kết hóa đơn
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Tạm tính:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="subtotal">{{ number_format($invoice->subtotal, 0, ',', '.') }}</span> VND
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <label for="tax_rate" class="form-label">Thuế (%)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" 
                                       id="tax_rate" name="tax_rate" 
                                       value="{{ old('tax_rate', $invoice->tax_amount > 0 ? ($invoice->tax_amount / $invoice->subtotal * 100) : 0) }}" 
                                       min="0" max="100" step="0.01">
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <strong>Thuế:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <span id="tax_amount">{{ number_format($invoice->tax_amount, 0, ',', '.') }}</span> VND
                            </div>
                        </div>
                        
                        <div class="row mb-2">
                            <div class="col-6">
                                <label for="discount_amount" class="form-label">Giảm giá (VND)</label>
                            </div>
                            <div class="col-6">
                                <input type="number" class="form-control form-control-sm" 
                                       id="discount_amount" name="discount_amount" 
                                       value="{{ old('discount_amount', $invoice->discount_amount) }}" 
                                       min="0" step="1000">
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <div class="col-6">
                                <strong>Tổng cộng:</strong>
                            </div>
                            <div class="col-6 text-right">
                                <strong><span id="total_amount">{{ number_format($invoice->total_amount, 0, ',', '.') }}</span> VND</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Cập nhật hóa đơn
                            </button>
                            <a href="{{ route('agent.invoices.show', $invoice->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let itemIndex = {{ $invoice->items->count() }};

    // Add new item
    $('#addItem').click(function() {
        const newItem = `
            <div class="invoice-item row mb-3" data-index="${itemIndex}">
                <div class="col-md-5">
                    <label class="form-label">Mô tả <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="items[${itemIndex}][description]" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                    <input type="number" class="form-control item-quantity" name="items[${itemIndex}][quantity]" 
                           value="1" min="0.01" step="0.01" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đơn giá <span class="text-danger">*</span></label>
                    <input type="number" class="form-control item-unit-price" name="items[${itemIndex}][unit_price]" 
                           value="0" min="0" step="1000" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Thành tiền</label>
                    <input type="number" class="form-control item-amount" name="items[${itemIndex}][amount]" 
                           value="0" readonly>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-danger remove-item">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
        $('#invoiceItems').append(newItem);
        itemIndex++;
        updateRemoveButtons();
    });

    // Remove item
    $(document).on('click', '.remove-item', function() {
        $(this).closest('.invoice-item').remove();
        updateRemoveButtons();
        calculateTotals();
    });

    // Calculate item amount
    $(document).on('input', '.item-quantity, .item-unit-price', function() {
        const row = $(this).closest('.invoice-item');
        const quantity = parseFloat(row.find('.item-quantity').val()) || 0;
        const unitPrice = parseFloat(row.find('.item-unit-price').val()) || 0;
        const amount = quantity * unitPrice;
        row.find('.item-amount').val(amount);
        calculateTotals();
    });

    // Calculate tax and discount
    $('#tax_rate, #discount_amount').on('input', function() {
        calculateTotals();
    });

    function updateRemoveButtons() {
        const items = $('.invoice-item');
        items.each(function(index) {
            const removeBtn = $(this).find('.remove-item');
            if (items.length > 1) {
                removeBtn.show();
            } else {
                removeBtn.hide();
            }
        });
    }

    function calculateTotals() {
        let subtotal = 0;
        $('.item-amount').each(function() {
            subtotal += parseFloat($(this).val()) || 0;
        });

        const taxRate = parseFloat($('#tax_rate').val()) || 0;
        const taxAmount = subtotal * (taxRate / 100);
        const discountAmount = parseFloat($('#discount_amount').val()) || 0;
        const totalAmount = subtotal + taxAmount - discountAmount;

        $('#subtotal').text(formatNumber(subtotal));
        $('#tax_amount').text(formatNumber(taxAmount));
        $('#total_amount').text(formatNumber(totalAmount));
    }

    function formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    // Initialize
    updateRemoveButtons();
    calculateTotals();

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

    // Form validation notifications
    $('#invoiceForm').on('submit', function(e) {
        const leaseId = $('#lease_id').val();
        const items = $('.invoice-item').length;
        
        if (!leaseId) {
            e.preventDefault();
            Notify.error('Vui lòng chọn hợp đồng', 'Thiếu thông tin');
            return false;
        }
        
        if (items === 0) {
            e.preventDefault();
            Notify.error('Vui lòng thêm ít nhất một mục hóa đơn', 'Thiếu thông tin');
            return false;
        }
        
        // Show loading notification
        Notify.info('Đang cập nhật hóa đơn...', 'Xử lý');
    });

    // Lease selection notification
    $('#lease_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        if (selectedOption.val()) {
            const tenantName = selectedOption.data('tenant');
            const propertyName = selectedOption.data('property');
            Notify.info(`Đã chọn hợp đồng: ${propertyName} - ${tenantName}`, 'Hợp đồng');
        }
    });

    // Item addition notification
    $('#addItem').on('click', function() {
        Notify.info('Đã thêm mục hóa đơn mới', 'Thêm mục');
    });

    // Auto-save notification
    let autoSaveTimeout;
    $('input, select, textarea').on('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(() => {
            Notify.info('Đã lưu thay đổi tự động', 'Tự động lưu');
        }, 2000);
    });

    // Show invoice info
    const invoiceNo = '{{ $invoice->invoice_no }}';
    const status = '{{ $invoice->status }}';
    
    Notify.info(`Đang chỉnh sửa hóa đơn ${invoiceNo} (${status})`, 'Chỉnh sửa hóa đơn');
});
</script>
@endpush
