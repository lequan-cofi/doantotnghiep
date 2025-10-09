// Notifications Page JavaScript
var notificationsData = {};
var unreadCount = 5;

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeNotifications();
    setupFilters();
    setupSearch();
    loadNotificationsData();
    setupHeaderNotifications();
});

// Initialize notifications functionality
function initializeNotifications() {
    console.log('Notifications page initialized');
    
    // Setup tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Animate stat cards
    animateStatCards();
    
    // Update header notification count
    updateHeaderNotificationCount();
}

// Setup header notifications
function setupHeaderNotifications() {
    // Add click handlers for header notification items
    var headerNotificationItems = document.querySelectorAll('.notification-item');
    
    for (var i = 0; i < headerNotificationItems.length; i++) {
        headerNotificationItems[i].addEventListener('click', function() {
            // Navigate to appropriate page based on notification type
            var iconElement = this.querySelector('.item-icon');
            if (iconElement.classList.contains('urgent')) {
                window.location.href = '/invoices';
            } else if (iconElement.classList.contains('contract')) {
                window.location.href = '/contracts';
            } else if (iconElement.classList.contains('review')) {
                window.location.href = '/reviews';
            } else if (iconElement.classList.contains('appointment')) {
                window.location.href = '/appointments';
            }
        });
    }
}

// Update header notification count
function updateHeaderNotificationCount() {
    var badge = document.querySelector('.notification-badge');
    if (badge) {
        badge.textContent = unreadCount;
        
        if (unreadCount === 0) {
            badge.style.display = 'none';
        } else {
            badge.style.display = 'flex';
        }
    }
}

// Mark all header notifications as read
function markAllHeaderAsRead() {
    var headerItems = document.querySelectorAll('.notification-item.unread');
    
    for (var i = 0; i < headerItems.length; i++) {
        headerItems[i].classList.remove('unread');
    }
    
    // Update count
    unreadCount = Math.max(0, unreadCount - headerItems.length);
    updateHeaderNotificationCount();
    
    showToast('Đã đánh dấu tất cả thông báo đã đọc', 'success');
}

// Setup filter functionality
function setupFilters() {
    var filterTabs = document.querySelectorAll('.filter-tab');
    var typeFilter = document.getElementById('typeFilter');
    
    // Status filter tabs
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            var status = this.getAttribute('data-status');
            
            // Update active tab
            for (var j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            // Filter notifications
            filterNotifications();
        });
    }
    
    // Type filter
    if (typeFilter) {
        typeFilter.addEventListener('change', function() {
            filterNotifications();
        });
    }
}

// Filter notifications by status and type
function filterNotifications() {
    var activeTab = document.querySelector('.filter-tab.active');
    var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
    var typeFilter = document.getElementById('typeFilter');
    var selectedType = typeFilter ? typeFilter.value : '';
    
    var notifications = document.querySelectorAll('.notification-card');
    var visibleCount = 0;
    
    for (var i = 0; i < notifications.length; i++) {
        var notification = notifications[i];
        var notificationStatus = notification.getAttribute('data-status');
        var notificationType = notification.getAttribute('data-type');
        
        var statusMatch = (status === 'all' || 
                          (status === 'unread' && notificationStatus === 'unread') ||
                          (status === 'read' && notificationStatus === 'read') ||
                          (status === 'important' && notification.classList.contains('important')));
        
        var typeMatch = (!selectedType || notificationType === selectedType);
        
        if (statusMatch && typeMatch) {
            notification.style.display = 'flex';
            visibleCount++;
        } else {
            notification.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
    
    console.log('Filtered notifications:', status, selectedType, 'visible:', visibleCount);
}

// Setup search functionality
function setupSearch() {
    var searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            searchNotifications(searchTerm);
        });
    }
}

// Search notifications
function searchNotifications(searchTerm) {
    var notifications = document.querySelectorAll('.notification-card');
    var visibleCount = 0;
    
    for (var i = 0; i < notifications.length; i++) {
        var notification = notifications[i];
        var title = notification.querySelector('.notification-title').textContent.toLowerCase();
        var message = notification.querySelector('.notification-message').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || message.includes(searchTerm)) {
            notification.style.display = 'flex';
            visibleCount++;
        } else {
            notification.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0 && searchTerm.length > 0) {
        emptyState.style.display = 'block';
        emptyState.querySelector('h3').textContent = 'Không tìm thấy thông báo nào';
        emptyState.querySelector('p').textContent = 'Không có thông báo nào khớp với từ khóa "' + searchTerm + '".';
    } else if (visibleCount > 0) {
        emptyState.style.display = 'none';
    }
}

