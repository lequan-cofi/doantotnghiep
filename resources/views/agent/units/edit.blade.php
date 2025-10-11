@extends('layouts.agent_dashboard')

@section('title', 'Chỉnh sửa phòng')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Chỉnh sửa phòng {{ $unit->code }}</h1>
                <p>Cập nhật thông tin phòng trọ</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.units.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-outline-primary">
                    <i class="fas fa-eye"></i> Xem chi tiết
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Chỉnh sửa thông tin phòng</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('agent.units.update', $unit->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="property_id" class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
                                        <option value="">Chọn bất động sản</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" 
                                                    {{ old('property_id', $unit->property_id) == $property->id ? 'selected' : '' }}>
                                                {{ $property->name }}
                                                @if($property->owner)
                                                    - {{ $property->owner->full_name }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('property_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="code" class="form-label">Mã phòng <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control @error('code') is-invalid @enderror" 
                                           value="{{ old('code', $unit->code) }}" placeholder="VD: P101, A201" required>
                                    @error('code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="floor" class="form-label">Tầng</label>
                                    <input type="number" name="floor" id="floor" class="form-control @error('floor') is-invalid @enderror" 
                                           value="{{ old('floor', $unit->floor) }}" placeholder="VD: 1, 2, 3" min="1" max="100">
                                    @error('floor')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="area_m2" class="form-label">Diện tích (m²)</label>
                                    <input type="number" name="area_m2" id="area_m2" class="form-control @error('area_m2') is-invalid @enderror" 
                                           value="{{ old('area_m2', $unit->area_m2) }}" placeholder="VD: 25.5" step="0.01" min="0" max="1000">
                                    @error('area_m2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="unit_type" class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                    <select name="unit_type" id="unit_type" class="form-select @error('unit_type') is-invalid @enderror" required>
                                        <option value="">Chọn loại phòng</option>
                                        <option value="room" {{ old('unit_type', $unit->unit_type) == 'room' ? 'selected' : '' }}>Phòng trọ</option>
                                        <option value="apartment" {{ old('unit_type', $unit->unit_type) == 'apartment' ? 'selected' : '' }}>Căn hộ</option>
                                        <option value="dorm" {{ old('unit_type', $unit->unit_type) == 'dorm' ? 'selected' : '' }}>Ký túc xá</option>
                                        <option value="shared" {{ old('unit_type', $unit->unit_type) == 'shared' ? 'selected' : '' }}>Phòng chung</option>
                                    </select>
                                    @error('unit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="base_rent" class="form-label">Giá thuê cơ bản (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" name="base_rent" id="base_rent" class="form-control @error('base_rent') is-invalid @enderror" 
                                           value="{{ old('base_rent', $unit->base_rent) }}" placeholder="VD: 2500000" min="0" required>
                                    @error('base_rent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="deposit_amount" class="form-label">Tiền cọc (VNĐ)</label>
                                    <input type="number" name="deposit_amount" id="deposit_amount" class="form-control @error('deposit_amount') is-invalid @enderror" 
                                           value="{{ old('deposit_amount', $unit->deposit_amount) }}" placeholder="VD: 2500000" min="0">
                                    @error('deposit_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="max_occupancy" class="form-label">Số người tối đa <span class="text-danger">*</span></label>
                                    <input type="number" name="max_occupancy" id="max_occupancy" class="form-control @error('max_occupancy') is-invalid @enderror" 
                                           value="{{ old('max_occupancy', $unit->max_occupancy) }}" placeholder="VD: 2" min="1" max="10" required>
                                    @error('max_occupancy')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Trống</option>
                                        <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Đã đặt</option>
                                        <option value="occupied" {{ old('status', $unit->status) == 'occupied' ? 'selected' : '' }}>Đã thuê</option>
                                        <option value="maintenance" {{ old('status', $unit->status) == 'maintenance' ? 'selected' : '' }}>Bảo trì</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea name="note" id="note" class="form-control @error('note') is-invalid @enderror" 
                                          rows="3" placeholder="Ghi chú về phòng (tùy chọn)">{{ old('note', $unit->note) }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('agent.units.show', $unit->id) }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Cập nhật
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-format currency inputs
    const currencyInputs = ['base_rent', 'deposit_amount'];
    
    currencyInputs.forEach(function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', function() {
                let value = this.value.replace(/[^\d]/g, '');
                if (value) {
                    this.value = parseInt(value).toLocaleString('vi-VN');
                }
            });
        }
    });
});
</script>
@endpush
