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
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/agent/dashboard.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/agent/properties.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/agent/units.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/preloader.css') }}?v={{ time() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/notifications.css') }}?v={{ time() }}">
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
        <script src="{{ asset('assets/js/agent/dashboard.js') }}"></script>
        
        <!-- Notification System -->
        @include('partials.notification-system')
        
        @stack('scripts')
    </body>
</html>