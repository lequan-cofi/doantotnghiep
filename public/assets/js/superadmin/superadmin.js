// Super Admin JavaScript - Standalone System

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Super Admin system
    initSuperAdminSidebar();
    initSuperAdminNavigation();
    initSuperAdminCharts();
    initSuperAdminNotifications();
    initSuperAdminMobileMenu();
    hidePreloader();
});

// Super Admin Sidebar Management
function initSuperAdminSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    
    // Desktop sidebar toggle
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            localStorage.setItem('superadminSidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
    }
    
    // Mobile sidebar toggle
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
            
            if (sidebar.classList.contains('mobile-open')) {
                // Add overlay
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.addEventListener('click', function() {
                    sidebar.classList.remove('mobile-open');
                    document.body.removeChild(overlay);
                });
                document.body.appendChild(overlay);
            } else {
                // Remove overlay
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    document.body.removeChild(overlay);
                }
            }
        });
    }
    
    // Restore sidebar state
    const savedState = localStorage.getItem('superadminSidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }
}

// Super Admin Navigation
function initSuperAdminNavigation() {
    // Handle navigation clicks
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Remove active from all items
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            
            // Add active to clicked item
            this.classList.add('active');
            
            // Close mobile sidebar if open
            const sidebar = document.getElementById('sidebar');
            if (sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                const overlay = document.querySelector('.sidebar-overlay');
                if (overlay) {
                    document.body.removeChild(overlay);
                }
            }
        });
    });
    
    // Handle disabled items
    document.querySelectorAll('.nav-item.disabled').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof Notify !== 'undefined') {
                Notify.toast('Tính năng này sẽ được phát triển trong phiên bản tiếp theo', 'info');
            } else {
                alert('Tính năng này sẽ được phát triển trong phiên bản tiếp theo');
            }
        });
    });
}

// Super Admin Charts
function initSuperAdminCharts() {
    // Set default chart options
    Chart.defaults.font.family = 'Segoe UI, Tahoma, Geneva, Verdana, sans-serif';
    Chart.defaults.color = '#2c3e50';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;
    Chart.defaults.plugins.legend.labels.padding = 20;
    
    // Super Admin color palette
    window.superAdminColors = {
        primary: '#2c3e50',
        secondary: '#34495e',
        accent: '#e74c3c',
        success: '#27ae60',
        warning: '#f39c12',
        info: '#3498db',
        light: '#ecf0f1',
        dark: '#2c3e50'
    };
}

// Super Admin Notifications
function initSuperAdminNotifications() {
    // Override notification styles for Super Admin
    if (typeof Notify !== 'undefined') {
        // Store original toast method
        const originalToast = Notify.toast;
        
        // Override with Super Admin styling
        Notify.toast = function(message, type = 'info', title = '') {
            const toastElement = originalToast.call(this, message, type, title);
            
            if (toastElement) {
                // Add Super Admin specific classes
                toastElement.classList.add('superadmin-toast');
                
                // Add crown icon for Super Admin notifications
                const icon = toastElement.querySelector('.toast-icon');
                if (icon && type === 'success') {
                    icon.innerHTML = '<i class="fas fa-crown"></i>';
                }
            }
            
            return toastElement;
        };
    }
}

// Super Admin Mobile Menu
function initSuperAdminMobileMenu() {
    // Handle window resize
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 992) {
            sidebar.classList.remove('mobile-open');
            const overlay = document.querySelector('.sidebar-overlay');
            if (overlay) {
                document.body.removeChild(overlay);
            }
        }
    });
}

// Hide preloader
function hidePreloader() {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.style.opacity = '0';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 300);
        }, 500);
    }
}

