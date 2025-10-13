@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết phiếu lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết phiếu lương</h1>
            <p class="mb-0">Thông tin chi tiết phiếu lương #{{ $payslip->id }}</p>
        </div>
        <a href="{{ route('agent.payslips.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- Payslip Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin phiếu lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID phiếu lương:</label>
                                <p class="form-control-plaintext">{{ $payslip->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Kỳ lương:</label>
                                <p class="form-control-plaintext">
                                    <strong>{{ $payslip->payrollCycle->period_month }}</strong>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái:</label>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Phương thức thanh toán:</label>
                                    <p class="form-control-plaintext">{{ $payslip->payment_method }}</p>
                                </div>
                            </div>
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

            <!-- Salary Breakdown -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Chi tiết lương</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded mb-3">
                                <h6 class="text-muted mb-2">Tổng lương</h6>
                                <h3 class="text-success mb-0">
                                    {{ number_format($payslip->gross_amount, 0, ',', '.') }} VND
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-light rounded mb-3">
                                <h6 class="text-muted mb-2">Khấu trừ</h6>
                                <h3 class="text-danger mb-0">
                                    {{ number_format($payslip->deduction_amount, 0, ',', '.') }} VND
                                </h3>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-4 bg-primary text-white rounded mb-3">
                                <h6 class="mb-2">Lương thực nhận</h6>
                                <h3 class="mb-0">
                                    {{ number_format($payslip->net_amount, 0, ',', '.') }} VND
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Mô tả</th>
                                            <th class="text-end">Số tiền (VND)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Tổng lương</strong></td>
                                            <td class="text-end text-success">
                                                <strong>+{{ number_format($payslip->gross_amount, 0, ',', '.') }}</strong>
                                            </td>
                                        </tr>
                                        @if($payslip->deduction_amount > 0)
                                            <tr>
                                                <td><strong>Khấu trừ</strong></td>
                                                <td class="text-end text-danger">
                                                    <strong>-{{ number_format($payslip->deduction_amount, 0, ',', '.') }}</strong>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                    <tfoot class="table-primary">
                                        <tr>
                                            <td><strong>Lương thực nhận</strong></td>
                                            <td class="text-end">
                                                <strong class="fs-5">{{ number_format($payslip->net_amount, 0, ',', '.') }} VND</strong>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tóm tắt</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">ID phiếu lương:</label>
                        <p class="form-control-plaintext">{{ $payslip->id }}</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Kỳ lương:</label>
                        <p class="form-control-plaintext">{{ $payslip->payrollCycle->period_month }}</p>
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

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Thông tin này được cập nhật lần cuối: {{ $payslip->updated_at->format('d/m/Y H:i') }}
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
                        <a href="{{ route('agent.payroll-cycles.show', $payslip->payrollCycle->id) }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check"></i> Xem kỳ lương
                        </a>
                        <a href="{{ route('agent.payslips.index', ['period' => $payslip->payrollCycle->period_month]) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Phiếu lương cùng kỳ
                        </a>
                        <a href="{{ route('agent.payslips.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Tất cả phiếu lương
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
