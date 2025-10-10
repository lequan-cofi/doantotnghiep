@extends('layouts.app')

@section('title', 'Danh sách phòng trọ')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="page-header">
                <h1>Danh sách phòng trọ</h1>
                <p>Tìm kiếm phòng trọ phù hợp với nhu cầu của bạn</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="filter-card">
                <form method="GET" action="{{ route('rooms.index') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type">Loại phòng</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Tất cả</option>
                                    <option value="shared" {{ request('type') == 'shared' ? 'selected' : '' }}>Nhà trọ chung chủ</option>
                                    <option value="mini" {{ request('type') == 'mini' ? 'selected' : '' }}>Chung cư mini</option>
                                    <option value="apartment" {{ request('type') == 'apartment' ? 'selected' : '' }}>Căn hộ cao cấp</option>
                                    <option value="house" {{ request('type') == 'house' ? 'selected' : '' }}>Nhà nguyên căn</option>
                                    <option value="homestay" {{ request('type') == 'homestay' ? 'selected' : '' }}>Homestay</option>
                                    <option value="hostel" {{ request('type') == 'hostel' ? 'selected' : '' }}>Hostel</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="price_min">Giá tối thiểu (VND)</label>
                                <input type="number" name="price_min" id="price_min" class="form-control" 
                                       value="{{ request('price_min') }}" placeholder="0">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="price_max">Giá tối đa (VND)</label>
                                <input type="number" name="price_max" id="price_max" class="form-control" 
                                       value="{{ request('price_max') }}" placeholder="10000000">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search"></i> Tìm kiếm
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results Section -->
    <div class="row">
        <div class="col-12">
            <div class="results-header">
                <h3>Kết quả tìm kiếm</h3>
                <p>Hiển thị {{ $rooms->count() ?? 0 }} phòng trọ</p>
            </div>
        </div>
    </div>

    <!-- Rooms Grid -->
    <div class="row">
        @if(isset($rooms) && $rooms->count() > 0)
            @foreach($rooms as $room)
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="room-card">
                    <div class="room-image">
                        <img src="{{ $room->image ?? '/assets/images/placeholder-room.jpg' }}" 
                             alt="{{ $room->title }}" class="img-fluid">
                        <div class="room-badge">
                            <span class="badge badge-primary">{{ $room->type ?? 'Phòng trọ' }}</span>
                        </div>
                    </div>
                    <div class="room-content">
                        <h4 class="room-title">{{ $room->title ?? 'Phòng trọ' }}</h4>
                        <p class="room-location">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $room->address ?? 'Địa chỉ không xác định' }}
                        </p>
                        <div class="room-features">
                            <span class="feature">
                                <i class="fas fa-bed"></i> {{ $room->bedrooms ?? 1 }} phòng ngủ
                            </span>
                            <span class="feature">
                                <i class="fas fa-bath"></i> {{ $room->bathrooms ?? 1 }} phòng tắm
                            </span>
                            <span class="feature">
                                <i class="fas fa-ruler-combined"></i> {{ $room->area ?? 20 }}m²
                            </span>
                        </div>
                        <div class="room-price">
                            <span class="price">{{ number_format($room->price ?? 0) }} VND</span>
                            <span class="period">/tháng</span>
                        </div>
                        <div class="room-actions">
                            <a href="{{ route('rooms.show', $room->id ?? 1) }}" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Xem chi tiết
                            </a>
                            <button class="btn btn-outline-primary">
                                <i class="fas fa-heart"></i> Yêu thích
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12">
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-search fa-3x"></i>
                    </div>
                    <h3>Không tìm thấy phòng trọ nào</h3>
                    <p>Hãy thử thay đổi bộ lọc tìm kiếm hoặc liên hệ với chúng tôi để được hỗ trợ.</p>
                    <a href="{{ route('home') }}" class="btn btn-primary">
                        <i class="fas fa-home"></i> Về trang chủ
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if(isset($rooms) && $rooms->hasPages())
    <div class="row">
        <div class="col-12">
            <div class="pagination-wrapper">
                {{ $rooms->links() }}
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.page-header {
    text-align: center;
    padding: 2rem 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    margin-bottom: 2rem;
    border-radius: 0.5rem;
}

.filter-card {
    background: white;
    padding: 1.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.results-header {
    margin-bottom: 1.5rem;
}

.room-card {
    background: white;
    border-radius: 0.5rem;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}

.room-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}

.room-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.room-badge {
    position: absolute;
    top: 1rem;
    right: 1rem;
}

.room-content {
    padding: 1.5rem;
}

.room-title {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #2c3e50;
}

.room-location {
    color: #6c757d;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.room-features {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1rem;
}

.feature {
    font-size: 0.85rem;
    color: #6c757d;
}

.room-price {
    margin-bottom: 1rem;
}

.price {
    font-size: 1.5rem;
    font-weight: 700;
    color: #e74c3c;
}

.period {
    color: #6c757d;
    font-size: 0.9rem;
}

.room-actions {
    display: flex;
    gap: 0.5rem;
}

.room-actions .btn {
    flex: 1;
}

.no-results {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 0.5rem;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-results-icon {
    color: #6c757d;
    margin-bottom: 1rem;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 2rem;
}
</style>
@endpush
