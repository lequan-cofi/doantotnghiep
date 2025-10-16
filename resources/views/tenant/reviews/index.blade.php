@extends('layouts.app')

@section('title', 'Đánh giá của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/reviews.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/user/reviews-enhanced.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/notifications.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="{{ asset('assets/js/notifications.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/user/reviews.js') }}?v={{ time() }}"></script>
<script>
// Page-specific initialization
document.addEventListener('DOMContentLoaded', function() {
    ReviewsModule.initIndex();
    
    // Show welcome message
    if (typeof Notify !== 'undefined') {
        Notify.info('Chào mừng đến với trang quản lý đánh giá của bạn!', 'Đánh giá của tôi');
    }
    
    // Handle delete buttons
    document.querySelectorAll('.delete-review-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reviewId = this.dataset.reviewId;
            const reviewTitle = this.dataset.reviewTitle || 'đánh giá này';
            
            if (typeof Notify !== 'undefined' && Notify.confirmDelete) {
                Notify.confirmDelete(reviewTitle, function() {
                    // Direct delete without ReviewsModule confirmation
                    fetch(`/tenant/reviews/${reviewId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('Delete response status:', response.status);
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        console.log('Delete response data:', data);
                        if (data.success) {
                            Notify.success(data.message);
                            window.location.reload();
                        } else {
                            Notify.error(data.message || 'Có lỗi xảy ra khi xóa đánh giá');
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting review:', error);
                        Notify.error('Có lỗi xảy ra khi xóa đánh giá: ' + error.message);
                    });
                });
            } else {
                // Fallback: use browser confirm
                if (confirm(`Bạn có chắc chắn muốn xóa ${reviewTitle}?`)) {
                    fetch(`/tenant/reviews/${reviewId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Đánh giá đã được xóa thành công!');
                            window.location.reload();
                        } else {
                            alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra khi xóa đánh giá'));
                        }
                    })
                    .catch(error => {
                        console.error('Fallback delete error:', error);
                        alert('Có lỗi xảy ra khi xóa đánh giá: ' + error.message);
                    });
                }
            }
        });
    });
    
    // Handle thank buttons
    document.querySelectorAll('.thank-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reviewId = this.dataset.reviewId;
            
            if (typeof Notify !== 'undefined' && Notify.confirm) {
                Notify.confirm({
                    title: 'Gửi lời cảm ơn',
                    message: 'Bạn có muốn gửi lời cảm ơn đến chủ nhà không?',
                    type: 'info',
                    confirmText: 'Gửi cảm ơn',
                    onConfirm: function() {
                        ReviewsModule.thankLandlord(reviewId);
                    }
                });
            } else {
                // Fallback: direct call without confirmation
                ReviewsModule.thankLandlord(reviewId);
            }
        });
    });
    
    // Handle share buttons
    document.querySelectorAll('.share-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const reviewId = this.dataset.reviewId;
            ReviewsModule.shareReview(reviewId);
        });
    });
});
</script>
@endpush

@section('content')
<div class="reviews-container">
    <div class="container">
        <!-- Page Header -->
        <div class="reviews-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Đánh giá của tôi</h1>
                            <p class="page-subtitle">Viết đánh giá và theo dõi phản hồi từ chủ nhà</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <a href="{{ route('tenant.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                        </a>
                        <a href="{{ route('tenant.reviews.create') }}" class="btn btn-primary ms-2">
                            <i class="fas fa-edit me-2"></i>Viết đánh giá
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error Messages -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Success Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['total'] ?? 0 }}</h3>
                            <p>Tổng đánh giá</p>
                            <div class="stat-extra">{{ $stats['avg_rating'] ?? 0 }}/5 ⭐</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['pending'] ?? 0 }}</h3>
                            <p>Chờ đánh giá</p>
                            <div class="stat-extra">Phòng đã thuê</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card replied">
                        <div class="stat-icon">
                            <i class="fas fa-reply"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['replied'] ?? 0 }}</h3>
                            <p>Có phản hồi</p>
                            <div class="stat-extra">Từ chủ nhà</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card helpful">
                        <div class="stat-icon">
                            <i class="fas fa-thumbs-up"></i>
                        </div>
                        <div class="stat-content">
                            <h3>{{ $stats['helpful'] ?? 0 }}</h3>
                            <p>Lượt hữu ích</p>
                            <div class="stat-extra">Từ người dùng</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm theo tên phòng..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-4">
                    <select class="form-select" id="ratingFilter">
                        <option value="">Tất cả đánh giá</option>
                        <option value="5">⭐⭐⭐⭐⭐ 5 sao</option>
                        <option value="4">⭐⭐⭐⭐ 4 sao</option>
                        <option value="3">⭐⭐⭐ 3 sao</option>
                        <option value="2">⭐⭐ 2 sao</option>
                        <option value="1">⭐ 1 sao</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-status="all">Tất cả</button>
                        <button class="filter-tab" data-status="pending">Chờ đánh giá</button>
                        <button class="filter-tab" data-status="published">Đã đăng</button>
                        <button class="filter-tab" data-status="replied">Có phản hồi</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews List -->
        <div class="reviews-list" id="reviewsList">
            @forelse($reviews as $review)
                @include('tenant.reviews.partials.review-card', ['review' => $review])
            @empty
                @include('tenant.reviews.partials.empty-state')
            @endforelse
        </div>

        <!-- Pagination -->
        @if($reviews->hasPages())
            <div class="pagination-section">
                <nav aria-label="Reviews pagination">
                    {{ $reviews->links() }}
                </nav>
            </div>
        @endif
    </div>
</div>
@endsection

