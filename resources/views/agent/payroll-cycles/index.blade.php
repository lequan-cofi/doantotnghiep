@extends('layouts.agent_dashboard')

@section('title', 'Kỳ lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Kỳ lương</h1>
            <p class="mb-0">Xem thông tin các kỳ lương của bạn</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.payroll-cycles.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kỳ lương (YYYY-MM)</label>
                    <input type="text" name="period" class="form-control" placeholder="2025-10" value="{{ request('period') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('agent.payroll-cycles.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payroll Cycles Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách kỳ lương</h6>
        </div>
        <div class="card-body">
            @if($payrollCycles->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Kỳ lương</th>
                                <th>Trạng thái</th>
                                <th>Ngày khóa</th>
                                <th>Ngày thanh toán</th>
                                <th>Tổng lương</th>
                                <th>Trạng thái thanh toán</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payrollCycles as $cycle)
                                @php
                                    $payslip = $cycle->payslips->first();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $cycle->period_month }}</strong>
                                        @if($cycle->note)
                                            <br><small class="text-muted">{{ $cycle->note }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $cycle->status == 'open' ? 'warning' : ($cycle->status == 'locked' ? 'info' : 'success') }}">
                                            {{ $statuses[$cycle->status] }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $cycle->locked_at ? $cycle->locked_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        {{ $cycle->paid_at ? $cycle->paid_at->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td>
                                        @if($payslip)
                                            <strong class="text-success">{{ number_format($payslip->gross_amount, 0, ',', '.') }} VND</strong>
                                        @else
                                            <span class="text-muted">Chưa có phiếu lương</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payslip)
                                            <span class="badge badge-{{ $payslip->status == 'paid' ? 'success' : 'warning' }}">
                                                {{ $payslip->status == 'paid' ? 'Đã thanh toán' : 'Chờ thanh toán' }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('agent.payroll-cycles.show', $cycle->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $payrollCycles->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có kỳ lương nào</h5>
                    <p class="text-muted">Bạn chưa có kỳ lương nào được tạo.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('select[name="status"], input[name="period"]').forEach(function(element) {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
