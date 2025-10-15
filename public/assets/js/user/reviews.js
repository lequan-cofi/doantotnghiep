// Reviews Page JavaScript
var currentReviewId = null;
var reviewRatings = {
    overall: 0,
    location: 0,
    quality: 0,
    service: 0,
    price: 0
};
var reviewsData = {};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    initializeReviews();
    setupFilters();
    setupSearch();
    setupStarRating();
    setupImageUpload();
    loadReviewsData();
});

// Initialize reviews functionality
function initializeReviews() {
    console.log('Reviews page initialized');
    
    // Setup tooltips if Bootstrap is available
    if (typeof bootstrap !== 'undefined') {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }
    
    // Animate stat cards
    animateStatCards();
}

// Setup filter functionality
function setupFilters() {
    var filterTabs = document.querySelectorAll('.filter-tab');
    var ratingFilter = document.getElementById('ratingFilter');
    
    // Status filter tabs
    for (var i = 0; i < filterTabs.length; i++) {
        filterTabs[i].addEventListener('click', function() {
            var status = this.getAttribute('data-status');
            
            // Update active tab
            for (var j = 0; j < filterTabs.length; j++) {
                filterTabs[j].classList.remove('active');
            }
            this.classList.add('active');
            
            // Filter reviews
            filterReviews();
        });
    }
    
    // Rating filter
    if (ratingFilter) {
        ratingFilter.addEventListener('change', function() {
            filterReviews();
        });
    }
}

// Filter reviews by status and rating
function filterReviews() {
    var activeTab = document.querySelector('.filter-tab.active');
    var status = activeTab ? activeTab.getAttribute('data-status') : 'all';
    var ratingFilter = document.getElementById('ratingFilter');
    var selectedRating = ratingFilter ? ratingFilter.value : '';
    
    var reviews = document.querySelectorAll('.review-card');
    var visibleCount = 0;
    
    for (var i = 0; i < reviews.length; i++) {
        var review = reviews[i];
        var reviewStatus = review.getAttribute('data-status');
        var reviewRating = review.getAttribute('data-rating');
        
        var statusMatch = (status === 'all' || reviewStatus === status);
        var ratingMatch = (!selectedRating || reviewRating === selectedRating);
        
        if (statusMatch && ratingMatch) {
            review.style.display = 'block';
            visibleCount++;
        } else {
            review.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0) {
        emptyState.style.display = 'block';
    } else {
        emptyState.style.display = 'none';
    }
    
    console.log('Filtered reviews:', status, selectedRating, 'visible:', visibleCount);
}

// Setup search functionality
function setupSearch() {
    var searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.toLowerCase();
            searchReviews(searchTerm);
        });
    }
}

// Search reviews
function searchReviews(searchTerm) {
    var reviews = document.querySelectorAll('.review-card');
    var visibleCount = 0;
    
    for (var i = 0; i < reviews.length; i++) {
        var review = reviews[i];
        var propertyTitle = review.querySelector('.property-title').textContent.toLowerCase();
        var propertyAddress = review.querySelector('.property-address').textContent.toLowerCase();
        
        if (propertyTitle.includes(searchTerm) || propertyAddress.includes(searchTerm)) {
            review.style.display = 'block';
            visibleCount++;
        } else {
            review.style.display = 'none';
        }
    }
    
    // Show/hide empty state
    var emptyState = document.querySelector('.empty-state');
    if (visibleCount === 0 && searchTerm.length > 0) {
        emptyState.style.display = 'block';
        emptyState.querySelector('h3').textContent = 'Không tìm thấy đánh giá nào';
        emptyState.querySelector('p').textContent = 'Không có đánh giá nào khớp với từ khóa "' + searchTerm + '".';
    } else if (visibleCount > 0) {
        emptyState.style.display = 'none';
    }
}

