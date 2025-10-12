@extends('layouts.app')

@section('title', $property->name . ' - Chi tiết bất động sản')

@section('content')
<!-- Hero Section -->
<section class="property-hero">
    <div class="hero-slider">
        @if($property->images && count($property->images) > 0)
            @foreach($property->images as $index => $image)
            <div class="hero-slide {{ $index === 0 ? 'active' : '' }}">
                <img src="{{ Storage::url($image) }}" alt="{{ $property->name }}">
            </div>
            @endforeach
        @else
            <div class="hero-slide active">
                <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=1200&h=600&fit=crop" alt="{{ $property->name }}">
            </div>
        @endif
    </div>
    
    <div class="hero-overlay">
        <div class="container">
            <div class="hero-content">
                <div class="property-badges">
                    <span class="badge badge-primary">{{ $property->propertyType ? $property->propertyType->name : 'Bất động sản' }}</span>
                    @if($stats['available_units'] > 0)
                        <span class="badge badge-success">{{ $stats['available_units'] }} phòng trống</span>
                    @endif
                </div>
                <h1 class="property-title">{{ $property->name }}</h1>
                <div class="property-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <span>{{ $property->location2025 ? $property->location2025->street . ', ' . $property->location2025->ward . ', ' . $property->location2025->city : 'Chưa cập nhật địa chỉ' }}</span>
                </div>
                @if($stats['min_price'] && $stats['max_price'])
                    <div class="property-price">
                        @if($stats['min_price'] == $stats['max_price'])
                            <span class="price">{{ number_format($stats['min_price'], 0, ',', '.') }} VNĐ/tháng</span>
                        @else
                            <span class="price">{{ number_format($stats['min_price'], 0, ',', '.') }} - {{ number_format($stats['max_price'], 0, ',', '.') }} VNĐ/tháng</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Property Details Section -->
