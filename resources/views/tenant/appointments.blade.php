@extends('layouts.app')

@section('title', 'Quản lý lịch hẹn')

@php
    $viewings = $viewings ?? collect();
    $stats = [
        'pending' => $viewings->where('status', 'requested')->count(),
        'confirmed' => $viewings->where('status', 'confirmed')->count(),
        'completed' => $viewings->where('status', 'done')->count(),
        'cancelled' => $viewings->where('status', 'cancelled')->count(),
    ];
@endphp

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/appointments.css') }}?v={{ time() }}">
<style>
/* Inline CSS fallback for appointments page */
.appointments-container {
    padding: 30px 0 60px;
    background-color: #f4f4f4;
    min-height: calc(100vh - 120px);
}

.appointments-header {
    background: white;
    padding: 30px;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.header-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #ff6b35, #ff8563);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.page-title {
    font-size: 2rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 5px;
}

.page-subtitle {
    color: #6b7280;
    margin: 0;
    font-size: 1rem;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.stat-icon.pending {
    background: linear-gradient(135deg, #f59e0b, #f97316);
}

.stat-icon.confirmed {
    background: linear-gradient(135deg, #10b981, #059669);
}

.stat-icon.completed {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
}

.stat-icon.cancelled {
    background: linear-gradient(135deg, #ef4444, #dc2626);
}

.appointment-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    margin-bottom: 20px;
}

.appointment-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.appointment-status {
    position: absolute;
    top: 20px;
    right: 20px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
    z-index: 2;
}

.appointment-status.pending,
.appointment-status.requested {
    background: rgba(245, 158, 11, 0.1);
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.3);
}

.appointment-status.confirmed {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}

.appointment-status.done,
.appointment-status.completed {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    border: 1px solid rgba(59, 130, 246, 0.3);
}

.appointment-status.no_show {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
    border: 1px solid rgba(239, 68, 68, 0.3);
}

.appointment-status.cancelled {
    background: rgba(107, 114, 128, 0.1);
    color: #6b7280;
    border: 1px solid rgba(107, 114, 128, 0.3);
}

.appointment-content {
    padding: 25px;
}

.property-image {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    height: 150px;
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.property-info {
    padding-left: 20px;
}

.property-title {
    font-size: 1.3rem;
    font-weight: bold;
    color: #1a1a1a;
    margin-bottom: 10px;
    line-height: 1.3;
}

.appointment-actions {
    padding: 20px 25px;
    border-top: 1px solid #e5e7eb;
    background: rgba(249, 250, 251, 0.5);
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.property-badges {
    position: absolute;
    top: 10px;
    left: 10px;
    z-index: 2;
}

.badge.unit {
    background: rgba(59, 130, 246, 0.9);
    color: white;
    font-size: 0.75rem;
    padding: 4px 8px;
    border-radius: 4px;
}

.property-details {
    display: flex;
    gap: 15px;
    margin-top: 10px;
    flex-wrap: wrap;
}

.detail {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    color: #6b7280;
}

.detail i {
    color: #9ca3af;
}

.detail.price {
    color: #059669;
    font-weight: 600;
}

.appointment-info {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.appointment-time,
.appointment-contact {
    display: flex;
    align-items: center;
    gap: 10px;
}

.appointment-time i,
.appointment-contact i {
    color: #6b7280;
    width: 20px;
    text-align: center;
}

.appointment-time div,
.appointment-contact div {
    display: flex;
    flex-direction: column;
}

.appointment-time strong,
.appointment-contact strong {
    font-size: 0.9rem;
    color: #1f2937;
}

.appointment-time span,
.appointment-contact span {
    font-size: 0.8rem;
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.empty-icon {
    font-size: 4rem;
    color: #d1d5db;
    margin-bottom: 20px;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 10px;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 30px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    background: white;
    color: #6b7280;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.filter-tab:hover {
    border-color: #3b82f6;
    color: #3b82f6;
}

.filter-tab.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.search-box {
    position: relative;
    max-width: 400px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #9ca3af;
}

.search-box input {
    padding-left: 45px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    height: 40px;
    width: 100%;
}

.star-rating {
    display: flex;
    gap: 5px;
    margin-top: 10px;
}

.star-rating .fas.fa-star {
    color: #ddd;
    cursor: pointer;
    font-size: 1.2rem;
    transition: color 0.2s ease;
}

.star-rating .fas.fa-star.active {
    color: #ffc107;
}
</style>
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/appointments.js') }}?v={{ time() }}"></script>
<script>
// Global variables
let currentAppointmentId = null;

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const searchInput = document.getElementById('searchInput');
    const appointmentCards = document.querySelectorAll('.appointment-card');

    // Filter by status
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const status = this.dataset.status;
            
            // Update active tab
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Filter cards
            filterCards(status);
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterCards('all', searchTerm);
    });

    function filterCards(status, searchTerm = '') {
        appointmentCards.forEach(card => {
            const cardStatus = card.dataset.status;
            const cardText = card.textContent.toLowerCase();
            
            const statusMatch = status === 'all' || cardStatus === status;
            const searchMatch = searchTerm === '' || cardText.includes(searchTerm);
            
            if (statusMatch && searchMatch) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Show/hide empty state
        const visibleCards = Array.from(appointmentCards).filter(card => card.style.display !== 'none');
        const emptyState = document.querySelector('.empty-state');
        if (emptyState) {
            emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
        }
    }
});

// Cancel appointment
function cancelAppointment(id) {
    currentAppointmentId = id;
    const modal = new bootstrap.Modal(document.getElementById('cancelModal'));
    modal.show();
}

// Confirm cancel
function confirmCancel() {
    if (!currentAppointmentId) return;
    
    const reason = document.getElementById('cancelReason').value;
    
    fetch(`/viewings/${currentAppointmentId}/cancel`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Đã hủy lịch hẹn thành công');
            location.reload();
        } else {
            showNotification('error', data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    })
    .finally(() => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('cancelModal'));
        modal.hide();
        currentAppointmentId = null;
    });
}

// Mark as completed
function markCompleted(id) {
    if (confirm('Bạn có chắc chắn đã xem phòng này chưa?')) {
        updateAppointmentStatus(id, 'done');
    }
}

// Update appointment status
function updateAppointmentStatus(id, status) {
    fetch(`/agent/viewings/${id}/status`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Cập nhật trạng thái thành công');
            location.reload();
        } else {
            showNotification('error', data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    });
}

// Rate property
function rateProperty(id) {
    currentAppointmentId = id;
    const modal = new bootstrap.Modal(document.getElementById('ratingModal'));
    modal.show();
}

// Submit rating
function submitRating() {
    if (!currentAppointmentId) return;
    
    const rating = document.querySelector('.star-rating .fas.fa-star.active')?.dataset.rating || 0;
    const review = document.getElementById('reviewText').value;
    
    if (rating == 0) {
        showNotification('error', 'Vui lòng chọn đánh giá');
        return;
    }
    
    // Here you would implement the rating submission
    // For now, just show success message
    showNotification('success', 'Cảm ơn bạn đã đánh giá!');
    
    const modal = bootstrap.Modal.getInstance(document.getElementById('ratingModal'));
    modal.hide();
    currentAppointmentId = null;
}

// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-rating .fas.fa-star');
    stars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = parseInt(this.dataset.rating);
            
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = parseInt(this.dataset.rating);
            stars.forEach((s, i) => {
                if (i < rating) {
                    s.style.color = '#ffc107';
                } else {
                    s.style.color = '#ddd';
                }
            });
        });
    });
    
    document.querySelector('.star-rating').addEventListener('mouseleave', function() {
        const stars = this.querySelectorAll('.fas.fa-star');
        stars.forEach(star => {
            if (star.classList.contains('active')) {
                star.style.color = '#ffc107';
            } else {
                star.style.color = '#ddd';
            }
        });
    });
});

