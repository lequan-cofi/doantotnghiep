@extends('layouts.manager_dashboard')

@section('title', 'Tạo Kỳ Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Tạo Kỳ Lương</h1>
            <p class="mb-0">Tạo kỳ lương mới cho tổ chức</p>
        </div>
        <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <form action="{{ route('manager.payroll-cycles.store') }}" method="POST">
        @csrf
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin Kỳ Lương</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kỳ lương <span class="text-danger">*</span></label>
                                <select class="form-select @error('period_month') is-invalid @enderror" 
                                        name="period_month" required>
                                    <option value="">Chọn kỳ lương</option>
                                    @foreach($availableMonths as $value => $label)
                                        <option value="{{ $value }}" {{ old('period_month', $currentMonth) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('period_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Chọn tháng/năm cho kỳ lương</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-success">Mở</span>
                                    <small class="text-muted d-block">Kỳ lương mới sẽ được tạo với trạng thái "Mở"</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          name="note" rows="3" placeholder="Ghi chú về kỳ lương...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Information Panel -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Thông tin</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Hướng dẫn</h6>
                            <ul class="mb-0 small">
                                <li>Kỳ lương sẽ được tạo với trạng thái "Mở"</li>
                                <li>Sau khi tạo, bạn có thể tạo phiếu lương cho nhân viên</li>
                                <li>Khi hoàn tất, hãy khóa kỳ lương để tránh chỉnh sửa</li>
                                <li>Mỗi tháng chỉ có thể tạo một kỳ lương</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Tạo kỳ lương
                        </button>
                        <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
