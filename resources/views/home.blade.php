 @extends('layouts.app')
 @section('content')
 <!-- Hero Section -->
  <section class="hero">
    <div class="hero-bg"></div>
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <div class="hero-badge">
                    <i class="fas fa-home"></i>
                    <span>Tìm Phòng Trọ Dễ Dàng</span>
                </div>
                <h1 class="hero-title">
                    Khám Phá Những Phòng Trọ
                    <span class="highlight">Tuyệt Vời Nhất</span>
                    Tại Hà Nội
                </h1>
                <p class="hero-description">
                    Tìm kiếm và thuê phòng trọ chất lượng cao với giá cả hợp lý từ hàng ngàn chủ nhà uy tín trên toàn quốc. Đăng ký ngay để nhận thông báo về những phòng trọ mới nhất.
                </p>
                <div class="hero-buttons">
                    <button class="btn-hero">
                        Khám phá ngay
                        <i class="fas fa-arrow-right"></i>
                    </button>
                    <button class="btn-secondary">
                        <i class="fas fa-play"></i>
                        Xem video
                    </button>
                </div>
            </div>

            <div class="search-section">
                <div class="search-card">
                    <div class="search-tabs">
                        <button class="tab-btn active" data-tab="rent">
                            <i class="fas fa-home"></i>
                            Thuê phòng
                        </button>
                        <button class="tab-btn" data-tab="post">
                            <i class="fas fa-plus"></i>
                            Đăng tin
                        </button>
                    </div>

                    <div class="search-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Khu vực</label>
                                <select class="form-select">
                                    <option>Chọn khu vực</option>
                                    <option>Hà Nội</option>
                                    <option>TP.HCM</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Loại phòng</label>
                                <select class="form-select">
                                    <option>Tất cả loại phòng</option>
                                    <option>Phòng trọ</option>
                                    <option>Chung cư mini</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Giá thuê</label>
                                <select class="form-select">
                                    <option>Chọn mức giá</option>
                                    <option>1-3 triệu</option>
                                    <option>3-5 triệu</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Diện tích</label>
                                <select class="form-select">
                                    <option>Chọn diện tích</option>
                                    <option>20-30m²</option>
                                    <option>30-50m²</option>
                                </select>
                            </div>
                        </div>
                        <button class="btn-search">
                            <i class="fas fa-search"></i>
                            Tìm kiếm ngay
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="categories">
    <div class="container">
        <div class="section-header">
            <h2>Bạn Muốn Tìm Loại Phòng Nào ?</h2>
            <p>Có nhiều sự lựa chọn khác nhau để bạn có thể tìm phòng phù hợp với nhu cầu và ngân sách.</p>
        </div>

        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon blue">
                    <i class="fas fa-home"></i>
                </div>
                <div class="category-content">
                    <h3>Nhà trọ chung chủ</h3>
                    <p>Có nhiều sự lựa chọn khác nhau</p>
                    <div class="category-footer">
                        <span>1,234 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="category-card">
                <div class="category-icon green">
                    <i class="fas fa-building"></i>
                </div>
                <div class="category-content">
                    <h3>Chung cư mini</h3>
                    <p>Hiện đại, tiện nghi đầy đủ</p>
                    <div class="category-footer">
                        <span>856 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="category-card">
                <div class="category-icon purple">
                    <i class="fas fa-hotel"></i>
                </div>
                <div class="category-content">
                    <h3>Chung cư cao cấp</h3>
                    <p>Sang trọng, dịch vụ hoàn hảo</p>
                    <div class="category-footer">
                        <span>542 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="category-card">
                <div class="category-icon orange">
                    <i class="fas fa-warehouse"></i>
                </div>
                <div class="category-content">
                    <h3>Nhà nguyên căn</h3>
                    <p>Không gian rộng rãi, riêng tư</p>
                    <div class="category-footer">
                        <span>324 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="category-card">
                <div class="category-icon pink">
                    <i class="fas fa-users"></i>
                </div>
                <div class="category-content">
                    <h3>Homestay</h3>
                    <p>Trải nghiệm như ở nhà</p>
                    <div class="category-footer">
                        <span>287 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="category-card">
                <div class="category-icon indigo">
                    <i class="fas fa-map-marker-alt"></i>
                </div>
                <div class="category-content">
                    <h3>Nhà trọ homestay</h3>
                    <p>Ấm cúng, gần gũi</p>
                    <div class="category-footer">
                        <span>198 phòng</span>
                        <a href="{{ route('rooms.index') }}" >
                            <button class="btn-link">Xem ngay</button>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-footer">
            <a href="{{ route('rooms.index') }}" >
                <button class="btn-outline">Xem tất cả danh mục</button>
            </a>
        </div>
    </div>
</section>

