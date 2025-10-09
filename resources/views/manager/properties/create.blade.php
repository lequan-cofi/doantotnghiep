@extends('layouts.manager_dashboard')

@section('title', 'Thêm Bất động sản')
@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Thêm Bất động sản mới</h1>
                <p>Nhập thông tin bất động sản</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.properties.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="propertyForm" method="POST" action="{{ route('manager.properties.store') }}">
                    @csrf
                    
                    <!-- Basic Info -->
                    <h5 class="mb-3"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Tên BĐS <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Loại BĐS</label>
                            <select name="property_type_id" class="form-select">
                                <option value="">-- Chọn loại --</option>
                                @foreach ($propertyTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name_local ?? $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Số tầng</label>
                            <input type="number" name="total_floors" class="form-control" min="1">
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label">Tổng số phòng</label>
                            <input type="number" name="total_rooms" class="form-control" min="0" value="0">
                            <small class="form-text text-muted">Tổng số phòng trong tòa nhà</small>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Chủ sở hữu</label>
                            <select name="owner_id" class="form-select">
                                <option value="">-- Chọn chủ --</option>
                                @foreach ($owners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->full_name }} ({{ $owner->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="1" selected>Hoạt động</option>
                                <option value="0">Tạm ngưng</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Mô tả</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>

                    <hr class="my-4">

                    <!-- Old Location -->
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt"></i> Địa chỉ (Hệ thống cũ)</h5>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <select name="province_code" id="provinceSelect" class="form-select">
                                <option value="">-- Chọn tỉnh/TP --</option>
                                @foreach ($provinces as $province)
                                <option value="{{ $province->code }}">{{ $province->name_local ?? $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Quận/Huyện</label>
                            <select name="district_code" id="districtSelect" class="form-select" disabled>
                                <option value="">-- Chọn quận/huyện --</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Phường/Xã</label>
                            <select name="ward_code" id="wardSelect" class="form-select" disabled>
                                <option value="">-- Chọn phường/xã --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Địa chỉ chi tiết</label>
                        <input type="text" name="street" class="form-control" placeholder="Số nhà, tên đường...">
                    </div>

                    <hr class="my-4">

                    <!-- New Location -->
                    <h5 class="mb-3"><i class="fas fa-map-marker-alt text-primary"></i> Địa chỉ (Hệ thống mới 2025)</h5>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Tỉnh/Thành phố</label>
                            <select name="province_code_2025" id="provinceSelect2025" class="form-select">
                                <option value="">-- Chọn tỉnh/TP --</option>
                                @foreach ($provinces2025 as $province)
                                <option value="{{ $province->code }}">{{ $province->name_local ?? $province->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phường/Xã</label>
                            <select name="ward_code_2025" id="wardSelect2025" class="form-select" disabled>
                                <option value="">-- Chọn phường/xã --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Địa chỉ chi tiết</label>
                        <input type="text" name="street_2025" class="form-control" placeholder="Số nhà, tên đường...">
                    </div>

                    <!-- Submit -->
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('manager.properties.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu BĐS
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('propertyForm');
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show preloader
        if (window.Preloader) {
            window.Preloader.show();
        }
        
        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            console.error('CSRF token not found');
            Notify.error('Lỗi bảo mật: Không tìm thấy CSRF token. Vui lòng tải lại trang và thử lại.', 'Lỗi bảo mật!');
            if (window.Preloader) {
                window.Preloader.hide();
            }
            return;
        }

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Thành công!');
                setTimeout(() => {
                    window.location.href = '{{ route("manager.properties.index") }}';
                }, 1500);
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể tạo bất động sản: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
});

// Cascading dropdowns
document.getElementById('provinceSelect').addEventListener('change', function() {
    const provinceCode = this.value;
    const districtSelect = document.getElementById('districtSelect');
    const wardSelect = document.getElementById('wardSelect');
    
    districtSelect.innerHTML = '<option value="">-- Chọn quận/huyện --</option>';
    wardSelect.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
    wardSelect.disabled = true;
    
    if (!provinceCode) {
        districtSelect.disabled = true;
        return;
    }
    
    fetch(`/api/geo/districts/${provinceCode}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.code;
                opt.textContent = d.name_local || d.name;
                districtSelect.appendChild(opt);
            });
            districtSelect.disabled = false;
        });
});

document.getElementById('districtSelect').addEventListener('change', function() {
    const districtCode = this.value;
    const wardSelect = document.getElementById('wardSelect');
    
    wardSelect.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
    
    if (!districtCode) {
        wardSelect.disabled = true;
        return;
    }
    
    fetch(`/api/geo/wards/${districtCode}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.code;
                opt.textContent = w.name_local || w.name;
                wardSelect.appendChild(opt);
            });
            wardSelect.disabled = false;
        });
});

// New location 2025 cascading dropdowns
document.getElementById('provinceSelect2025').addEventListener('change', function() {
    const provinceCode = this.value;
    const wardSelect2025 = document.getElementById('wardSelect2025');
    
    wardSelect2025.innerHTML = '<option value="">-- Chọn phường/xã --</option>';
    
    if (!provinceCode) {
        wardSelect2025.disabled = true;
        return;
    }
    
    fetch(`/api/geo/wards-2025/${provinceCode}`)
        .then(r => r.json())
        .then(data => {
            data.forEach(w => {
                const opt = document.createElement('option');
                opt.value = w.code;
                opt.textContent = w.name_local || w.name;
                wardSelect2025.appendChild(opt);
            });
            wardSelect2025.disabled = false;
        });
});
</script>
@endsection

