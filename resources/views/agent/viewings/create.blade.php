@extends('layouts.agent_dashboard')

@section('title', 'Tạo lịch hẹn mới')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-calendar-plus me-2"></i>Tạo lịch hẹn mới
                    </h1>
                    <p class="text-muted mb-0">Thêm lịch hẹn xem phòng mới</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Quay lại
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Form -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calendar me-2"></i>Thông tin lịch hẹn
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.viewings.store') }}" method="POST" id="viewingForm">
                        @csrf
                        
                        <div class="row">
                            <!-- Customer Type Selection -->
                            <div class="col-12 mb-4">
                                <label class="form-label">Loại khách hàng <span class="text-danger">*</span></label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_type" id="customer_type_lead" value="lead" {{ old('customer_type', 'lead') == 'lead' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="customer_type_lead">
                                                <i class="fas fa-user-plus me-1"></i>Lead mới (chưa có tài khoản)
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="customer_type" id="customer_type_tenant" value="tenant" {{ old('customer_type') == 'tenant' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="customer_type_tenant">
                                                <i class="fas fa-user me-1"></i>Khách thuê (đã có tài khoản)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @error('customer_type')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Lead Section -->
                            <div id="leadSection" class="col-12 mb-4" style="display: {{ old('customer_type', 'lead') == 'lead' ? 'block' : 'none' }}">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user-plus me-1"></i>Thông tin Lead
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Lead Selection -->
                                            <div class="col-md-6 mb-3">
                                                <label for="lead_id" class="form-label">Chọn Lead</label>
                                                <select class="form-select @error('lead_id') is-invalid @enderror" id="lead_id" name="lead_id">
                                                    <option value="">Chọn lead hoặc nhập thông tin mới</option>
                                                    @foreach($leads as $lead)
                                                        <option value="{{ $lead->id }}" {{ old('lead_id') == $lead->id ? 'selected' : '' }}>
                                                            {{ $lead->name }} - {{ $lead->phone }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('lead_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Lead Name -->
                                            <div class="col-md-6 mb-3">
                                                <label for="lead_name" class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('lead_name') is-invalid @enderror" 
                                                       id="lead_name" name="lead_name" value="{{ old('lead_name') }}">
                                                @error('lead_name')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Lead Phone -->
                                            <div class="col-md-6 mb-3">
                                                <label for="lead_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control @error('lead_phone') is-invalid @enderror" 
                                                       id="lead_phone" name="lead_phone" value="{{ old('lead_phone') }}">
                                                @error('lead_phone')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <!-- Lead Email -->
                                            <div class="col-md-6 mb-3">
                                                <label for="lead_email" class="form-label">Email</label>
                                                <input type="email" class="form-control @error('lead_email') is-invalid @enderror" 
                                                       id="lead_email" name="lead_email" value="{{ old('lead_email') }}">
                                                @error('lead_email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tenant Section -->
                            <div id="tenantSection" class="col-12 mb-4" style="display: {{ old('customer_type') == 'tenant' ? 'block' : 'none' }}">
                                <div class="card border-info">
                                    <div class="card-header bg-info bg-opacity-10">
                                        <h6 class="mb-0">
                                            <i class="fas fa-user me-1"></i>Thông tin Khách thuê
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <!-- Tenant Selection -->
                                            <div class="col-12 mb-3">
                                                <label for="tenant_id" class="form-label">Chọn khách thuê <span class="text-danger">*</span></label>
                                                <select class="form-select @error('tenant_id') is-invalid @enderror" id="tenant_id" name="tenant_id">
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
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Property Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="property_id" class="form-label">Bất động sản <span class="text-danger">*</span></label>
                                <select class="form-select @error('property_id') is-invalid @enderror" id="property_id" name="property_id" required>
                                    <option value="">Chọn bất động sản</option>
                                    @foreach($properties as $property)
                                        <option value="{{ $property->id }}" {{ old('property_id') == $property->id ? 'selected' : '' }}>
                                            {{ $property->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('property_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Unit Selection -->
                            <div class="col-md-6 mb-3">
                                <label for="unit_id" class="form-label">Phòng <span class="text-danger">*</span></label>
                                <select class="form-select @error('unit_id') is-invalid @enderror" id="unit_id" name="unit_id" required disabled>
                                    <option value="">Chọn bất động sản trước</option>
                                </select>
                                @error('unit_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Schedule Date & Time -->
                            <div class="col-md-6 mb-3">
                                <label for="schedule_at" class="form-label">Thời gian hẹn <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control @error('schedule_at') is-invalid @enderror" 
                                       id="schedule_at" name="schedule_at" value="{{ old('schedule_at') }}" required>
                                @error('schedule_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="requested" {{ old('status', 'requested') == 'requested' ? 'selected' : '' }}>Chờ xác nhận</option>
                                    <option value="confirmed" {{ old('status') == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="col-12 mb-3">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          id="note" name="note" rows="3" placeholder="Ghi chú về lịch hẹn...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i>Hủy
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Tạo lịch hẹn
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="{{ asset('assets/js/agent/viewings.js') }}"></script>
@endpush

@push('styles')
<link href="{{ asset('assets/css/notifications.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/agent/viewings.css') }}" rel="stylesheet">
@endpush
