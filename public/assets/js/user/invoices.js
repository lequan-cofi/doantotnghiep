// Invoices Page JavaScript
var currentInvoiceId = null;
var selectedPaymentMethod = null;
var invoicesData = {};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeInvoices();
    setupFilters();
    setupSearch();
    setupPaymentMethods();
    loadInvoicesData();
});

// Initialize invoices functionality
function initializeInvoices() {
    console.log('Invoices page initialized');
    
    // Setup tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Animate stat cards
    animateStatCards();
}

// Setup filter functionality
function setupFilters() {
    var filterTabs = document.querySelectorAll('.filter-tab');
    var monthFilter = document.getElementById('monthFilter');
    
    // Status filter tabs
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            var status = this.getAttribute('data-status');
            
            // Update active tab
            for (var j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            // Filter invoices
            filterInvoices();
        });
    }
    
    // Month filter
    if (monthFilter) {
        monthFilter.addEventListener('change', function() {
            filterInvoices();
        });
    }
}

// Filter invoices by status and month
function filterInvoices() {
    var activeTab = document.querySelector('.filter-tab.active');
    var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
    var monthFilter = document.getElementById('monthFilter');
    var selectedMonth = monthFilter ? monthFilter.value : '';
    
    var invoices = document.querySelectorAll('.invoice-card');
    var visibleCount = 0;
    
    for (var i = 0; i < invoices.length; i++) {
        var invoice = invoices[i];
        var invoiceStatus = invoice.getAttribute('data-status');
        var invoiceMonth = invoice.getAttribute('data-month');
        
        var statusMatch = (status === 'all' || invoiceStatus === status);
        var monthMatch = (!selectedMonth || invoiceMonth === selectedMonth);
        
        if (statusMatch && monthMatch) {
            invoice.style.display = 'block';
            visibleCount++;
        } else {
            invoice.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
    
    console.log('Filtered invoices:', status, selectedMonth, 'visible:', visibleCount);
}

// Setup search functionality
function setupSearch() {
    var searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            searchInvoices(searchTerm);
        });
    }
}