// Show notification
function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Edit appointment (placeholder)
function editAppointment(id) {
    showNotification('info', 'Chức năng chỉnh sửa đang được phát triển');
}

// Reschedule appointment (placeholder)
function rescheduleAppointment(id) {
    showNotification('info', 'Chức năng đổi lịch đang được phát triển');
}
</script>
@endpush

@section('content')
<div class="appointments-container">
    <div class="container">
        <!-- Header -->
        <div class="appointments-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Quản lý lịch hẹn</h1>
                            <p class="page-subtitle">Theo dõi và quản lý các lịch hẹn xem phòng của bạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-2"></i>Tìm phòng mới
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon pending">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending'] }}</h3>
                            <p>Chờ xác nhận</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon confirmed">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['confirmed'] }}</h3>
                            <p>Đã xác nhận</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon completed">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['completed'] }}</h3>
                            <p>Đã hoàn thành</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card">
                        <div class="stat-icon cancelled">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['cancelled'] }}</h3>
                            <p>Đã hủy</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm theo tên phòng, địa chỉ..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-status="all">Tất cả</button>
                        <button class="filter-tab" data-status="pending">Chờ xác nhận</button>
                        <button class="filter-tab" data-status="confirmed">Đã xác nhận</button>
                        <button class="filter-tab" data-status="completed">Hoàn thành</button>
                        <button class="filter-tab" data-status="cancelled">Đã hủy</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <div class="appointments-list">
            @forelse($viewings as $viewing)
                <div class="appointment-card" data-status="{{ $viewing->status }}" data-id="{{ $viewing->id }}">
                    <div class="appointment-status {{ $viewing->status }}">
                        @switch($viewing->status)
                            @case('requested')
                                <i class="fas fa-clock"></i>
                                <span>Chờ xác nhận</span>
                                @break
                            @case('confirmed')
                                <i class="fas fa-check-circle"></i>
                                <span>Đã xác nhận</span>
                                @break
                            @case('done')
                                <i class="fas fa-calendar-check"></i>
                                <span>Đã hoàn thành</span>
                                @break
                            @case('no_show')
                                <i class="fas fa-user-times"></i>
                                <span>Không đến</span>
                                @break
                            @case('cancelled')
                                <i class="fas fa-times-circle"></i>
                                <span>Đã hủy</span>
                                @break
                        @endswitch
                    </div>
                    <div class="appointment-content">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="property-image">
                                    @if($viewing->property && $viewing->property->images && count($viewing->property->images) > 0)
                                        <img src="{{ Storage::url($viewing->property->images[0]) }}" alt="{{ $viewing->property->title }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="Phòng trọ">
                                    @endif
                                    @if($viewing->unit)
                                        <div class="property-badges">
                                            <span class="badge unit">{{ $viewing->unit->code }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="property-info">
                                    <h4 class="property-title">
                                        {{ $viewing->property->title ?? 'Không có thông tin' }}
                                    </h4>
                                    <p class="property-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        {{ $viewing->property->location2025->full_address ?? 'Không có địa chỉ' }}
                                    </p>
                                    <div class="property-details">
                                        @if($viewing->unit)
                                            <span class="detail">
                                                <i class="fas fa-expand-arrows-alt"></i>
                                                {{ $viewing->unit->area }}m²
                                            </span>
                                            <span class="detail">
                                                <i class="fas fa-users"></i>
                                                {{ $viewing->unit->max_occupancy }} người
                                            </span>
                                            <span class="detail price">
                                                <i class="fas fa-money-bill-wave"></i>
                                                {{ number_format($viewing->unit->base_rent, 0, ',', '.') }} VNĐ/tháng
                                            </span>
                                        @else
                                            <span class="detail">
                                                <i class="fas fa-building"></i>
                                                Xem toàn bộ tòa nhà
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="appointment-info">
                                    <div class="appointment-time">
                                        <i class="fas fa-calendar"></i>
                                        <div>
                                            <strong>{{ $viewing->schedule_at->format('d/m/Y') }}</strong>
                                            <span>{{ $viewing->schedule_at->format('H:i') }} - {{ $viewing->schedule_at->addHour()->format('H:i') }}</span>
                                        </div>
                                    </div>
                                    @if($viewing->agent)
                                        <div class="appointment-contact">
                                            <i class="fas fa-user"></i>
                                            <div>
                                                <strong>{{ $viewing->agent->name }}</strong>
                                                <span>{{ $viewing->agent->phone ?? 'Không có SĐT' }}</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="appointment-actions">
                        @switch($viewing->status)
                            @case('requested')
                                <button class="btn btn-outline-danger btn-sm" onclick="cancelAppointment({{ $viewing->id }})">
                                    <i class="fas fa-times"></i>
                                    Hủy lịch
                                </button>
                                <a href="{{ route('viewings.show', $viewing->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                @if($viewing->agent && $viewing->agent->phone)
                                    <a href="tel:{{ $viewing->agent->phone }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-phone"></i>
                                        Gọi điện
                                    </a>
                                @endif
                                @break
                            @case('confirmed')
                                <button class="btn btn-outline-warning btn-sm" onclick="markCompleted({{ $viewing->id }})">
                                    <i class="fas fa-check"></i>
                                    Đã xem
                                </button>
                                <a href="{{ route('viewings.show', $viewing->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                @if($viewing->agent && $viewing->agent->phone)
                                    <a href="tel:{{ $viewing->agent->phone }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-phone"></i>
                                        Gọi điện
                                    </a>
                                @endif
                                @break
                            @case('done')
                                <button class="btn btn-outline-primary btn-sm" onclick="rateProperty({{ $viewing->id }})">
                                    <i class="fas fa-star"></i>
                                    Đánh giá
                                </button>
                                @if($viewing->unit)
                                    <a href="{{ route('tenant.deposit', $viewing->unit->id) }}" class="btn btn-outline-success btn-sm">
                                        <i class="fas fa-home"></i>
                                        Thuê phòng
                                    </a>
                                @endif
                                @if($viewing->agent && $viewing->agent->phone)
                                    <a href="tel:{{ $viewing->agent->phone }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-phone"></i>
                                        Gọi lại
                                    </a>
                                @endif
                                @break
                            @case('cancelled')
                                <a href="{{ route('viewings.show', $viewing->id) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                    Xem chi tiết
                                </a>
                                @if($viewing->property)
                                    <a href="{{ route('property.show', $viewing->property->id) }}" class="btn btn-outline-info btn-sm">
                                        <i class="fas fa-redo"></i>
                                        Đặt lại
                                    </a>
                                @endif
                                @break
                        @endswitch
                    </div>
                </div>
            @empty
                <!-- Empty State -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Không có lịch hẹn nào</h3>
                    <p>Bạn chưa có lịch hẹn xem phòng nào. Hãy tìm kiếm và đặt lịch xem phòng mới!</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Tìm phòng ngay
                    </a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Appointments pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <span class="page-link">Trước</span>
                    </li>
                    <li class="page-item active">
                        <span class="page-link">1</span>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">2</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">3</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Modals -->
<!-- Cancel Appointment Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hủy lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Bạn có chắc chắn muốn hủy lịch hẹn này không?</p>
                <div class="mb-3">
                    <label for="cancelReason" class="form-label">Lý do hủy (tùy chọn)</label>
                    <textarea class="form-control" id="cancelReason" rows="3" placeholder="Nhập lý do hủy lịch..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Không</button>
                <button type="button" class="btn btn-danger" onclick="confirmCancel()">Xác nhận hủy</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Appointment Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editAppointmentForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="editDate" class="form-label">Ngày hẹn</label>
                            <input type="date" class="form-control" id="editDate" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="editStartTime" class="form-label">Từ giờ</label>
                            <input type="time" class="form-control" id="editStartTime" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="editEndTime" class="form-label">Đến giờ</label>
                            <input type="time" class="form-control" id="editEndTime" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editNote" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="editNote" rows="3" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="saveEdit()">Lưu thay đổi</button>
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div class="modal fade" id="ratingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đánh giá phòng trọ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="rating-section mb-3">
                    <label class="form-label">Đánh giá tổng thể</label>
                    <div class="star-rating">
                        <i class="fas fa-star" data-rating="1"></i>
                        <i class="fas fa-star" data-rating="2"></i>
                        <i class="fas fa-star" data-rating="3"></i>
                        <i class="fas fa-star" data-rating="4"></i>
                        <i class="fas fa-star" data-rating="5"></i>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="reviewText" class="form-label">Nhận xét</label>
                    <textarea class="form-control" id="reviewText" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitRating()">Gửi đánh giá</button>
            </div>
        </div>
    </div>
</div>
@endsection