<section class="property-details-section">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Property Info -->
                <div class="property-info-card">
                    <h3><i class="fas fa-info-circle"></i> Thông tin chi tiết</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <i class="fas fa-building"></i>
                            <div>
                                <strong>Tổng số phòng</strong>
                                <span>{{ $stats['total_units'] }} phòng</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-door-open"></i>
                            <div>
                                <strong>Phòng trống</strong>
                                <span>{{ $stats['available_units'] }} phòng</span>
                            </div>
                        </div>
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <strong>Phòng đã thuê</strong>
                                <span>{{ $stats['occupied_units'] }} phòng</span>
                            </div>
                        </div>
                        @if($property->total_floors)
                        <div class="info-item">
                            <i class="fas fa-layer-group"></i>
                            <div>
                                <strong>Số tầng</strong>
                                <span>{{ $property->total_floors }} tầng</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Description -->
                @if($property->description)
                <div class="property-description-card">
                    <h3><i class="fas fa-align-left"></i> Mô tả</h3>
                    <p>{{ $property->description }}</p>
                </div>
                @endif

                <!-- Amenities -->
                @if($allAmenities->count() > 0)
                <div class="amenities-card">
                    <h3><i class="fas fa-star"></i> Tiện ích</h3>
                    <div class="amenities-grid">
                        @foreach($allAmenities as $amenity)
                        <div class="amenity-item">
                            <i class="fas fa-check"></i>
                            <span>{{ $amenity->name }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Available Units -->
                <div class="units-section">
                    <h3><i class="fas fa-home"></i> Phòng trống ({{ $stats['available_units'] }})</h3>
                    
                    <!-- Viewing Instructions -->
                    <div class="viewing-instructions">
                        <div class="instruction-card">
                            <div class="instruction-header">
                                <i class="fas fa-calendar-check"></i>
                                <h4>Hướng dẫn đặt lịch xem phòng</h4>
                            </div>
                            <div class="instruction-content">
                                <div class="instruction-steps">
                                    <div class="step">
                                        <div class="step-number">1</div>
                                        <div class="step-content">
                                            <strong>Chọn phòng quan tâm</strong>
                                            <p>Nhấn vào phòng bạn muốn xem để xem chi tiết</p>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="step-number">2</div>
                                        <div class="step-content">
                                            <strong>Đặt lịch xem</strong>
                                            <p>Nhấn "Đặt lịch xem" và điền thông tin liên hệ</p>
                                        </div>
                                    </div>
                                    <div class="step">
                                        <div class="step-number">3</div>
                                        <div class="step-content">
                                            <strong>Chờ xác nhận</strong>
                                            <p>Agent sẽ liên hệ lại để xác nhận lịch hẹn</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="instruction-note">
                                    <i class="fas fa-info-circle"></i>
                                    <p><strong>Lưu ý:</strong> Bạn có thể đặt lịch xem nhiều phòng cùng lúc. Agent sẽ sắp xếp lịch phù hợp nhất.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($availableUnits->count() > 0)
                        <div class="units-grid">
                            @foreach($availableUnits as $unit)
                            <div class="unit-card" onclick="showUnitDetail({{ $unit->id }})">
                                <div class="unit-image">
                                    @if($unit->images && count($unit->images) > 0)
                                        <img src="{{ Storage::url($unit->images[0]) }}" alt="{{ $unit->code }}">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=300&h=200&fit=crop" alt="{{ $unit->code }}">
                                    @endif
                                    <div class="unit-badge">
                                        <span class="badge available">Trống</span>
                                    </div>
                                    <div class="unit-overlay">
                                        @auth
                                            <a href="{{ route('booking', [$property->id, $unit->id]) }}" class="btn btn-primary btn-sm" onclick="event.stopPropagation();">
                                                <i class="fas fa-calendar-plus"></i>
                                                Đặt lịch xem
                                            </a>
                                        @else
                                            <button class="btn btn-primary btn-sm" onclick="event.stopPropagation(); scheduleViewingForUnit({{ $unit->id }})">
                                                <i class="fas fa-calendar-plus"></i>
                                                Đặt lịch xem
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                                <div class="unit-content">
                                    <h4>{{ $unit->code }}</h4>
                                    <div class="unit-specs">
                                        <div class="spec">
                                            <i class="fas fa-expand-arrows-alt"></i>
                                            <span>{{ $unit->area_m2 ?? 'N/A' }}m²</span>
                                        </div>
                                        <div class="spec">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $unit->max_occupancy }} người</span>
                                        </div>
                                        <div class="spec">
                                            <i class="fas fa-layer-group"></i>
                                            <span>Tầng {{ $unit->floor ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="unit-price">
                                        {{ number_format($unit->base_rent, 0, ',', '.') }} VNĐ/tháng
                                    </div>
                                    <div class="unit-actions">
                                        @auth
                                            <a href="{{ route('booking', [$property->id, $unit->id]) }}" class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation();">
                                                <i class="fas fa-calendar-alt"></i>
                                                Đặt lịch xem
                                            </a>
                                        @else
                                            <button class="btn btn-outline-primary btn-sm" onclick="event.stopPropagation(); scheduleViewingForUnit({{ $unit->id }})">
                                                <i class="fas fa-calendar-alt"></i>
                                                Đặt lịch xem
                                            </button>
                                        @endauth
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="no-units">
                            <i class="fas fa-door-closed"></i>
                            <h4>Hiện tại không có phòng trống</h4>
                            <p>Vui lòng liên hệ để được thông báo khi có phòng mới.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Contact Card -->
                <div class="contact-card">
                    <h4><i class="fas fa-user-tie"></i> Thông tin liên hệ</h4>
                    @if($agent)
                        <div class="agent-info">
                            <div class="agent-avatar">
                                <img src="{{ $agent->avatar ? Storage::url($agent->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=007bff&color=fff' }}" alt="{{ $agent->name }}">
                            </div>
                            <div class="agent-details">
                                <h5>{{ $agent->name }}</h5>
                                <p class="agent-role">Chủ nhà / Agent</p>
                                @if($agent->phone)
                                    <p class="agent-phone">
                                        <i class="fas fa-phone"></i>
                                        {{ $agent->phone }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="agent-info">
                            <div class="agent-avatar">
                                <img src="https://ui-avatars.com/api/?name=Admin&background=007bff&color=fff" alt="Admin">
                            </div>
                            <div class="agent-details">
                                <h5>Quản lý bất động sản</h5>
                                <p class="agent-role">Hỗ trợ khách hàng</p>
                                <p class="agent-phone">
                                    <i class="fas fa-phone"></i>
                                    0123 456 789
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="contact-buttons">
                        <button class="btn-contact btn-phone" onclick="makeCall()">
                            <i class="fas fa-phone"></i>
                            Gọi ngay
                        </button>
                        <button class="btn-contact btn-zalo" onclick="openZalo()">
                            <i class="fab fa-facebook-messenger"></i>
                            Zalo
                        </button>
                        @auth
                            <a href="{{ route('booking', $property->id) }}" class="btn-contact btn-schedule">
                                <i class="fas fa-calendar-alt"></i>
                                Đặt lịch xem
                            </a>
                        @else
                            <button class="btn-contact btn-schedule" onclick="scheduleViewing()">
                                <i class="fas fa-calendar-alt"></i>
                                Đặt lịch xem
                            </button>
                        @endauth
                    </div>
                </div>

                <!-- Price Info -->
                @if($stats['min_price'] && $stats['max_price'])
                <div class="price-info-card">
                    <h4><i class="fas fa-tag"></i> Thông tin giá</h4>
                    <div class="price-details">
                        @if($stats['min_price'] == $stats['max_price'])
                            <div class="price-item">
                                <span class="label">Giá thuê:</span>
                                <span class="value">{{ number_format($stats['min_price'], 0, ',', '.') }} VNĐ/tháng</span>
                            </div>
                        @else
                            <div class="price-item">
                                <span class="label">Giá từ:</span>
                                <span class="value">{{ number_format($stats['min_price'], 0, ',', '.') }} VNĐ/tháng</span>
                            </div>
                            <div class="price-item">
                                <span class="label">Giá đến:</span>
                                <span class="value">{{ number_format($stats['max_price'], 0, ',', '.') }} VNĐ/tháng</span>
                            </div>
                        @endif
                        @if($stats['min_area'] && $stats['max_area'])
                            <div class="price-item">
                                <span class="label">Diện tích:</span>
                                <span class="value">{{ $stats['min_area'] }} - {{ $stats['max_area'] }}m²</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                <!-- Quick Actions -->
                <div class="quick-actions-card">
                    <h4><i class="fas fa-bolt"></i> Thao tác nhanh</h4>
                    <div class="action-buttons">
                        <button class="btn-action" onclick="shareProperty()">
                            <i class="fas fa-share-alt"></i>
                            Chia sẻ
                        </button>
                        <button class="btn-action" onclick="saveProperty()">
                            <i class="fas fa-heart"></i>
                            Lưu yêu thích
                        </button>
                        <button class="btn-action" onclick="printProperty()">
                            <i class="fas fa-print"></i>
                            In thông tin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Similar Properties -->
@if($similarProperties->count() > 0)
<section class="similar-properties">
    <div class="container">
        <div class="section-header">
            <h2>Bất động sản tương tự</h2>
            <p>Những bất động sản cùng loại khác có thể bạn quan tâm</p>
        </div>
        <div class="properties-grid">
            @foreach($similarProperties as $similarProperty)
            <div class="property-card" onclick="window.location.href='{{ route('property.show', $similarProperty->id) }}'">
                <div class="property-image">
                    @if($similarProperty->images && count($similarProperty->images) > 0)
                        <img src="{{ Storage::url($similarProperty->images[0]) }}" alt="{{ $similarProperty->name }}">
                    @else
                        <img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=400&h=250&fit=crop" alt="{{ $similarProperty->name }}">
                    @endif
                    <div class="property-badge">
                        <span class="badge">{{ $similarProperty->propertyType ? $similarProperty->propertyType->name : 'BĐS' }}</span>
                    </div>
                </div>
                <div class="property-content">
                    <h4>{{ $similarProperty->name }}</h4>
                    <div class="property-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>{{ $similarProperty->location2025 ? $similarProperty->location2025->city : 'Chưa cập nhật' }}</span>
                    </div>
                    <div class="property-info">
                        <span>{{ $similarProperty->units->count() }} phòng trống</span>
                    </div>
                    @php
                        $minPrice = $similarProperty->units->min('base_rent');
                        $maxPrice = $similarProperty->units->max('base_rent');
                    @endphp
                    @if($minPrice && $maxPrice)
                        <div class="property-price">
                            @if($minPrice == $maxPrice)
                                {{ number_format($minPrice, 0, ',', '.') }} VNĐ/tháng
                            @else
                                {{ number_format($minPrice, 0, ',', '.') }} - {{ number_format($maxPrice, 0, ',', '.') }} VNĐ/tháng
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- Unit Detail Modal -->
<div class="modal fade" id="unitDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-home"></i>
                    <span id="unitModalTitle">Chi tiết phòng</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="unitDetailContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Đóng
                </button>
                <button type="button" class="btn btn-success" onclick="makeCall()">
                    <i class="fas fa-phone"></i> Gọi ngay
                </button>
                <button type="button" class="btn btn-primary" onclick="contactAboutUnit()">
                    <i class="fas fa-comments"></i> Liên hệ về phòng này
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Schedule Viewing Modal -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-calendar-plus"></i>
                    Đặt lịch xem bất động sản
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scheduleForm">
                    <input type="hidden" name="property_id" value="{{ $property->id }}">
                    <input type="hidden" name="unit_id" id="selected_unit_id">
                    
                    <!-- Property Info -->
                    <div class="property-info-summary mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <h6 class="mb-1">{{ $property->name }}</h6>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                    {{ $property->location2025 ? $property->location2025->street . ', ' . $property->location2025->ward . ', ' . $property->location2025->city : 'Chưa cập nhật địa chỉ' }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="selected-unit-info" id="selectedUnitInfo" style="display: none;">
                                    <span class="badge bg-primary" id="selectedUnitBadge"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Họ và tên *</label>
                                <input type="text" class="form-control" name="lead_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Số điện thoại *</label>
                                <input type="tel" class="form-control" name="lead_phone" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="lead_email">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày muốn xem *</label>
                                <input type="date" class="form-control" name="schedule_date" id="schedule_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Giờ muốn xem *</label>
                                <select class="form-select" name="schedule_time" id="schedule_time" required>
                                    <option value="">Chọn giờ</option>
                                </select>
                                <div class="form-text">Giờ sẽ được cập nhật sau khi chọn ngày</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" rows="3" placeholder="Ghi chú thêm về yêu cầu xem phòng..."></textarea>
                    </div>

                    <!-- Available Time Slots Info -->
                    <div class="time-slots-info" id="timeSlotsInfo" style="display: none;">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Các khung giờ đã được đặt sẽ không hiển thị trong danh sách.
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Hủy
                </button>
                <button type="button" class="btn btn-primary" onclick="submitSchedule()">
                    <i class="fas fa-calendar-check"></i> Đặt lịch
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* Hero Section */
.property-hero {
    position: relative;
    height: 500px;
    overflow: hidden;
}

.hero-slider {
    position: relative;
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    transition: opacity 0.5s ease;
}

.hero-slide.active {
    opacity: 1;
}

.hero-slide img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(0,0,0,0.7), rgba(0,0,0,0.3));
    display: flex;
    align-items: center;
}

.hero-content {
    color: white;
    max-width: 600px;
}

.property-badges {
    margin-bottom: 20px;
}

.badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    margin-right: 10px;
}

