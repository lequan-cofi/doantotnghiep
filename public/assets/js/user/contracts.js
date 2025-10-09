// Contracts Page JavaScript
var currentContractId = null;
var contractsData = {};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeContracts();
    setupFilters();
    setupSearch();
    loadContractsData();
});

// Initialize contracts functionality
function initializeContracts() {
    console.log('Contracts page initialized');
    
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
    
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            var status = this.getAttribute('data-status');
            
            // Update active tab
            for (var j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            // Filter contracts
            filterContracts(status);
        });
    }
}

// Filter contracts by status
function filterContracts(status) {
    var contracts = document.querySelectorAll('.contract-card');
    var visibleCount = 0;
    
    for (var i = 0; i < contracts.length; i++) {
        var contractStatus = contracts[i].getAttribute('data-status');
        
        if (status === 'all' || contractStatus === status) {
            contracts[i].style.display = 'block';
            visibleCount++;
        } else {
            contracts[i].style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
    
    console.log('Filtered contracts:', status, 'visible:', visibleCount);
}

// Setup search functionality
function setupSearch() {
    var searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            searchContracts(searchTerm);
        });
    }
}

// Search contracts
function searchContracts(searchTerm) {
    var contracts = document.querySelectorAll('.contract-card');
    var visibleCount = 0;
    
    for (var i = 0; i < contracts.length; i++) {
        var contract = contracts[i];
        var title = contract.querySelector('.contract-title').textContent.toLowerCase();
        var address = contract.querySelector('.property-address').textContent.toLowerCase();
        var contractId = contract.querySelector('.detail-item .value').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || address.includes(searchTerm) || contractId.includes(searchTerm)) {
            contract.style.display = 'block';
            visibleCount++;
        } else {
            contract.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0 && searchTerm.length > 0) {
        emptyState.style.display = 'block';
        emptyState.querySelector('h3').textContent = 'Không tìm thấy hợp đồng nào';
        emptyState.querySelector('p').textContent = 'Không có hợp đồng nào khớp với từ khóa "' + searchTerm + '".';
    } else if (visibleCount > 0) {
        emptyState.style.display = 'none';
    }
}

