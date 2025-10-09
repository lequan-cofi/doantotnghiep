@extends('layouts.app')

@section('title', 'Đánh giá của tôi')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/user/reviews.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/user/reviews.js') }}?v={{ time() }}"></script>
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
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Về Dashboard
                        </a>
                        <button class="btn btn-primary ms-2" onclick="openWriteReviewModal()">
                            <i class="fas fa-edit me-2"></i>Viết đánh giá
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-section">
            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card total">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-content">
                            <h3>7</h3>
                            <p>Tổng đánh giá</p>
                            <div class="stat-extra">4.6/5 ⭐</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="stat-card pending">
                        <div class="stat-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="stat-content">
                            <h3>2</h3>
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
                            <h3>4</h3>
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
                            <h3>25</h3>
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
        <div class="reviews-list">
            <!-- Pending Review Item -->
            <div class="review-card pending" data-status="pending" data-rating="0">
                <div class="review-status pending">
                    <i class="fas fa-clock"></i>
                    <span>Chờ đánh giá</span>
                </div>
                <div class="review-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="Phòng trọ">
                                <div class="property-badge">
                                    <span class="badge rentable">Đang thuê</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="property-info">
                                <h4 class="property-title">Phòng trọ cao cấp Cầu Giấy</h4>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội
                                </p>
                                <div class="rental-info">
                                    <div class="rental-period">
                                        <i class="fas fa-calendar"></i>
                                        <span>Đã thuê: 3 tháng (01/12/2023 - hiện tại)</span>
                                    </div>
                                    <div class="rental-price">
                                        <i class="fas fa-money-bill"></i>
                                        <span>2.500.000 VNĐ/tháng</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="review-prompt">
                                <div class="prompt-icon">
                                    <i class="fas fa-edit"></i>
                                </div>
                                <div class="prompt-text">
                                    <h5>Chia sẻ trải nghiệm</h5>
                                    <p>Hãy đánh giá phòng trọ này để giúp người khác</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="review-actions">
                    <button class="btn btn-primary" onclick="writeReview('room1')">
                        <i class="fas fa-star me-1"></i>Viết đánh giá
                    </button>
                </div>
            </div>

            <!-- Published Review Item -->
            <div class="review-card published" data-status="published" data-rating="5">
                <div class="review-status published">
                    <i class="fas fa-check-circle"></i>
                    <span>Đã đăng</span>
                </div>
                <div class="review-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=300&h=200&fit=crop" alt="Homestay">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="review-info">
                                <div class="review-header">
                                    <h4 class="property-title">Homestay Hạnh Đào</h4>
                                    <div class="review-rating">
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="rating-value">5.0</span>
                                    </div>
                                </div>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="review-text">
                                    <p>"Phòng rất sạch sẽ, thoáng mát. Chủ nhà thân thiện, hỗ trợ nhiệt tình. Vị trí thuận lợi, gần trường học và chợ. Tôi rất hài lòng với chỗ ở này và sẽ tiếp tục thuê dài hạn."</p>
                                </div>
                                <div class="review-meta">
                                    <div class="review-date">
                                        <i class="fas fa-calendar"></i>
                                        Đánh giá ngày: 15/11/2023
                                    </div>
                                    <div class="review-stats">
                                        <span class="helpful-count">
                                            <i class="fas fa-thumbs-up"></i>
                                            12 hữu ích
                                        </span>
                                        <span class="view-count">
                                            <i class="fas fa-eye"></i>
                                            156 lượt xem
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="review-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewReviewDetails('review1')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="editReview('review1')">
                        <i class="fas fa-edit me-1"></i>Chỉnh sửa
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="deleteReview('review1')">
                        <i class="fas fa-trash me-1"></i>Xóa
                    </button>
                </div>
            </div>

            <!-- Review with Reply -->
            <div class="review-card replied" data-status="replied" data-rating="4">
                <div class="review-status replied">
                    <i class="fas fa-reply"></i>
                    <span>Có phản hồi</span>
                </div>
                <div class="review-content">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="property-image">
                                <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=300&h=200&fit=crop" alt="Chung cư">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <div class="review-info">
                                <div class="review-header">
                                    <h4 class="property-title">Chung cư mini Mạnh Hà</h4>
                                    <div class="review-rating">
                                        <div class="stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                        </div>
                                        <span class="rating-value">4.0</span>
                                    </div>
                                </div>
                                <p class="property-address">
                                    <i class="fas fa-map-marker-alt"></i>
                                    456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội
                                </p>
                                <div class="review-text">
                                    <p>"Phòng khá ổn, không gian rộng rãi. Tuy nhiên âm thanh hơi ồn vào buổi tối do gần đường lớn. Chủ nhà dễ tính, giá cả hợp lý."</p>
                                </div>
                                <div class="review-meta">
                                    <div class="review-date">
                                        <i class="fas fa-calendar"></i>
                                        Đánh giá ngày: 20/10/2023
                                    </div>
                                    <div class="review-stats">
                                        <span class="helpful-count">
                                            <i class="fas fa-thumbs-up"></i>
                                            8 hữu ích
                                        </span>
                                        <span class="reply-indicator">
                                            <i class="fas fa-reply text-success"></i>
                                            Có phản hồi
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Landlord Reply -->
                                <div class="landlord-reply">
                                    <div class="reply-header">
                                        <div class="reply-avatar">
                                            <img src="https://ui-avatars.com/api/?name=Chu+Nha&background=10b981&color=fff&size=40" alt="Chủ nhà">
                                        </div>
                                        <div class="reply-info">
                                            <strong>Chị Lan (Chủ nhà)</strong>
                                            <span class="reply-date">22/10/2023</span>
                                        </div>
                                    </div>
                                    <div class="reply-text">
                                        <p>"Cảm ơn bạn đã đánh giá! Tôi sẽ cải thiện vấn đề về âm thanh bằng cách lắp thêm cửa cách âm. Hy vọng bạn sẽ có trải nghiệm tốt hơn."</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="review-actions">
                    <button class="btn btn-outline-primary btn-sm" onclick="viewReviewDetails('review2')">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </button>
                    <button class="btn btn-outline-success btn-sm" onclick="thankLandlord('review2')">
                        <i class="fas fa-heart me-1"></i>Cảm ơn
                    </button>
                    <button class="btn btn-outline-info btn-sm" onclick="shareReview('review2')">
                        <i class="fas fa-share me-1"></i>Chia sẻ
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div class="empty-state" style="display: none;">
                <div class="empty-icon">
                    <i class="fas fa-star"></i>
                </div>
                <h3>Chưa có đánh giá nào</h3>
                <p>Bạn chưa viết đánh giá nào. Hãy chia sẻ trải nghiệm của bạn!</p>
                <button class="btn btn-primary" onclick="openWriteReviewModal()">
                    <i class="fas fa-edit me-2"></i>Viết đánh giá đầu tiên
                </button>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Reviews pagination">
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
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Write Review Modal -->
<div class="modal fade" id="writeReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Viết đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="writeReviewForm">
                    <div class="mb-4">
                        <label for="reviewProperty" class="form-label">Chọn phòng để đánh giá <span class="required">*</span></label>
                        <select class="form-select" id="reviewProperty" required>
                            <option value="">Chọn phòng bạn đã/đang thuê</option>
                            <option value="room1">Phòng trọ cao cấp Cầu Giấy</option>
                            <option value="room2">Homestay Hạnh Đào</option>
                            <option value="room3">Chung cư mini Mạnh Hà</option>
                        </select>
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
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reviewTitle" class="form-label">Tiêu đề đánh giá <span class="required">*</span></label>
                        <input type="text" class="form-control" id="reviewTitle" placeholder="Ví dụ: Phòng tuyệt vời, chủ nhà thân thiện" required>
                    </div>

                    <div class="mb-4">
                        <label for="reviewContent" class="form-label">Nội dung đánh giá <span class="required">*</span></label>
                        <textarea class="form-control" id="reviewContent" rows="6" placeholder="Chia sẻ chi tiết trải nghiệm của bạn về phòng trọ này..." required></textarea>
                        <div class="form-text">Tối thiểu 50 ký tự</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Điểm nổi bật</label>
                        <div class="highlight-options">
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="clean">
                                <span class="option-text">
                                    <i class="fas fa-sparkles"></i>
                                    Sạch sẽ
                                </span>
                            </label>
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="location">
                                <span class="option-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                    Vị trí tốt
                                </span>
                            </label>
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="price">
                                <span class="option-text">
                                    <i class="fas fa-dollar-sign"></i>
                                    Giá hợp lý
                                </span>
                            </label>
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="friendly">
                                <span class="option-text">
                                    <i class="fas fa-smile"></i>
                                    Chủ nhà thân thiện
                                </span>
                            </label>
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="quiet">
                                <span class="option-text">
                                    <i class="fas fa-volume-mute"></i>
                                    Yên tĩnh
                                </span>
                            </label>
                            <label class="highlight-option">
                                <input type="checkbox" name="highlights" value="convenient">
                                <span class="option-text">
                                    <i class="fas fa-shopping-cart"></i>
                                    Tiện ích
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="reviewImages" class="form-label">Hình ảnh (tùy chọn)</label>
                        <input type="file" class="form-control" id="reviewImages" multiple accept="image/*">
                        <div class="form-text">Tải lên hình ảnh thực tế của phòng</div>
                        <div id="reviewImagePreview" class="image-preview"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Bạn có giới thiệu phòng này không?</label>
                        <div class="recommend-options">
                            <label class="recommend-option">
                                <input type="radio" name="recommend" value="yes">
                                <span class="option-text">
                                    <i class="fas fa-thumbs-up text-success"></i>
                                    Có, tôi sẽ giới thiệu
                                </span>
                            </label>
                            <label class="recommend-option">
                                <input type="radio" name="recommend" value="maybe">
                                <span class="option-text">
                                    <i class="fas fa-meh text-warning"></i>
                                    Có thể
                                </span>
                            </label>
                            <label class="recommend-option">
                                <input type="radio" name="recommend" value="no">
                                <span class="option-text">
                                    <i class="fas fa-thumbs-down text-danger"></i>
                                    Không
                                </span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" onclick="submitReview()">
                    <i class="fas fa-paper-plane me-1"></i>Đăng đánh giá
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Review Details Modal -->
<div class="modal fade" id="reviewDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="review-details-content" id="reviewDetailsContent">
                    <!-- Content will be loaded by JS -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-outline-primary" onclick="editCurrentReview()">
                    <i class="fas fa-edit me-1"></i>Chỉnh sửa
                </button>
                <button type="button" class="btn btn-outline-info" onclick="shareCurrentReview()">
                    <i class="fas fa-share me-1"></i>Chia sẻ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="success-icon">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <h4 class="text-success mt-3" id="successTitle">Thành công!</h4>
                <p id="successMessage">Đánh giá của bạn đã được đăng thành công.</p>
                <div class="success-features">
                    <div class="feature-item">
                        <i class="fas fa-eye text-primary"></i>
                        <span>Hiển thị công khai</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-bell text-warning"></i>
                        <span>Thông báo cho chủ nhà</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-users text-info"></i>
                        <span>Giúp người khác tham khảo</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-success" data-bs-dismiss="modal">Tuyệt vời!</button>
            </div>
        </div>
    </div>
</div>

<!-- Thank You Modal -->
<div class="modal fade" id="thankModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="thank-icon">
                    <i class="fas fa-heart text-danger"></i>
                </div>
                <h4 class="mt-3">Cảm ơn chủ nhà</h4>
                <p>Gửi lời cảm ơn đến chủ nhà vì đã phản hồi đánh giá của bạn?</p>
                <textarea class="form-control" id="thankMessage" rows="3" placeholder="Viết lời cảm ơn (tùy chọn)..."></textarea>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" onclick="sendThankYou()">
                    <i class="fas fa-heart me-1"></i>Gửi cảm ơn
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