.badge-primary {
    background: #007bff;
    color: white;
}

.badge-success {
    background: #28a745;
    color: white;
}

.property-title {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 15px;
    line-height: 1.2;
}

.property-location {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    font-size: 1.1rem;
    opacity: 0.9;
}

.property-price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #28a745;
}

/* Property Details */
.property-details-section {
    padding: 60px 0;
}

.property-info-card,
.property-description-card,
.amenities-card,
.units-section {
    background: white;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.property-info-card h3,
.property-description-card h3,
.amenities-card h3,
.units-section h3 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 25px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.info-item i {
    font-size: 1.5rem;
    color: #007bff;
    width: 30px;
}

.info-item strong {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
}

.info-item span {
    color: #6c757d;
    font-weight: 500;
}

/* Amenities */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.amenity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #28a745;
}

.amenity-item i {
    color: #28a745;
}

/* Viewing Instructions */
.viewing-instructions {
    margin-bottom: 30px;
}

.instruction-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 25px;
    color: white;
    margin-bottom: 20px;
}

.instruction-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.instruction-header i {
    font-size: 1.5rem;
}

.instruction-header h4 {
    margin: 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.instruction-steps {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.step {
    display: flex;
    gap: 15px;
    align-items: flex-start;
}

.step-number {
    width: 30px;
    height: 30px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
    flex-shrink: 0;
}

.step-content strong {
    display: block;
    margin-bottom: 5px;
    font-size: 1rem;
}

.step-content p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.9;
}

.instruction-note {
    background: rgba(255, 255, 255, 0.1);
    padding: 15px;
    border-radius: 8px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}

.instruction-note i {
    color: #ffc107;
    margin-top: 2px;
}

.instruction-note p {
    margin: 0;
    font-size: 0.9rem;
}

/* Units */
.units-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 25px;
}

