@extends('layouts.agent_dashboard')

@section('title', 'Thêm công tơ đo mới')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus me-2"></i>Thêm công tơ đo mới
                    </h1>
                    <p class="text-muted mb-0">Thêm công tơ đo điện, nước cho phòng</p>
                </div>
                <a href="{{ route('agent.meters.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Thông tin công tơ đo
                    </h5>
                </div>
                <div class="card-body">
                    <form id="meterForm" method="POST" action="{{ route('agent.meters.store') }}">
                        @csrf
                        
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
                                                    {{ old('property_id', $selectedProperty?->id) == $property->id ? 'selected' : '' }}>
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
                                    <label for="unit_id" class="form-label">
                                        Phòng <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('unit_id') is-invalid @enderror" 
                                            id="unit_id" name="unit_id" required>
                                        <option value="">Chọn phòng</option>
                                        @if($selectedProperty)
                                            @foreach($units as $unit)
                                                <option value="{{ $unit->id }}" 
                                                        {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->code }} - {{ $unit->unit_type }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="service_id" class="form-label">
                                        Loại dịch vụ <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('service_id') is-invalid @enderror" 
                                            id="service_id" name="service_id" required>
                                        <option value="">Chọn loại dịch vụ</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}" 
                                                    {{ old('service_id') == $service->id ? 'selected' : '' }}>
                                                {{ $service->name }} ({{ $service->key_code }})
                                                @if($service->unit_label)
                                                    - {{ $service->unit_label }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('service_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="serial_no" class="form-label">
                                        Số seri công tơ <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('serial_no') is-invalid @enderror" 
                                           id="serial_no" name="serial_no" 
                                           value="{{ old('serial_no') }}" 
                                           placeholder="Nhập số seri công tơ" required>
                                    @error('serial_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="installed_at" class="form-label">
                                        Ngày lắp đặt <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('installed_at') is-invalid @enderror" 
                                           id="installed_at" name="installed_at" 
                                           value="{{ old('installed_at', date('Y-m-d')) }}" required>
                                    @error('installed_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Trạng thái</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="status" name="status" value="1" 
                                               {{ old('status', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            Hoạt động
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.meters.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Lưu công tơ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Hướng dẫn
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>Lưu ý quan trọng:</h6>
                        <ul class="mb-0">
                            <li>Số seri công tơ phải là duy nhất</li>
                            <li>Chọn đúng loại dịch vụ (điện/nước)</li>
                            <li>Ngày lắp đặt sẽ ảnh hưởng đến tính toán hóa đơn</li>
                            <li>Công tơ phải được kích hoạt để có thể đo số liệu</li>
                        </ul>
                    </div>
                    
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Cảnh báo:</h6>
                        <p class="mb-0">Sau khi tạo công tơ, bạn có thể thêm số liệu đo đầu tiên để bắt đầu theo dõi sử dụng.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('property_id');
    const unitSelect = document.getElementById('unit_id');
    
    // Load units when property changes
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        unitSelect.innerHTML = '<option value="">Đang tải...</option>';
        
        if (propertyId) {
            fetch(`/agent/api/leases/units/${propertyId}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
                
                // Check if data is an array (from leases API)
                if (Array.isArray(data)) {
                    data.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = `${unit.code} - ${unit.unit_type}`;
                        unitSelect.appendChild(option);
                    });
                    
                    if (data.length === 0) {
                        unitSelect.innerHTML = '<option value="">Không có phòng nào</option>';
                    }
                } else if (data.units && data.units.length > 0) {
                    // Fallback for meters API format
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = `${unit.code} - ${unit.unit_type}`;
                        unitSelect.appendChild(option);
                    });
                } else {
                    unitSelect.innerHTML = '<option value="">Không có phòng nào</option>';
                }
            })
            .catch(error => {
                console.error('Error loading units:', error);
                unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                
                // Show error notification
                if (typeof Notify !== 'undefined') {
                    Notify.error('Không thể tải danh sách phòng. Vui lòng thử lại.');
                }
            });
        } else {
            unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
        }
    });
    
    // Form submission with loading state
    const form = document.getElementById('meterForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';
        submitBtn.disabled = true;
        
        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notify.success(data.message);
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            } else {
                Notify.error(data.message);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Có lỗi xảy ra khi lưu công tơ đo');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>
@endpush