// Load notifications data
function loadNotificationsData() {
    // Simulate API call to load notifications data
    notificationsData = {
        'notif1': {
            id: 'notif1',
            type: 'payment',
            title: 'Hóa đơn quá hạn thanh toán',
            message: 'Hóa đơn HD2023001 cho phòng trọ Cầu Giấy đã quá hạn thanh toán.',
            time: '2 giờ trước',
            status: 'unread',
            important: true,
            data: {
                invoiceId: 'HD2023001',
                amount: '2.500.000 VNĐ',
                overdueDays: 3
            }
        },
        'notif2': {
            id: 'notif2',
            type: 'contract',
            title: 'Hợp đồng sắp hết hạn',
            message: 'Hợp đồng HD2022002 cho chung cư mini Mạnh Hà sẽ hết hạn trong 7 ngày.',
            time: '1 ngày trước',
            status: 'unread',
            important: false,
            data: {
                contractId: 'HD2022002',
                expiryDate: '01/01/2024',
                landlordPhone: '0912 345 678'
            }
        },
        'notif3': {
            id: 'notif3',
            type: 'appointment',
            title: 'Lịch hẹn được xác nhận',
            message: 'Chủ nhà đã xác nhận lịch hẹn xem phòng Homestay Hạnh Đào.',
            time: '5 giờ trước',
            status: 'read',
            important: false,
            data: {
                appointmentId: 'APP001',
                time: '28/12/2023, 14:00 - 16:00',
                contact: 'Anh Nam - 0901 234 567'
            }
        }
    };
    
    console.log('Notifications data loaded');
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

// Mark notification as read
function markAsRead(button) {
    var notificationCard = button.closest('.notification-card');
    
    if (notificationCard && notificationCard.classList.contains('unread')) {
        // Update UI
        notificationCard.classList.remove('unread');
        notificationCard.setAttribute('data-status', 'read');
        
        // Update button
        button.style.background = 'var(--border)';
        button.style.color = 'var(--muted)';
        button.style.cursor = 'default';
        
        // Update count
        unreadCount = Math.max(0, unreadCount - 1);
        updateHeaderNotificationCount();
        
        // Show feedback
        showToast('Đã đánh dấu thông báo đã đọc', 'success');
    }
}

// Mark all notifications as read
function markAllAsRead() {
    var unreadCards = document.querySelectorAll('.notification-card.unread');
    
    if (unreadCards.length === 0) {
        showToast('Tất cả thông báo đã được đọc', 'info');
        return;
    }
    
    // Update all unread notifications
    for (var i = 0; i < unreadCards.length; i++) {
        var card = unreadCards[i];
        card.classList.remove('unread');
        card.setAttribute('data-status', 'read');
        
        // Update button
        var button = card.querySelector('.btn-mark-read');
        if (button) {
            button.style.background = 'var(--border)';
            button.style.color = 'var(--muted)';
            button.style.cursor = 'default';
        }
    }
    
    // Update count
    unreadCount = 0;
    updateHeaderNotificationCount();
    
    showToast('Đã đánh dấu tất cả thông báo đã đọc', 'success');
}

// View invoice detail
function viewInvoiceDetail(invoiceId) {
    window.location.href = '/invoices';
}

// Thank for reply
function thankForReply(reviewId) {
    showToast('Đang gửi lời cảm ơn...', 'info');
    
    setTimeout(function() {
        showToast('Đã gửi lời cảm ơn đến chủ nhà!', 'success');
    }, 1500);
}

// Set reminder
function setReminder(appointmentId) {
    showToast('Đã đặt nhắc nhở cho lịch hẹn', 'success');
}

// Download receipt
function downloadReceipt(invoiceId) {
    showToast('Đang tải biên lai...', 'info');
    
    setTimeout(function() {
        // Simulate download
        var link = document.createElement('a');
        link.href = 'data:text/plain;charset=utf-8,' + encodeURIComponent('Biên lai thanh toán - ' + invoiceId);
        link.download = 'bien-lai-' + invoiceId.toLowerCase() + '.txt';
        link.click();
        
        showToast('Đã tải xuống biên lai thành công!', 'success');
    }, 2000);
}

// Save notification settings
function saveSettings() {
    // Collect settings
    var settings = {};
    var toggles = document.querySelectorAll('.setting-toggle input[type="checkbox"]');
    var selects = document.querySelectorAll('.settings-section select');
    
    for (var i = 0; i < toggles.length; i++) {
        var toggle = toggles[i];
        var label = toggle.parentElement.textContent.trim();
        settings[label] = toggle.checked;
    }
    
    for (var j = 0; j < selects.length; j++) {
        var select = selects[j];
        var label = select.previousElementSibling.textContent.trim();
        settings[label] = select.value;
    }
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var settingsModal = bootstrap.Modal.getInstance(document.getElementById('settingsModal'));
        if (settingsModal) {
            settingsModal.hide();
        }
    }
    
    // Show success
    showToast('Đã lưu cài đặt thông báo', 'success');
    
    console.log('Notification settings saved:', settings);
}

