@extends('layouts.app')

@section('title', 'Tất cả phòng trọ')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/rooms.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/rooms.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<div class="rooms-container">
    <div class="container">
        <!-- Page Header -->
        <div class="rooms-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div>
                            <h1 class="page-title">Tất cả phòng trọ</h1>
                            <p class="page-subtitle">Khám phá và tìm kiếm phòng trọ phù hợp với nhu cầu của bạn</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid" title="Xem dạng lưới">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list" title="Xem dạng danh sách">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="filter-label">Loại phòng</label>
                    <select class="form-select" id="typeFilter">
                        <option value="">Tất cả loại</option>
                        <option value="phongtro">🏠 Phòng trọ</option>
                        <option value="chungcumini">🏢 Chung cư mini</option>
                        <option value="nhanguyencan">🏘️ Nhà nguyên căn</option>
                        <option value="matbang">🏪 Mặt bằng</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="filter-label">Khoảng giá</label>
                    <select class="form-select" id="priceFilter">
                        <option value="">Tất cả mức giá</option>
                        <option value="0-2">Dưới 2 triệu</option>
                        <option value="2-5">2 - 5 triệu</option>
                        <option value="5-10">5 - 10 triệu</option>
                        <option value="10-20">10 - 20 triệu</option>
                        <option value="20+">Trên 20 triệu</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="filter-label">Diện tích</label>
                    <select class="form-select" id="areaFilter">
                        <option value="">Tất cả diện tích</option>
                        <option value="0-20">Dưới 20m²</option>
                        <option value="20-30">20 - 30m²</option>
                        <option value="30-50">30 - 50m²</option>
                        <option value="50-100">50 - 100m²</option>
                        <option value="100+">Trên 100m²</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="filter-label">Quận/Huyện</label>
                    <select class="form-select" id="districtFilter">
                        <option value="">Tất cả quận</option>
                        <option value="caugiay">Cầu Giấy</option>
                        <option value="hoangmai">Hoàng Mai</option>
                        <option value="dongda">Đống Đa</option>
                        <option value="thanhxuan">Thanh Xuân</option>
                        <option value="hadong">Hà Đông</option>
                        <option value="longbien">Long Biên</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Tìm kiếm theo tên, địa chỉ..." id="searchInput">
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="sort-options">
                        <label class="filter-label">Sắp xếp theo:</label>
                        <select class="form-select" id="sortFilter">
                            <option value="newest">Mới nhất</option>
                            <option value="price-asc">Giá thấp đến cao</option>
                            <option value="price-desc">Giá cao đến thấp</option>
                            <option value="area-asc">Diện tích nhỏ đến lớn</option>
                            <option value="area-desc">Diện tích lớn đến nhỏ</option>
                            <option value="popular">Phổ biến nhất</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-summary">
                <span class="results-count">Tìm thấy <strong id="resultsCount">24</strong> phòng trọ</span>
                <button class="btn btn-outline-secondary btn-sm" onclick="clearFilters()">
                    <i class="fas fa-times me-1"></i>Xóa bộ lọc
                </button>
            </div>
        </div>

        <!-- Rooms Grid/List -->
        <div class="rooms-content">
            <div class="rooms-grid" id="roomsGrid">
                <!-- Room Item 1 -->
                <div class="room-card" data-type="phongtro" data-price="2.5" data-area="25" data-district="caugiay">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop" alt="Phòng trọ">
                        <div class="room-badges">
                            <span class="badge new">Mới</span>
                            <span class="badge featured">Nổi bật</span>
                        </div>
                        <button class="btn-favorite" onclick="toggleFavorite(this)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Phòng trọ cao cấp Cầu Giấy</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                25m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-users"></i>
                                2 người
                            </span>
                            <span class="spec">
                                <i class="fas fa-bed"></i>
                                1 phòng ngủ
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">WiFi</span>
                            <span class="feature">Điều hòa</span>
                            <span class="feature">Máy giặt</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">2.500.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 1) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 1) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Item 2 -->
                <div class="room-card" data-type="chungcumini" data-price="10" data-area="45" data-district="hoangmai">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=400&h=300&fit=crop" alt="Chung cư mini">
                        <div class="room-badges">
                            <span class="badge hot">Hot</span>
                        </div>
                        <button class="btn-favorite" onclick="toggleFavorite(this)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Chung cư mini Mạnh Hà</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            456 Đường Mạnh Hà, Quận Hoàng Mai, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                45m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-users"></i>
                                3 người
                            </span>
                            <span class="spec">
                                <i class="fas fa-bed"></i>
                                2 phòng ngủ
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">WiFi</span>
                            <span class="feature">Thang máy</span>
                            <span class="feature">Ban công</span>
                            <span class="feature">Bảo vệ 24/7</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">10.000.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 2) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 2) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Item 3 -->
                <div class="room-card" data-type="homestay" data-price="8" data-area="35" data-district="hoangmai">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop" alt="Homestay">
                        <button class="btn-favorite active" onclick="toggleFavorite(this)">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Homestay Hạnh Đào</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            789 Đường Hạnh Đào, Quận Hoàng Mai, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                35m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-users"></i>
                                2 người
                            </span>
                            <span class="spec">
                                <i class="fas fa-bed"></i>
                                1 phòng ngủ
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">WiFi</span>
                            <span class="feature">Bếp riêng</span>
                            <span class="feature">Gác lửng</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">8.000.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 3) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 3) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Item 4 -->
                <div class="room-card" data-type="nhanguyencan" data-price="15" data-area="80" data-district="thanhxuan">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1571055107559-3e67626fa8be?w=400&h=300&fit=crop" alt="Nhà nguyên căn">
                        <div class="room-badges">
                            <span class="badge premium">Premium</span>
                        </div>
                        <button class="btn-favorite" onclick="toggleFavorite(this)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Nhà nguyên căn Thanh Xuân</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            321 Đường Thanh Xuân, Quận Thanh Xuân, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                80m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-users"></i>
                                4 người
                            </span>
                            <span class="spec">
                                <i class="fas fa-bed"></i>
                                3 phòng ngủ
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">WiFi</span>
                            <span class="feature">Sân thượng</span>
                            <span class="feature">Chỗ đậu xe</span>
                            <span class="feature">Bảo vệ</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">15.000.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 4) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 4) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Item 5 -->
                <div class="room-card" data-type="matbang" data-price="25" data-area="120" data-district="dongda">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=400&h=300&fit=crop" alt="Mặt bằng">
                        <button class="btn-favorite" onclick="toggleFavorite(this)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Mặt bằng kinh doanh Đống Đa</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            654 Đường Đống Đa, Quận Đống Đa, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                120m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-store"></i>
                                Kinh doanh
                            </span>
                            <span class="spec">
                                <i class="fas fa-car"></i>
                                Chỗ đậu xe
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">Vị trí đẹp</span>
                            <span class="feature">Mặt tiền</span>
                            <span class="feature">Gần chợ</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">25.000.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 5) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 5) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Room Item 6 -->
                <div class="room-card" data-type="phongtro" data-price="3" data-area="30" data-district="hadong">
                    <div class="room-image">
                        <img src="https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=300&fit=crop" alt="Phòng trọ">
                        <button class="btn-favorite" onclick="toggleFavorite(this)">
                            <i class="far fa-heart"></i>
                        </button>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">Phòng trọ sinh viên Hà Đông</h4>
                        <p class="room-address">
                            <i class="fas fa-map-marker-alt"></i>
                            987 Đường Hà Đông, Quận Hà Đông, Hà Nội
                        </p>
                        <div class="room-specs">
                            <span class="spec">
                                <i class="fas fa-expand-arrows-alt"></i>
                                30m²
                            </span>
                            <span class="spec">
                                <i class="fas fa-users"></i>
                                2 người
                            </span>
                            <span class="spec">
                                <i class="fas fa-bed"></i>
                                1 phòng ngủ
                            </span>
                        </div>
                        <div class="room-features">
                            <span class="feature">WiFi</span>
                            <span class="feature">Gần trường</span>
                            <span class="feature">An ninh</span>
                        </div>
                        <div class="room-footer">
                            <div class="room-price">
                                <span class="price">3.000.000 VNĐ</span>
                                <span class="period">/tháng</span>
                            </div>
                            <div class="room-actions">
                                <a href="{{ route('detail', 6) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('booking', 6) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-calendar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div class="empty-state" style="display: none;">
                    <div class="empty-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <h3>Không tìm thấy phòng nào</h3>
                    <p>Không có phòng trọ nào phù hợp với tiêu chí tìm kiếm của bạn.</p>
                    <button class="btn btn-primary" onclick="clearFilters()">
                        <i class="fas fa-refresh me-2"></i>Xóa bộ lọc
                    </button>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="pagination-section">
            <nav aria-label="Rooms pagination">
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
                        <a class="page-link" href="#">4</a>
                    </li>
                    <li class="page-item">
                        <a class="page-link" href="#">Sau</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<!-- Quick Filter Modal -->
