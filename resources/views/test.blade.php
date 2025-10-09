@extends('layouts.app')

@section('title', 'Test CSS/JS Loading')

@push('styles')
<link rel="stylesheet" href="{{ asset('test-style.css') }}">
@endpush

@section('content')
<div class="container" style="padding: 40px 0;">
    <h1 class="test-class">Test CSS và JS Loading</h1>
    
    <div class="alert alert-info">
        <h4>Kiểm tra CSS:</h4>
        <p>Nếu CSS load đúng, bạn sẽ thấy:</p>
        <ul>
            <li>Background gradient cho button</li>
            <li>Hover effects</li>
            <li>Typography đẹp</li>
        </ul>
    </div>
    
    <div class="mb-4">
        <button class="btn-hero" id="test-btn">
            <i class="fas fa-test"></i>
            Test Button (CSS + JS)
        </button>
    </div>
    
    <div class="property-card" style="max-width: 400px;">
        <div class="property-image">
            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" alt="Test">
            <button class="favorite-btn">
                <i class="fas fa-heart"></i>
            </button>
        </div>
        <div class="property-content">
            <h3>Test Property Card</h3>
            <div class="property-location">
                <i class="fas fa-map-marker-alt"></i>
                <span>Test Location</span>
            </div>
            <div class="property-footer">
                <div class="price">2,500,000 VNĐ/tháng</div>
                <button class="btn-view">
                    <i class="fas fa-eye"></i>
                    Chi tiết
                </button>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        <div class="alert alert-warning">
            <h4>Kiểm tra JS:</h4>
            <p>Click vào button trên hoặc favorite heart để test JavaScript.</p>
            <p>Mở Console (F12) để xem log messages.</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Test page JavaScript loaded!');
    console.log('CSS Variables:', getComputedStyle(document.documentElement).getPropertyValue('--primary'));
    
    document.getElementById('test-btn').addEventListener('click', function() {
        alert('JavaScript hoạt động! CSS và JS đã load thành công.');
        console.log('Test button clicked!');
    });
});
</script>
@endsection