// Load contracts data
function loadContractsData() {
    // Simulate API call to load contracts data
    contractsData = {
        'HD2023001': {
            id: 'HD2023001',
            title: 'Hợp đồng thuê phòng trọ Cầu Giấy',
            property: 'Phòng trọ cao cấp Cầu Giấy',
            address: '123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội',
            landlord: 'Anh Minh',
            phone: '0987 654 321',
            price: '2.500.000',
            startDate: '01/12/2023',
            endDate: '01/12/2024',
            status: 'active'
        },
        'HD2022002': {
            id: 'HD2022002',
            title: 'Hợp đồng thuê chung cư mini Mạnh Hà',
            property: 'Chung cư mini Mạnh Hà',
            address: '456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội',
            landlord: 'Chị Lan',
            phone: '0912 345 678',
            price: '10.000.000',
            startDate: '01/01/2023',
            endDate: '01/01/2024',
            status: 'expiring'
        },
        'HD2023003': {
            id: 'HD2023003',
            title: 'Hợp đồng thuê homestay Hạnh Đào',
            property: 'Homestay Hạnh Đão',
            address: '789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội',
            landlord: 'Anh Nam',
            phone: '0901 234 567',
            price: '8.000.000',
            startDate: '15/06/2023',
            endDate: '15/06/2024',
            status: 'active'
        },
        'HD2021001': {
            id: 'HD2021001',
            title: 'Hợp đồng thuê căn hộ Thanh Xuân',
            property: 'Căn hộ Thanh Xuân',
            address: '321 Đường Thanh Xuân, Quận Thanh Xuân, Hà Nội',
            landlord: 'Cô Hoa',
            phone: '0903 456 789',
            price: '12.000.000',
            startDate: '01/01/2022',
            endDate: '01/01/2023',
            status: 'expired'
        }
    };
    
    console.log('Contracts data loaded');
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

// View contract details
function viewContract(contractId) {
    currentContractId = contractId;
    var contractData = contractsData[contractId];
    
    if (!contractData) {
        showToast('Không tìm thấy thông tin hợp đồng', 'error');
        return;
    }
    
    // Show modal
    if (typeof bootstrap !== 'undefined') {
        var modal = new bootstrap.Modal(document.getElementById('contractDetailModal'));
        modal.show();
        
        // Load contract details
        loadContractDetails(contractData);
    } else {
        alert('Chi tiết hợp đồng: ' + contractData.title);
    }
}

// Load contract details into modal
function loadContractDetails(contractData) {
    var content = document.getElementById('contractDetailContent');
    
    // Show loading initially
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải thông tin hợp đồng...</p>
        </div>
    `;
    
    // Simulate loading delay
    setTimeout(function() {
        content.innerHTML = `
            <div class="contract-detail-header">
                <div class="contract-icon">
                    <i class="fas fa-file-contract"></i>
                </div>
                <div class="contract-detail-info">
                    <h4>${contractData.title}</h4>
                    <p>Mã hợp đồng: ${contractData.id}</p>
                </div>
            </div>
            
            <div class="contract-sections">
                <div class="contract-section">
                    <h5><i class="fas fa-home me-2"></i>Thông tin bất động sản</h5>
                    <div class="detail-row">
                        <span class="label">Tên phòng:</span>
                        <span class="value">${contractData.property}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Địa chỉ:</span>
                        <span class="value">${contractData.address}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Giá thuê:</span>
                        <span class="value">${formatCurrency(contractData.price)}/tháng</span>
                    </div>
                </div>
                
                <div class="contract-section">
                    <h5><i class="fas fa-user me-2"></i>Thông tin chủ nhà</h5>
                    <div class="detail-row">
                        <span class="label">Họ tên:</span>
                        <span class="value">${contractData.landlord}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Số điện thoại:</span>
                        <span class="value">${contractData.phone}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Email:</span>
                        <span class="value">landlord@example.com</span>
                    </div>
                </div>
                
                <div class="contract-section">
                    <h5><i class="fas fa-calendar me-2"></i>Thời gian hợp đồng</h5>
                    <div class="detail-row">
                        <span class="label">Ngày bắt đầu:</span>
                        <span class="value">${contractData.startDate}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Ngày kết thúc:</span>
                        <span class="value">${contractData.endDate}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Thời hạn:</span>
                        <span class="value">12 tháng</span>
                    </div>
                </div>
                
                <div class="contract-section">
                    <h5><i class="fas fa-money-bill me-2"></i>Thông tin thanh toán</h5>
                    <div class="detail-row">
                        <span class="label">Tiền thuê:</span>
                        <span class="value">${formatCurrency(contractData.price)}/tháng</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Tiền cọc:</span>
                        <span class="value">${formatCurrency(contractData.price)}</span>
                    </div>
                    <div class="detail-row">
                        <span class="label">Chu kỳ thanh toán:</span>
                        <span class="value">Hàng tháng</span>
                    </div>
                </div>
            </div>
            
            <div class="contract-section mt-4">
                <h5><i class="fas fa-list me-2"></i>Điều khoản hợp đồng</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Người thuê có trách nhiệm bảo quản tài sản</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Thanh toán tiền thuê đúng hạn mỗi tháng</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Không được chuyển nhượng hợp đồng</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Thông báo trước khi kết thúc hợp đồng</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Tuân thủ quy định của chung cư/khu vực</li>
                </ul>
            </div>
        `;
    }, 1000);
}

// Download contract PDF
function downloadContract(contractId) {
    var contractData = contractsData[contractId];
    
    if (!contractData) {
        showToast('Không tìm thấy thông tin hợp đồng', 'error');
        return;
    }
    
    // Show download modal
    if (typeof bootstrap !== 'undefined') {
        var downloadModal = new bootstrap.Modal(document.getElementById('downloadModal'));
        downloadModal.show();
        
        // Simulate PDF generation progress
        simulateDownloadProgress(contractData);
    } else {
        // Fallback for browsers without Bootstrap
        alert('Đang tải PDF hợp đồng: ' + contractData.title);
        simulatePDFDownload(contractData);
    }
}

// Simulate download progress
function simulateDownloadProgress(contractData) {
    var progressBar = document.getElementById('downloadProgress');
    var progress = 0;
    
    var interval = setInterval(function() {
        progress += Math.random() * 20;
        if (progress >= 100) {
            progress = 100;
            clearInterval(interval);
            
            // Complete download
            setTimeout(function() {
                hideDownloadModal();
                completePDFDownload(contractData);
            }, 500);
        }
        
        if (progressBar) {
            progressBar.style.width = progress + '%';
        }
    }, 200);
}

// Hide download modal
function hideDownloadModal() {
    if (typeof bootstrap !== 'undefined') {
        var downloadModal = bootstrap.Modal.getInstance(document.getElementById('downloadModal'));
        if (downloadModal) {
            downloadModal.hide();
        }
    }
}

// Complete PDF download
function completePDFDownload(contractData) {
    // Create a blob with contract data (simulate PDF)
    var pdfContent = generatePDFContent(contractData);
    var blob = new Blob([pdfContent], { type: 'application/pdf' });
    
    // Create download link
    var url = window.URL.createObjectURL(blob);
    var link = document.createElement('a');
    link.href = url;
    link.download = 'hop-dong-' + contractData.id.toLowerCase() + '.pdf';
    
    // Trigger download
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    
    // Clean up
    window.URL.revokeObjectURL(url);
    
    showToast('Đã tải xuống hợp đồng thành công!', 'success');
}

// Generate PDF content (simplified)
function generatePDFContent(contractData) {
    return `
HỢP ĐỒNG THUÊ NHÀ
==================

Mã hợp đồng: ${contractData.id}
Tên hợp đồng: ${contractData.title}

THÔNG TIN BẤT ĐỘNG SẢN:
- Tên phòng: ${contractData.property}
- Địa chỉ: ${contractData.address}
- Giá thuê: ${formatCurrency(contractData.price)}/tháng

THÔNG TIN CHỦ NHÀ:
- Họ tên: ${contractData.landlord}
- Số điện thoại: ${contractData.phone}

THỜI GIAN HỢP ĐỒNG:
- Ngày bắt đầu: ${contractData.startDate}
- Ngày kết thúc: ${contractData.endDate}

ĐIỀU KHOẢN HỢP ĐỒNG:
1. Người thuê có trách nhiệm bảo quản tài sản
2. Thanh toán tiền thuê đúng hạn mỗi tháng
3. Không được chuyển nhượng hợp đồng
4. Thông báo trước khi kết thúc hợp đồng
5. Tuân thủ quy định của chung cư/khu vực

Ngày tạo: ${new Date().toLocaleDateString('vi-VN')}
    `;
}

// Simulate PDF download fallback
function simulatePDFDownload(contractData) {
    setTimeout(function() {
        completePDFDownload(contractData);
    }, 2000);
}

// Download current contract from modal
function downloadCurrentContract() {
    if (currentContractId) {
        downloadContract(currentContractId);
    }
}

// Print contract
function printContract() {
    if (currentContractId) {
        var contractData = contractsData[currentContractId];
        if (contractData) {
            // Open print window with contract details
            var printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                <head>
                    <title>In hợp đồng - ${contractData.id}</title>
                    <style>
                        body { font-family: Arial, sans-serif; padding: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .section { margin-bottom: 20px; }
                        .section h3 { color: #333; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
                        .detail { margin: 5px 0; }
                        .label { font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>HỢP ĐỒNG THUÊ NHÀ</h1>
                        <p>Mã hợp đồng: ${contractData.id}</p>
                    </div>
                    
                    <div class="section">
                        <h3>Thông tin bất động sản</h3>
                        <div class="detail"><span class="label">Tên phòng:</span> ${contractData.property}</div>
                        <div class="detail"><span class="label">Địa chỉ:</span> ${contractData.address}</div>
                        <div class="detail"><span class="label">Giá thuê:</span> ${formatCurrency(contractData.price)}/tháng</div>
                    </div>
                    
                    <div class="section">
                        <h3>Thông tin chủ nhà</h3>
                        <div class="detail"><span class="label">Họ tên:</span> ${contractData.landlord}</div>
                        <div class="detail"><span class="label">Số điện thoại:</span> ${contractData.phone}</div>
                    </div>
                    
                    <div class="section">
                        <h3>Thời gian hợp đồng</h3>
                        <div class="detail"><span class="label">Ngày bắt đầu:</span> ${contractData.startDate}</div>
                        <div class="detail"><span class="label">Ngày kết thúc:</span> ${contractData.endDate}</div>
                    </div>
                </body>
                </html>
            `);
            printWindow.document.close();
            printWindow.print();
        }
    }
}

