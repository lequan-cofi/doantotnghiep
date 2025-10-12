@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa phòng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa phòng: {{ $unit->code }}
                    </h1>
                    <p class="text-muted mb-0">{{ $unit->property->name }}</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </a>
                    <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @foreach($errors->all() as $error)
                    Notify.error('{{ $error }}');
                @endforeach
            });
        </script>
    @endif

    <form method="POST" action="{{ route('agent.units.update', $unit->id) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Form -->
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="property_id" class="form-label">
                                        Bất động sản <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('property_id') is-invalid @enderror" 
                                            id="property_id" name="property_id" required>
                                        <option value="">Chọn bất động sản</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" 
                                                    {{ old('property_id', $unit->property_id) == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="code" class="form-label">
                                        Mã phòng <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           id="code" 
                                           name="code" 
                                           value="{{ old('code', $unit->code) }}" 
                                           placeholder="VD: P101, A201..."
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="floor" class="form-label">Tầng</label>
                                    <input type="number" 
                                           class="form-control @error('floor') is-invalid @enderror" 
                                           id="floor" 
                                           name="floor" 
                                           value="{{ old('floor', $unit->floor) }}" 
                                           min="1" 
                                           max="100"
                                           placeholder="VD: 1, 2, 3...">
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="area_m2" class="form-label">Diện tích (m²)</label>
                                    <input type="number" 
                                           class="form-control @error('area_m2') is-invalid @enderror" 
                                           id="area_m2" 
                                           name="area_m2" 
                                           value="{{ old('area_m2', $unit->area_m2) }}" 
                                           min="0" 
                                           max="1000"
                                           step="0.1"
                                           placeholder="VD: 25.5">
                                    @error('area_m2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="unit_type" class="form-label">
                                        Loại phòng <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('unit_type') is-invalid @enderror" 
                                            id="unit_type" name="unit_type" required>
                                        <option value="">Chọn loại phòng</option>
                                        <option value="room" {{ old('unit_type', $unit->unit_type) == 'room' ? 'selected' : '' }}>Phòng</option>
                                        <option value="apartment" {{ old('unit_type', $unit->unit_type) == 'apartment' ? 'selected' : '' }}>Căn hộ</option>
                                        <option value="dorm" {{ old('unit_type', $unit->unit_type) == 'dorm' ? 'selected' : '' }}>Ký túc xá</option>
                                        <option value="shared" {{ old('unit_type', $unit->unit_type) == 'shared' ? 'selected' : '' }}>Chung</option>
                                    </select>
                                    @error('unit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="max_occupancy" class="form-label">
                                        Số người tối đa <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('max_occupancy') is-invalid @enderror" 
                                           id="max_occupancy" 
                                           name="max_occupancy" 
                                           value="{{ old('max_occupancy', $unit->max_occupancy) }}" 
                                           min="1" 
                                           max="10"
                                           required>
                                    @error('max_occupancy')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        Trạng thái <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Có sẵn</option>
                                        <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Đã đặt</option>
                                        <option value="occupied" {{ old('status', $unit->status) == 'occupied' ? 'selected' : '' }}>Đã thuê</option>
                                        <option value="maintenance" {{ old('status', $unit->status) == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" 
                                      name="note" 
                                      rows="3"
                                      placeholder="Ghi chú thêm về phòng...">{{ old('note', $unit->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-dollar-sign me-2"></i>Thông tin giá cả
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_rent" class="form-label">
                                        Giá thuê cơ bản (đ/tháng) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           class="form-control @error('base_rent') is-invalid @enderror" 
                                           id="base_rent" 
                                           name="base_rent" 
                                           value="{{ old('base_rent', $unit->base_rent) }}" 
                                           min="0"
                                           step="1000"
                                           required>
                                    @error('base_rent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="deposit_amount" class="form-label">Tiền cọc (đ)</label>
                                    <input type="number" 
                                           class="form-control @error('deposit_amount') is-invalid @enderror" 
                                           id="deposit_amount" 
                                           name="deposit_amount" 
                                           value="{{ old('deposit_amount', $unit->deposit_amount) }}" 
                                           min="0"
                                           step="1000"
                                           placeholder="Thường bằng 1-2 tháng tiền thuê">
                                    @error('deposit_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Images -->
                @if($unit->images && count($unit->images) > 0)
                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-images me-2"></i>Hình ảnh hiện tại
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                @foreach($unit->images as $index => $image)
                                    <div class="col-md-4">
                                        <div class="position-relative">
                                            <img src="{{ asset('storage/' . $image) }}" 
                                                 class="img-thumbnail" 
                                                 style="height: 150px; object-fit: cover;">
                                            <div class="position-absolute top-0 end-0 p-1">
                                                <input type="checkbox" 
                                                       class="form-check-input" 
                                                       name="deleted_images[]" 
                                                       value="{{ $image }}"
                                                       id="delete_image_{{ $index }}"
                                                       onchange="toggleDeleteButton(this)">
                                                <label for="delete_image_{{ $index }}" class="form-check-label text-danger">
                                                    <i class="fas fa-trash"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Chọn hình ảnh muốn xóa</small>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- New Images -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-plus me-2"></i>Thêm hình ảnh mới
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="images" class="form-label">Chọn hình ảnh mới</label>
                            <input type="file" 
                                   class="form-control @error('images') is-invalid @enderror" 
                                   id="images" 
                                   name="images[]" 
                                   multiple 
                                   accept="image/*">
                            <div class="form-text">Có thể chọn nhiều hình ảnh. Định dạng: JPEG, PNG, JPG, GIF, WebP. Tối đa 5MB mỗi file.</div>
                            @error('images')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Image Preview -->
                        <div id="image-preview" class="row g-2"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Amenities -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-star me-2"></i>Tiện ích
                        </h5>
                    </div>
                    <div class="card-body">
                        @if($amenities->count() > 0)
                            <div class="amenities-list">
                                @foreach($amenities->groupBy('category') as $category => $categoryAmenities)
                                    <div class="amenity-category mb-3">
                                        <h6 class="text-muted small mb-2">{{ $category }}</h6>
                                        @foreach($categoryAmenities as $amenity)
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="amenity_{{ $amenity->id }}" 
                                                       name="amenities[]" 
                                                       value="{{ $amenity->id }}"
                                                       {{ in_array($amenity->id, old('amenities', $unit->amenities->pluck('id')->toArray())) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="amenity_{{ $amenity->id }}">
                                                    {{ $amenity->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted">Chưa có tiện ích nào được định nghĩa.</p>
                        @endif
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Cập nhật phòng
                            </button>
                            <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </a>
                            <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Help -->
                <div class="card shadow-sm mt-3">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-question-circle me-2"></i>Hướng dẫn
                        </h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled small">
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Mã phòng phải duy nhất trong cùng bất động sản
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Hình ảnh mới sẽ được thêm vào hình ảnh hiện tại
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                Chọn hình ảnh muốn xóa trước khi cập nhật
                            </li>
                            <li>
                                <i class="fas fa-check text-success me-2"></i>
                                Cập nhật tiện ích phù hợp với phòng
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Image preview
    const imageInput = document.getElementById('images');
    const imagePreview = document.getElementById('image-preview');

    imageInput.addEventListener('change', function(e) {
        imagePreview.innerHTML = '';
        
        if (e.target.files) {
            Array.from(e.target.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const col = document.createElement('div');
                        col.className = 'col-md-4';
                        col.innerHTML = `
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0" 
                                        onclick="removeImagePreview(this)">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `;
                        imagePreview.appendChild(col);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
    });
});

function removeImagePreview(button) {
    button.closest('.col-md-4').remove();
}

function toggleDeleteButton(checkbox) {
    const label = checkbox.nextElementSibling;
    if (checkbox.checked) {
        label.classList.add('text-danger');
    } else {
        label.classList.remove('text-danger');
    }
}
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/agent/units.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission handling
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Show loading notification
            const loadingToast = Notify.info('Đang cập nhật phòng...');
            
            // Disable submit button to prevent double submission
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...';
            }
        });
    }
});
</script>
@endpush
