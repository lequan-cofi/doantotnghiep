<div class="dashboard-container">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <i class="fas fa-user-tie"></i>
                <span class="logo-text">Agent Panel</span>
            </div>
            <button class="sidebar-toggle" id="sidebarToggle">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('agent.dashboard') }}" class="nav-item {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tổng quan</span>
            </a>
            
            <a href="{{ route('agent.properties.index') }}" class="nav-item {{ request()->routeIs('agent.properties.*') ? 'active' : '' }}">
                <i class="fas fa-building"></i>
                <span>Bất động sản</span>
            </a>
            
            <a href="{{ route('agent.profile') }}" class="nav-item {{ request()->routeIs('agent.profile') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Hồ sơ cá nhân</span>
            </a>
        </nav>
    </aside>



<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const sidebar = document.getElementById('sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
        });
    }
    
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('mobile-open');
        });
    }
    
    // Close mobile menu when clicking outside
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                sidebar.classList.remove('mobile-open');
            }
        }
    });
});
</script>
