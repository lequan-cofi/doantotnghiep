@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết kỳ lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết kỳ lương</h1>
            <p class="mb-0">Thông tin chi tiết kỳ lương {{ $payrollCycle->period_month }}</p>
        </div>
        <a href="{{ route('agent.payroll-cycles.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- Cycle Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin kỳ lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kỳ lương:</label>
                                <p class="form-control-plaintext">
                                    <strong>{{ $payrollCycle->period_month }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-{{ $payrollCycle->status == 'open' ? 'warning' : ($payrollCycle->status == 'locked' ? 'info' : 'success') }}">
                                        @switch($payrollCycle->status)
                                            @case('open')
                                                Mở
                                                @break
                                            @case('locked')
                                                Đã khóa
                                                @break
                                            @case('paid')
                                                Đã thanh toán
                                                @break
                                        @endswitch
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày khóa:</label>
                                <p class="form-control-plaintext">
                                    {{ $payrollCycle->locked_at ? $payrollCycle->locked_at->format('d/m/Y H:i') : 'Chưa khóa' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày thanh toán:</label>
                                <p class="form-control-plaintext">
                                    {{ $payrollCycle->paid_at ? $payrollCycle->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($payrollCycle->note)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Ghi chú:</label>
                            <p class="form-control-plaintext">{{ $payrollCycle->note }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payslip Details -->
            @if($payrollCycle->payslips->count() > 0)
                @php
                    $payslip = $payrollCycle->payslips->first();
                @endphp
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Chi tiết phiếu lương</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Tổng lương</h6>
                                    <h4 class="text-success mb-0">
                                        {{ number_format($payslip->gross_amount, 0, ',', '.') }} VND
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <h6 class="text-muted mb-1">Khấu trừ</h6>
                                    <h4 class="text-danger mb-0">
                                        {{ number_format($payslip->deduction_amount, 0, ',', '.') }} VND
                                    </h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-primary text-white rounded">
                                    <h6 class="mb-1">Lương thực nhận</h6>
                                    <h4 class="mb-0">
                                        {{ number_format($payslip->net_amount, 0, ',', '.') }} VND
                                    </h4>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Trạng thái thanh toán:</label>
                                    <p class="form-control-plaintext">
                                        <span class="badge badge-{{ $payslip->status == 'paid' ? 'success' : 'warning' }}">
                                            {{ $payslip->status == 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Ngày thanh toán:</label>
                                    <p class="form-control-plaintext">
                                        {{ $payslip->paid_at ? $payslip->paid_at->format('d/m/Y H:i') : 'Chưa thanh toán' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($payslip->payment_method)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phương thức thanh toán:</label>
                                <p class="form-control-plaintext">{{ $payslip->payment_method }}</p>
                            </div>
                        @endif

                        @if($payslip->note)
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ghi chú:</label>
                                <p class="form-control-plaintext">{{ $payslip->note }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có phiếu lương</h5>
                        <p class="text-muted">Kỳ lương này chưa có phiếu lương được tạo.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt</h6>
                </div>
                <div class="card-body">
                    @if($payrollCycle->payslips->count() > 0)
                        @php
                            $payslip = $payrollCycle->payslips->first();
                        @endphp
                        <div class="mb-3">
                            <label class="form-label fw-bold">Kỳ lương:</label>
                            <p class="form-control-plaintext">{{ $payrollCycle->period_month }}</p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tổng lương:</label>
                            <p class="form-control-plaintext text-success">
                                <strong>{{ number_format($payslip->gross_amount, 0, ',', '.') }} VND</strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Khấu trừ:</label>
                            <p class="form-control-plaintext text-danger">
                                <strong>{{ number_format($payslip->deduction_amount, 0, ',', '.') }} VND</strong>
                            </p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Lương thực nhận:</label>
                            <p class="form-control-plaintext text-primary">
                                <strong class="fs-5">{{ number_format($payslip->net_amount, 0, ',', '.') }} VND</strong>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái:</label>
                            <p class="form-control-plaintext">
                                <span class="badge badge-{{ $payslip->status == 'paid' ? 'success' : 'warning' }}">
                                    {{ $payslip->status == 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}
                                </span>
                            </p>
                        </div>
                    @else
                        <div class="text-center">
                            <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                            <p class="text-muted">Chưa có phiếu lương cho kỳ này</p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Thông tin này được cập nhật lần cuối: {{ $payrollCycle->updated_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thao tác</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('agent.payslips.index', ['period' => $payrollCycle->period_month]) }}" class="btn btn-outline-primary">
                            <i class="fas fa-receipt"></i> Xem tất cả phiếu lương
                        </a>
                        <a href="{{ route('agent.payroll-cycles.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Danh sách kỳ lương
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
