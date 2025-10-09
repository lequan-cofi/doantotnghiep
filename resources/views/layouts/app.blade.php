<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title', config('app.name', 'Laravel'))</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
        
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        
        <!-- Custom CSS -->
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/home.css') }}?v={{ time() }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/preloader.css') }}?v={{ time() }}">
        
        <!-- Additional CSS -->
        @stack('styles')
    </head>
    <body>
        {{-- Preloader --}}
        <x-preloader />

        @include('partials.header')

        <main>
            @yield('content')
        </main>

        @include('partials.footer')
        
        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="{{ asset('assets/js/home.js') }}?v={{ time() }}"></script>
        
        <!-- Additional Scripts -->
        @stack('scripts')
    </body>
</html>