.unit-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    cursor: pointer;
    position: relative;
}

.unit-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.15);
}

.unit-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.unit-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.unit-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
}

.unit-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1;
}

.unit-card:hover .unit-overlay {
    opacity: 1;
}

.unit-overlay .btn {
    transform: translateY(20px);
    transition: transform 0.3s ease;
}

.unit-card:hover .unit-overlay .btn {
    transform: translateY(0);
}

.unit-content {
    padding: 20px;
}

.unit-content h4 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2c3e50;
}

.unit-specs {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 15px;
}

.spec {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 0.9rem;
}

.spec i {
    color: #007bff;
}

.unit-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: #28a745;
    margin-bottom: 15px;
}

.unit-actions {
    display: flex;
    gap: 10px;
}

.unit-actions .btn {
    flex: 1;
    font-size: 0.85rem;
    padding: 8px 12px;
}

.no-units {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.no-units i {
    font-size: 3rem;
    margin-bottom: 20px;
}

/* Sidebar */
.contact-card,
.price-info-card,
.quick-actions-card {
    background: white;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.contact-card h4,
.price-info-card h4,
.quick-actions-card h4 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 20px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 10px;
}

.agent-info {
    display: flex;
    gap: 15px;
    margin-bottom: 25px;
}

.agent-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    overflow: hidden;
}

