@extends('layouts.app')

@section('title', 'Chi tiết đánh giá')

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
    ReviewsModule.initShow();
    
    // Initialize notification system
    if (typeof Notify !== 'undefined') {
        // Show view mode notification
        Notify.info('Bạn đang xem chi tiết đánh giá. Có thể thực hiện các hành động như chỉnh sửa, xóa hoặc chia sẻ.', 'Chi tiết đánh giá');
        
        // Check if review has replies
        @if($review->replies->count() > 0)
            Notify.success('Chủ nhà đã phản hồi đánh giá của bạn! Bạn có thể gửi lời cảm ơn.', 'Có phản hồi mới');
        @endif
    }
    
    
    const thankBtn = document.querySelector('[onclick*="thankLandlord"]');
    if (thankBtn) {
        thankBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof Notify !== 'undefined') {
                Notify.confirm({
                    title: 'Gửi lời cảm ơn',
                    message: 'Bạn có muốn gửi lời cảm ơn đến chủ nhà không?',
                    type: 'info',
                    confirmText: 'Gửi cảm ơn',
                    onConfirm: function() {
                        ReviewsModule.thankLandlord({{ $review->id }});
                    }
                });
            }
        });
    }
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
                            <i class="fas fa-eye"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Chi tiết đánh giá</h1>
                            <p class="page-subtitle">Xem chi tiết đánh giá và phản hồi</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="header-actions">
                        <a href="{{ route('tenant.reviews.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Alert Messages with Notification Integration -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show notification-alert" role="alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-text">
                        <strong>Lỗi:</strong> {{ session('error') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show notification-alert" role="alert">
                <div class="alert-content">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-text">
                        <strong>Thành công:</strong> {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        <!-- Review Details -->
        <div class="review-details-container">
            <div class="row">
                <div class="col-lg-8">
                    <!-- Main Review Content -->
                    <div class="review-detail-card">
                        <div class="review-detail-header">
                            <div class="review-detail-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="review-detail-info">
                                <h4>{{ $review->title }}</h4>
                                <p>{{ $review->unit->property->name }} - {{ $review->unit->name }}</p>
                            </div>
                        </div>

                        <div class="review-sections">
                            <!-- Overall Rating -->
                            <div class="review-section">
                                <h5>Đánh giá tổng thể</h5>
                                <div class="overall-rating-display">
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->overall_rating ? '' : ' far' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="rating-value">{{ number_format($review->overall_rating, 1) }}/5</span>
                                </div>
                            </div>

                            <!-- Detail Ratings -->
                            @if($review->location_rating || $review->quality_rating || $review->service_rating || $review->price_rating)
                            <div class="review-section">
                                <h5>Đánh giá chi tiết</h5>
                                @if($review->location_rating)
                                <div class="detail-rating">
                                    <span class="label">Vị trí</span>
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->location_rating ? '' : ' far' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @endif
                                @if($review->quality_rating)
                                <div class="detail-rating">
                                    <span class="label">Chất lượng phòng</span>
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->quality_rating ? '' : ' far' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @endif
                                @if($review->service_rating)
                                <div class="detail-rating">
                                    <span class="label">Thái độ chủ nhà</span>
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->service_rating ? '' : ' far' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @endif
                                @if($review->price_rating)
                                <div class="detail-rating">
                                    <span class="label">Giá cả</span>
                                    <div class="stars">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->price_rating ? '' : ' far' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endif

                            <!-- Review Content -->
                            <div class="review-section">
                                <h5>Nội dung đánh giá</h5>
                                <div class="review-content-text">
                                    <p>{{ $review->content }}</p>
                                </div>
                            </div>

                            <!-- Highlights -->
                            @if($review->highlights && count($review->highlights) > 0)
                            <div class="review-section">
                                <h5>Điểm nổi bật</h5>
                                <div class="highlights-display">
                                    @foreach($review->highlights as $highlight)
                                        <span class="highlight-tag">
                                            @switch($highlight)
                                                @case('clean')
                                                    <i class="fas fa-sparkles"></i> Sạch sẽ
                                                    @break
                                                @case('location')
                                                    <i class="fas fa-map-marker-alt"></i> Vị trí tốt
                                                    @break
                                                @case('price')
                                                    <i class="fas fa-dollar-sign"></i> Giá hợp lý
                                                    @break
                                                @case('friendly')
                                                    <i class="fas fa-smile"></i> Chủ nhà thân thiện
                                                    @break
                                                @case('quiet')
                                                    <i class="fas fa-volume-mute"></i> Yên tĩnh
                                                    @break
                                                @case('convenient')
                                                    <i class="fas fa-shopping-cart"></i> Tiện ích
                                                    @break
                                            @endswitch
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Recommendation -->
                            @if($review->recommend)
                            <div class="review-section">
                                <h5>Khuyến nghị</h5>
                                <div class="recommendation-display">
                                    @switch($review->recommend)
                                        @case('yes')
                                            <i class="fas fa-thumbs-up text-success"></i>
                                            <span class="text-success">Có, tôi sẽ giới thiệu</span>
                                            @break
                                        @case('maybe')
                                            <i class="fas fa-meh text-warning"></i>
                                            <span class="text-warning">Có thể</span>
                                            @break
                                        @case('no')
                                            <i class="fas fa-thumbs-down text-danger"></i>
                                            <span class="text-danger">Không</span>
                                            @break
                                    @endswitch
                                </div>
                            </div>
                            @endif

                            <!-- Images -->
                            @if($review->images && count($review->images) > 0)
                            <div class="review-section">
                                <h5>Hình ảnh</h5>
                                <div class="review-images">
                                    @foreach($review->images as $image)
                                        <div class="review-image-item">
                                            <img src="{{ Storage::url($image) }}" alt="Review image" class="img-fluid">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Replies Section -->
                    @if($review->replies->count() > 0)
                    <div class="replies-section">
                        <h5>Phản hồi từ chủ nhà</h5>
                        @foreach($review->replies as $reply)
                            @include('tenant.reviews.partials.reply-item', ['reply' => $reply])
                        @endforeach
                    </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <!-- Property Info Sidebar -->
                    <div class="property-info-sidebar">
                        <div class="property-info-card">
                            <h5>Thông tin phòng</h5>
                            <div class="property-details">
                                <!-- Property Name -->
                                <div class="stat-item">
                                    <i class="fas fa-building"></i>
                                    <span>{{ $review->unit->property->name }}</span>
                                </div>
                                
                                <!-- Unit Name -->
                                <div class="stat-item">
                                    <i class="fas fa-door-open"></i>
                                    <span>{{ $review->unit->name }}</span>
                                </div>
                                
                                <!-- Address -->
                                <div class="stat-item">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>
                                        @if($review->unit->property->new_address && $review->unit->property->new_address !== 'Chưa có địa chỉ mới')
                                            {{ $review->unit->property->new_address }}
                                        @elseif($review->unit->property->old_address && $review->unit->property->old_address !== 'Chưa có địa chỉ cũ')
                                            {{ $review->unit->property->old_address }}
                                        @else
                                            <span class="text-muted">Địa chỉ chưa cập nhật</span>
                                        @endif
                                    </span>
                                </div>
                                
                                <!-- Rent Amount -->
                                @if($review->lease && $review->lease->rent_amount)
                                <div class="stat-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span>{{ number_format($review->lease->rent_amount) }} VNĐ/tháng</span>
                                </div>
                                @endif
                                
                                <!-- Unit Type -->
                                @if($review->unit->type)
                                <div class="stat-item">
                                    <i class="fas fa-home"></i>
                                    <span>{{ $review->unit->type }}</span>
                                </div>
                                @endif
                                
                                <!-- Area -->
                                @if($review->unit->area)
                                <div class="stat-item">
                                    <i class="fas fa-expand-arrows-alt"></i>
                                    <span>{{ $review->unit->area }} m²</span>
                                </div>
                                @endif
                                
                                <!-- Lease Period -->
                                @if($review->lease)
                                <div class="stat-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>
                                        {{ $review->lease->start_date ? $review->lease->start_date->format('d/m/Y') : 'N/A' }}
                                        @if($review->lease->end_date)
                                            - {{ $review->lease->end_date->format('d/m/Y') }}
                                        @else
                                            - Hiện tại
                                        @endif
                                    </span>
                                </div>
                                @endif
                                
                                <!-- Review Date -->
                                <div class="stat-item">
                                    <i class="fas fa-star"></i>
                                    <span>{{ $review->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                
                                <!-- Property Owner -->
                                @if($review->unit->property->owner)
                                <div class="stat-item">
                                    <i class="fas fa-user-tie"></i>
                                    <span>{{ $review->unit->property->owner->full_name ?? $review->unit->property->owner->name ?? 'Chưa cập nhật' }}</span>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Review Stats -->
                        <div class="review-stats-card">
                            <h5>Thống kê</h5>
                            <div class="stats-list">
                                <div class="stat-item">
                                    <i class="fas fa-eye"></i>
                                    <span>{{ $review->view_count }} lượt xem</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>{{ $review->helpful_count }} hữu ích</span>
                                </div>
                                <div class="stat-item">
                                    <i class="fas fa-reply"></i>
                                    <span>{{ $review->replies->count() }} phản hồi</span>
                                </div>
                            </div>
                        </div>

                        <!-- Enhanced Actions -->
                        <div class="review-actions-card">
                            <h5>Hành động</h5>
                            <div class="action-buttons">
                                @if($review->canBeDeletedBy(auth()->user()))
                                    <button class="btn btn-outline-danger btn-sm w-100 mb-2" id="deleteReviewBtn">
                                        <i class="fas fa-trash me-2"></i>Xóa đánh giá
                                    </button>
                                @endif
                                @if($review->replies->count() > 0)
                                    <button class="btn btn-outline-success btn-sm w-100 mb-2" id="thankLandlordBtn">
                                        <i class="fas fa-heart me-2"></i>Cảm ơn chủ nhà
                                    </button>
                                @endif
                                <button class="btn btn-outline-info btn-sm w-100 mb-2" onclick="ReviewsModule.shareReview({{ $review->id }})">
                                    <i class="fas fa-share me-2"></i>Chia sẻ
                                </button>
                                <button class="btn btn-outline-secondary btn-sm w-100" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>In đánh giá
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete button
    const deleteBtn = document.getElementById('deleteReviewBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            if (typeof Notify !== 'undefined' && Notify.confirmDelete) {
                Notify.confirmDelete('đánh giá này', function() {
                    // Direct delete without ReviewsModule confirmation
                    fetch(`/tenant/reviews/{{ $review->id }}`, {
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
                            // Redirect to reviews index after successful deletion
                            window.location.href = '/tenant/reviews';
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
                if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
                    fetch(`/tenant/reviews/{{ $review->id }}`, {
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
                            window.location.href = '/tenant/reviews';
                        } else {
                            alert('Lỗi: ' + (data.message || 'Có lỗi xảy ra khi xóa đánh giá'));
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting review:', error);
                        alert('Có lỗi xảy ra khi xóa đánh giá: ' + error.message);
                    });
                }
            }
        });
    }
    
    // Handle thank button
    const thankBtn = document.getElementById('thankLandlordBtn');
    if (thankBtn) {
        thankBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (typeof Notify !== 'undefined' && Notify.confirm) {
                Notify.confirm({
                    title: 'Gửi lời cảm ơn',
                    message: 'Bạn có muốn gửi lời cảm ơn đến chủ nhà không?',
                    type: 'info',
                    confirmText: 'Gửi cảm ơn',
                    onConfirm: function() {
                        ReviewsModule.thankLandlord({{ $review->id }});
                    }
                });
            } else {
                // Fallback to ReviewsModule's own confirmation
                ReviewsModule.thankLandlord({{ $review->id }});
            }
        });
    }
});
</script>
@endpush
