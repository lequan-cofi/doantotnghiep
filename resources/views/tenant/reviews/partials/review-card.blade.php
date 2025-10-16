{{-- Review Card Partial --}}
<div class="review-card {{ $review->allReplies->count() > 0 ? 'replied' : 'published' }}" 
     data-status="{{ $review->allReplies->count() > 0 ? 'replied' : 'published' }}" 
     data-rating="{{ $review->overall_rating }}">
    
    <div class="review-status {{ $review->allReplies->count() > 0 ? 'replied' : 'published' }}">
        <i class="fas fa-{{ $review->allReplies->count() > 0 ? 'reply' : 'check-circle' }}"></i>
        <span>{{ $review->allReplies->count() > 0 ? 'Có phản hồi' : 'Đã đăng' }}</span>
    </div>
    
    <div class="review-content">
        <div class="row">
            <div class="col-md-3">
                <div class="property-image">
                    @if($review->images && count($review->images) > 0)
                        <img src="{{ Storage::url($review->images[0]) }}" alt="{{ $review->property_name }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="{{ $review->property_name }}">
                    @endif
                </div>
            </div>
            <div class="col-md-9">
                <div class="review-info">
                    <div class="review-header">
                        <h4 class="property-title">{{ $review->property_name }}</h4>
                        <div class="review-rating">
                            <div class="stars">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->overall_rating ? '' : ' far' }}"></i>
                                @endfor
                            </div>
                            <span class="rating-value">{{ number_format($review->overall_rating, 1) }}</span>
                        </div>
                    </div>
                    <p class="property-address">
                        <i class="fas fa-map-marker-alt"></i>
                        {{ $review->location_address ?? $review->location2025_address ?? 'Địa chỉ không xác định' }}
                    </p>
                    <div class="review-text">
                        <h5>{{ $review->title }}</h5>
                        <p>"{{ Str::limit($review->content, 200) }}"</p>
                    </div>
                    <div class="review-meta">
                        <div class="review-date">
                            <i class="fas fa-calendar"></i>
                            Đánh giá ngày: {{ $review->created_at->format('d/m/Y') }}
                        </div>
                        <div class="review-stats">
                            <span class="helpful-count">
                                <i class="fas fa-thumbs-up"></i>
                                {{ $review->helpful_count }} hữu ích
                            </span>
                            <span class="view-count">
                                <i class="fas fa-eye"></i>
                                {{ $review->view_count }} lượt xem
                            </span>
                            @if($review->allReplies->count() > 0)
                                <span class="reply-indicator">
                                    <i class="fas fa-reply text-success"></i>
                                    Có phản hồi
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if($review->replies->count() > 0)
                        @include('tenant.reviews.partials.landlord-reply', ['replies' => $review->replies])
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="review-actions">
        <a href="{{ route('tenant.reviews.show', $review->id) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-eye me-1"></i>Xem chi tiết
        </a>
        @if($review->canBeDeletedBy(auth()->user()))
            <button class="btn btn-outline-danger btn-sm delete-review-btn" 
                    data-review-id="{{ $review->id }}" 
                    data-review-title="{{ $review->title }}">
                <i class="fas fa-trash me-1"></i>Xóa
            </button>
        @endif
        @if($review->allReplies->count() > 0)
            <button class="btn btn-outline-success btn-sm thank-btn" data-review-id="{{ $review->id }}">
                <i class="fas fa-heart me-1"></i>Cảm ơn
            </button>
        @endif
        <button class="btn btn-outline-info btn-sm share-btn" data-review-id="{{ $review->id }}">
            <i class="fas fa-share me-1"></i>Chia sẻ
        </button>
    </div>
</div>
