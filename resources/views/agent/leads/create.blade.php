@extends('layouts.agent_dashboard')

@section('title', 'Tạo Lead mới')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-user-plus me-2"></i>Tạo Lead mới
                    </h1>
                    <p class="text-muted mb-0">Thêm khách hàng tiềm năng mới</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-secondary">
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
                        <i class="fas fa-user me-2"></i>Thông tin Lead
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('agent.leads.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <!-- Source -->
                            <div class="col-md-6 mb-3">
                                <label for="source" class="form-label">Nguồn <span class="text-danger">*</span></label>
                                <select class="form-select @error('source') is-invalid @enderror" id="source" name="source" required>
                                    <option value="">Chọn nguồn</option>
                                    <option value="facebook" {{ old('source') == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                    <option value="google" {{ old('source') == 'google' ? 'selected' : '' }}>Google</option>
                                    <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Giới thiệu</option>
                                    <option value="walk-in" {{ old('source') == 'walk-in' ? 'selected' : '' }}>Đến trực tiếp</option>
                                    <option value="phone" {{ old('source') == 'phone' ? 'selected' : '' }}>Điện thoại</option>
                                    <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>Khác</option>
                                </select>
                                @error('source')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Tên khách hàng <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Desired City -->
                            <div class="col-md-6 mb-3">
                                <label for="desired_city" class="form-label">Thành phố mong muốn</label>
                                <input type="text" class="form-control @error('desired_city') is-invalid @enderror" 
                                       id="desired_city" name="desired_city" value="{{ old('desired_city') }}">
                                @error('desired_city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="new" {{ old('status', 'new') == 'new' ? 'selected' : '' }}>Mới</option>
                                    <option value="contacted" {{ old('status') == 'contacted' ? 'selected' : '' }}>Đã liên hệ</option>
                                    <option value="qualified" {{ old('status') == 'qualified' ? 'selected' : '' }}>Đủ điều kiện</option>
                                    <option value="proposal" {{ old('status') == 'proposal' ? 'selected' : '' }}>Đề xuất</option>
                                    <option value="negotiation" {{ old('status') == 'negotiation' ? 'selected' : '' }}>Đàm phán</option>
                                    <option value="converted" {{ old('status') == 'converted' ? 'selected' : '' }}>Đã chuyển đổi</option>
                                    <option value="lost" {{ old('status') == 'lost' ? 'selected' : '' }}>Mất khách</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Budget Min -->
                            <div class="col-md-6 mb-3">
                                <label for="budget_min" class="form-label">Ngân sách tối thiểu</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('budget_min') is-invalid @enderror" 
                                           id="budget_min" name="budget_min" value="{{ old('budget_min') }}" 
                                           placeholder="Nhập số tiền">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('budget_min')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Budget Max -->
                            <div class="col-md-6 mb-3">
                                <label for="budget_max" class="form-label">Ngân sách tối đa</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('budget_max') is-invalid @enderror" 
                                           id="budget_max" name="budget_max" value="{{ old('budget_max') }}" 
                                           placeholder="Nhập số tiền">
                                    <span class="input-group-text">đ</span>
                                </div>
                                @error('budget_max')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Note -->
                            <div class="col-12 mb-3">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          id="note" name="note" rows="4" placeholder="Ghi chú về khách hàng...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <hr>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('agent.leads.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Tạo Lead
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format currency inputs
    function formatCurrency(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('vi-VN');
        }
        input.value = value;
    }

    document.getElementById('budget_min').addEventListener('input', function() {
        formatCurrency(this);
    });

    document.getElementById('budget_max').addEventListener('input', function() {
        formatCurrency(this);
    });
});
</script>
@endpush
