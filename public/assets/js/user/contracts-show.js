// Contract Show Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    initializeTabs();
    
    // Initialize invoice filters
    initializeInvoiceFilters();
    
    // Initialize print functionality
    initializePrintFunctionality();
    
    // Initialize modals
    initializeModals();
});

// Initialize meter reading tabs
function initializeTabs() {
    const tabButtons = document.querySelectorAll('#meterTabs button[data-bs-toggle="tab"]');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Show corresponding tab content
            const targetId = this.getAttribute('data-bs-target');
            const tabPanes = document.querySelectorAll('.tab-pane');
            
            tabPanes.forEach(pane => {
                pane.classList.remove('show', 'active');
            });
            
            const targetPane = document.querySelector(targetId);
            if (targetPane) {
                targetPane.classList.add('show', 'active');
            }
        });
    });
}

// Initialize invoice filters
function initializeInvoiceFilters() {
    const filterTabs = document.querySelectorAll('.invoice-filters .filter-tab');
    const invoiceRows = document.querySelectorAll('.invoices-table tbody tr');
    
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all filter tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get filter status
            const status = this.getAttribute('data-status');
            
            // Filter invoice rows
            invoiceRows.forEach(row => {
                if (status === 'all') {
                    row.style.display = '';
                } else {
                    const statusBadge = row.querySelector('.status-badge');
                    if (statusBadge) {
                        const rowStatus = statusBadge.classList.contains(`status-${status}`);
                        row.style.display = rowStatus ? '' : 'none';
                    }
                }
            });
        });
    });
}

// Initialize print functionality
function initializePrintFunctionality() {
    // Add print button event listener if it exists
    const printButton = document.querySelector('[onclick="printContract()"]');
    if (printButton) {
        printButton.addEventListener('click', function() {
            window.print();
        });
    }
}

// Initialize modals
function initializeModals() {
    // Initialize Bootstrap modals
    const modals = document.querySelectorAll('.modal');
    modals.forEach(modal => {
        new bootstrap.Modal(modal);
    });
}

// Download contract PDF
function downloadContract(contractId) {
    // Show download modal
    const downloadModal = new bootstrap.Modal(document.getElementById('downloadModal'));
    downloadModal.show();
    
    // Simulate download progress
    const progressBar = document.getElementById('downloadProgress');
    let progress = 0;
    
    const interval = setInterval(() => {
        progress += 10;
        progressBar.style.width = progress + '%';
        
        if (progress >= 100) {
            clearInterval(interval);
            
            // Hide modal after completion
            setTimeout(() => {
                downloadModal.hide();
                progressBar.style.width = '0%';
                
                // Show success message
                showNotification('Tải file PDF thành công!', 'success');
            }, 500);
        }
    }, 200);
    
    // In a real implementation, you would make an AJAX request to download the PDF
    // fetch(`/tenant/contracts/${contractId}/download`, {
    //     method: 'GET',
    //     headers: {
    //         'X-Requested-With': 'XMLHttpRequest'
    //     }
    // })
    // .then(response => response.blob())
    // .then(blob => {
    //     const url = window.URL.createObjectURL(blob);
    //     const a = document.createElement('a');
    //     a.href = url;
    //     a.download = `hop-dong-${contractId}.pdf`;
    //     document.body.appendChild(a);
    //     a.click();
    //     window.URL.revokeObjectURL(url);
    //     document.body.removeChild(a);
    //     downloadModal.hide();
    // })
    // .catch(error => {
    //     console.error('Error downloading PDF:', error);
    //     downloadModal.hide();
    //     showNotification('Có lỗi xảy ra khi tải file PDF', 'error');
    // });
}

// Print contract
function printContract() {
    window.print();
}

// Renew contract
function renewContract(contractId) {
    // Store contract ID for renewal
    window.currentContractId = contractId;
    
    // Show renewal modal
    const renewalModal = new bootstrap.Modal(document.getElementById('renewalModal'));
    renewalModal.show();
}

// Confirm renewal
function confirmRenewal() {
    const contractId = window.currentContractId;
    const period = document.getElementById('renewalPeriod').value;
    const note = document.getElementById('renewalNote').value;
    
    if (!contractId) {
        showNotification('Không tìm thấy thông tin hợp đồng', 'error');
        return;
    }
    
    // Show loading state
    const confirmButton = document.querySelector('#renewalModal .btn-warning');
    const originalText = confirmButton.innerHTML;
    confirmButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang xử lý...';
    confirmButton.disabled = true;
    
    // In a real implementation, you would make an AJAX request to renew the contract
    // fetch(`/tenant/contracts/${contractId}/renew`, {
    //     method: 'POST',
    //     headers: {
    //         'Content-Type': 'application/json',
    //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    //     },
    //     body: JSON.stringify({
    //         period: period,
    //         note: note
    //     })
    // })
    // .then(response => response.json())
    // .then(data => {
    //     if (data.success) {
    //         showNotification('Gia hạn hợp đồng thành công!', 'success');
    //         bootstrap.Modal.getInstance(document.getElementById('renewalModal')).hide();
    //         // Reload page to show updated contract info
    //         setTimeout(() => {
    //             window.location.reload();
    //         }, 1500);
    //     } else {
    //         showNotification(data.message || 'Có lỗi xảy ra khi gia hạn hợp đồng', 'error');
    //     }
    // })
    // .catch(error => {
    //     console.error('Error renewing contract:', error);
    //     showNotification('Có lỗi xảy ra khi gia hạn hợp đồng', 'error');
    // })
    // .finally(() => {
    //     confirmButton.innerHTML = originalText;
    //     confirmButton.disabled = false;
    // });
    
    // Simulate API call
    setTimeout(() => {
        confirmButton.innerHTML = originalText;
        confirmButton.disabled = false;
        
        showNotification('Gia hạn hợp đồng thành công!', 'success');
        bootstrap.Modal.getInstance(document.getElementById('renewalModal')).hide();
    }, 2000);
}

// View invoice details
function viewInvoice(invoiceId) {
    // In a real implementation, you would open an invoice detail modal or navigate to invoice page
    showNotification(`Xem chi tiết hóa đơn ${invoiceId}`, 'info');
    
    // Example: Open invoice detail modal
    // fetch(`/tenant/invoices/${invoiceId}`)
    // .then(response => response.text())
    // .then(html => {
    //     const modalBody = document.getElementById('invoiceDetailContent');
    //     modalBody.innerHTML = html;
    //     const modal = new bootstrap.Modal(document.getElementById('invoiceDetailModal'));
    //     modal.show();
    // })
    // .catch(error => {
    //     console.error('Error loading invoice details:', error);
    //     showNotification('Có lỗi xảy ra khi tải chi tiết hóa đơn', 'error');
    // });
}

// Pay invoice
function payInvoice(invoiceId) {
    // In a real implementation, you would redirect to payment page or open payment modal
    showNotification(`Chuyển đến trang thanh toán hóa đơn ${invoiceId}`, 'info');
    
    // Example: Redirect to payment page
    // window.location.href = `/tenant/invoices/${invoiceId}/pay`;
}

// Show notification
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Utility function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

// Utility function to format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Export functions for global access
window.downloadContract = downloadContract;
window.printContract = printContract;
window.renewContract = renewContract;
window.confirmRenewal = confirmRenewal;
window.viewInvoice = viewInvoice;
window.payInvoice = payInvoice;
