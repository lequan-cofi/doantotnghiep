@extends('layouts.agent_dashboard')

@section('title', 'Tạo Đặt Cọc Mới')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tạo Đặt Cọc Mới</h3>
                    <div class="card-tools">
                        <a href="{{ route('agent.booking-deposits.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form id="depositForm" action="{{ route('agent.booking-deposits.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="property_id">Bất động sản <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-control @error('property_id') is-invalid @enderror" required>
                                        <option value="">Chọn bất động sản</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" {{ $selectedProperty && $selectedProperty->id == $property->id ? 'selected' : '' }}>
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
                                <div class="form-group">
                                    <label for="unit_id">Phòng/Căn hộ <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-control @error('unit_id') is-invalid @enderror" required>
                                        <option value="">Chọn phòng/căn hộ</option>
                                    </select>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tenant_user_id">Người thuê</label>
                                    <select name="tenant_user_id" id="tenant_user_id" class="form-control @error('tenant_user_id') is-invalid @enderror">
                                        <option value="">Chọn người thuê</option>
                                        @foreach($tenantUsers as $user)
                                            <option value="{{ $user->id }}" {{ old('tenant_user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->full_name }} ({{ $user->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lead_id">Lead khách hàng</label>
                                    <select name="lead_id" id="lead_id" class="form-control @error('lead_id') is-invalid @enderror">
                                        <option value="">Chọn lead khách hàng</option>
                                        @foreach($leads as $lead)
                                            <option value="{{ $lead->id }}" {{ old('lead_id') == $lead->id ? 'selected' : '' }}>
                                                {{ $lead->name }} ({{ $lead->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('lead_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="amount">Số tiền đặt cọc <span class="text-danger">*</span></label>
                                    <input type="text" name="amount" id="amount" class="form-control @error('amount') is-invalid @enderror" 
                                           value="{{ old('amount') }}" placeholder="Nhập số tiền" required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="deposit_type">Loại đặt cọc <span class="text-danger">*</span></label>
                                    <select name="deposit_type" id="deposit_type" class="form-control @error('deposit_type') is-invalid @enderror" required>
                                        <option value="">Chọn loại đặt cọc</option>
                                        <option value="booking" {{ old('deposit_type') == 'booking' ? 'selected' : '' }}>Đặt cọc giữ chỗ</option>
                                        <option value="security" {{ old('deposit_type') == 'security' ? 'selected' : '' }}>Tiền cọc an ninh</option>
                                        <option value="advance" {{ old('deposit_type') == 'advance' ? 'selected' : '' }}>Tiền ứng trước</option>
                                    </select>
                                    @error('deposit_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hold_until">Ngày hết hạn <span class="text-danger">*</span></label>
                                    <input type="datetime-local" name="hold_until" id="hold_until" class="form-control @error('hold_until') is-invalid @enderror" 
                                           value="{{ old('hold_until') }}" required>
                                    @error('hold_until')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="notes">Ghi chú</label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="3" placeholder="Nhập ghi chú (tùy chọn)">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo Đặt Cọc
                            </button>
                            <a href="{{ route('agent.booking-deposits.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test button for debugging -->
<div class="container-fluid mt-3">
    <div class="row">
        <div class="col-12">
            <button id="testAjaxBtn" class="btn btn-info">Test AJAX Endpoint</button>
            <button id="testUnitsBtn" class="btn btn-warning">Test Units Loading</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Simple, robust units loading function
function loadUnits(propertyId) {
    const unitSelect = document.getElementById('unit_id');
    
    if (!propertyId) {
        unitSelect.innerHTML = '<option value="">Chọn phòng/căn hộ</option>';
        return;
    }
    
    // Show loading
    unitSelect.innerHTML = '<option value="">Đang tải...</option>';
    
    // Use a simple approach - create a hidden iframe to make the request
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = '{{ route("agent.api.booking-deposits.units") }}?property_id=' + propertyId;
    
    // Handle the response
    iframe.onload = function() {
        try {
            // Try to get the response from the iframe
            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const responseText = iframeDoc.body.textContent || iframeDoc.body.innerText;
            
            if (responseText) {
                const data = JSON.parse(responseText);
                updateUnitSelect(data);
            } else {
                unitSelect.innerHTML = '<option value="">Không có dữ liệu</option>';
            }
        } catch (error) {
            console.error('Error parsing response:', error);
            unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
        }
        
        // Clean up
        document.body.removeChild(iframe);
    };
    
    iframe.onerror = function() {
        unitSelect.innerHTML = '<option value="">Lỗi kết nối</option>';
        document.body.removeChild(iframe);
    };
    
    document.body.appendChild(iframe);
}

function updateUnitSelect(data) {
    const unitSelect = document.getElementById('unit_id');
    unitSelect.innerHTML = '<option value="">Chọn phòng/căn hộ</option>';
    
    if (data && Array.isArray(data) && data.length > 0) {
        data.forEach(unit => {
            // Only show units without active lease or deposit
            if (!unit.has_active_lease && !unit.has_active_deposit) {
                const option = document.createElement('option');
                option.value = unit.id;
                const rentAmount = unit.base_rent || 0;
                option.textContent = `${unit.code} - ${unit.unit_type} (${new Intl.NumberFormat('vi-VN').format(rentAmount)} VNĐ/tháng)`;
                unitSelect.appendChild(option);
            }
        });
        
        if (unitSelect.children.length === 1) { // Only default option
            unitSelect.innerHTML = '<option value="">Không có phòng/căn hộ khả dụng</option>';
        }
    } else {
        unitSelect.innerHTML = '<option value="">Không có phòng/căn hộ nào</option>';
    }
}

// Property change handler
document.getElementById('property_id').addEventListener('change', function() {
    const propertyId = this.value;
    loadUnits(propertyId);
});

// Test buttons
document.getElementById('testAjaxBtn').addEventListener('click', function() {
    console.log('Testing AJAX endpoint...');
    loadUnits(1); // Test with property ID 1
});

document.getElementById('testUnitsBtn').addEventListener('click', function() {
    const propertyId = document.getElementById('property_id').value;
    if (propertyId) {
        console.log('Testing units loading for property:', propertyId);
        loadUnits(propertyId);
    } else {
        alert('Vui lòng chọn bất động sản trước');
    }
});

// Form validation
document.getElementById('depositForm').addEventListener('submit', function(e) {
    const tenantUserId = document.getElementById('tenant_user_id').value;
    const leadId = document.getElementById('lead_id').value;
    
    if (!tenantUserId && !leadId) {
        e.preventDefault();
        alert('Vui lòng chọn người thuê hoặc lead khách hàng.');
        return false;
    }
});

// Set default hold_until to 7 days from now
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    now.setDate(now.getDate() + 7);
    const holdUntilInput = document.getElementById('hold_until');
    if (holdUntilInput && !holdUntilInput.value) {
        holdUntilInput.value = now.toISOString().slice(0, 16);
    }
});
</script>
@endsection
