@extends('layouts.manager_dashboard')

@section('title', 'Tạo Hóa đơn Mới')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Tạo Hóa đơn Mới</h1>
                <p>Tạo hóa đơn mới cho hợp đồng thuê</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.invoices.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="invoiceForm" method="POST" action="{{ route('manager.invoices.store') }}">
                    @csrf
                    
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
                                            data-property="{{ $lease->unit->property->name ?? 'N/A' }}">
                                        {{ $lease->contract_no ?? 'HD#' . $lease->id }} - {{ $lease->tenant->full_name ?? 'N/A' }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số hóa đơn</label>
                                <input type="text" name="invoice_no" class="form-control" 
                                       placeholder="Tự động tạo nếu để trống">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Ngày phát hành <span class="text-danger">*</span></label>
                                        <input type="date" name="issue_date" class="form-control" 
                                               value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Hạn thanh toán <span class="text-danger">*</span></label>
                                        <input type="date" name="due_date" class="form-control" 
                                               value="{{ date('Y-m-d', strtotime('+30 days')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft">Nháp</option>
                                    <option value="issued">Đã phát hành</option>
                                    <option value="paid">Đã thanh toán</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea name="note" class="form-control" rows="3" 
                                          placeholder="Ghi chú thêm cho hóa đơn"></textarea>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Chi tiết hóa đơn</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Tiền tệ</label>
                                <select name="currency" class="form-select">
                                    <option value="VND">VND</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tổng tiền trước thuế</label>
                                        <input type="number" name="subtotal" id="subtotal" class="form-control" 
                                               step="0.01" min="0" value="0" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Thuế</label>
                                        <input type="number" name="tax_amount" id="tax_amount" class="form-control" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Giảm giá</label>
                                        <input type="number" name="discount_amount" id="discount_amount" class="form-control" 
                                               step="0.01" min="0" value="0">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Tổng tiền <span class="text-danger">*</span></label>
                                        <input type="number" name="total_amount" id="total_amount" class="form-control" 
                                               step="0.01" min="0" value="0" required readonly>
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
                                <button type="button" class="btn btn-outline-primary" id="addItem">
                                    <i class="fas fa-plus"></i> Thêm khoản
                                </button>
                            </div>

                            <div id="invoiceItems">
                                <!-- Default rent item -->
                                <div class="invoice-item border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <label class="form-label">Loại</label>
                                            <select name="items[0][item_type]" class="form-select item-type">
                                                <option value="rent">Tiền thuê</option>
                                                <option value="service">Dịch vụ</option>
                                                <option value="meter">Đồng hồ</option>
                                                <option value="deposit">Cọc</option>
                                                <option value="other">Khác</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Mô tả</label>
                                            <input type="text" name="items[0][description]" class="form-control" 
                                                   value="Tiền thuê phòng" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Số lượng</label>
                                            <input type="number" name="items[0][quantity]" class="form-control item-quantity" 
                                                   step="0.001" min="0.001" value="1" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Đơn giá</label>
                                            <input type="number" name="items[0][unit_price]" class="form-control item-unit-price" 
                                                   step="0.01" min="0" value="0" required>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">Thành tiền</label>
                                            <input type="number" name="items[0][amount]" class="form-control item-amount" 
                                                   step="0.01" min="0" value="0" required readonly>
                                        </div>
                                        <div class="col-md-1">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-outline-danger remove-item" style="display: none;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('manager.invoices.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Tạo hóa đơn
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
let itemIndex = 1;

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

// Lease selection handler
document.getElementById('leaseSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (selectedOption.value) {
        const leaseId = selectedOption.value;
        
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
            
            // Clear existing items except the first one
            const itemsContainer = document.getElementById('invoiceItems');
            const existingItems = itemsContainer.querySelectorAll('.invoice-item');
            for (let i = 1; i < existingItems.length; i++) {
                existingItems[i].remove();
            }
            
            // Update rent item
            const rentItem = document.querySelector('.invoice-item');
            const rentInput = rentItem.querySelector('.item-unit-price');
            rentInput.value = data.rent_amount || 0;
            calculateItemAmount(rentItem);
            
            // Add service items
            if (data.services && data.services.length > 0) {
                data.services.forEach((service, index) => {
                    addServiceItem(service, itemIndex + index);
                });
                itemIndex += data.services.length;
            }
            
            // Show remove buttons if more than one item
            if (document.querySelectorAll('.invoice-item').length > 1) {
                document.querySelectorAll('.remove-item').forEach(btn => btn.style.display = 'block');
            }
            
            Notify.success('Đã tải thông tin hợp đồng và dịch vụ', 'Thành công!');
        })
        .catch(error => {
            // Hide loading
            const toastElement = document.getElementById(loadingToast);
            if (toastElement) {
                const bsToast = bootstrap.Toast.getInstance(toastElement);
                if (bsToast) bsToast.hide();
            }
            
            console.error('Error:', error);
            Notify.error('Không thể tải thông tin hợp đồng', 'Lỗi!');
        });
    }
});

// Tax and discount handlers
document.getElementById('tax_amount').addEventListener('input', calculateTotals);
document.getElementById('discount_amount').addEventListener('input', calculateTotals);

// Initialize
addItemEventListeners(document.querySelector('.invoice-item'));

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
        Notify.error('Không thể tạo hóa đơn: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
    })
    .finally(() => {
        if (window.Preloader) {
            window.Preloader.hide();
        }
    });
});
</script>
@endpush