.agent-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.agent-details h5 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 5px;
    color: #2c3e50;
}

.agent-role {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 8px;
}

.agent-phone {
    color: #007bff;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 5px;
}

.contact-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-contact {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-phone {
    background: #28a745;
    color: white;
}

.btn-phone:hover {
    background: #218838;
}

.btn-zalo {
    background: #0068ff;
    color: white;
}

.btn-zalo:hover {
    background: #0056cc;
}

.btn-schedule {
    background: #ffc107;
    color: #212529;
}

.btn-schedule:hover {
    background: #e0a800;
}

.btn-contact {
    text-decoration: none;
    color: inherit;
}

.btn-contact:hover {
    color: inherit;
    text-decoration: none;
}

.price-details {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.price-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.price-item:last-child {
    border-bottom: none;
}

.price-item .label {
    color: #6c757d;
    font-weight: 500;
}

.price-item .value {
    color: #28a745;
    font-weight: 600;
}

.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.btn-action {
    padding: 10px 16px;
    border: 1px solid #dee2e6;
    background: white;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.btn-action:hover {
    background: #f8f9fa;
    border-color: #007bff;
    color: #007bff;
}

/* Similar Properties */
.similar-properties {
    padding: 60px 0;
    background: #f8f9fa;
}

.section-header {
    text-align: center;
    margin-bottom: 40px;
}

.section-header h2 {
    font-size: 2.5rem;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 10px;
}

.section-header p {
    font-size: 1.1rem;
    color: #6c757d;
}

.properties-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 30px;
}

.property-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
    cursor: pointer;
}

.property-card:hover {
    transform: translateY(-5px);
}

.property-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.property-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.property-badge {
    position: absolute;
    top: 15px;
    left: 15px;
}

.property-content {
    padding: 20px;
}

.property-content h4 {
    font-size: 1.2rem;
    font-weight: 600;
    margin-bottom: 10px;
    color: #2c3e50;
}

.property-location {
    display: flex;
    align-items: center;
    gap: 5px;
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.property-info {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 10px;
}

.property-price {
    font-size: 1.1rem;
    font-weight: 700;
    color: #28a745;
}

/* Unit Detail Modal Styles */
.unit-detail-content {
    padding: 20px 0;
}

.unit-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.unit-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
    margin: 0;
}

.unit-status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
}

.unit-status.available {
    background: #d4edda;
    color: #155724;
}

.unit-specifications {
    margin-bottom: 25px;
}

.spec-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f8f9fa;
}

.spec-item:last-child {
    border-bottom: none;
}

.spec-item i {
    font-size: 1.2rem;
    color: #007bff;
    width: 25px;
    text-align: center;
}

.spec-item strong {
    display: block;
    margin-bottom: 3px;
    color: #2c3e50;
    font-size: 0.9rem;
}

.spec-item span {
    color: #6c757d;
    font-weight: 500;
}

.unit-pricing {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 25px;
}

.price-main,
.price-deposit {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.price-deposit {
    margin-bottom: 0;
}

.price-label {
    color: #6c757d;
    font-weight: 500;
}

.price-value {
    color: #28a745;
    font-weight: 700;
    font-size: 1.1rem;
}

.unit-note {
    background: #fff3cd;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
}

.unit-note h6 {
    color: #856404;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unit-note p {
    color: #856404;
    margin: 0;
}

.unit-images-section,
.unit-amenities-section {
    margin-bottom: 25px;
}

.unit-images-section h6,
.unit-amenities-section h6 {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 15px;
    color: #2c3e50;
    display: flex;
    align-items: center;
    gap: 8px;
}

.unit-images-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
}