// Add new notification (for real-time updates)
function addNotification(notificationData) {
    // Create notification element
    var notificationHtml = createNotificationHTML(notificationData);
    
    // Add to list
    var notificationsList = document.querySelector('.notifications-list');
    if (notificationsList) {
        notificationsList.insertAdjacentHTML('afterbegin', notificationHtml);
    }
    
    // Update count
    if (notificationData.status === 'unread') {
        unreadCount++;
        updateHeaderNotificationCount();
    }
    
    // Add to header dropdown
    addToHeaderDropdown(notificationData);
    
    // Show toast
    showToast('Bạn có thông báo mới: ' + notificationData.title, 'info');
}

// Create notification HTML
function createNotificationHTML(data) {
    var iconClass = getIconClass(data.type, data.urgent);
    var statusClass = data.status === 'unread' ? 'unread' : 'read';
    var importantClass = data.important ? 'important' : '';
    
    return `
        <div class="notification-card ${statusClass} ${importantClass}" data-status="${data.status}" data-type="${data.type}">
            <div class="notification-icon ${data.type} ${data.urgent ? 'urgent' : ''}">
                <i class="${iconClass}"></i>
            </div>
            <div class="notification-content">
                <div class="notification-header">
                    <h4 class="notification-title">${data.title}</h4>
                    <div class="notification-time">${data.time}</div>
                </div>
                <p class="notification-message">${data.message}</p>
                ${data.details ? `<div class="notification-details">${data.details}</div>` : ''}
                ${data.actions ? `<div class="notification-actions">${data.actions}</div>` : ''}
            </div>
            <div class="notification-status">
                <button class="btn-mark-read" onclick="markAsRead(this)" title="Đánh dấu đã đọc">
                    <i class="fas fa-check"></i>
                </button>
            </div>
        </div>
    `;
}

// Get icon class for notification type
function getIconClass(type, urgent) {
    var icons = {
        'payment': urgent ? 'fas fa-exclamation-triangle' : 'fas fa-credit-card',
        'contract': 'fas fa-file-contract',
        'appointment': 'fas fa-calendar-alt',
        'review': 'fas fa-star',
        'maintenance': 'fas fa-tools',
        'system': 'fas fa-cog'
    };
    return icons[type] || 'fas fa-bell';
}

// Add notification to header dropdown
function addToHeaderDropdown(data) {
    var headerItems = document.querySelector('.notification-items');
    if (headerItems) {
        var itemHtml = `
            <div class="notification-item unread">
                <div class="item-icon ${data.type}">
                    <i class="${getIconClass(data.type, data.urgent)}"></i>
                </div>
                <div class="item-content">
                    <div class="item-title">${data.title}</div>
                    <div class="item-message">${data.message.substring(0, 50)}...</div>
                    <div class="item-time">${data.time}</div>
                </div>
            </div>
        `;
        
        headerItems.insertAdjacentHTML('afterbegin', itemHtml);
        
        // Remove oldest item if more than 5
        var items = headerItems.querySelectorAll('.notification-item');
        if (items.length > 5) {
            items[items.length - 1].remove();
        }
    }
}

// Simulate real-time notifications
function simulateRealTimeNotifications() {
    var sampleNotifications = [
        {
            type: 'payment',
            title: 'Nhắc nhở thanh toán',
            message: 'Hóa đơn HD2023005 sẽ đến hạn trong 2 ngày',
            time: 'Vừa xong',
            status: 'unread',
            important: false
        },
        {
            type: 'maintenance',
            title: 'Cập nhật sửa chữa',
            message: 'Kỹ thuật viên đang trên đường đến phòng của bạn',
            time: 'Vừa xong',
            status: 'unread',
            important: false
        },
        {
            type: 'appointment',
            title: 'Lịch hẹn mới',
            message: 'Bạn có lịch hẹn xem phòng mới được tạo',
            time: 'Vừa xong',
            status: 'unread',
            important: false
        }
    ];
    
    // Add random notification every 30 seconds for demo
    setInterval(function() {
        if (Math.random() > 0.7) { // 30% chance
            var randomNotif = sampleNotifications[Math.floor(Math.random() * sampleNotifications.length)];
            addNotification(randomNotif);
        }
    }, 30000);
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

// Start real-time notifications simulation
// simulateRealTimeNotifications();
