@extends('layouts.manager_dashboard')

@section('title', 'Thêm Hợp đồng Mới')

@section('content')
<main class="main-content">
    <header class="header">
        <div class="header-content">
            <div class="header-info">
                <h1>Thêm Hợp đồng Mới</h1>
                <p>Tạo hợp đồng thuê mới cho khách hàng</p>
            </div>
            <div class="header-actions">
                <a href="{{ route('manager.leases.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Quay lại
                </a>
            </div>
        </div>
    </header>
    
    <div class="content" id="content">
        <div class="card">
            <div class="card-body">
                <form id="leaseForm" method="POST" action="{{ route('manager.leases.store') }}">
                    @csrf
                    
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin cơ bản</h5>
                            
                            <div class="mb-3">
                                <label class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                <select name="property_id" id="propertySelect" class="form-select" required>
                                    <option value="">Chọn bất động sản</option>
                                    @foreach ($properties as $property)
                                    <option value="{{ $property->id }}">{{ $property->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phòng <span class="text-danger">*</span></label>
                                <select name="unit_id" id="unitSelect" class="form-select" required disabled>
                                    <option value="">Chọn bất động sản trước</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Khách thuê <span class="text-danger">*</span></label>
                                <select name="tenant_id" class="form-select" required>
                                    <option value="">Chọn khách thuê</option>
                                    @foreach ($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->full_name }} ({{ $tenant->email }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nhân viên phụ trách</label>
                                <select name="agent_id" class="form-select">
                                    <option value="">Chọn nhân viên</option>
                                    @foreach ($agents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Số hợp đồng</label>
                                <div class="input-group">
                                    <input type="text" name="contract_no" id="contractNo" class="form-control" placeholder="Nhập số hợp đồng" readonly>
                                    <button type="button" id="generateContractNo" class="btn btn-outline-primary">
                                        <i class="fas fa-sync-alt"></i> Tự sinh
                                    </button>
                                </div>
                                <small class="form-text text-muted">Mã hợp đồng sẽ được tự động sinh theo format HD + số tăng dần</small>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Thông tin hợp đồng</h5>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày bắt đầu <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control" 
                                           value="{{ old('start_date', now()->format('Y-m-d')) }}" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Ngày kết thúc <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control" 
                                           value="{{ old('end_date', now()->addYear()->format('Y-m-d')) }}" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiền thuê/tháng <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="rent_amount" class="form-control" placeholder="0" min="0" step="1000" required>
                                    <span class="input-group-text">VND</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tiền cọc</label>
                                <div class="input-group">
                                    <input type="number" name="deposit_amount" class="form-control" placeholder="0" min="0" step="1000">
                                    <span class="input-group-text">VND</span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày tạo hóa đơn</label>
                                <select name="billing_day" class="form-select">
                                    @for ($i = 1; $i <= 28; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>Ngày {{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="form-text">Ngày trong tháng để tạo hóa đơn</div>
                            </div>

                            <!-- Payment Cycle Settings -->
                            <div class="mb-3">
                                <label class="form-label">Chu kỳ thanh toán</label>
                                <select name="lease_payment_cycle" id="lease_payment_cycle" class="form-select">
                                    <option value="">-- Chọn chu kỳ --</option>
                                    <option value="monthly">Hàng tháng</option>
                                    <option value="quarterly">Hàng quý</option>
                                    <option value="yearly">Hàng năm</option>
                                    <option value="custom">Tùy chỉnh (nhập số tháng)</option>
                                </select>
                            </div>

                            <div class="mb-3" id="lease_custom_months_field" style="display: none;">
                                <label class="form-label">Số tháng tùy chỉnh</label>
                                <input type="number" name="lease_custom_months" id="lease_custom_months" class="form-control" 
                                       min="1" max="60" placeholder="Nhập số tháng (1-60)">
                                <div class="form-text">Số tháng cho chu kỳ thanh toán tùy chỉnh (1-60)</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Hạn thanh toán</label>
                                <select name="lease_payment_day" class="form-select">
                                    @for ($i = 1; $i <= 31; $i++)
                                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>Ngày {{ $i }}</option>
                                    @endfor
                                </select>
                                <div class="form-text">Ngày hạn thanh toán trong chu kỳ</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ghi chú chu kỳ thanh toán</label>
                                <textarea name="lease_payment_notes" class="form-control" rows="2" 
                                          placeholder="Ghi chú về chu kỳ thanh toán..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="draft">Nháp</option>
                                    <option value="active">Đang hoạt động</option>
                                    <option value="terminated">Đã chấm dứt</option>
                                    <option value="expired">Đã hết hạn</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ngày ký hợp đồng</label>
                                <input type="date" name="signed_at" class="form-control" 
                                       value="{{ old('signed_at', now()->format('Y-m-d')) }}">
                            </div>
                        </div>
                    </div>

                    <!-- Services Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <h5 class="mb-3">Dịch vụ kèm theo</h5>
                            <div id="servicesContainer">
                                <div class="service-item row g-3 mb-3">
                                    <div class="col-md-6">
                                        <select name="services[0][service_id]" class="form-select">
                                            <option value="">Chọn dịch vụ</option>
                                            @foreach ($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="input-group">
                                            <input type="number" name="services[0][price]" class="form-control" placeholder="0" min="0" step="1000">
                                            <span class="input-group-text">VND</span>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="addService" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus"></i> Thêm dịch vụ
                            </button>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('manager.leases.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Tạo hợp đồng
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const propertySelect = document.getElementById('propertySelect');
    const unitSelect = document.getElementById('unitSelect');
    const addServiceBtn = document.getElementById('addService');
    const servicesContainer = document.getElementById('servicesContainer');
    const contractNoInput = document.getElementById('contractNo');
    const generateContractNoBtn = document.getElementById('generateContractNo');
    let serviceIndex = 1;

    // Tự động sinh mã hợp đồng khi trang load
    generateContractNumber();

    // Generate contract number function
    function generateContractNumber() {
        fetch('/manager/api/leases/next-contract-number', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            contractNoInput.value = data.contract_no;
        })
        .catch(error => {
            console.error('Error generating contract number:', error);
            Notify.error('Không thể sinh mã hợp đồng. Vui lòng thử lại.', 'Lỗi sinh mã');
        });
    }

    // Generate contract number button click handler
    generateContractNoBtn.addEventListener('click', function() {
        generateContractNumber();
    });

    // Property change handler
    propertySelect.addEventListener('change', function() {
        const propertyId = this.value;
        
        if (!propertyId) {
            unitSelect.innerHTML = '<option value="">Chọn bất động sản trước</option>';
            unitSelect.disabled = true;
            return;
        }

        // Fetch units for selected property
        fetch(`/manager/api/properties/${propertyId}/units`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                unitSelect.innerHTML = '<option value="">Chọn phòng</option>';
                if (data.error) {
                    throw new Error(data.error);
                }
                data.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.id;
                    option.textContent = unit.code || `Phòng ${unit.id}`;
                    if (unit.floor) {
                        option.textContent += ` (Tầng ${unit.floor})`;
                    }
                    
                    // Kiểm tra nếu phòng đã có hợp đồng hoạt động
                    if (unit.has_active_lease) {
                        option.textContent += ' - Đã có hợp đồng hoạt động';
                        option.disabled = true;
                        option.style.color = '#dc3545';
                    }
                    
                    unitSelect.appendChild(option);
                });
                unitSelect.disabled = false;
                
                // Load payment cycle settings from property
                loadPropertyPaymentCycle(propertyId);
            })
            .catch(error => {
                console.error('Error fetching units:', error);
                unitSelect.innerHTML = '<option value="">Lỗi tải dữ liệu</option>';
                Notify.error('Không thể tải danh sách phòng. Vui lòng thử lại.', 'Lỗi tải dữ liệu');
            });
    });

    // Add service handler
    addServiceBtn.addEventListener('click', function() {
        const serviceItem = document.createElement('div');
        serviceItem.className = 'service-item row g-3 mb-3';
        serviceItem.innerHTML = `
            <div class="col-md-6">
                <select name="services[${serviceIndex}][service_id]" class="form-select">
                    <option value="">Chọn dịch vụ</option>
                    @foreach ($services as $service)
                    <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <div class="input-group">
                    <input type="number" name="services[${serviceIndex}][price]" class="form-control" placeholder="0" min="0" step="1000">
                    <span class="input-group-text">VND</span>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm remove-service">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        servicesContainer.appendChild(serviceItem);
        serviceIndex++;
    });

    // Remove service handler
    servicesContainer.addEventListener('click', function(e) {
        if (e.target.closest('.remove-service')) {
            e.target.closest('.service-item').remove();
        }
    });

    // Load payment cycle settings from property
    function loadPropertyPaymentCycle(propertyId) {
        fetch(`/manager/api/properties/${propertyId}/payment-cycle`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Set payment cycle values from property
                const paymentCycleSelect = document.getElementById('lease_payment_cycle');
                const paymentDaySelect = document.querySelector('select[name="lease_payment_day"]');
                const paymentNotesTextarea = document.querySelector('textarea[name="lease_payment_notes"]');
                const customMonthsInput = document.getElementById('lease_custom_months');
                
                if (paymentCycleSelect && data.property.prop_payment_cycle) {
                    paymentCycleSelect.value = data.property.prop_payment_cycle;
                    // Trigger change event to show/hide custom months field
                    paymentCycleSelect.dispatchEvent(new Event('change'));
                }
                
                if (paymentDaySelect && data.property.prop_payment_day) {
                    paymentDaySelect.value = data.property.prop_payment_day;
                }
                
                if (paymentNotesTextarea && data.property.prop_payment_notes) {
                    paymentNotesTextarea.value = data.property.prop_payment_notes;
                }
                
                if (customMonthsInput && data.property.prop_custom_months) {
                    customMonthsInput.value = data.property.prop_custom_months;
                }
            }
        })
        .catch(error => {
            console.error('Error loading property payment cycle:', error);
        });
    }

    // Payment cycle change handler
    document.getElementById('lease_payment_cycle').addEventListener('change', function() {
        const customMonthsField = document.getElementById('lease_custom_months_field');
        if (this.value === 'custom') {
            customMonthsField.style.display = 'block';
        } else {
            customMonthsField.style.display = 'none';
        }
    });

    // Form submission
    document.getElementById('leaseForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Kiểm tra phòng đã có hợp đồng hoạt động
        const selectedUnit = unitSelect.options[unitSelect.selectedIndex];
        if (selectedUnit && selectedUnit.disabled) {
            Notify.error('Phòng này đã có hợp đồng hoạt động. Vui lòng chọn phòng khác hoặc chấm dứt hợp đồng hiện tại trước.', 'Không thể tạo hợp đồng');
            return;
        }
        
        if (window.Preloader) {
            window.Preloader.show();
        }

        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Notify.success(data.message, 'Thành công!');
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                }
            } else {
                Notify.error(data.message || 'Có lỗi xảy ra', 'Lỗi!');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Notify.error('Không thể tạo hợp đồng: ' + error.message + '. Vui lòng thử lại sau hoặc liên hệ Admin để được hỗ trợ.', 'Lỗi hệ thống!');
        })
        .finally(() => {
            if (window.Preloader) {
                window.Preloader.hide();
            }
        });
    });
});
</script>
@endpush
@endsection