.unit-image-item {
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.unit-image-item:hover {
    transform: scale(1.05);
}

.unit-image-item img {
    width: 100%;
    height: 120px;
    object-fit: cover;
}

.no-images {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.no-images i {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.amenities-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.amenity-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    background: #e9ecef;
    color: #495057;
    border-radius: 15px;
    font-size: 0.85rem;
    font-weight: 500;
}

.amenity-badge i {
    color: #28a745;
    font-size: 0.8rem;
}

.unit-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 2px solid #f8f9fa;
}

.unit-actions .action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.unit-actions .btn-action {
    flex: 1;
    min-width: 120px;
    padding: 12px 16px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.unit-actions .btn-call {
    background: #28a745;
    color: white;
}

.unit-actions .btn-call:hover {
    background: #218838;
}

.unit-actions .btn-zalo {
    background: #0068ff;
    color: white;
}

.unit-actions .btn-zalo:hover {
    background: #0056cc;
}

.unit-actions .btn-schedule {
    background: #ffc107;
    color: #212529;
}

.unit-actions .btn-schedule:hover {
    background: #e0a800;
}

.unit-actions .btn-share {
    background: #6c757d;
    color: white;
}

.unit-actions .btn-share:hover {
    background: #5a6268;
}

/* Responsive */
@media (max-width: 768px) {
    .property-title {
        font-size: 2rem;
    }
    
    .hero-content {
        padding: 0 20px;
    }
    
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .units-grid {
        grid-template-columns: 1fr;
    }
    
    .amenities-grid {
        grid-template-columns: 1fr;
    }
    
    .properties-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-buttons {
        flex-direction: column;
    }
    
    .unit-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .unit-actions .action-buttons {
        flex-direction: column;
    }
    
    .unit-actions .btn-action {
        min-width: auto;
    }
    
    .unit-images-grid {
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    }
}
</style>
@endpush

@push('scripts')
<script>
// Unit detail functions
function showUnitDetail(unitId) {
    // Find unit data from the available units
    const unit = window.availableUnitsData.find(u => u.id === unitId);
    
    if (!unit) {
        alert('Không tìm thấy thông tin phòng');
        return;
    }
    
    // Store current unit ID for other functions
    window.currentUnitId = unitId;
    
    const modal = new bootstrap.Modal(document.getElementById('unitDetailModal'));
    document.getElementById('unitModalTitle').textContent = `Phòng ${unit.code}`;
    
    // Build unit detail content
    let amenitiesHtml = '';
    if (unit.amenities && unit.amenities.length > 0) {
        amenitiesHtml = `
            <div class="unit-amenities-section">
                <h6><i class="fas fa-star"></i> Tiện ích phòng</h6>
                <div class="amenities-list">
                    ${unit.amenities.map(amenity => `
                        <span class="amenity-badge">
                            <i class="fas fa-check"></i>
                            ${amenity.name}
                        </span>
                    `).join('')}
                </div>
            </div>
        `;
    }
    
    let imagesHtml = '';
    if (unit.images && unit.images.length > 0) {
        imagesHtml = `
            <div class="unit-images-section">
                <h6><i class="fas fa-images"></i> Hình ảnh phòng</h6>
                <div class="unit-images-grid">
                    ${unit.images.map((image, index) => `
                        <div class="unit-image-item">
                            <img src="${image}" alt="Hình ${index + 1}" onclick="openImageModal('${image}')">
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    } else {
        imagesHtml = `
            <div class="unit-images-section">
                <h6><i class="fas fa-images"></i> Hình ảnh phòng</h6>
                <div class="no-images">
                    <i class="fas fa-image"></i>
                    <p>Chưa có hình ảnh</p>
                </div>
            </div>
        `;
    }
    
    document.getElementById('unitDetailContent').innerHTML = `
        <div class="unit-detail-content">
            <div class="row">
                <div class="col-md-6">
                    <div class="unit-main-info">
                        <div class="unit-header">
                            <h4 class="unit-title">${unit.code}</h4>
                            <span class="unit-status available">Trống</span>
                        </div>
                        
                        <div class="unit-specifications">
                            <div class="spec-item">
                                <i class="fas fa-expand-arrows-alt"></i>
                                <div>
                                    <strong>Diện tích</strong>
                                    <span>${unit.area_m2 || 'N/A'}m²</span>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <strong>Sức chứa</strong>
                                    <span>${unit.max_occupancy} người</span>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-layer-group"></i>
                                <div>
                                    <strong>Tầng</strong>
                                    <span>Tầng ${unit.floor || 'N/A'}</span>
                                </div>
                            </div>
                            <div class="spec-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <strong>Loại phòng</strong>
                                    <span>${unit.unit_type || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="unit-pricing">
                            <div class="price-main">
                                <span class="price-label">Giá thuê:</span>
                                <span class="price-value">${formatPrice(unit.base_rent)} VNĐ/tháng</span>
                            </div>
                            ${unit.deposit_amount ? `
                                <div class="price-deposit">
                                    <span class="price-label">Tiền cọc:</span>
                                    <span class="price-value">${formatPrice(unit.deposit_amount)} VNĐ</span>
                                </div>
                            ` : ''}
                        </div>
                        
                        ${unit.note ? `
                            <div class="unit-note">
                                <h6><i class="fas fa-sticky-note"></i> Ghi chú</h6>
                                <p>${unit.note}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="col-md-6">
                    ${imagesHtml}
                    ${amenitiesHtml}
                </div>
            </div>
            
            <div class="unit-actions">
                <div class="action-buttons">
                    <button class="btn-action btn-call" onclick="makeCall()">
                        <i class="fas fa-phone"></i>
                        Gọi ngay
                    </button>
                    <button class="btn-action btn-zalo" onclick="openZalo()">
                        <i class="fab fa-facebook-messenger"></i>
                        Zalo
                    </button>
                    <button class="btn-action btn-schedule" onclick="scheduleViewing()">
                        <i class="fas fa-calendar-alt"></i>
                        Đặt lịch xem
                    </button>
                    <button class="btn-action btn-share" onclick="shareUnit()">
                        <i class="fas fa-share-alt"></i>
                        Chia sẻ
                    </button>
                </div>
            </div>
        </div>
    `;
    
    modal.show();
}

// Helper function to format price
function formatPrice(price) {
    return new Intl.NumberFormat('vi-VN').format(price);
}

// Share unit function
function shareUnit() {
    const unit = window.availableUnitsData.find(u => u.id === window.currentUnitId);
    if (unit) {
        const shareText = `Phòng ${unit.code} - ${formatPrice(unit.base_rent)} VNĐ/tháng - ${unit.area_m2}m²`;
        if (navigator.share) {
            navigator.share({
                title: `Phòng ${unit.code}`,
                text: shareText,
                url: window.location.href
            });
        } else {
            navigator.clipboard.writeText(`${shareText}\n${window.location.href}`);
            alert('Thông tin phòng đã được sao chép!');
        }
    }
}

// Open image modal
function openImageModal(imageSrc) {
    // Create image modal
    const imageModal = document.createElement('div');
    imageModal.className = 'modal fade';
    imageModal.innerHTML = `
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hình ảnh phòng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <img src="${imageSrc}" class="img-fluid" alt="Hình ảnh phòng">
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(imageModal);
    const modal = new bootstrap.Modal(imageModal);
    modal.show();
    
    // Remove modal from DOM when hidden
    imageModal.addEventListener('hidden.bs.modal', () => {
        document.body.removeChild(imageModal);
    });
}

// Contact functions
function makeCall() {
    const phone = '{{ $agent ? $agent->phone : "0123456789" }}';
    window.location.href = `tel:${phone}`;
}

function openZalo() {
    // Open Zalo chat
    alert('Chức năng Zalo sẽ được tích hợp sau');
}

// Global variables
let selectedUnitId = null;

function scheduleViewing() {
    selectedUnitId = null;
    document.getElementById('selected_unit_id').value = '';
    document.getElementById('selectedUnitInfo').style.display = 'none';
    
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
    
    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('schedule_date').min = tomorrow.toISOString().split('T')[0];
}

function scheduleViewingForUnit(unitId) {
    selectedUnitId = unitId;
    document.getElementById('selected_unit_id').value = unitId;
    
    // Find unit data
    const unit = window.availableUnitsData.find(u => u.id === unitId);
    if (unit) {
        document.getElementById('selectedUnitBadge').textContent = `Phòng ${unit.code}`;
        document.getElementById('selectedUnitInfo').style.display = 'block';
    }
    
    const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
    modal.show();
    
    // Set minimum date to tomorrow
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    document.getElementById('schedule_date').min = tomorrow.toISOString().split('T')[0];
}

function submitSchedule() {
    const form = document.getElementById('scheduleForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    // Show loading
    const submitBtn = document.querySelector('#scheduleModal .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
    submitBtn.disabled = true;
    
    // Submit to backend
    fetch('{{ route("viewings.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification('success', data.message);
            
            // Close modal and reset form
            const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
            modal.hide();
            form.reset();
            document.getElementById('selectedUnitInfo').style.display = 'none';
            selectedUnitId = null;
        } else {
            showNotification('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
    })
    .finally(() => {
        // Reset button
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

// Load available time slots when date changes
document.getElementById('schedule_date').addEventListener('change', function() {
    const date = this.value;
    const timeSelect = document.getElementById('schedule_time');
    
    if (!date) {
        timeSelect.innerHTML = '<option value="">Chọn giờ</option>';
        return;
    }
    
    // Show loading
    timeSelect.innerHTML = '<option value="">Đang tải...</option>';
    timeSelect.disabled = true;
    
    // Fetch available slots
    fetch(`{{ route("viewings.available-slots") }}?property_id={{ $property->id }}&date=${date}`)
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            timeSelect.innerHTML = '<option value="">Chọn giờ</option>';
            
            if (data.available_slots.length > 0) {
                data.available_slots.forEach(slot => {
                    const option = document.createElement('option');
                    option.value = slot;
                    option.textContent = `${slot} - ${getNextHour(slot)}`;
                    timeSelect.appendChild(option);
                });
                document.getElementById('timeSlotsInfo').style.display = 'block';
            } else {
                timeSelect.innerHTML = '<option value="">Không có khung giờ trống</option>';
                document.getElementById('timeSlotsInfo').style.display = 'none';
            }
        } else {
            timeSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        timeSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
    })
    .finally(() => {
        timeSelect.disabled = false;
    });
});

function getNextHour(time) {
    const [hour, minute] = time.split(':');
    const nextHour = parseInt(hour) + 1;
    return `${nextHour.toString().padStart(2, '0')}:${minute}`;
}

function showNotification(type, message) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
    notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function contactAboutUnit() {
    const unit = window.availableUnitsData.find(u => u.id === window.currentUnitId);
    if (unit) {
        const message = `Tôi quan tâm đến phòng ${unit.code} - ${formatPrice(unit.base_rent)} VNĐ/tháng - ${unit.area_m2}m². Vui lòng liên hệ lại với tôi.`;
        
        // Open Zalo or show contact info
        if (confirm('Bạn muốn liên hệ qua Zalo hay gọi điện?')) {
            openZalo();
        } else {
            makeCall();
        }
    }
}

// Quick actions
function shareProperty() {
    if (navigator.share) {
        navigator.share({
            title: '{{ $property->name }}',
            text: 'Xem bất động sản này',
            url: window.location.href
        });
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href);
        alert('Link đã được sao chép!');
    }
}

function saveProperty() {
    alert('Đã lưu vào danh sách yêu thích!');
}

function printProperty() {
    window.print();
}

// Load units data for JavaScript
window.availableUnitsData = {!! json_encode($availableUnits->map(function($unit) {
    return [
        'id' => $unit->id,
        'code' => $unit->code,
        'area_m2' => $unit->area_m2,
        'max_occupancy' => $unit->max_occupancy,
        'floor' => $unit->floor,
        'unit_type' => $unit->unit_type,
        'base_rent' => $unit->base_rent,
        'deposit_amount' => $unit->deposit_amount,
        'note' => $unit->note,
        'images' => $unit->images ? array_map(function($image) { return \Storage::url($image); }, $unit->images) : [],
        'amenities' => $unit->amenities->map(function($amenity) {
            return ['id' => $amenity->id, 'name' => $amenity->name];
        })->toArray()
    ];
})) !!};

// Image slider (if multiple images)
document.addEventListener('DOMContentLoaded', function() {
    const slides = document.querySelectorAll('.hero-slide');
    if (slides.length > 1) {
        let currentSlide = 0;
        setInterval(() => {
            slides[currentSlide].classList.remove('active');
            currentSlide = (currentSlide + 1) % slides.length;
            slides[currentSlide].classList.add('active');
        }, 5000);
    }
});
</script>
@endpush
