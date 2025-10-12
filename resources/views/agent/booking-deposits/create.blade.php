@extends('layouts.agent_dashboard')

@section('title', 'Tạo đặt cọc mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plus-circle me-2"></i>Tạo đặt cọc mới
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Property Selection Form -->
                    <form action="{{ route('agent.booking-deposits.create') }}" method="GET" id="propertyForm" class="mb-4">
                        <div class="row">
                            <div class="col-md-8">
                                <label for="property_id" class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                <select class="form-select" id="property_id" name="property_id" required onchange="submitPropertyForm()">
                                    <option value="">Chọn bất động sản</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" 
                                            {{ $selectedProperty && $selectedProperty->id == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }} 
                                            @if($property->units && $property->units->count() > 0)
                                                ({{ $property->units->count() }} phòng khả dụng)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-primary" onclick="submitPropertyForm()">
                                    <i class="fas fa-sync-alt me-1"></i>Tải danh sách phòng
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Main Deposit Form -->
                    <form action="{{ route('agent.booking-deposits.store') }}" method="POST" id="depositForm">
                        @csrf
                        
                        <!-- Hidden property_id field -->
                        <input type="hidden" name="property_id" value="{{ $selectedProperty ? $selectedProperty->id : '' }}">
                        
                        <div class="row">

                            <!-- Unit Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_id" class="form-label">Phòng/Căn hộ <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required>
                                    <option value="">Chọn phòng/căn hộ</option>
                                    @if(isset($units) && $units->count() > 0)
                                        @foreach($units as $unit)
                                            <option value="{{ $unit->id }}">
                                                {{ $unit->code }} - {{ $unit->unit_type }} 
                                                ({{ number_format($unit->base_rent ?? 0, 0, ',', '.') }} VNĐ/tháng)
                                                - [Khả dụng]
                                            </option>
                                        @endforeach
                                    @elseif(isset($selectedProperty))
                                        <option value="" disabled style="color: #999;">
                                            Không có phòng khả dụng trong property này
                                        </option>
                                    @endif
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tenant User Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="tenant_user_id" class="form-label">Người thuê</label>
                                <select class="form-select @error('tenant_user_id') is-invalid @enderror" id="tenant_user_id" name="tenant_user_id">
                                    <option value="">Chọn người thuê (tùy chọn)</option>
                                    @foreach($tenantUsers as $user)
                                        <option value="{{ $user->id }}">{{ $user->full_name }} ({{ $user->phone }})</option>
                                    @endforeach
                                </select>
                                @error('tenant_user_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lead Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="lead_id" class="form-label">Lead khách hàng</label>
                                <select class="form-select @error('lead_id') is-invalid @enderror" id="lead_id" name="lead_id">
                                    <option value="">Chọn lead (tùy chọn)</option>
                                    @foreach($leads as $lead)
                                        <option value="{{ $lead->id }}">{{ $lead->name }} ({{ $lead->phone }})</option>
                                    @endforeach
                                </select>
                                @error('lead_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Amount -->
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Số tiền đặt cọc <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount') }}" 
                                           placeholder="Nhập số tiền" required oninput="formatCurrency(this)">
                                    <span class="input-group-text">VNĐ</span>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Deposit Type -->
                            <div class="col-md-6 mb-3">
                                <label for="deposit_type" class="form-label">Loại đặt cọc <span class="text-danger">*</span></label>
                                <select class="form-select @error('deposit_type') is-invalid @enderror" id="deposit_type" name="deposit_type" required>
                                    <option value="">Chọn loại đặt cọc</option>
                                    <option value="booking" {{ old('deposit_type') == 'booking' ? 'selected' : '' }}>Đặt cọc</option>
                                    <option value="security" {{ old('deposit_type') == 'security' ? 'selected' : '' }}>Cọc an toàn</option>
                                    <option value="advance" {{ old('deposit_type') == 'advance' ? 'selected' : '' }}>Trả trước</option>
                                </select>
                                @error('deposit_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Hold Until -->
                            <div class="col-md-6 mb-3">
                                <label for="hold_until" class="form-label">Giữ chỗ đến ngày <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('hold_until') is-invalid @enderror" 
                                       id="hold_until" name="hold_until" value="{{ old('hold_until') }}" required>
                                @error('hold_until')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Notes -->
                            <div class="col-12 mb-3">
                                <label for="notes" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="3" 
                                          placeholder="Nhập ghi chú (tùy chọn)">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.booking-deposits.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Tạo đặt cọc
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Simple functions without external dependencies
function formatCurrency(input) {
    let value = input.value.replace(/[^\d]/g, '');
    if (value) {
        value = parseInt(value).toLocaleString('vi-VN');
    }
    input.value = value;
}

function submitPropertyForm() {
    // Submit the property form to reload page with selected property
    document.getElementById('propertyForm').submit();
}

// Set default hold_until to 7 days from now
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    now.setDate(now.getDate() + 7);
    const holdUntilInput = document.getElementById('hold_until');
    if (holdUntilInput && !holdUntilInput.value) {
        holdUntilInput.value = now.toISOString().slice(0, 16);
    }
    
    // Check if property is pre-selected from URL
    const urlParams = new URLSearchParams(window.location.search);
    const urlPropertyId = urlParams.get('property_id');
    if (urlPropertyId) {
        const propertySelect = document.getElementById('property_id');
        if (propertySelect && propertySelect.value !== urlPropertyId) {
            propertySelect.value = urlPropertyId;
        }
    }
});

// Form validation
document.getElementById('depositForm').addEventListener('submit', function(e) {
    const tenantUserId = document.getElementById('tenant_user_id').value;
    const leadId = document.getElementById('lead_id').value;
    const unitId = document.getElementById('unit_id').value;
    
    // Check if tenant/lead is selected
    if (!tenantUserId && !leadId) {
        e.preventDefault();
        alert('Vui lòng chọn người thuê hoặc lead khách hàng.');
        return false;
    }
    
    // Check if unit is selected
    if (!unitId) {
        e.preventDefault();
        alert('Vui lòng chọn phòng/căn hộ.');
        return false;
    }
});
</script>
@endsection
