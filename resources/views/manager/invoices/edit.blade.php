@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa Hóa đơn')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chỉnh sửa Hóa đơn</h1>
                <p>Cập nhật thông tin hóa đơn #{{ $invoice->invoice_no ?? $invoice->id }}</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
                <a href="{{ route('manager.invoices.show', $invoice->id) }}" class="btn btn-outline-info">
                    <i class="fas fa-eye"></i>
                    Xem chi tiết
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="invoiceForm" method="POST" action="{{ route('manager.invoices.update', $invoice->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin cơ bản</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Hợp đồng <span class="text-danger">*</span></label>
                                <select name="lease_id" id="leaseSelect" class="form-select" required>
                                    <option value="">Chọn hợp đồng</option>
                                    @foreach ($leases as $lease)
                                    <option value="{{ $lease->id }}" 
                                            data-rent="{{ $lease->rent_amount }}"
                                            data-tenant="{{ $lease->tenant->full_name ?? 'N/A' }}"
                                            data-property="{{ $lease->unit->property->name ?? 'N/A' }}"
                                            {{ $invoice->lease_id == $lease->id ? 'selected' : '' }}>
                                        {{ $lease->contract_no ?? 'HD#' . $lease->id }} - {{ $lease->tenant->full_name ?? 'N/A' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số hóa đơn</label>
                                <input type="text" name="invoice_no" class="form-control" 
                                       value="{{ $invoice->invoice_no }}"
                                       placeholder="Tự động tạo nếu để trống">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày phát hành <span class="text-danger">*</span></label>
                                        <input type="date" name="issue_date" class="form-control" 
                                               value="{{ $invoice->issue_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hạn thanh toán <span class="text-danger">*</span></label>
                                        <input type="date" name="due_date" class="form-control" 
                                               value="{{ $invoice->due_date->format('Y-m-d') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft" {{ $invoice->status == 'draft' ? 'selected' : '' }}>Nháp</option>
                                    <option value="issued" {{ $invoice->status == 'issued' ? 'selected' : '' }}>Đã phát hành</option>
                                    <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                                    <option value="overdue" {{ $invoice->status == 'overdue' ? 'selected' : '' }}>Quá hạn</option>
                                    <option value="cancelled" {{ $invoice->status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="3" 
                                          placeholder="Ghi chú thêm cho hóa đơn">{{ $invoice->note }}</textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Chi tiết hóa đơn</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Tiền tệ</label>
                                <select name="currency" class="form-select">
                                    <option value="VND" {{ $invoice->currency == 'VND' ? 'selected' : '' }}>VND</option>
                                    <option value="USD" {{ $invoice->currency == 'USD' ? 'selected' : '' }}>USD</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tổng tiền trước thuế</label>
                                        <input type="number" name="subtotal" id="subtotal" class="form-control" 
                                               step="0.01" min="0" value="{{ $invoice->subtotal }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Thuế</label>
                                        <input type="number" name="tax_amount" id="tax_amount" class="form-control" 
                                               step="0.01" min="0" value="{{ $invoice->tax_amount }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Giảm giá</label>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" 
                                               step="0.01" min="0" value="{{ $invoice->discount_amount }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tổng tiền <span class="text-danger">*</span></label>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control" 
                                               step="0.01" min="0" value="{{ $invoice->total_amount }}" required readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoice Items -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5>Chi tiết các khoản</h5>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-success" id="loadServices">
                                        <i class="fas fa-sync"></i> Tải dịch vụ từ hợp đồng
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="addItem">
                                        <i class="fas fa-plus"></i> Thêm khoản
                                    </button>
                                </div>
                            </div>

                            <div id="invoiceItems">
                                @foreach($invoice->items as $index => $item)
                                <div class="invoice-item border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label">Loại</label>
                                            <select name="items[{{ $index }}][item_type]" class="form-select item-type">
                                                <option value="rent" {{ $item->item_type == 'rent' ? 'selected' : '' }}>Tiền thuê</option>
                                                <option value="service" {{ $item->item_type == 'service' ? 'selected' : '' }}>Dịch vụ</option>
                                                <option value="meter" {{ $item->item_type == 'meter' ? 'selected' : '' }}>Đồng hồ</option>
                                                <option value="deposit" {{ $item->item_type == 'deposit' ? 'selected' : '' }}>Cọc</option>
                                                <option value="other" {{ $item->item_type == 'other' ? 'selected' : '' }}>Khác</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Mô tả</label>
                                            <input type="text" name="items[{{ $index }}][description]" class="form-control" 
                                                   value="{{ $item->description }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Số lượng</label>
                                            <input type="number" name="items[{{ $index }}][quantity]" class="form-control item-quantity" 
                                                   step="0.001" min="0.001" value="{{ $item->quantity }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Đơn giá</label>
                                            <input type="number" name="items[{{ $index }}][unit_price]" class="form-control item-unit-price" 
                                                   step="0.01" min="0" value="{{ $item->unit_price }}" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Thành tiền</label>
                                            <input type="number" name="items[{{ $index }}][amount]" class="form-control item-amount" 
                                                   step="0.01" min="0" value="{{ $item->amount }}" required readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger remove-item" 
                                                    {{ $invoice->items->count() == 1 ? 'style=display:none' : '' }}>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('manager.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Cập nhật hóa đơn
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
let itemIndex = {{ $invoice->items->count() }};

// Calculate totals
function calculateTotals() {
    let subtotal = 0;
    document.querySelectorAll('.item-amount').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    const taxAmount = parseFloat(document.getElementById('tax_amount').value) || 0;
    const discountAmount = parseFloat(document.getElementById('discount_amount').value) || 0;
    const totalAmount = subtotal + taxAmount - discountAmount;
    
    document.getElementById('subtotal').value = subtotal.toFixed(2);
    document.getElementById('total_amount').value = totalAmount.toFixed(2);
}

// Calculate item amount
function calculateItemAmount(itemElement) {
    const quantity = parseFloat(itemElement.querySelector('.item-quantity').value) || 0;
    const unitPrice = parseFloat(itemElement.querySelector('.item-unit-price').value) || 0;
    const amount = quantity * unitPrice;
    
    itemElement.querySelector('.item-amount').value = amount.toFixed(2);
    calculateTotals();
}

// Add item event listeners
function addItemEventListeners(itemElement) {
    itemElement.querySelector('.item-quantity').addEventListener('input', () => calculateItemAmount(itemElement));
    itemElement.querySelector('.item-unit-price').addEventListener('input', () => calculateItemAmount(itemElement));
}

// Add service item from lease
function addServiceItem(service, index) {
    const itemsContainer = document.getElementById('invoiceItems');
    const newItem = document.createElement('div');
    newItem.className = 'invoice-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Loại</label>
                <select name="items[${index}][item_type]" class="form-select item-type">
                    <option value="service" selected>Dịch vụ</option>
                    <option value="rent">Tiền thuê</option>
                    <option value="meter">Đồng hồ</option>
                    <option value="deposit">Cọc</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mô tả</label>
                <input type="text" name="items[${index}][description]" class="form-control" 
                       value="${service.service_name}" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Số lượng</label>
                <input type="number" name="items[${index}][quantity]" class="form-control item-quantity" 
                       step="0.001" min="0.001" value="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Đơn giá</label>
                <input type="number" name="items[${index}][unit_price]" class="form-control item-unit-price" 
                       step="0.01" min="0" value="${service.price}" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">Thành tiền</label>
                <input type="number" name="items[${index}][amount]" class="form-control item-amount" 
                       step="0.01" min="0" value="${service.price}" required readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    itemsContainer.appendChild(newItem);
    addItemEventListeners(newItem);
}

// Add new item
document.getElementById('addItem').addEventListener('click', function() {
    const itemsContainer = document.getElementById('invoiceItems');
    const newItem = document.createElement('div');
    newItem.className = 'invoice-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-2">
                <label class="form-label">Loại</label>
                <select name="items[${itemIndex}][item_type]" class="form-select item-type">
                    <option value="rent">Tiền thuê</option>
                    <option value="service">Dịch vụ</option>
                    <option value="meter">Đồng hồ</option>
                    <option value="deposit">Cọc</option>
                    <option value="other">Khác</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Mô tả</label>
                <input type="text" name="items[${itemIndex}][description]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Số lượng</label>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control item-quantity" 
                       step="0.001" min="0.001" value="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label">Đơn giá</label>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control item-unit-price" 
                       step="0.01" min="0" value="0" required>
            </div>
            <div class="col-md-1">
                <label class="form-label">Thành tiền</label>
                <input type="number" name="items[${itemIndex}][amount]" class="form-control item-amount" 
                       step="0.01" min="0" value="0" required readonly>
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <button type="button" class="btn btn-outline-danger remove-item">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    
    itemsContainer.appendChild(newItem);
    addItemEventListeners(newItem);
    itemIndex++;
    
    // Show remove buttons for all items
    document.querySelectorAll('.remove-item').forEach(btn => btn.style.display = 'block');
});

// Remove item handler
document.getElementById('invoiceItems').addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('.invoice-item').remove();
        calculateTotals();
        
        // Hide remove buttons if only one item left
        if (document.querySelectorAll('.invoice-item').length === 1) {
            document.querySelectorAll('.remove-item').forEach(btn => btn.style.display = 'none');
        }
    }
});

// Load services from lease
document.getElementById('loadServices').addEventListener('click', function() {
    const leaseSelect = document.getElementById('leaseSelect');
    const leaseId = leaseSelect.value;
    
    if (!leaseId) {
        Notify.warning('Vui lòng chọn hợp đồng trước', 'Cảnh báo!');
        return;
    }
    
    // Show loading
    const loadingToast = Notify.toast({
        title: 'Đang tải...',
        message: 'Vui lòng chờ trong giây lát',
        type: 'info',
        duration: 0
    });
    
    // Fetch lease details
    fetch(`/api/invoices/leases/${leaseId}/details`, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        // Hide loading
        const toastElement = document.getElementById(loadingToast);
        if (toastElement) {
            const bsToast = bootstrap.Toast.getInstance(toastElement);
            if (bsToast) bsToast.hide();
        }
        
        // Add service items
        if (data.services && data.services.length > 0) {
            data.services.forEach((service, index) => {
                addServiceItem(service, itemIndex + index);
            });
            itemIndex += data.services.length;
            
            // Show remove buttons
            document.querySelectorAll('.remove-item').forEach(btn => btn.style.display = 'block');
            
            Notify.success(`Đã thêm ${data.services.length} dịch vụ từ hợp đồng`, 'Thành công!');
        } else {
            Notify.info('Hợp đồng này không có dịch vụ nào', 'Thông báo');
        }
    })
    .catch(error => {
        // Hide loading
        const toastElement = document.getElementById(loadingToast);
        if (toastElement) {
            const bsToast = bootstrap.Toast.getInstance(toastElement);
            if (bsToast) bsToast.hide();
        }
        
        console.error('Error:', error);
        Notify.error('Không thể tải dịch vụ từ hợp đồng', 'Lỗi!');
    });
});

// Tax and discount handlers
document.getElementById('tax_amount').addEventListener('input', calculateTotals);
document.getElementById('discount_amount').addEventListener('input', calculateTotals);

// Initialize
document.querySelectorAll('.invoice-item').forEach(item => addItemEventListeners(item));

// Form submission
document.getElementById('invoiceForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (window.Preloader) {
        window.Preloader.show();
    }

    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Notify.success(data.message, 'Thành công!');
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
        } else {
            Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Notify.error('Không thể cập nhật hóa đơn: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
    })
    .finally(() => {
        if (window.Preloader) {
            window.Preloader.hide();
        }
    });
});
</script>
@endpush
