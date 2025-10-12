@extends('layouts.agent_dashboard')

@section('title', 'Thêm số liệu đo mới')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-plus me-2"></i>Thêm số liệu đo mới
                    </h1>
                    <p class="text-muted mb-0">Thêm số liệu đo cho công tơ điện, nước</p>
                </div>
                <a href="{{ route('agent.meter-readings.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-chart-line me-2"></i>Thông tin số liệu đo
                    </h5>
                </div>
                <div class="card-body">
                    <form id="readingForm" method="POST" action="{{ route('agent.meter-readings.store') }}" 
                          enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="meter_id" class="form-label">
                                        Công tơ đo <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('meter_id') is-invalid @enderror" 
                                            id="meter_id" name="meter_id" required>
                                        <option value="">Chọn công tơ đo</option>
                                        @foreach($meters as $meter)
                                            <option value="{{ $meter->id }}" 
                                                    {{ old('meter_id', $selectedMeter?->id) == $meter->id ? 'selected' : '' }}>
                                                {{ $meter->serial_no }} - {{ $meter->property->name }} - {{ $meter->unit->code }}
                                                ({{ $meter->service->name }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('meter_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reading_date" class="form-label">
                                        Ngày đo <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('reading_date') is-invalid @enderror" 
                                           id="reading_date" name="reading_date" 
                                           value="{{ old('reading_date', date('Y-m-d')) }}" required>
                                    @error('reading_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="value" class="form-label">
                                        Số liệu đo <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.001" min="0" 
                                           class="form-control @error('value') is-invalid @enderror" 
                                           id="value" name="value" 
                                           value="{{ old('value') }}" 
                                           placeholder="Nhập số liệu đo" required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <span id="unitLabel">Đơn vị sẽ hiển thị khi chọn công tơ</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Hình ảnh công tơ</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Chọn hình ảnh chụp công tơ (tùy chọn)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="3" 
                                      placeholder="Nhập ghi chú (tùy chọn)">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.meter-readings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Lưu số liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Last Reading Info -->
            @if($selectedMeter && $lastReading)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-history me-2"></i>Số liệu cuối
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Ngày đo:</strong></div>
                            <div class="col-sm-8">{{ $lastReading->reading_date->format('d/m/Y') }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Số liệu:</strong></div>
                            <div class="col-sm-8">
                                <div class="fw-bold text-primary">
                                    {{ number_format($lastReading->value, 3) }}
                                </div>
                                <small class="text-muted">{{ $selectedMeter->service->unit_label }}</small>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Người đo:</strong></div>
                            <div class="col-sm-8">{{ $lastReading->takenBy->name ?? 'N/A' }}</div>
                        </div>
                        @if($lastReading->note)
                            <div class="row mb-3">
                                <div class="col-sm-4"><strong>Ghi chú:</strong></div>
                                <div class="col-sm-8">{{ $lastReading->note }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Meter Info -->
            @if($selectedMeter)
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-tachometer-alt me-2"></i>Thông tin công tơ
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Số seri:</strong></div>
                            <div class="col-sm-8">{{ $selectedMeter->serial_no }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Bất động sản:</strong></div>
                            <div class="col-sm-8">{{ $selectedMeter->property->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Phòng:</strong></div>
                            <div class="col-sm-8">{{ $selectedMeter->unit->code }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Dịch vụ:</strong></div>
                            <div class="col-sm-8">{{ $selectedMeter->service->name }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-sm-4"><strong>Đơn vị:</strong></div>
                            <div class="col-sm-8">{{ $selectedMeter->service->unit_label }}</div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Instructions -->
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
                            <li>Số liệu đo phải lớn hơn hoặc bằng số liệu trước đó</li>
                            <li>Ngày đo không được trùng với ngày đã có số liệu</li>
                            <li>Hệ thống sẽ tự động tính lượng sử dụng và tạo hóa đơn</li>
                            <li>Hình ảnh giúp xác minh số liệu đo</li>
                        </ul>
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
    const meterSelect = document.getElementById('meter_id');
    const unitLabel = document.getElementById('unitLabel');
    const valueInput = document.getElementById('value');
    
    // Load meter info when meter changes
    meterSelect.addEventListener('change', function() {
        const meterId = this.value;
        
        if (meterId) {
            fetch(`/agent/meter-readings/get-last-reading?meter_id=${meterId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.lastReading) {
                        // Show last reading info
                        const lastValue = parseFloat(data.lastReading.value);
                        valueInput.min = lastValue;
                        valueInput.placeholder = `Tối thiểu: ${lastValue.toFixed(3)}`;
                        
                        // Update unit label
                        const selectedOption = meterSelect.options[meterSelect.selectedIndex];
                        const serviceName = selectedOption.text.split('(')[1]?.split(')')[0] || '';
                        unitLabel.textContent = `Đơn vị: ${serviceName}`;
                    }
                })
                .catch(error => {
                    console.error('Error loading last reading:', error);
                });
        } else {
            valueInput.min = 0;
            valueInput.placeholder = 'Nhập số liệu đo';
            unitLabel.textContent = 'Đơn vị sẽ hiển thị khi chọn công tơ';
        }
    });
    
    // Form submission with loading state
    const form = document.getElementById('readingForm');
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
            Notify.error('Có lỗi xảy ra khi lưu số liệu đo');
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
    
    // Image preview
    const imageInput = document.getElementById('image');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Create preview if not exists
                let preview = document.getElementById('imagePreview');
                if (!preview) {
                    preview = document.createElement('div');
                    preview.id = 'imagePreview';
                    preview.className = 'mt-2';
                    imageInput.parentNode.appendChild(preview);
                }
                preview.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-width: 200px; max-height: 200px;" alt="Preview">
                    <div class="mt-1">
                        <small class="text-muted">Hình ảnh đã chọn: ${file.name}</small>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