// Load reviews data
function loadReviewsData() {
    // Simulate API call to load reviews data
    reviewsData = {
        'review1': {
            id: 'review1',
            property: 'Homestay Hạnh Đào',
            address: '789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội',
            rating: {
                overall: 5,
                location: 5,
                quality: 5,
                service: 5,
                price: 4
            },
            title: 'Phòng tuyệt vời, chủ nhà thân thiện',
            content: 'Phòng rất sạch sẽ, thoáng mát. Chủ nhà thân thiện, hỗ trợ nhiệt tình. Vị trí thuận lợi, gần trường học và chợ. Tôi rất hài lòng với chỗ ở này và sẽ tiếp tục thuê dài hạn.',
            highlights: ['clean', 'location', 'friendly'],
            recommend: 'yes',
            date: '15/11/2023',
            status: 'published',
            helpfulCount: 12,
            viewCount: 156,
            hasReply: false
        },
        'review2': {
            id: 'review2',
            property: 'Chung cư mini Mạnh Hà',
            address: '456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội',
            rating: {
                overall: 4,
                location: 4,
                quality: 4,
                service: 5,
                price: 4
            },
            title: 'Phòng ổn, hơi ồn vào buổi tối',
            content: 'Phòng khá ổn, không gian rộng rãi. Tuy nhiên âm thanh hơi ồn vào buổi tối do gần đường lớn. Chủ nhà dễ tính, giá cả hợp lý.',
            highlights: ['price', 'friendly'],
            recommend: 'maybe',
            date: '20/10/2023',
            status: 'replied',
            helpfulCount: 8,
            viewCount: 98,
            hasReply: true,
            reply: {
                author: 'Chị Lan (Chủ nhà)',
                date: '22/10/2023',
                content: 'Cảm ơn bạn đã đánh giá! Tôi sẽ cải thiện vấn đề về âm thanh bằng cách lắp thêm cửa cách âm. Hy vọng bạn sẽ có trải nghiệm tốt hơn.',
                avatar: 'https://ui-avatars.com/api/?name=Chu+Nha&background=10b981&color=fff&size=40'
            }
        }
    };
    
    console.log('Reviews data loaded');
}

