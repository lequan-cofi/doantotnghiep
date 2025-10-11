@extends('layouts.manager_dashboard')

@section('title', 'Tạo Chính sách Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tạo Chính sách Hoa hồng</h1>
            <p class="mb-0">Thiết lập chính sách hoa hồng mới cho nhân viên</p>
        </div>
        <a href="{{ route('manager.commission-policies.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="{{ route('manager.commission-policies.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Mã chính sách <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       name="code" value="{{ old('code') }}" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tên chính sách <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sự kiện kích hoạt <span class="text-danger">*</span></label>
                                <select class="form-select @error('trigger_event') is-invalid @enderror" 
                                        name="trigger_event" required>
                                    <option value="">Chọn sự kiện kích hoạt</option>
                                    @foreach($triggerEvents as $key => $label)
                                        <option value="{{ $key }}" {{ old('trigger_event') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('trigger_event')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cơ sở tính toán <span class="text-danger">*</span></label>
                                <select class="form-select @error('basis') is-invalid @enderror" 
                                        name="basis" required>
                                    <option value="">Chọn cơ sở tính toán</option>
                                    @foreach($basisTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('basis') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('basis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Loại tính toán <span class="text-danger">*</span></label>
                                <select class="form-select @error('calc_type') is-invalid @enderror" 
                                        name="calc_type" id="calc_type" required>
                                    <option value="">Chọn loại tính toán</option>
                                    @foreach($calcTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('calc_type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('calc_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="active" value="1" 
                                           {{ old('active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label">Hoạt động</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calculation Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Cài đặt tính toán</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3" id="percent_value_field" style="display: none;">
                                <label class="form-label">Phần trăm (%)</label>
                                <input type="number" class="form-control @error('percent_value') is-invalid @enderror" 
                                       name="percent_value" value="{{ old('percent_value') }}" 
                                       min="0" max="100" step="0.01">
                                @error('percent_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3" id="flat_amount_field" style="display: none;">
                                <label class="form-label">Số tiền cố định (VND)</label>
                                <input type="number" class="form-control @error('flat_amount') is-invalid @enderror" 
                                       name="flat_amount" value="{{ old('flat_amount') }}" 
                                       min="0" step="1000">
                                @error('flat_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số tháng áp dụng</label>
                                <input type="number" class="form-control @error('apply_limit_months') is-invalid @enderror" 
                                       name="apply_limit_months" value="{{ old('apply_limit_months') }}" 
                                       min="1" max="12">
                                @error('apply_limit_months')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số tiền tối thiểu (VND)</label>
                                <input type="number" class="form-control @error('min_amount') is-invalid @enderror" 
                                       name="min_amount" value="{{ old('min_amount') }}" 
                                       min="0" step="1000">
                                @error('min_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Số tiền tối đa (VND)</label>
                                <input type="number" class="form-control @error('cap_amount') is-invalid @enderror" 
                                       name="cap_amount" value="{{ old('cap_amount') }}" 
                                       min="0" step="1000">
                                @error('cap_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>


                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Tạo chính sách
                        </button>
                        <a href="{{ route('manager.commission-policies.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>

// Show/hide calculation fields based on calc_type
document.getElementById('calc_type').addEventListener('change', function() {
    const calcType = this.value;
    const percentField = document.getElementById('percent_value_field');
    const flatField = document.getElementById('flat_amount_field');
    
    // Hide all fields first
    percentField.style.display = 'none';
    flatField.style.display = 'none';
    
    // Show relevant field
    if (calcType === 'percent') {
        percentField.style.display = 'block';
    } else if (calcType === 'flat') {
        flatField.style.display = 'block';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const calcType = document.getElementById('calc_type').value;
    if (calcType === 'percent') {
        document.getElementById('percent_value_field').style.display = 'block';
    } else if (calcType === 'flat') {
        document.getElementById('flat_amount_field').style.display = 'block';
    }
});
</script>
@endpush
