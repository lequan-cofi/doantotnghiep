<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'Agent Dashboard')</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/manager/dashboard.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/preloader.css') }}?v={{ time() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/notifications.css') }}?v={{ time() }}">
        <style>
        /* Top Header Styles */
        .top-header {
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 18px;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
        }

        .mobile-menu-toggle:hover {
            background: #f1f5f9;
        }

        .breadcrumb {
            font-size: 14px;
            color: #64748b;
        }

        /* Notification Dropdown */
        .notification-dropdown {
            position: relative;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            font-size: 18px;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .notification-btn:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #dc2626;
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 16px;
            text-align: center;
        }

        .notification-menu {
            min-width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
        }

        .notification-header h6 {
            margin: 0;
            font-weight: 600;
            color: #1e293b;
        }

        .view-all {
            font-size: 12px;
            color: #dc2626;
            text-decoration: none;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }

        .notification-item {
            padding: 12px 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-content p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }

        /* User Dropdown */
        .user-dropdown {
            position: relative;
        }

        .user-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .user-btn:hover {
            background: #f1f5f9;
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: #dc2626;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .user-name {
            font-size: 14px;
            font-weight: 600;
            color: #1e293b;
            line-height: 1.2;
        }

        .user-role {
            font-size: 12px;
            color: #64748b;
            line-height: 1.2;
        }

        .user-menu {
            min-width: 200px;
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #64748b;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .dropdown-item:hover {
            background: #f8fafc;
            color: #1e293b;
        }

        .dropdown-item i {
            width: 16px;
            text-align: center;
        }

        .logout-btn {
            background: none;
            border: none;
            width: 100%;
            text-align: left;
            cursor: pointer;
        }

        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 8px 0;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        /* Top Header */
        .top-header {
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        /* Header (for content sections) */
        .header {
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 24px;
            height: 80px;
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
        }

        .header-info h1 {
            font-size: 24px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .header-info p {
            font-size: 14px;
            color: #64748b;
            margin: 4px 0 0 0;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Content */
        .content {
            flex: 1;
            padding: 24px;
            overflow-y: auto;
            background: #f8fafc;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .content {
                padding: 16px;
            }
            
            .top-header {
                padding: 0 16px;
            }
            
            .header {
                padding: 0 16px;
            }
            
            .header-info h1 {
                font-size: 20px;
            }
            
            .header-info p {
                font-size: 12px;
            }
            
            /* Mobile sidebar */
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                z-index: 1000;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 4px 0 12px rgba(0, 0, 0, 0.2);
            }
            
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }
        </style>
        @stack('styles')
    </head>
    <body>
        {{-- Preloader --}}
        <x-preloader style="minimal" />

        <main>
            @include('partials.agent.header')
            @yield('content')
        </main>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script src="{{ asset('assets/js/preloader.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('assets/js/notifications.js') }}?v={{ time() }}"></script>
        <script src="{{ asset('assets/js/manager/dashboard.js') }}"></script>
        @stack('scripts')
    </body>
</html>
