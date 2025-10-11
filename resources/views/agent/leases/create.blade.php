@extends('layouts.agent_dashboard')

@section('title', 'Tạo hợp đồng mới')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Tạo hợp đồng mới</h1>
                <p>Tạo hợp đồng thuê cho khách thuê</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('agent.leases.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus"></i> Thông tin hợp đồng</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('agent.leases.store') }}" method="POST" id="leaseForm">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="property_id" class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                    <select name="property_id" id="property_id" class="form-select @error('property_id') is-invalid @enderror" required>
                                        <option value="">Chọn bất động sản</option>
                                        @foreach($properties as $property)
                                            <option value="{{ $property->id }}" 
                                                    {{ old('property_id', $selectedProperty?->id) == $property->id ? 'selected' : '' }}>
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
                                    <label for="unit_id" class="form-label">Phòng <span class="text-danger">*</span></label>
                                    <select name="unit_id" id="unit_id" class="form-select @error('unit_id') is-invalid @enderror" required>
                                        <option value="">Chọn phòng</option>
                                    </select>
                                    @error('unit_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="tenant_id" class="form-label">Khách thuê <span class="text-danger">*</span></label>
                                    <select name="tenant_id" id="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                                        <option value="">Chọn khách thuê</option>
                                        @foreach($tenants as $tenant)
                                            <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                                                {{ $tenant->full_name }} - {{ $tenant->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('tenant_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="contract_no" class="form-label">Mã hợp đồng</label>
                                    <div class="input-group">
                                        <input type="text" name="contract_no" id="contract_no" class="form-control @error('contract_no') is-invalid @enderror" 
                                               value="{{ old('contract_no') }}" placeholder="Tự động sinh nếu để trống">
                                        <button type="button" class="btn btn-outline-secondary" id="generateContractNo">
                                            <i class="fas fa-sync"></i> Tự động
                                        </button>
                                    </div>
                                    @error('contract_no')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="start_date" class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" id="start_date" class="form-control @error('start_date') is-invalid @enderror" 
                                           value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="end_date" class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" id="end_date" class="form-control @error('end_date') is-invalid @enderror" 
                                           value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="rent_amount" class="form-label">Giá thuê (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="text" name="rent_amount" id="rent_amount" class="form-control @error('rent_amount') is-invalid @enderror" 
                                           value="{{ old('rent_amount') }}" placeholder="VD: 25.000.000" required>
                                    @error('rent_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="deposit_amount" class="form-label">Tiền cọc (VNĐ)</label>
                                    <input type="text" name="deposit_amount" id="deposit_amount" class="form-control @error('deposit_amount') is-invalid @enderror" 
                                           value="{{ old('deposit_amount') }}" placeholder="VD: 25.000.000">
                                    @error('deposit_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="billing_day" class="form-label">Ngày thanh toán</label>
                                    <input type="number" name="billing_day" id="billing_day" class="form-control @error('billing_day') is-invalid @enderror" 
                                           value="{{ old('billing_day', 1) }}" placeholder="VD: 1" min="1" max="28">
                                    @error('billing_day')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Nháp</option>
                                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                                        <option value="terminated" {{ old('status') == 'terminated' ? 'selected' : '' }}>Chấm dứt</option>
                                        <option value="expired" {{ old('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="signed_at" class="form-label">Ngày ký</label>
                                    <input type="date" name="signed_at" id="signed_at" class="form-control @error('signed_at') is-invalid @enderror" 
                                           value="{{ old('signed_at') }}">
                                    @error('signed_at')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Services Section -->
                            <div class="mb-4">
                                <h6 class="fw-bold mb-3">Dịch vụ kèm theo (tùy chọn)</h6>
                                <div id="services-container">
                                    <div class="service-item row g-3 mb-3">
                                        <div class="col-md-5">
                                            <select name="services[0][service_id]" class="form-select service-select">
                                                <option value="">Chọn dịch vụ</option>
                                                @foreach($services as $service)
                                                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-5">
                                            <input type="number" name="services[0][price]" class="form-control" 
                                                   placeholder="Giá dịch vụ" min="0">
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-outline-danger remove-service">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-outline-primary" id="add-service">
                                    <i class="fas fa-plus"></i> Thêm dịch vụ
                                </button>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('agent.leases.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Tạo hợp đồng
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
    let serviceIndex = 1;

    // Property change handler
    document.getElementById('property_id').addEventListener('change', function() {
        const propertyId = this.value;
        const unitSelect = document.getElementById('unit_id');
        
        unitSelect.innerHTML = '<option value="">Đang tải...</option>';
        
        if (propertyId) {
            fetch(`/agent/api/properties/${propertyId}/units`)
                .then(response => response.json())
                .then(units => {
                    unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
                    units.forEach(unit => {
                        if (!unit.has_active_lease) {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = `${unit.code} - ${unit.unit_type} - ${unit.area_m2 ? unit.area_m2 + 'm²' : 'N/A'}`;
                            unitSelect.appendChild(option);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading units:', error);
                    unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                });
        } else {
            unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
        }
    });

    // Generate contract number
    document.getElementById('generateContractNo').addEventListener('click', function() {
        fetch('/agent/api/leases/next-contract-number')
            .then(response => response.json())
            .then(data => {
                if (data.contract_no) {
                    document.getElementById('contract_no').value = data.contract_no;
                }
            })
            .catch(error => {
                console.error('Error generating contract number:', error);
            });
    });

    // Add service
    document.getElementById('add-service').addEventListener('click', function() {
        const container = document.getElementById('services-container');
        const newService = document.createElement('div');
        newService.className = 'service-item row g-3 mb-3';
        newService.innerHTML = `
            <div class="col-md-5">
                <select name="services[${serviceIndex}][service_id]" class="form-select service-select">
                    <option value="">Chọn dịch vụ</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <input type="number" name="services[${serviceIndex}][price]" class="form-control" 
                       placeholder="Giá dịch vụ" min="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger remove-service">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(newService);
        serviceIndex++;
    });

    // Remove service
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service')) {
            e.target.closest('.service-item').remove();
        }
    });

    // Auto-format currency inputs
    const currencyInputs = ['rent_amount', 'deposit_amount'];
    currencyInputs.forEach(function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            // Format on input
            input.addEventListener('input', function() {
                let value = this.value.replace(/[^\d]/g, '');
                if (value) {
                    // Store numeric value in data attribute
                    this.setAttribute('data-numeric-value', value);
                    // Display formatted value with dots as thousands separators
                    this.value = Number(value).toLocaleString('vi-VN');
                } else {
                    this.removeAttribute('data-numeric-value');
                    this.value = '';
                }
            });

            // On focus, show raw number for editing
            input.addEventListener('focus', function() {
                const numericValue = this.getAttribute('data-numeric-value');
                if (numericValue) {
                    this.value = numericValue;
                }
            });

            // On blur, format again
            input.addEventListener('blur', function() {
                const numericValue = this.getAttribute('data-numeric-value');
                if (numericValue) {
                    this.value = Number(numericValue).toLocaleString('vi-VN');
                }
            });
        }
    });

    // Before form submission, restore all numeric values
    document.getElementById('leaseForm').addEventListener('submit', function() {
        currencyInputs.forEach(function(inputId) {
            const input = document.getElementById(inputId);
            if (input) {
                const numericValue = input.getAttribute('data-numeric-value');
                if (numericValue) {
                    input.value = numericValue;
                }
            }
        });
    });

    // Set default dates
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').value = today;
    
    const nextYear = new Date();
    nextYear.setFullYear(nextYear.getFullYear() + 1);
    document.getElementById('end_date').value = nextYear.toISOString().split('T')[0];
});
</script>
@endpush