// Animate stat cards
function animateStatCards() {
    var statCards = document.querySelectorAll('.stat-card');
    
    for (var i = 0; i < statCards.length; i++) {
        (function(index, card) {
            setTimeout(function() {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                
                setTimeout(function() {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 50);
            }, index * 100);
        })(i, statCards[i]);
    }
}

// Open write review modal
function openWriteReviewModal() {
    resetReviewForm();
    
    if (typeof bootstrap !== 'undefined') {
        var writeModal = new bootstrap.Modal(document.getElementById('writeReviewModal'));
        writeModal.show();
    } else {
        alert('Tính năng viết đánh giá');
    }
}

// Write review for specific property
function writeReview(propertyId) {
    resetReviewForm();
    
    // Pre-select property
    var propertySelect = document.getElementById('reviewProperty');
    if (propertySelect) {
        propertySelect.value = propertyId;
    }
    
    openWriteReviewModal();
}

// Reset review form
function resetReviewForm() {
    // Reset ratings
    reviewRatings = {
        overall: 0,
        location: 0,
        quality: 0,
        service: 0,
        price: 0
    };
    
    // Reset star displays
    var ratingContainers = document.querySelectorAll('.star-rating-large, .star-rating-small');
    for (var i = 0; i < ratingContainers.length; i++) {
        var stars = ratingContainers[i].querySelectorAll('i');
        for (var j = 0; j < stars.length; j++) {
            stars[j].classList.remove('active');
        }
    }
    
    // Reset rating text
    var ratingTexts = document.querySelectorAll('.rating-text');
    for (var k = 0; k < ratingTexts.length; k++) {
        ratingTexts[k].textContent = 'Chưa đánh giá';
    }
    
    // Reset form
    var form = document.getElementById('writeReviewForm');
    if (form) {
        form.reset();
    }
    
    // Clear image preview
    var preview = document.getElementById('reviewImagePreview');
    if (preview) {
        preview.innerHTML = '';
    }
}

// Setup star rating
function setupStarRating() {
    var ratingContainers = document.querySelectorAll('.star-rating-large, .star-rating-small');
    
    for (var i = 0; i < ratingContainers.length; i++) {
        setupSingleRating(ratingContainers[i]);
    }
}

// Setup single rating container
function setupSingleRating(container) {
    var stars = container.querySelectorAll('i');
    var ratingType = getRatingType(container.id);
    var ratingText = container.nextElementSibling;
    
    for (var i = 0; i < stars.length; i++) {
        stars[i].addEventListener('click', function() {
            var rating = parseInt(this.getAttribute('data-rating'));
            reviewRatings[ratingType] = rating;
            
            // Update star display
            for (var j = 0; j < stars.length; j++) {
                if (j < rating) {
                    stars[j].classList.add('active');
                } else {
                    stars[j].classList.remove('active');
                }
            }
            
            // Update rating text for overall rating
            if (ratingType === 'overall' && ratingText) {
                var ratingTexts = ['Rất tệ', 'Tệ', 'Trung bình', 'Tốt', 'Rất tốt'];
                ratingText.textContent = ratingTexts[rating - 1];
            }
        });
        
        stars[i].addEventListener('mouseover', function() {
            var rating = parseInt(this.getAttribute('data-rating'));
            
            // Highlight stars on hover
            for (var j = 0; j < stars.length; j++) {
                if (j < rating) {
                    stars[j].style.color = '#fbbf24';
                } else {
                    stars[j].style.color = '';
                }
            }
        });
    }
    
    // Reset hover effect
    container.addEventListener('mouseleave', function() {
        var currentRatingValue = reviewRatings[ratingType];
        for (var i = 0; i < stars.length; i++) {
            if (i < currentRatingValue) {
                stars[i].style.color = '#fbbf24';
            } else {
                stars[i].style.color = '';
            }
        }
    });
}

// Get rating type from container ID
function getRatingType(containerId) {
    var types = {
        'overallRating': 'overall',
        'locationRating': 'location',
        'qualityRating': 'quality',
        'serviceRating': 'service',
        'priceRating': 'price'
    };
    return types[containerId] || 'overall';
}

// Setup image upload
function setupImageUpload() {
    var imageInput = document.getElementById('reviewImages');
    
    if (imageInput) {
        imageInput.addEventListener('change', function() {
            handleImageUpload(this, 'reviewImagePreview');
        });
    }
}

// Handle image upload
function handleImageUpload(input, previewId) {
    var preview = document.getElementById(previewId);
    if (preview) {
        preview.innerHTML = '';
    }
    
    if (input.files) {
        for (var i = 0; i < Math.min(input.files.length, 5); i++) {
            var file = input.files[i];
            var reader = new FileReader();
            
            reader.onload = function(e) {
                var previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.innerHTML = 
                    '<img src="' + e.target.result + '" alt="Preview">' +
                    '<button type="button" class="remove-btn" onclick="removePreviewImage(this)">' +
                        '<i class="fas fa-times"></i>' +
                    '</button>';
                if (preview) {
                    preview.appendChild(previewItem);
                }
            };
            
            reader.readAsDataURL(file);
        }
    }
}

// Remove preview image
function removePreviewImage(button) {
    button.parentElement.remove();
}

// Submit review
function submitReview() {
    var form = document.getElementById('writeReviewForm');
    
    // Validate required fields
    var property = document.getElementById('reviewProperty').value;
    var title = document.getElementById('reviewTitle').value;
    var content = document.getElementById('reviewContent').value;
    
    if (!property || !title || !content.trim()) {
        showToast('Vui lòng điền đầy đủ thông tin bắt buộc', 'error');
        return;
    }
    
    if (reviewRatings.overall === 0) {
        showToast('Vui lòng đánh giá tổng thể', 'error');
        return;
    }
    
    if (content.length < 50) {
        showToast('Nội dung đánh giá phải có ít nhất 50 ký tự', 'error');
        return;
    }
    
    // Collect form data
    var highlights = [];
    var highlightInputs = document.querySelectorAll('input[name="highlights"]:checked');
    for (var i = 0; i < highlightInputs.length; i++) {
        highlights.push(highlightInputs[i].value);
    }
    
    var recommend = null;
    var recommendInputs = document.querySelectorAll('input[name="recommend"]');
    for (var j = 0; j < recommendInputs.length; j++) {
        if (recommendInputs[j].checked) {
            recommend = recommendInputs[j].value;
            break;
        }
    }
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var writeModal = bootstrap.Modal.getInstance(document.getElementById('writeReviewModal'));
        if (writeModal) {
            writeModal.hide();
        }
    }
    
    // Show loading toast
    showToast('Đang đăng đánh giá...', 'info');
    
    // Simulate API call
    setTimeout(function() {
        // Generate new review ID
        var newId = 'review' + (Object.keys(reviewsData).length + 1);
        
        // Add to reviews data
        reviewsData[newId] = {
            id: newId,
            property: getPropertyName(property),
            title: title,
            content: content,
            rating: Object.assign({}, reviewRatings),
            highlights: highlights,
            recommend: recommend,
            date: new Date().toLocaleDateString('vi-VN'),
            status: 'published',
            helpfulCount: 0,
            viewCount: 0,
            hasReply: false
        };
        
        // Show success modal
        showSuccessModal('Đánh giá đã được đăng!', 'Đánh giá của bạn đã được đăng thành công và sẽ hiển thị công khai.');
        
        // Reset form
        resetReviewForm();
        
        console.log('New review submitted:', newId);
    }, 2000);
}

