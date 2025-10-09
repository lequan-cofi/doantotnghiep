{{--
    ========================================
    PRELOADER COMPONENT
    Sử dụng: @include('components.preloader')
    hoặc: <x-preloader />
    
    Props (optional):
    - $style: 'default', 'house', 'minimal' (default: 'default')
    - $showPercentage: true/false (default: true)
    ========================================
--}}

@props([
    'style' => 'default',
    'showPercentage' => true
])

<div id="preloader" class="preloader">
    @if($style === 'house')
        {{-- House Style --}}
        <div class="preloader__house">
            <div class="house__roof"></div>
            <div class="house__body">
                <div class="house__window"></div>
                <div class="house__window"></div>
                <div class="house__door"></div>
            </div>
        </div>
    @elseif($style === 'minimal')
        {{-- Minimal Style --}}
        <div class="preloader__logo">
            <div class="preloader__logo-icon">
                <i class="fas fa-building"></i>
            </div>
        </div>
    @else
        {{-- Default Style with Logo --}}
        <div class="preloader__logo">
            <div class="preloader__logo-icon">
                <i class="fas fa-home"></i>
            </div>
        </div>
    @endif

    <h2 class="preloader__text">Nhà Trọ Platform</h2>
    <p class="preloader__subtitle">Hệ thống quản lý nhà trọ thông minh</p>

    {{-- Spinner --}}
    <div class="preloader__spinner">
        <div class="preloader__spinner-circle"></div>
    </div>

    {{-- Loading Bar --}}
    <div class="preloader__bar">
        <div class="preloader__bar-fill"></div>
    </div>

    {{-- Loading Dots --}}
    <div class="preloader__dots">
        <div class="preloader__dot"></div>
        <div class="preloader__dot"></div>
        <div class="preloader__dot"></div>
    </div>

    {{-- Percentage Counter --}}
    @if($showPercentage)
        <div class="preloader__percentage">0%</div>
    @endif
</div>

{{-- Include Preloader Assets --}}
@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/preloader.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('assets/js/preloader.js') }}"></script>
    @endpush
@endonce