<!-- Featured Properties -->
<section class="featured-properties">
    <div class="container">
        <div class="section-header">
            <div class="section-badge">
                <i class="fas fa-trending-up"></i>
                <span>Phòng Trọ Mới Nhất</span>
            </div>
            <h2>Khám Phá Những Phòng Trọ Được Quan Tâm Nhất</h2>
            <p>Khám phá những phòng trọ mới nhất và được quan tâm nhất từ các chủ nhà uy tín trên toàn quốc.</p>
        </div>

        <div class="properties-grid">
            <!-- Property 1 -->
            <div class="property-card" onclick="window.location.href='{{ route('detail', 1) }}'">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" alt="Phòng trọ cao cấp Phương Canh">
                    <div class="property-badges">
                        <span class="badge new">Mới</span>
                        <span class="badge type">Phòng đôi</span>
                    </div>
                    <button class="favorite-btn" onclick="event.stopPropagation()">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Phòng trọ cao cấp Phương Canh</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Phương Canh, Nam Từ Liêm, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>25m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>2 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">1,500,000 VNĐ/tháng</div>
                        <a href="{{ route('detail', 1) }}" class="btn-view" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </a>
                    </div>
                </div>
            </div>

            <!-- Property 2 -->
            <div class="property-card" onclick="window.location.href='{{ route('detail', 2) }}'">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=300&fit=crop" alt="Chung cư mini Mạnh Hà">
                    <div class="property-badges">
                        <span class="badge type">Chung cư mini</span>
                    </div>
                    <button class="favorite-btn active" onclick="event.stopPropagation()">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Chung cư mini Mạnh Hà</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Mạnh Hà, Quận Hoàng Mai, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>45m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>3 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">10,000,000 VNĐ/tháng</div>
                        <a href="{{ route('detail', 2) }}" class="btn-view" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </a>
                    </div>
                </div>
            </div>

            <!-- Property 3 -->
            <div class="property-card" onclick="window.location.href='{{ route('detail', 3) }}'">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop" alt="Homestay Hạnh Đào">
                    <div class="property-badges">
                        <span class="badge type">Homestay</span>
                    </div>
                    <button class="favorite-btn" onclick="event.stopPropagation()">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Homestay Hạnh Đão</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Hạnh Đào, Quận Hoàng Mai, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>35m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>2 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">8,000,000 VNĐ/tháng</div>
                        <a href="{{ route('detail', 3) }}" class="btn-view" onclick="event.stopPropagation()">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </a>
                    </div>
                </div>
            </div>

            <!-- Property 4 -->
            <div class="property-card" onclick="window.location.href='{{ route('detail', 4) }}'">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1484154218962-a197022b5858?w=400&h=300&fit=crop" alt="Khu trọ Phú Kiều">
                    <div class="property-badges">
                        <span class="badge new">Mới</span>
                        <span class="badge type">Khu trọ</span>
                    </div>
                    <button class="favorite-btn">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Khu trọ Phú Kiều</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Phú Kiều, Quận Bắc Từ Liêm, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>30m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>2 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">3,000,000 VNĐ/tháng</div>
                        <button class="btn-view">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </button>
                    </div>
                </div>
            </div>

            <!-- Property 5 -->
            <div class="property-card">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1505691938895-1758d7feb511?w=400&h=300&fit=crop" alt="Khu trọ Nguyên Liên">
                    <div class="property-badges">
                        <span class="badge type">Khu trọ</span>
                    </div>
                    <button class="favorite-btn active">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Khu trọ Nguyên Liên</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Nguyên Liên, Quận Long Biên, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>28m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>2 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">4,500,000 VNĐ/tháng</div>
                        <button class="btn-view">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </button>
                    </div>
                </div>
            </div>

            <!-- Property 6 -->
            <div class="property-card">
                <div class="property-image">
                    <img src="https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=300&fit=crop" alt="Khu trọ abc">
                    <div class="property-badges">
                        <span class="badge type">Khu trọ</span>
                    </div>
                    <button class="favorite-btn">
                        <i class="fas fa-heart"></i>
                    </button>
                    <div class="image-indicators">
                        <span class="indicator active"></span>
                        <span class="indicator"></span>
                    </div>
                </div>
                <div class="property-content">
                    <h3>Khu trọ abc</h3>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>abc, Quận Đống Đa, Hà Nội</span>
                    </div>
                    <div class="property-details">
                        <div class="detail">
                            <i class="fas fa-expand-arrows-alt"></i>
                            <span>32m²</span>
                        </div>
                        <div class="detail">
                            <i class="fas fa-users"></i>
                            <span>3 người</span>
                        </div>
                    </div>
                    <div class="property-footer">
                        <div class="price">4,000,000 VNĐ/tháng</div>
                        <button class="btn-view">
                            <i class="fas fa-eye"></i>
                            Chi tiết
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-footer">
            <a href="{{ route('rooms.index') }}" >
            <button class="btn-hero">
                Xem tất cả phòng trọ
                    <i class="fas fa-arrow-right"></i>
                </button>
            </a>
        </div>
    </div>
</section>
@endsection