// Super Admin Cache Management
function clearSuperAdminCache() {
    if (typeof Notify !== 'undefined') {
        Notify.confirm(
            'Làm mới dữ liệu Super Admin',
            'Bạn có chắc chắn muốn làm mới tất cả dữ liệu Super Admin? Thao tác này sẽ xóa cache và tải lại dữ liệu mới nhất.',
            function() {
                // Show Super Admin loading
                const loadingToast = Notify.toast('Đang làm mới dữ liệu Super Admin...', 'info');
                
                // Make AJAX request
                fetch('/superadmin/clear-cache', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Notify.toast('Dữ liệu Super Admin đã được làm mới thành công!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Notify.toast('Có lỗi xảy ra khi làm mới dữ liệu Super Admin', 'error');
                    }
                })
                .catch(error => {
                    console.error('Super Admin Cache Error:', error);
                    Notify.toast('Có lỗi xảy ra khi làm mới dữ liệu Super Admin', 'error');
                });
            }
        );
    } else {
        // Fallback if Notify is not available
        if (confirm('Bạn có chắc chắn muốn làm mới tất cả dữ liệu Super Admin?')) {
            fetch('/superadmin/clear-cache', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Dữ liệu Super Admin đã được làm mới thành công!');
                    window.location.reload();
                } else {
                    alert('Có lỗi xảy ra khi làm mới dữ liệu Super Admin');
                }
            })
            .catch(error => {
                console.error('Super Admin Cache Error:', error);
                alert('Có lỗi xảy ra khi làm mới dữ liệu Super Admin');
            });
        }
    }
}

// Super Admin Organization Management
function toggleOrganizationStatus(organizationId, newStatus) {
    const action = newStatus ? 'kích hoạt' : 'tạm dừng';
    
    if (typeof Notify !== 'undefined') {
        Notify.confirm(
            `Bạn có chắc chắn muốn ${action} tổ chức này?`,
            function() {
                Notify.toast('Đang cập nhật trạng thái tổ chức...', 'info');
                
                fetch(`/superadmin/organizations/${organizationId}/toggle-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Notify.toast(`Tổ chức đã được ${action} thành công!`, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Notify.toast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Organization Toggle Error:', error);
                    Notify.toast('Có lỗi xảy ra khi cập nhật trạng thái tổ chức', 'error');
                });
            }
        );
    }
}

// Super Admin Delete Organization
function deleteOrganization(organizationId, organizationName) {
    if (typeof Notify !== 'undefined') {
        Notify.confirmDelete(
            `Bạn có chắc chắn muốn xóa tổ chức "${organizationName}"? Hành động này không thể hoàn tác và sẽ ảnh hưởng đến tất cả dữ liệu liên quan.`,
            function() {
                Notify.toast('Đang xóa tổ chức...', 'info');
                
                fetch(`/superadmin/organizations/${organizationId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Notify.toast('Tổ chức đã được xóa thành công!', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    } else {
                        Notify.toast(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Organization Delete Error:', error);
                    Notify.toast('Có lỗi xảy ra khi xóa tổ chức', 'error');
                });
            }
        );
    }
}

// Super Admin Performance Monitoring
function initSuperAdminPerformance() {
    // Monitor page load performance
    window.addEventListener('load', function() {
        const loadTime = performance.now();
        console.log(`Super Admin Dashboard loaded in ${loadTime.toFixed(2)}ms`);
        
        // Send performance data to server (optional)
        if (loadTime > 3000) {
            console.warn('Super Admin Dashboard load time is slow:', loadTime);
        }
    });
}

// Initialize performance monitoring
document.addEventListener('DOMContentLoaded', function() {
    initSuperAdminPerformance();
});

// Super Admin Utility Functions
window.SuperAdmin = {
    // Clear cache
    clearCache: clearSuperAdminCache,
    
    // Organization management
    toggleOrganizationStatus: toggleOrganizationStatus,
    deleteOrganization: deleteOrganization,
    
    // Performance monitoring
    getPerformanceMetrics: function() {
        return {
            loadTime: performance.now(),
            memoryUsage: performance.memory ? performance.memory.usedJSHeapSize : 'N/A',
            timestamp: new Date().toISOString()
        };
    },
    
    // System info
    getSystemInfo: function() {
        return {
            userAgent: navigator.userAgent,
            platform: navigator.platform,
            language: navigator.language,
            cookieEnabled: navigator.cookieEnabled,
            onLine: navigator.onLine
        };
    }
};