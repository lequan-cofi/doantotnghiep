document.addEventListener('DOMContentLoaded', function() {
    // Cache original dashboard markup for quick restore
    try {
        const contentEl = document.getElementById('content');
        if (contentEl && !window.__dashboardInitialHTML) {
            window.__dashboardInitialHTML = contentEl.innerHTML;
        }
    } catch (_) {}
    // Initialize dashboard
    initSidebar();
    initNavigation();
    initChart();
    updateStats();
    
    // Auto-refresh data every 30 seconds
    setInterval(updateStats, 30000);
});

// Sidebar functionality
function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        // Close all submenus when collapsing
        if (sidebar.classList.contains('collapsed')) {
            document.querySelectorAll('.submenu').forEach(menu => {
                menu.classList.remove('open');
            });
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
        }
        
        // Save state to localStorage
        const isCollapsed = sidebar.classList.contains('collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        // Add animation class for smooth transition
        sidebar.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
    });
    
    // Restore sidebar state from localStorage
    const savedState = localStorage.getItem('sidebarCollapsed');
    if (savedState === 'true') {
        sidebar.classList.add('collapsed');
    }
    
    // Mobile sidebar toggle
    if (window.innerWidth <= 768) {
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && !e.target.closest('.sidebar-toggle')) {
                sidebar.classList.remove('open');
            }
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
        }
    });
}

// Navigation functionality
function initNavigation() {
    const navItems = document.querySelectorAll('.nav-item');
    const submenuItems = document.querySelectorAll('.submenu-item');
    
    // Handle main navigation items
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            // Check if this item has submenu
            if (this.classList.contains('has-submenu')) {
                e.preventDefault();
                toggleSubmenu(this);
            } else {
                e.preventDefault();
                
                // Remove active class from all items
                navItems.forEach(nav => nav.classList.remove('active'));
                submenuItems.forEach(sub => sub.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Get page name from data attribute
                const page = this.getAttribute('data-page');
                
                // Load page content (placeholder for now)
                loadPageContent(page);
            }
        });
    });
    
    // Handle submenu items
    submenuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            navItems.forEach(nav => nav.classList.remove('active'));
            submenuItems.forEach(sub => sub.classList.remove('active'));
            
            // Add active class to clicked submenu item
            this.classList.add('active');
            
            // Get page name from data attribute
            const page = this.getAttribute('data-page');
            
            // Load page content (placeholder for now)
            loadPageContent(page);
        });
    });
}

// Toggle submenu functionality
function toggleSubmenu(navItem) {
    const submenu = navItem.nextElementSibling;
    const isOpen = submenu.classList.contains('open');
    
    // Close all other submenus
    document.querySelectorAll('.submenu').forEach(menu => {
        menu.classList.remove('open');
    });
    
    // Remove active class from all nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Toggle current submenu
    if (!isOpen) {
        submenu.classList.add('open');
        navItem.classList.add('active');
    }
}

// Load page content based on navigation - Router approach
function loadPageContent(page) {
    // Define routes mapping
    const routes = {
        'dashboard': '/agent/dashboard',
        'rooms': '/agent/rooms',
        'rooms-list': '/agent/rooms',
        'rooms-create': '/agent/rooms/create',
        'rooms-categories': '/agent/rooms/categories',
        'users': '/agent/users',
        'users-list': '/agent/users',
        'users-tenants': '/agent/users/tenants',
        'users-owners': '/agent/users/owners',
        'posts': '/agent/posts',
        'posts-list': '/agent/posts',
        'posts-pending': '/agent/posts/pending',
        'posts-news': '/agent/posts/news',
        'revenue': '/agent/revenue',
        'analytics': '/agent/analytics',
        'appointments': '/agent/appointments',
        'messages': '/agent/messages',
        'notifications': '/agent/notifications',
        'settings': '/agent/settings',
        'settings-general': '/agent/settings/general',
        'settings-payment': '/agent/settings/payment',
        'settings-email': '/agent/settings/email'
    };
    
    // Get the route for the page
    const route = routes[page];
    
    if (route) {
        // Navigate to the route
        window.location.href = route;
    } else {
        // Fallback to dashboard
        console.warn(`Route not found for page: ${page}`);
        window.location.href = '/agent/dashboard';
    }
}

function restoreDashboard(){
    const content = document.getElementById('content');
    // 1) Try restore from cached HTML
    if (window.__dashboardInitialHTML && typeof window.__dashboardInitialHTML === 'string' && window.__dashboardInitialHTML.length) {
        content.innerHTML = window.__dashboardInitialHTML;
        initChart();
        updateStats();
        return;
    }

    // 2) Fallback: fetch server-rendered dashboard and extract #content
    fetch('/agent/dashboard', { headers: { 'X-Requested-With': 'XMLHttpRequest' }})
        .then(r => r.text())
        .then(html => {
            const tmp = document.createElement('div');
            tmp.innerHTML = html;
            const newContent = tmp.querySelector('#content');
            if (newContent) {
                content.innerHTML = newContent.innerHTML;
                // cache for next time
                window.__dashboardInitialHTML = newContent.innerHTML;
                initChart();
                updateStats();
            }
        })
        .catch(() => {
            // Soft fallback: simple message
            content.innerHTML = '<div class="alert alert-warning">Không thể tải lại dashboard. Vui lòng tải lại trang.</div>';
        });
}

// Router-based navigation - content creation functions removed
// All navigation now uses actual routes instead of dynamic content

// Initialize chart (placeholder)
function initChart() {
    const canvas = document.getElementById('revenueChart');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        
        // Simple chart placeholder
        ctx.fillStyle = '#dc2626';
        ctx.fillRect(50, 150, 300, 30);
        
        ctx.fillStyle = '#1e293b';
        ctx.font = '14px Arial';
        ctx.fillText('Biểu đồ doanh thu sẽ được hiển thị ở đây', 50, 100);
        ctx.fillText('Tích hợp thư viện Chart.js để có biểu đồ tương tác', 50, 120);
    }
}

// Update stats with animation
function updateStats() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(stat => {
        // Add pulse animation
        stat.style.transform = 'scale(1.05)';
        setTimeout(() => {
            stat.style.transform = 'scale(1)';
        }, 200);
    });
}

// Utility functions
function formatNumber(num) {
    return new Intl.NumberFormat('vi-VN').format(num);
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND'
    }).format(amount);
}

function timeAgo(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (minutes < 60) {
        return `${minutes} phút trước`;
    } else if (hours < 24) {
        return `${hours} giờ trước`;
    } else {
        return `${days} ngày trước`;
    }
}

// Handle responsive behavior
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('sidebar');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('collapsed');
    }
});

// Add some interactivity to quick action buttons
document.addEventListener('click', function(e) {
    if (e.target.matches('.quick-action-btn') || e.target.closest('.quick-action-btn')) {
        const button = e.target.matches('.quick-action-btn') ? e.target : e.target.closest('.quick-action-btn');
        
        // Add click effect
        button.style.transform = 'scale(0.95)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);
        
        // Show notification (placeholder)
        console.log('Quick action clicked:', button.textContent.trim());
    }
});

// Add CSS for page header
const style = document.createElement('style');
style.textContent = `
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 32px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .page-header h2 {
        font-size: 28px;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }
    
    .stat-value {
        transition: transform 0.2s ease;
    }
    
    .quick-action-btn {
        transition: transform 0.15s ease;
    }
`;
document.head.appendChild(style);
