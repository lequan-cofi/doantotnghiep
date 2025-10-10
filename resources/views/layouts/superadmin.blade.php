<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Super Admin - SaaS Platform')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    
    <!-- Custom Super Admin CSS -->
    <link href="{{ asset('assets/css/superadmin/superadmin.css') }}?v={{ time() }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body class="superadmin-body">
    <!-- Preloader -->
    <div id="preloader" class="preloader">
        <div class="preloader-spinner">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="superadmin-container">
        <!-- Sidebar -->
        <aside class="superadmin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-brand">
                    <i class="fas fa-crown text-warning"></i>
                    <span class="brand-text">Super Admin</span>
                </div>
                <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <!-- Dashboard -->
                <div class="nav-section">
                    <a href="{{ route('superadmin.dashboard') }}" class="nav-item {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <!-- Organizations Management -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-building"></i>
                        <span>Organizations</span>
                    </div>
                    <a href="{{ route('superadmin.organizations.index') }}" class="nav-item {{ request()->routeIs('superadmin.organizations.*') ? 'active' : '' }}">
                        <i class="fas fa-list"></i>
                        <span>All Organizations</span>
                    </a>
                    <a href="{{ route('superadmin.organizations.create') }}" class="nav-item">
                        <i class="fas fa-plus"></i>
                        <span>Add Organization</span>
                    </a>
                </div>

                <!-- Users Management - TO BE IMPLEMENTED -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </div>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-list"></i>
                        <span>All Users</span>
                    </a>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-user-plus"></i>
                        <span>Add User</span>
                    </a>
                </div>

                <!-- Revenue & Analytics - TO BE IMPLEMENTED -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </div>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Revenue Analytics</span>
                    </a>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-chart-bar"></i>
                        <span>Growth Metrics</span>
                    </a>
                </div>

                <!-- System Management - TO BE IMPLEMENTED -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-cogs"></i>
                        <span>System</span>
                    </div>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-heartbeat"></i>
                        <span>System Health</span>
                    </a>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-file-alt"></i>
                        <span>System Logs</span>
                    </a>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-sliders-h"></i>
                        <span>Settings</span>
                    </a>
                </div>

                <!-- Support & Tickets - TO BE IMPLEMENTED -->
                <div class="nav-section">
                    <div class="nav-section-title">
                        <i class="fas fa-headset"></i>
                        <span>Support</span>
                    </div>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Support Tickets</span>
                    </a>
                    <a href="#" class="nav-item disabled" onclick="return false;">
                        <i class="fas fa-history"></i>
                        <span>Audit Logs</span>
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="superadmin-main">
            <!-- Top Header -->
            <header class="superadmin-header">
                <div class="header-left">
                    <button class="sidebar-toggle d-lg-none" id="mobileSidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="breadcrumb-container">
                        @yield('breadcrumb')
                    </div>
                </div>
                
                <div class="header-right">
                    <!-- Quick Actions -->
                    <div class="header-actions">
                        <button class="btn btn-outline-primary btn-sm" onclick="clearSuperAdminCache()" title="Refresh Data">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="user-menu dropdown">
                        <button class="user-menu-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <div class="user-avatar">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <span class="user-name">Super Admin</span>
                            <i class="fas fa-chevron-down"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user"></i> Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog"></i> Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="superadmin-content">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Notifications -->
    <script src="{{ asset('assets/js/notifications.js') }}?v={{ time() }}"></script>
    <!-- Super Admin JS -->
    <script src="{{ asset('assets/js/superadmin/superadmin.js') }}?v={{ time() }}"></script>
    
    @stack('scripts')
</body>
</html>