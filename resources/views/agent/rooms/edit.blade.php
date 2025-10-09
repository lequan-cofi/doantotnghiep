@extends('layouts.agent_dashboad')

@section('title', 'Chỉnh sửa phòng')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/rooms.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('assets/css/agent/rooms-edit.css') }}?v={{ time() }}">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/agent/rooms.js') }}?v={{ time() }}"></script>
<script src="{{ asset('assets/js/agent/rooms-edit.js') }}?v={{ time() }}"></script>
@endpush

@section('content')
<!-- Main Content -->
<main class="main-content">
    <!-- Header -->
    <header class="bg-white border-bottom py-4 mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="fas fa-edit text-warning fs-4"></i>
                        </div>
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-dark">Chỉnh sửa phòng</h1>
                            <p class="text-muted mb-0">Cập nhật thông tin phòng trọ #{{ $id }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <div class="btn-group" role="group">
                        <a href="{{ route('agent.rooms.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>
                            Quay lại
                        </a>
                        <button class="btn btn-warning" onclick="saveRoom()">
                            <i class="fas fa-save me-2"></i>
                            Cập nhật
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Form Content -->
    <div class="container-fluid">
        <form id="roomForm" class="needs-validation" novalidate>
            <input type="hidden" id="roomId" value="{{ $id }}">
            
            <div class="row">
                <!-- Left Column - Main Form -->
                <div class="col-lg-8">
                    <!-- Basic Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-info-circle text-primary me-2"></i>
                                Thông tin cơ bản
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="roomTitle" class="form-label fw-medium">
                                        Tên phòng <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="roomTitle" name="title" 
                                           value="Phòng trọ cao cấp Cầu Giấy" required>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập tên phòng.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="roomType" class="form-label fw-medium">
                                        Loại phòng <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="roomType" name="type" required>
                                        <option value="">Chọn loại phòng</option>
                                        <option value="phongtro" selected>🏠 Phòng trọ</option>
                                        <option value="chungcumini">🏢 Chung cư mini</option>
                                        <option value="nhanguyencan">🏘️ Nhà nguyên căn</option>
                                        <option value="matbang">🏪 Mặt bằng</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Vui lòng chọn loại phòng.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="roomPrice" class="form-label fw-medium">
                                        Giá thuê (VNĐ/tháng) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="roomPrice" name="price" 
                                               value="2500000" required>
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập giá thuê.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="roomArea" class="form-label fw-medium">
                                        Diện tích (m²) <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="roomArea" name="area" 
                                               value="25" required>
                                        <span class="input-group-text">m²</span>
                                    </div>
                                    <div class="invalid-feedback">
                                        Vui lòng nhập diện tích.
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="roomDescription" class="form-label fw-medium">Mô tả chi tiết</label>
                                    <textarea class="form-control" id="roomDescription" name="description" rows="4" 
                                              placeholder="Mô tả chi tiết về phòng trọ, tiện ích, quy định...">Phòng trọ cao cấp với đầy đủ tiện ích hiện đại, vị trí thuận lợi gần trung tâm thành phố. Phòng được trang bị đầy đủ nội thất cơ bản, WiFi miễn phí, điều hòa, máy giặt. An ninh 24/7, có bảo vệ và camera giám sát.</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                Thông tin địa chỉ
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="roomDistrict" class="form-label fw-medium">
                                        Quận/Huyện <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="roomDistrict" name="district" required>
                                        <option value="">Chọn quận/huyện</option>
                                        <option value="caugiay" selected>Cầu Giấy</option>
                                        <option value="hoangmai">Hoàng Mai</option>
                                        <option value="dongda">Đống Đa</option>
                                        <option value="thanhxuan">Thanh Xuân</option>
                                        <option value="hadong">Hà Đông</option>
                                        <option value="longbien">Long Biên</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Vui lòng chọn quận/huyện.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="roomWard" class="form-label fw-medium">Phường/Xã</label>
                                    <input type="text" class="form-control" id="roomWard" name="ward" 
                                           value="Dịch Vọng" placeholder="Nhập phường/xã">
                                </div>
                                <div class="col-12">
                                    <label for="roomAddress" class="form-label fw-medium">
                                        Địa chỉ chi tiết <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" id="roomAddress" name="address" 
                                           value="123 Đường Cầu Giấy, Phường Dịch Vọng, Quận Cầu Giấy, Hà Nội" required
                                           placeholder="Số nhà, tên đường, tên khu vực...">
                                    <div class="invalid-feedback">
                                        Vui lòng nhập địa chỉ chi tiết.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-star text-primary me-2"></i>
                                Tiện ích
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="amenities-grid">
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="wifi" checked>
                                    <span class="amenity-icon">📶</span>
                                    <span class="amenity-name">WiFi</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="aircon" checked>
                                    <span class="amenity-icon">❄️</span>
                                    <span class="amenity-name">Điều hòa</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="washing" checked>
                                    <span class="amenity-icon">🧺</span>
                                    <span class="amenity-name">Máy giặt</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="kitchen">
                                    <span class="amenity-icon">🍳</span>
                                    <span class="amenity-name">Bếp</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="parking">
                                    <span class="amenity-icon">🚗</span>
                                    <span class="amenity-name">Chỗ đậu xe</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="security" checked>
                                    <span class="amenity-icon">🔒</span>
                                    <span class="amenity-name">Bảo vệ</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="elevator">
                                    <span class="amenity-icon">🛗</span>
                                    <span class="amenity-name">Thang máy</span>
                                </label>
                                <label class="amenity-item">
                                    <input type="checkbox" name="amenities[]" value="balcony">
                                    <span class="amenity-icon">🏞️</span>
                                    <span class="amenity-name">Ban công</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Room History -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-history text-primary me-2"></i>
                                Lịch sử thay đổi
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-success"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Phòng được tạo</h6>
                                        <p class="text-muted mb-1">Phòng trọ được thêm vào hệ thống</p>
                                        <small class="text-muted">25/12/2023 14:30 - Nguyễn Văn A</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-warning"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Cập nhật giá thuê</h6>
                                        <p class="text-muted mb-1">Giá thuê được điều chỉnh từ 2.000.000 lên 2.500.000 VNĐ</p>
                                        <small class="text-muted">20/12/2023 10:15 - Nguyễn Văn B</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker bg-info"></div>
                                    <div class="timeline-content">
                                        <h6 class="mb-1">Thêm tiện ích</h6>
                                        <p class="text-muted mb-1">Bổ sung tiện ích WiFi và máy giặt</p>
                                        <small class="text-muted">18/12/2023 16:45 - Nguyễn Văn C</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Images and Settings -->
                <div class="col-lg-4">
                    <!-- Current Images -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-images text-primary me-2"></i>
                                Hình ảnh hiện tại
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="current-images mb-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="position-relative">
                                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=150&h=100&fit=crop" 
                                                 alt="Room" class="img-fluid rounded">
                                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                    onclick="removeImage(1)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="position-relative">
                                            <img src="https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=150&h=100&fit=crop" 
                                                 alt="Room" class="img-fluid rounded">
                                            <button class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" 
                                                    onclick="removeImage(2)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Add New Images -->
                            <div class="image-upload-area" id="imageUploadArea">
                                <div class="upload-placeholder">
                                    <i class="fas fa-plus fs-4 text-muted mb-2"></i>
                                    <p class="mb-1">Thêm hình ảnh mới</p>
                                    <small class="text-muted">Click để chọn</small>
                                </div>
                                <input type="file" id="imageInput" name="new_images[]" multiple accept="image/*" style="display: none;">
                            </div>
                            <div class="image-preview" id="imagePreview"></div>
                        </div>
                    </div>

                    <!-- Room Settings -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-cog text-primary me-2"></i>
                                Cài đặt
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="roomStatus" class="form-label fw-medium">Trạng thái phòng</label>
                                <select class="form-select" id="roomStatus" name="status">
                                    <option value="available" selected>🟢 Còn trống</option>
                                    <option value="rented">🔵 Đã cho thuê</option>
                                    <option value="maintenance">🟡 Bảo trì</option>
                                    <option value="inactive">🔴 Không hoạt động</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="roomPriority" class="form-label fw-medium">Độ ưu tiên</label>
                                <select class="form-select" id="roomPriority" name="priority">
                                    <option value="normal">⭐ Bình thường</option>
                                    <option value="high" selected>⭐⭐ Cao</option>
                                    <option value="premium">⭐⭐⭐ Premium</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="roomCapacity" class="form-label fw-medium">Sức chứa (người)</label>
                                <input type="number" class="form-control" id="roomCapacity" name="capacity" min="1" max="10" value="2">
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="roomFeatured" name="featured" checked>
                                <label class="form-check-label fw-medium" for="roomFeatured">
                                    Phòng nổi bật
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="roomPublished" name="published" checked>
                                <label class="form-check-label fw-medium" for="roomPublished">
                                    Hiển thị công khai
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Room Statistics -->
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-chart-bar text-primary me-2"></i>
                                Thống kê
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-1">1,234</h4>
                                        <small class="text-muted">Lượt xem</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <h4 class="text-success mb-1">45</h4>
                                    <small class="text-muted">Lượt yêu thích</small>
                                </div>
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="text-warning mb-1">12</h4>
                                        <small class="text-muted">Lượt liên hệ</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-info mb-1">3</h4>
                                    <small class="text-muted">Lượt đặt lịch</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-bolt text-primary me-2"></i>
                                Thao tác nhanh
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-outline-primary" onclick="previewRoom()">
                                    <i class="fas fa-eye me-2"></i>
                                    Xem trước
                                </button>
                                <button type="button" class="btn btn-outline-success" onclick="duplicateRoom()">
                                    <i class="fas fa-copy me-2"></i>
                                    Sao chép phòng
                                </button>
                                <button type="button" class="btn btn-outline-info" onclick="viewHistory()">
                                    <i class="fas fa-history me-2"></i>
                                    Xem lịch sử
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="deleteRoom()">
                                    <i class="fas fa-trash me-2"></i>
                                    Xóa phòng
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</main>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem trước phòng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="preview-image">
                            <img src="https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=500&h=300&fit=crop" 
                                 alt="Preview" class="img-fluid rounded">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h4 id="previewTitle">Tên phòng</h4>
                        <p id="previewDescription">Mô tả phòng...</p>
                        <div class="preview-details">
                            <p><strong>Giá:</strong> <span id="previewPrice">0</span> VNĐ/tháng</p>
                            <p><strong>Diện tích:</strong> <span id="previewArea">0</span>m²</p>
                            <p><strong>Địa chỉ:</strong> <span id="previewAddress">Địa chỉ...</span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