// Search invoices
function searchInvoices(searchTerm) {
    var invoices = document.querySelectorAll('.invoice-card');
    var visibleCount = 0;
    
    for (var i = 0; i < invoices.length; i++) {
        var invoice = invoices[i];
        var invoiceId = invoice.querySelector('.invoice-id').textContent.toLowerCase();
        var propertyName = invoice.querySelector('.property-name').textContent.toLowerCase();
        
        if (invoiceId.includes(searchTerm) || propertyName.includes(searchTerm)) {
            invoice.style.display = 'block';
            visibleCount++;
        } else {
            invoice.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0 && searchTerm.length > 0) {
        emptyState.style.display = 'block';
        emptyState.querySelector('h3').textContent = 'Không tìm thấy hóa đơn nào';
        emptyState.querySelector('p').textContent = 'Không có hóa đơn nào khớp với từ khóa "' + searchTerm + '".';
    } else if (visibleCount > 0) {
        emptyState.style.display = 'none';
    }
}

// Setup payment methods
function setupPaymentMethods() {
    var methodOptions = document.querySelectorAll('.method-option');
    var confirmBtn = document.getElementById('confirmPaymentBtn');
    
    for (var i = 0; i < methodOptions.length; i++) {
        methodOptions[i].addEventListener('click', function() {
            selectPaymentMethod(this);
        });
    }
    
    // Radio button change event
    var radioButtons = document.querySelectorAll('input[name="payment_method"]');
    for (var j = 0; j < radioButtons.length; j++) {
        radioButtons[j].addEventListener('change', function() {
            var method = document.querySelector('[data-method="' + this.value + '"]');
            if (method) {
                selectPaymentMethod(method);
            }
        });
    }
}

// Select payment method
function selectPaymentMethod(methodElement) {
    // Remove previous selections
    var allMethods = document.querySelectorAll('.method-option');
    for (var i = 0; i < allMethods.length; i++) {
        allMethods[i].classList.remove('selected');
    }
    
    // Add selection to current method
    methodElement.classList.add('selected');
    
    // Update radio button
    var radio = methodElement.querySelector('input[type="radio"]');
    if (radio) {
        radio.checked = true;
        selectedPaymentMethod = radio.value;
    }
    
    // Enable confirm button
    var confirmBtn = document.getElementById('confirmPaymentBtn');
    if (confirmBtn) {
        confirmBtn.disabled = false;
    }
    
    console.log('Selected payment method:', selectedPaymentMethod);
}

// Load invoices data
function loadInvoicesData() {
    // Simulate API call to load invoices data
    invoicesData = {
        'HD2023001': {
            id: 'HD2023001',
            property: 'Phòng trọ cao cấp Cầu Giấy',
            address: '123 Đường Cầu Giấy, Hà Nội',
            period: 'Tháng 12/2023',
            amount: 2500000,
            status: 'overdue',
            dueDate: '05/12/2023',
            breakdown: {
                rent: 2500000,
                electricity: 0,
                water: 0
            }
        },
        'HD2023002': {
            id: 'HD2023002',
            property: 'Homestay Hạnh Đào',
            address: '789 Đường Hạnh Đào, Hà Nội',
            period: 'Tháng 12/2023',
            amount: 8230000,
            status: 'pending',
            dueDate: '30/12/2023',
            breakdown: {
                rent: 8000000,
                electricity: 150000,
                water: 80000
            }
        },
        'HD2023003': {
            id: 'HD2023003',
            property: 'Phòng trọ cao cấp Cầu Giấy',
            address: '123 Đường Cầu Giấy, Hà Nội',
            period: 'Tháng 11/2023',
            amount: 2680000,
            status: 'paid',
            paidDate: '03/11/2023',
            paymentMethod: 'momo',
            breakdown: {
                rent: 2500000,
                electricity: 120000,
                water: 60000
            }
        },
        'HD2023004': {
            id: 'HD2023004',
            property: 'Homestay Hạnh Đào',
            address: '789 Đường Hạnh Đào, Hà Nội',
            period: 'Tháng 10/2023',
            amount: 8270000,
            status: 'paid',
            paidDate: '02/10/2023',
            paymentMethod: 'bank',
            breakdown: {
                rent: 8000000,
                electricity: 180000,
                water: 90000
            }
        }
    };
    
    console.log('Invoices data loaded');
}

// Animate stat cards
function animateStatCards() {
    var statCards = document.querySelectorAll('.stat-card');
    
    for (var i = 0; i < statCards.length; i++) {
        (function(index, card) {
            setTimeout(function() {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                
                setTimeout(function() {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100);
        })(i, statCards[i]);
    }
}

// Pay invoice
function payInvoice(invoiceId) {
    currentInvoiceId = invoiceId;
    var invoiceData = invoicesData[invoiceId];
    
    if (!invoiceData) {
        showToast('Không tìm thấy thông tin hóa đơn', 'error');
        return;
    }
    
    // Populate payment modal
    document.getElementById('paymentInvoiceId').textContent = invoiceData.id;
    document.getElementById('paymentProperty').textContent = invoiceData.property;
    document.getElementById('paymentPeriod').textContent = invoiceData.period;
    document.getElementById('paymentAmount').textContent = formatCurrency(invoiceData.amount);
    
    // Reset payment method selection
    selectedPaymentMethod = null;
    var allMethods = document.querySelectorAll('.method-option');
    for (var i = 0; i < allMethods.length; i++) {
        allMethods[i].classList.remove('selected');
    }
    var radioButtons = document.querySelectorAll('input[name="payment_method"]');
    for (var j = 0; j < radioButtons.length; j++) {
        radioButtons[j].checked = false;
    }
    document.getElementById('confirmPaymentBtn').disabled = true;
    
    // Show modal
    if (typeof bootstrap !== 'undefined') {
        var paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        paymentModal.show();
    } else {
        alert('Thanh toán hóa đơn: ' + invoiceData.id);
    }
}

// Process payment
function processPayment() {
    if (!selectedPaymentMethod || !currentInvoiceId) {
        showToast('Vui lòng chọn phương thức thanh toán', 'error');
        return;
    }
    
    // Hide payment modal
    if (typeof bootstrap !== 'undefined') {
        var paymentModal = bootstrap.Modal.getInstance(document.getElementById('paymentModal'));
        if (paymentModal) {
            paymentModal.hide();
        }
    }
    
    // Show processing modal
    showProcessingModal();
    
    // Simulate payment processing
    var progress = 0;
    var progressBar = document.getElementById('paymentProgress');
    
    var interval = setInterval(function() {
        progress += Math.random() * 15;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            
            // Complete payment
            setTimeout(function() {
                hideProcessingModal();
                completePayment();
            }, 500);
        }
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
    }, 300);
}

// Complete payment
function completePayment() {
    var invoiceData = invoicesData[currentInvoiceId];
    if (invoiceData) {
        // Update invoice data
        invoiceData.status = 'paid';
        invoiceData.paidDate = new Date().toLocaleDateString('vi-VN');
        invoiceData.paymentMethod = selectedPaymentMethod;
        
        // Update UI
        updateInvoiceCard(currentInvoiceId);
        
        showToast('Thanh toán thành công! Hóa đơn ' + currentInvoiceId + ' đã được thanh toán.', 'success');
        
        // Update stats
        updateStats();
    }
}

// Update invoice card in UI
function updateInvoiceCard(invoiceId) {
    var invoiceCards = document.querySelectorAll('.invoice-card');
    
    for (var i = 0; i < invoiceCards.length; i++) {
        var card = invoiceCards[i];
        var cardInvoiceId = card.querySelector('.invoice-id').textContent;
        
        if (cardInvoiceId === invoiceId) {
            // Update status
            card.setAttribute('data-status', 'paid');
            var statusElement = card.querySelector('.invoice-status');
            statusElement.className = 'invoice-status paid';
            statusElement.innerHTML = '<i class="fas fa-check-circle"></i><span>Đã thanh toán</span>';
            
            // Update due date
            var dueDateElement = card.querySelector('.due-date');
            dueDateElement.textContent = 'Đã thanh toán: ' + new Date().toLocaleDateString('vi-VN');
            dueDateElement.classList.remove('overdue');
            
            // Update payment method
            var methodValueElement = card.querySelector('.method-value');
            var methodInfo = getPaymentMethodInfo(selectedPaymentMethod);
            methodValueElement.innerHTML = '<div class="method-icon ' + selectedPaymentMethod + '">' + methodInfo.icon + '<span>' + methodInfo.name + '</span></div>';
            
            // Update actions
            var actionsElement = card.querySelector('.invoice-actions');
            actionsElement.innerHTML = 
                '<button class="btn btn-outline-primary btn-sm" onclick="viewInvoice(\'' + invoiceId + '\')">' +
                    '<i class="fas fa-eye me-1"></i>Xem chi tiết' +
                '</button>' +
                '<button class="btn btn-outline-success btn-sm" onclick="downloadInvoice(\'' + invoiceId + '\')">' +
                    '<i class="fas fa-download me-1"></i>Tải PDF' +
                '</button>' +
                '<button class="btn btn-outline-info btn-sm" onclick="viewReceipt(\'' + invoiceId + '\')">' +
                    '<i class="fas fa-receipt me-1"></i>Biên lai' +
                '</button>';
            
            break;
        }
    }
}

// Get payment method info
function getPaymentMethodInfo(method) {
    var methods = {
        'momo': {
            name: 'MoMo',
            icon: '<img src="https://developers.momo.vn/v3/assets/images/logo.png" alt="MoMo" style="width: 20px;">'
        },
        'bank': {
            name: 'Chuyển khoản',
            icon: '<i class="fas fa-university"></i>'
        },
        'vnpay': {
            name: 'VNPay',
            icon: '<img src="https://vnpay.vn/s1/statics.vnpay.vn/2023/9/06ncktiwd6dc1694418196384.png" alt="VNPay" style="width: 20px;">'
        },
        'zalopay': {
            name: 'ZaloPay',
            icon: '<img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Logo-ZaloPay-Square.png" alt="ZaloPay" style="width: 20px;">'
        }
    };
    
    return methods[method] || methods['bank'];
}

// Update statistics
function updateStats() {
    // Recalculate stats based on current data
    var paid = 0, pending = 0, overdue = 0, total = 0;
    var paidAmount = 0, pendingAmount = 0, overdueAmount = 0, totalAmount = 0;
    
    for (var id in invoicesData) {
        var invoice = invoicesData[id];
        total++;
        totalAmount += invoice.amount;
        
        if (invoice.status === 'paid') {
            paid++;
            paidAmount += invoice.amount;
        } else if (invoice.status === 'pending') {
            pending++;
            pendingAmount += invoice.amount;
        } else if (invoice.status === 'overdue') {
            overdue++;
            overdueAmount += invoice.amount;
        }
    }
    
    // Update stat cards
    var statCards = document.querySelectorAll('.stat-card');
    if (statCards.length >= 4) {
        statCards[0].querySelector('h3').textContent = paid;
        statCards[0].querySelector('.stat-amount').textContent = formatCurrency(paidAmount, true);
        
        statCards[1].querySelector('h3').textContent = pending;
        statCards[1].querySelector('.stat-amount').textContent = formatCurrency(pendingAmount, true);
        
        statCards[2].querySelector('h3').textContent = overdue;
        statCards[2].querySelector('.stat-amount').textContent = formatCurrency(overdueAmount, true);
        
        statCards[3].querySelector('h3').textContent = total;
        statCards[3].querySelector('.stat-amount').textContent = formatCurrency(totalAmount, true);
    }
}

// View invoice details
function viewInvoice(invoiceId) {
    currentInvoiceId = invoiceId;
    var invoiceData = invoicesData[invoiceId];
    
    if (!invoiceData) {
        showToast('Không tìm thấy thông tin hóa đơn', 'error');
        return;
    }
    
    // Show modal
    if (typeof bootstrap !== 'undefined') {
        var modal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
        modal.show();
        
        // Load invoice details
        loadInvoiceDetails(invoiceData);
    } else {
        alert('Chi tiết hóa đơn: ' + invoiceData.id);
    }
}

// Load invoice details into modal
function loadInvoiceDetails(invoiceData) {
    var content = document.getElementById('invoiceDetailContent');
    
    // Show loading initially
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải thông tin hóa đơn...</p>
        </div>
    `;
    
    // Simulate loading delay
    setTimeout(function() {
        var statusText = invoiceData.status === 'paid' ? 'Đã thanh toán' : 
                        invoiceData.status === 'pending' ? 'Chờ thanh toán' : 'Quá hạn';
        var statusColor = invoiceData.status === 'paid' ? 'success' : 
                         invoiceData.status === 'pending' ? 'primary' : 'danger';
        
        content.innerHTML = `
            <div class="invoice-detail-header">
                <div class="invoice-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="invoice-detail-info">
                    <h4>Hóa đơn ${invoiceData.id}</h4>
                    <p>Trạng thái: <span class="text-${statusColor}">${statusText}</span></p>
                </div>
            </div>
            
            <div class="invoice-sections">
                <div class="invoice-section">
                    <h5><i class="fas fa-home me-2"></i>Thông tin bất động sản</h5>
                    <div class="detail-row">
                        <span class="label">Tên phòng:</span>
                        <span class="value">${invoiceData.property}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Địa chỉ:</span>
                        <span class="value">${invoiceData.address}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Kỳ thanh toán:</span>
                        <span class="value">${invoiceData.period}</span>
                    </div>
                </div>
                
                <div class="invoice-section">
                    <h5><i class="fas fa-calculator me-2"></i>Chi tiết thanh toán</h5>
                    <div class="detail-row">
                        <span class="label">Tiền phòng:</span>
                        <span class="value">${formatCurrency(invoiceData.breakdown.rent)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Tiền điện:</span>
                        <span class="value">${formatCurrency(invoiceData.breakdown.electricity)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Tiền nước:</span>
                        <span class="value">${formatCurrency(invoiceData.breakdown.water)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><strong>Tổng cộng:</strong></span>
                        <span class="value"><strong>${formatCurrency(invoiceData.amount)}</strong></span>
                    </div>
                </div>
                
                <div class="invoice-section">
                    <h5><i class="fas fa-calendar me-2"></i>Thông tin thời gian</h5>
                    <div class="detail-row">
                        <span class="label">Ngày tạo:</span>
                        <span class="value">01/${invoiceData.period.split('/')[1]}</span>
                    </div>
                    ${invoiceData.status === 'paid' ? 
                        `<div class="detail-row">
                            <span class="label">Ngày thanh toán:</span>
                            <span class="value">${invoiceData.paidDate}</span>
                        </div>` : 
                        `<div class="detail-row">
                            <span class="label">Hạn thanh toán:</span>
                            <span class="value">${invoiceData.dueDate || 'Chưa xác định'}</span>
                        </div>`
                    }
                </div>
                
                ${invoiceData.paymentMethod ? 
                    `<div class="invoice-section">
                        <h5><i class="fas fa-credit-card me-2"></i>Phương thức thanh toán</h5>
                        <div class="detail-row">
                            <span class="label">Phương thức:</span>
                            <span class="value">${getPaymentMethodInfo(invoiceData.paymentMethod).name}</span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Trạng thái:</span>
                            <span class="value text-success">Thành công</span>
                        </div>
                    </div>` : ''
                }
            </div>
        `;
    }, 1000);
}

// Download invoice
function downloadInvoice(invoiceId) {
    var invoiceData = invoicesData[invoiceId];
    
    if (!invoiceData) {
        showToast('Không tìm thấy thông tin hóa đơn', 'error');
        return;
    }
    
    // Simulate PDF generation and download
    showToast('Đang tạo file PDF...', 'info');
    
    setTimeout(function() {
        // Create PDF content (simplified)
        var pdfContent = generateInvoicePDF(invoiceData);
        var blob = new Blob([pdfContent], { type: 'application/pdf' });
        
        // Create download link
        var url = window.URL.createObjectURL(blob);
        var link = document.createElement('a');
        link.href = url;
        link.download = 'hoa-don-' + invoiceData.id.toLowerCase() + '.pdf';
        
        // Trigger download
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        // Clean up
        window.URL.revokeObjectURL(url);
        
        showToast('Đã tải xuống hóa đơn thành công!', 'success');
    }, 2000);
}

// Generate PDF content
function generateInvoicePDF(invoiceData) {
    return `
HÓA ĐƠN THANH TOÁN
==================

Mã hóa đơn: ${invoiceData.id}
Kỳ thanh toán: ${invoiceData.period}

THÔNG TIN BẤT ĐỘNG SẢN:
- Tên phòng: ${invoiceData.property}
- Địa chỉ: ${invoiceData.address}

CHI TIẾT THANH TOÁN:
- Tiền phòng: ${formatCurrency(invoiceData.breakdown.rent)}
- Tiền điện: ${formatCurrency(invoiceData.breakdown.electricity)}
- Tiền nước: ${formatCurrency(invoiceData.breakdown.water)}
- Tổng cộng: ${formatCurrency(invoiceData.amount)}

TRẠNG THÁI: ${invoiceData.status === 'paid' ? 'Đã thanh toán' : 
             invoiceData.status === 'pending' ? 'Chờ thanh toán' : 'Quá hạn'}

${invoiceData.paymentMethod ? 
  `PHƯƠNG THỨC THANH TOÁN: ${getPaymentMethodInfo(invoiceData.paymentMethod).name}` : ''}

Ngày tạo: ${new Date().toLocaleDateString('vi-VN')}
    `;
}

// Download current invoice from modal
function downloadCurrentInvoice() {
    if (currentInvoiceId) {
        downloadInvoice(currentInvoiceId);
    }
}

// Print invoice
function printInvoice() {
    if (currentInvoiceId) {
        var invoiceData = invoicesData[currentInvoiceId];
        if (invoiceData) {
            // Open print window with invoice details
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>In hóa đơn - ${invoiceData.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
                        .detail { margin: 5px 0; }
                        .label { font-weight: bold; }
                        .total { font-size: 1.2em; font-weight: bold; color: #ff6b35; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>HÓA ĐƠN THANH TOÁN</h1>
                        <p>Mã hóa đơn: ${invoiceData.id}</p>
                    </div>
                    
                    <div class="section">
                        <h3>Thông tin bất động sản</h3>
                        <div class="detail"><span class="label">Tên phòng:</span> ${invoiceData.property}</div>
                        <div class="detail"><span class="label">Địa chỉ:</span> ${invoiceData.address}</div>
                        <div class="detail"><span class="label">Kỳ thanh toán:</span> ${invoiceData.period}</div>
                    </div>
                    
                    <div class="section">
                        <h3>Chi tiết thanh toán</h3>
                        <div class="detail"><span class="label">Tiền phòng:</span> ${formatCurrency(invoiceData.breakdown.rent)}</div>
                        <div class="detail"><span class="label">Tiền điện:</span> ${formatCurrency(invoiceData.breakdown.electricity)}</div>
                        <div class="detail"><span class="label">Tiền nước:</span> ${formatCurrency(invoiceData.breakdown.water)}</div>
                        <div class="detail total"><span class="label">Tổng cộng:</span> ${formatCurrency(invoiceData.amount)}</div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    }
}

// View receipt
function viewReceipt(invoiceId) {
    showToast('Tính năng xem biên lai sẽ được cập nhật sớm', 'info');
}

// Export invoices
function exportInvoices() {
    showToast('Đang xuất file Excel...', 'info');
    
    setTimeout(function() {
        // Simulate Excel export
        var csvContent = 'Mã hóa đơn,Phòng,Kỳ thanh toán,Số tiền,Trạng thái\n';
        
        for (var id in invoicesData) {
            var invoice = invoicesData[id];
            var status = invoice.status === 'paid' ? 'Đã thanh toán' : 
                        invoice.status === 'pending' ? 'Chờ thanh toán' : 'Quá hạn';
            csvContent += `${invoice.id},"${invoice.property}","${invoice.period}",${invoice.amount},"${status}"\n`;
        }
        
        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'danh-sach-hoa-don.csv';
        link.click();
        
        showToast('Đã xuất danh sách hóa đơn thành công!', 'success');
    }, 2000);
}

// Show processing modal
function showProcessingModal() {
    if (typeof bootstrap !== 'undefined') {
        var processingModal = new bootstrap.Modal(document.getElementById('processingModal'));
        processingModal.show();
    }
}

// Hide processing modal
function hideProcessingModal() {
    if (typeof bootstrap !== 'undefined') {
        var processingModal = bootstrap.Modal.getInstance(document.getElementById('processingModal'));
        if (processingModal) {
            processingModal.hide();
        }
    }
}

// Format currency
function formatCurrency(amount, short) {
    if (short && amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M VNĐ';
    }
    return new Intl.NumberFormat('vi-VN').format(amount) + ' VNĐ';
}

// Show toast notification
function showToast(message, type) {
    // Remove existing toasts
    var existingToasts = document.querySelectorAll('.custom-toast');
    for (var i = 0; i < existingToasts.length; i++) {
        existingToasts[i].remove();
    }
    
    var toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    var toast = document.createElement('div');
    toast.className = 'custom-toast ' + type;
    
    var icon = 'info-circle';
    if (type === 'success') icon = 'check-circle';
    else if (type === 'error') icon = 'times-circle';
    else if (type === 'warning') icon = 'exclamation-triangle';
    
    toast.innerHTML = '<i class="fas fa-' + icon + '"></i><span>' + message + '</span>';
    
    toastContainer.appendChild(toast);
    
    // Auto remove after 4 seconds
    setTimeout(function() {
        if (toast.parentElement) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(function() {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }
    }, 4000);
}
