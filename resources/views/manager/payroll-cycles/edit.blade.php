@extends('layouts.manager_dashboard')

@section('title', 'Chỉnh sửa Kỳ Lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chỉnh sửa Kỳ Lương</h1>
            <p class="mb-0">{{ \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->format('m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('manager.payroll-cycles.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('manager.payroll-cycles.show', $payrollCycle->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
        </div>
    </div>

    <form action="{{ route('manager.payroll-cycles.update', $payrollCycle->id) }}" method="POST">
        @csrf
        @method('PUT')
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
                                <label class="form-label">Kỳ lương</label>
                                <div class="form-control-plaintext">
                                    <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $payrollCycle->period_month)->format('m/Y') }}</strong>
                                    <small class="text-muted d-block">Không thể thay đổi kỳ lương</small>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Trạng thái</label>
                                <div class="form-control-plaintext">
                                    @php
                                        $statusColors = [
                                            'open' => 'success',
                                            'locked' => 'warning',
                                            'paid' => 'info'
                                        ];
                                        $statusLabels = [
                                            'open' => 'Mở',
                                            'locked' => 'Đã khóa',
                                            'paid' => 'Đã thanh toán'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$payrollCycle->status] }}">
                                        {{ $statusLabels[$payrollCycle->status] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control @error('note') is-invalid @enderror" 
                                          name="note" rows="3" placeholder="Ghi chú về kỳ lương...">{{ old('note', $payrollCycle->note) }}</textarea>
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
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Lưu ý</h6>
                            <ul class="mb-0 small">
                                <li>Chỉ có thể chỉnh sửa kỳ lương đang mở</li>
                                <li>Không thể thay đổi kỳ lương (tháng/năm)</li>
                                <li>Chỉ có thể chỉnh sửa ghi chú</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card shadow">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="fas fa-save"></i> Cập nhật kỳ lương
                        </button>
                        <a href="{{ route('manager.payroll-cycles.show', $payrollCycle->id) }}" class="btn btn-secondary w-100">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