// Renew contract
function renewContract(contractId) {
    currentContractId = contractId;
    var contractData = contractsData[contractId];
    
    if (!contractData) {
        showToast('Không tìm thấy thông tin hợp đồng', 'error');
        return;
    }
    
    // Show renewal modal
    if (typeof bootstrap !== 'undefined') {
        var renewalModal = new bootstrap.Modal(document.getElementById('renewalModal'));
        renewalModal.show();
    } else {
        if (confirm('Bạn có muốn gia hạn hợp đồng ' + contractData.id + ' không?')) {
            confirmRenewal();
        }
    }
}

// Confirm contract renewal
function confirmRenewal() {
    var period = document.getElementById('renewalPeriod') ? document.getElementById('renewalPeriod').value : '12';
    var note = document.getElementById('renewalNote') ? document.getElementById('renewalNote').value : '';
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var renewalModal = bootstrap.Modal.getInstance(document.getElementById('renewalModal'));
        if (renewalModal) {
            renewalModal.hide();
        }
    }
    
    // Show loading toast
    showToast('Đang xử lý yêu cầu gia hạn...', 'info');
    
    // Simulate API call
    setTimeout(function() {
        showToast('Yêu cầu gia hạn đã được gửi thành công! Chủ nhà sẽ liên hệ với bạn sớm.', 'success');
        
        // Update contract data (simulate)
        if (contractsData[currentContractId]) {
            console.log('Contract renewal requested:', {
                contractId: currentContractId,
                period: period + ' tháng',
                note: note
            });
        }
    }, 2000);
}

// Format currency
function formatCurrency(amount) {
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
