@extends('layouts.app')

@section('title', 'Viết đánh giá')

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
    ReviewsModule.initCreate();
    
    // Initialize notification system
    if (typeof Notify !== 'undefined') {
        // Show welcome message
        Notify.info('Chia sẻ trải nghiệm của bạn để giúp người khác tìm được phòng trọ phù hợp!', 'Chào mừng đến với hệ thống đánh giá');
    }
    
    // Enhanced form validation with notifications
    const form = document.getElementById('writeReviewForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Create form submitted');
            // Show loading notification
            if (typeof Notify !== 'undefined') {
                Notify.info('Đang xử lý đánh giá của bạn...', 'Vui lòng chờ');
            }
            
            // Show loading state on button
            const submitBtn = document.getElementById('submitReviewBtn');
            if (submitBtn) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang đăng...';
                submitBtn.disabled = true;
                
                // Reset after 5 seconds if no response
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 5000);
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
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Viết đánh giá</h1>
                            <p class="page-subtitle">Chia sẻ trải nghiệm của bạn về phòng trọ</p>
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

        <!-- Review Form -->
        <div class="review-form-container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="review-form-card">
                        <form id="writeReviewForm" method="POST" action="{{ route('tenant.reviews.store') }}" enctype="multipart/form-data">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="reviewProperty" class="form-label">Chọn phòng để đánh giá <span class="required">*</span></label>
                                <select class="form-select" id="reviewProperty" name="lease_id" required>
                                    <option value="">Chọn phòng bạn đã/đang thuê</option>
                                    @foreach($leases as $lease)
                                        <option value="{{ $lease->id }}">
                                            {{ $lease->unit->property->name }} - {{ $lease->unit->name }} 
                                            ({{ number_format($lease->rent_amount) }} VNĐ/tháng)
                                        </option>
                                    @endforeach
                                </select>
                                @error('lease_id')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rating-sections">
                                <div class="rating-section mb-4">
                                    <label class="form-label">Đánh giá tổng thể <span class="required">*</span></label>
                                    <div class="star-rating-large" id="overallRating">
                                        <i class="fas fa-star" data-rating="1"></i>
                                        <i class="fas fa-star" data-rating="2"></i>
                                        <i class="fas fa-star" data-rating="3"></i>
                                        <i class="fas fa-star" data-rating="4"></i>
                                        <i class="fas fa-star" data-rating="5"></i>
                                    </div>
                                    <div class="rating-text">Chưa đánh giá</div>
                                    <input type="hidden" name="overall_rating" id="overallRatingInput" value="">
                                    @error('overall_rating')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="detailed-ratings">
                                    <h6>Đánh giá chi tiết</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Vị trí</label>
                                            <div class="star-rating-small" id="locationRating">
                                                <i class="fas fa-star" data-rating="1"></i>
                                                <i class="fas fa-star" data-rating="2"></i>
                                                <i class="fas fa-star" data-rating="3"></i>
                                                <i class="fas fa-star" data-rating="4"></i>
                                                <i class="fas fa-star" data-rating="5"></i>
                                            </div>
                                            <input type="hidden" name="location_rating" id="locationRatingInput" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Chất lượng phòng</label>
                                            <div class="star-rating-small" id="qualityRating">
                                                <i class="fas fa-star" data-rating="1"></i>
                                                <i class="fas fa-star" data-rating="2"></i>
                                                <i class="fas fa-star" data-rating="3"></i>
                                                <i class="fas fa-star" data-rating="4"></i>
                                                <i class="fas fa-star" data-rating="5"></i>
                                            </div>
                                            <input type="hidden" name="quality_rating" id="qualityRatingInput" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Thái độ chủ nhà</label>
                                            <div class="star-rating-small" id="serviceRating">
                                                <i class="fas fa-star" data-rating="1"></i>
                                                <i class="fas fa-star" data-rating="2"></i>
                                                <i class="fas fa-star" data-rating="3"></i>
                                                <i class="fas fa-star" data-rating="4"></i>
                                                <i class="fas fa-star" data-rating="5"></i>
                                            </div>
                                            <input type="hidden" name="service_rating" id="serviceRatingInput" value="">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giá cả</label>
                                            <div class="star-rating-small" id="priceRating">
                                                <i class="fas fa-star" data-rating="1"></i>
                                                <i class="fas fa-star" data-rating="2"></i>
                                                <i class="fas fa-star" data-rating="3"></i>
                                                <i class="fas fa-star" data-rating="4"></i>
                                                <i class="fas fa-star" data-rating="5"></i>
                                            </div>
                                            <input type="hidden" name="price_rating" id="priceRatingInput" value="">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="reviewTitle" class="form-label">Tiêu đề đánh giá <span class="required">*</span></label>
                                <input type="text" class="form-control" id="reviewTitle" name="title" 
                                       placeholder="Ví dụ: Phòng tuyệt vời, chủ nhà thân thiện" 
                                       value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="reviewContent" class="form-label">Nội dung đánh giá <span class="required">*</span></label>
                                <textarea class="form-control" id="reviewContent" name="content" rows="6" 
                                          placeholder="Chia sẻ chi tiết trải nghiệm của bạn về phòng trọ này..." required>{{ old('content') }}</textarea>
                                <div class="form-text">Tối thiểu 50 ký tự</div>
                                @error('content')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Điểm nổi bật</label>
                                <div class="highlight-options">
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="clean" {{ in_array('clean', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-sparkles"></i>
                                            Sạch sẽ
                                        </span>
                                    </label>
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="location" {{ in_array('location', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-map-marker-alt"></i>
                                            Vị trí tốt
                                        </span>
                                    </label>
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="price" {{ in_array('price', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-dollar-sign"></i>
                                            Giá hợp lý
                                        </span>
                                    </label>
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="friendly" {{ in_array('friendly', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-smile"></i>
                                            Chủ nhà thân thiện
                                        </span>
                                    </label>
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="quiet" {{ in_array('quiet', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-volume-mute"></i>
                                            Yên tĩnh
                                        </span>
                                    </label>
                                    <label class="highlight-option">
                                        <input type="checkbox" name="highlights[]" value="convenient" {{ in_array('convenient', old('highlights', [])) ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-shopping-cart"></i>
                                            Tiện ích
                                        </span>
                                    </label>
                                </div>
                                @error('highlights')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="reviewImages" class="form-label">Hình ảnh (tùy chọn)</label>
                                <input type="file" class="form-control" id="reviewImages" name="images[]" multiple accept="image/*">
                                <div class="form-text">Tải lên hình ảnh thực tế của phòng (tối đa 5 ảnh, mỗi ảnh tối đa 2MB)</div>
                                <div id="reviewImagePreview" class="image-preview"></div>
                                @error('images')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Bạn có giới thiệu phòng này không?</label>
                                <div class="recommend-options">
                                    <label class="recommend-option">
                                        <input type="radio" name="recommend" value="yes" {{ old('recommend') == 'yes' ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-thumbs-up text-success"></i>
                                            Có, tôi sẽ giới thiệu
                                        </span>
                                    </label>
                                    <label class="recommend-option">
                                        <input type="radio" name="recommend" value="maybe" {{ old('recommend') == 'maybe' ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-meh text-warning"></i>
                                            Có thể
                                        </span>
                                    </label>
                                    <label class="recommend-option">
                                        <input type="radio" name="recommend" value="no" {{ old('recommend') == 'no' ? 'checked' : '' }}>
                                        <span class="option-text">
                                            <i class="fas fa-thumbs-down text-danger"></i>
                                            Không
                                        </span>
                                    </label>
                                </div>
                                @error('recommend')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary btn-lg" onclick="window.history.back()">
                                    <i class="fas fa-times me-2"></i>Hủy
                                </button>
                                <button type="submit" class="btn btn-primary btn-lg" id="submitReviewBtn">
                                    <i class="fas fa-paper-plane me-2"></i>Đăng đánh giá
                                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
