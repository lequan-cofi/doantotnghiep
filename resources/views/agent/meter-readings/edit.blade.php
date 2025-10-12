@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa số liệu đo')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-edit me-2"></i>Chỉnh sửa số liệu đo
                    </h1>
                    <p class="text-muted mb-0">{{ $meterReading->meter->serial_no }} - {{ $meterReading->reading_date->format('d/m/Y') }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('agent.meter-readings.show', $meterReading->id) }}" class="btn btn-outline-info">
                        <i class="fas fa-eye me-1"></i>Xem chi tiết
                    </a>
                    <a href="{{ route('agent.meter-readings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
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
                    <form id="readingForm" method="POST" action="{{ route('agent.meter-readings.update', $meterReading->id) }}" 
                          enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reading_date" class="form-label">
                                        Ngày đo <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control @error('reading_date') is-invalid @enderror" 
                                           id="reading_date" name="reading_date" 
                                           value="{{ old('reading_date', $meterReading->reading_date->format('Y-m-d')) }}" required>
                                    @error('reading_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="value" class="form-label">
                                        Số liệu đo <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" step="0.001" min="0" 
                                           class="form-control @error('value') is-invalid @enderror" 
                                           id="value" name="value" 
                                           value="{{ old('value', $meterReading->value) }}" 
                                           placeholder="Nhập số liệu đo" required>
                                    @error('value')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        Đơn vị: {{ $meterReading->meter->service->unit_label }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="image" class="form-label">Hình ảnh công tơ</label>
                                    <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                           id="image" name="image" accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Chọn hình ảnh mới để thay thế (tùy chọn)</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                @if($meterReading->image_url)
                                    <div class="mb-3">
                                        <label class="form-label">Hình ảnh hiện tại</label>
                                        <div>
                                            <img src="{{ Storage::url($meterReading->image_url) }}" 
                                                 class="img-thumbnail" style="max-width: 150px; max-height: 150px;" 
                                                 alt="Hình ảnh hiện tại">
                                            <div class="mt-1">
                                                <small class="text-muted">Hình ảnh hiện tại</small>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="note" class="form-label">Ghi chú</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="3" 
                                      placeholder="Nhập ghi chú (tùy chọn)">{{ old('note', $meterReading->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.meter-readings.show', $meterReading->id) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Cập nhật số liệu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Current Reading Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Thông tin hiện tại
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Công tơ:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->serial_no }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Bất động sản:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->property->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Phòng:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->unit->code }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Dịch vụ:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->service->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Người đo:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->takenBy->name ?? 'N/A' }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Ngày tạo:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Cập nhật:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            <!-- Meter Info -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-tachometer-alt me-2"></i>Thông tin công tơ
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Số seri:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->serial_no }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Dịch vụ:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->service->name }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Đơn vị:</strong></div>
                        <div class="col-sm-8">{{ $meterReading->meter->service->unit_label }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4"><strong>Trạng thái:</strong></div>
                        <div class="col-sm-8">
                            @if($meterReading->meter->status)
                                <span class="badge bg-success">Hoạt động</span>
                            @else
                                <span class="badge bg-secondary">Ngừng hoạt động</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Warning -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>Cảnh báo
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <h6><i class="fas fa-info-circle me-2"></i>Lưu ý:</h6>
                        <ul class="mb-0">
                            <li>Thay đổi số liệu có thể ảnh hưởng đến tính toán hóa đơn</li>
                            <li>Thay đổi ngày đo có thể ảnh hưởng đến lịch sử tính tiền</li>
                            <li>Hệ thống sẽ tự động cập nhật hóa đơn liên quan</li>
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
    // Form submission with loading state
    const form = document.getElementById('readingForm');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...';
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
            Notify.error('Có lỗi xảy ra khi cập nhật số liệu đo');
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
                        <small class="text-muted">Hình ảnh mới: ${file.name}</small>
                    </div>
                `;
            };
            reader.readAsDataURL(file);
        }
    });
});
</script>
@endpush