// Get property name by ID
function getPropertyName(propertyId) {
    var properties = {
        'room1': 'Phòng trọ cao cấp Cầu Giấy',
        'room2': 'Homestay Hạnh Đào',
        'room3': 'Chung cư mini Mạnh Hà'
    };
    return properties[propertyId] || 'Phòng không xác định';
}

// View review details
function viewReviewDetails(reviewId) {
    currentReviewId = reviewId;
    var reviewData = reviewsData[reviewId];
    
    if (!reviewData) {
        showToast('Không tìm thấy thông tin đánh giá', 'error');
        return;
    }
    
    // Show modal
    if (typeof bootstrap !== 'undefined') {
        var detailModal = new bootstrap.Modal(document.getElementById('reviewDetailsModal'));
        detailModal.show();
        
        // Load review details
        loadReviewDetails(reviewData);
    } else {
        alert('Chi tiết đánh giá: ' + reviewData.title);
    }
}

// Load review details into modal
function loadReviewDetails(reviewData) {
    var content = document.getElementById('reviewDetailsContent');
    
    // Show loading initially
    content.innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Đang tải chi tiết đánh giá...</p>
        </div>
    `;
    
    // Simulate loading delay
    setTimeout(function() {
        var overallStars = generateStars(reviewData.rating.overall);
        var locationStars = generateStars(reviewData.rating.location);
        var qualityStars = generateStars(reviewData.rating.quality);
        var serviceStars = generateStars(reviewData.rating.service);
        var priceStars = generateStars(reviewData.rating.price);
        
        var highlightsHtml = reviewData.highlights.map(function(highlight) {
            return getHighlightDisplay(highlight);
        }).join('');
        
        content.innerHTML = `
            <div class="review-detail-header">
                <div class="review-detail-icon">
                    <i class="fas fa-star"></i>
                </div>
                <div class="review-detail-info">
                    <h4>${reviewData.title}</h4>
                    <p>Đánh giá cho: ${reviewData.property}</p>
                </div>
            </div>
            
            <div class="review-sections">
                <div class="review-section">
                    <h5><i class="fas fa-star me-2"></i>Đánh giá chi tiết</h5>
                    <div class="detail-rating">
                        <span class="label">Tổng thể:</span>
                        <div class="stars">${overallStars} (${reviewData.rating.overall}/5)</div>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Vị trí:</span>
                        <div class="stars">${locationStars} (${reviewData.rating.location}/5)</div>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Chất lượng:</span>
                        <div class="stars">${qualityStars} (${reviewData.rating.quality}/5)</div>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Thái độ:</span>
                        <div class="stars">${serviceStars} (${reviewData.rating.service}/5)</div>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Giá cả:</span>
                        <div class="stars">${priceStars} (${reviewData.rating.price}/5)</div>
                    </div>
                </div>
                
                <div class="review-section">
                    <h5><i class="fas fa-comment me-2"></i>Nội dung đánh giá</h5>
                    <p style="line-height: 1.6; color: var(--foreground);">${reviewData.content}</p>
                    
                    ${reviewData.highlights.length > 0 ? 
                        `<div class="highlights-display">
                            ${highlightsHtml}
                        </div>` : ''
                    }
                </div>
                
                <div class="review-section">
                    <h5><i class="fas fa-chart-bar me-2"></i>Thống kê</h5>
                    <div class="detail-rating">
                        <span class="label">Ngày đăng:</span>
                        <span class="value">${reviewData.date}</span>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Lượt xem:</span>
                        <span class="value">${reviewData.viewCount}</span>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Hữu ích:</span>
                        <span class="value">${reviewData.helpfulCount}</span>
                    </div>
                    <div class="detail-rating">
                        <span class="label">Giới thiệu:</span>
                        <span class="value">${getRecommendText(reviewData.recommend)}</span>
                    </div>
                </div>
                
                ${reviewData.hasReply ? 
                    `<div class="review-section">
                        <h5><i class="fas fa-reply me-2"></i>Phản hồi từ chủ nhà</h5>
                        <div class="landlord-reply">
                            <div class="reply-header">
                                <div class="reply-avatar">
                                    <img src="${reviewData.reply.avatar}" alt="Chủ nhà">
                                </div>
                                <div class="reply-info">
                                    <strong>${reviewData.reply.author}</strong>
                                    <span class="reply-date">${reviewData.reply.date}</span>
                                </div>
                            </div>
                            <div class="reply-text">
                                <p>${reviewData.reply.content}</p>
                            </div>
                        </div>
                    </div>` : ''
                }
            </div>
        `;
    }, 1000);
}

// Generate stars HTML
function generateStars(rating) {
    var html = '';
    for (var i = 1; i <= 5; i++) {
        if (i <= rating) {
            html += '<i class="fas fa-star"></i>';
        } else {
            html += '<i class="far fa-star"></i>';
        }
    }
    return html;
}

// Get highlight display
function getHighlightDisplay(highlight) {
    var highlights = {
        'clean': '<span class="highlight-tag"><i class="fas fa-sparkles"></i>Sạch sẽ</span>',
        'location': '<span class="highlight-tag"><i class="fas fa-map-marker-alt"></i>Vị trí tốt</span>',
        'price': '<span class="highlight-tag"><i class="fas fa-dollar-sign"></i>Giá hợp lý</span>',
        'friendly': '<span class="highlight-tag"><i class="fas fa-smile"></i>Thân thiện</span>',
        'quiet': '<span class="highlight-tag"><i class="fas fa-volume-mute"></i>Yên tĩnh</span>',
        'convenient': '<span class="highlight-tag"><i class="fas fa-shopping-cart"></i>Tiện ích</span>'
    };
    return highlights[highlight] || '';
}

// Get recommend text
function getRecommendText(recommend) {
    var texts = {
        'yes': 'Có, sẽ giới thiệu',
        'maybe': 'Có thể',
        'no': 'Không giới thiệu'
    };
    return texts[recommend] || 'Chưa xác định';
}

// Edit review
function editReview(reviewId) {
    var reviewData = reviewsData[reviewId];
    
    if (!reviewData) {
        showToast('Không tìm thấy thông tin đánh giá', 'error');
        return;
    }
    
    // Pre-fill form with existing data
    document.getElementById('reviewTitle').value = reviewData.title;
    document.getElementById('reviewContent').value = reviewData.content;
    
    // Set ratings
    reviewRatings = Object.assign({}, reviewData.rating);
    updateAllStarDisplays();
    
    // Set highlights
    var highlightInputs = document.querySelectorAll('input[name="highlights"]');
    for (var i = 0; i < highlightInputs.length; i++) {
        highlightInputs[i].checked = reviewData.highlights.includes(highlightInputs[i].value);
    }
    
    // Set recommend
    if (reviewData.recommend) {
        var recommendInput = document.querySelector('input[name="recommend"][value="' + reviewData.recommend + '"]');
        if (recommendInput) {
            recommendInput.checked = true;
        }
    }
    
    currentReviewId = reviewId;
    openWriteReviewModal();
}

// Update all star displays
function updateAllStarDisplays() {
    for (var ratingType in reviewRatings) {
        var containerId = ratingType + 'Rating';
        if (ratingType === 'overall') {
            containerId = 'overallRating';
        }
        
        var container = document.getElementById(containerId);
        if (container) {
            var stars = container.querySelectorAll('i');
            var rating = reviewRatings[ratingType];
            
            for (var i = 0; i < stars.length; i++) {
                if (i < rating) {
                    stars[i].classList.add('active');
                } else {
                    stars[i].classList.remove('active');
                }
            }
            
            // Update rating text for overall
            if (ratingType === 'overall') {
                var ratingText = container.nextElementSibling;
                if (ratingText && rating > 0) {
                    var ratingTexts = ['Rất tệ', 'Tệ', 'Trung bình', 'Tốt', 'Rất tốt'];
                    ratingText.textContent = ratingTexts[rating - 1];
                }
            }
        }
    }
}

// Edit current review from detail modal
function editCurrentReview() {
    if (currentReviewId) {
        // Hide detail modal first
        if (typeof bootstrap !== 'undefined') {
            var detailModal = bootstrap.Modal.getInstance(document.getElementById('reviewDetailsModal'));
            if (detailModal) {
                detailModal.hide();
            }
        }
        
        // Open edit modal
        setTimeout(function() {
            editReview(currentReviewId);
        }, 300);
    }
}

// Delete review
function deleteReview(reviewId) {
    if (confirm('Bạn có chắc chắn muốn xóa đánh giá này không?')) {
        showToast('Đang xóa đánh giá...', 'info');
        
        setTimeout(function() {
            // Remove from data
            delete reviewsData[reviewId];
            
            // Remove from UI
            var reviewCard = document.querySelector('.review-card[data-status="published"]');
            if (reviewCard) {
                reviewCard.style.opacity = '0';
                reviewCard.style.transform = 'translateX(-100%)';
                setTimeout(function() {
                    reviewCard.remove();
                }, 300);
            }
            
            showToast('Đã xóa đánh giá thành công', 'success');
        }, 1000);
    }
}

// Thank landlord
function thankLandlord(reviewId) {
    currentReviewId = reviewId;
    
    if (typeof bootstrap !== 'undefined') {
        var thankModal = new bootstrap.Modal(document.getElementById('thankModal'));
        thankModal.show();
    } else {
        if (confirm('Gửi lời cảm ơn đến chủ nhà?')) {
            sendThankYou();
        }
    }
}

// Send thank you message
function sendThankYou() {
    var message = document.getElementById('thankMessage') ? document.getElementById('thankMessage').value : '';
    
    // Hide modal
    if (typeof bootstrap !== 'undefined') {
        var thankModal = bootstrap.Modal.getInstance(document.getElementById('thankModal'));
        if (thankModal) {
            thankModal.hide();
        }
    }
    
    // Simulate sending
    showToast('Đang gửi lời cảm ơn...', 'info');
    
    setTimeout(function() {
        showToast('Đã gửi lời cảm ơn đến chủ nhà!', 'success');
        
        console.log('Thank you sent:', {
            reviewId: currentReviewId,
            message: message
        });
    }, 1500);
}

// Share review
function shareReview(reviewId) {
    var reviewData = reviewsData[reviewId];
    
    if (!reviewData) {
        showToast('Không tìm thấy thông tin đánh giá', 'error');
        return;
    }
    
    // Create share text
    var shareText = `Đánh giá ${reviewData.rating.overall}/5 sao cho ${reviewData.property}: "${reviewData.title}"`;
    var shareUrl = window.location.origin + '/reviews/' + reviewId;
    
    // Use Web Share API if available
    if (navigator.share) {
        navigator.share({
            title: 'Đánh giá phòng trọ',
            text: shareText,
            url: shareUrl
        }).then(function() {
            showToast('Đã chia sẻ đánh giá!', 'success');
        }).catch(function() {
            fallbackShare(shareText, shareUrl);
        });
    } else {
        fallbackShare(shareText, shareUrl);
    }
}

// Fallback share method
function fallbackShare(text, url) {
    // Copy to clipboard
    var textToCopy = text + '\n' + url;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(textToCopy).then(function() {
            showToast('Đã sao chép link chia sẻ vào clipboard!', 'success');
        });
    } else {
        // Fallback for older browsers
        var textArea = document.createElement('textarea');
        textArea.value = textToCopy;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        showToast('Đã sao chép link chia sẻ!', 'success');
    }
}

// Share current review from detail modal
function shareCurrentReview() {
    if (currentReviewId) {
        shareReview(currentReviewId);
    }
}

// Show success modal
function showSuccessModal(title, message) {
    document.getElementById('successTitle').textContent = title;
    document.getElementById('successMessage').textContent = message;
    
    if (typeof bootstrap !== 'undefined') {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    } else {
        alert(title + '\n' + message);
    }
}

// Show toast notification - using unified notification system
function showToast(message, type) {
    if (typeof window.Notify !== 'undefined') {
        const notificationType = type === 'error' ? 'error' : (type === 'success' ? 'success' : (type === 'warning' ? 'warning' : 'info'));
        const title = type === 'error' ? 'Lỗi' : (type === 'success' ? 'Thành công' : (type === 'warning' ? 'Cảnh báo' : 'Thông báo'));
        
        window.Notify.toast({
            title: title,
            message: message,
            type: notificationType,
            duration: type === 'error' ? 8000 : (type === 'success' ? 5000 : 6000)
        });
    } else {
        // Fallback to simple alert if Notify is not available
        alert(message);
    }
}