<div class="modal fade" id="quickFilterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bộ lọc nhanh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="quick-filters">
                    <h6>Khoảng giá phổ biến</h6>
                    <div class="filter-chips">
                        <button class="filter-chip" data-price="0-2">Dưới 2 triệu</button>
                        <button class="filter-chip" data-price="2-5">2-5 triệu</button>
                        <button class="filter-chip" data-price="5-10">5-10 triệu</button>
                        <button class="filter-chip" data-price="10+">Trên 10 triệu</button>
                    </div>
                    
                    <h6 class="mt-4">Loại phòng</h6>
                    <div class="filter-chips">
                        <button class="filter-chip" data-type="phongtro">Phòng trọ</button>
                        <button class="filter-chip" data-type="chungcumini">Chung cư mini</button>
                        <button class="filter-chip" data-type="nhanguyencan">Nhà nguyên căn</button>
                    </div>
                    
                    <h6 class="mt-4">Quận phổ biến</h6>
                    <div class="filter-chips">
                        <button class="filter-chip" data-district="caugiay">Cầu Giấy</button>
                        <button class="filter-chip" data-district="hoangmai">Hoàng Mai</button>
                        <button class="filter-chip" data-district="dongda">Đống Đa</button>
                        <button class="filter-chip" data-district="thanhxuan">Thanh Xuân</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-primary" onclick="applyQuickFilter()">Áp dụng</button>
            </div>
        </div>
    </div>
</div>
@endsection
