@extends('layouts.agent_dashboard')

@section('title', 'Phiếu lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Phiếu lương</h1>
            <p class="mb-0">Xem thông tin phiếu lương của bạn</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.payslips.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả</option>
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kỳ lương (YYYY-MM)</label>
                    <input type="text" name="period" class="form-control" placeholder="2025-10" value="{{ request('period') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('agent.payslips.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payslips Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách phiếu lương</h6>
        </div>
        <div class="card-body">
            @if($payslips->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kỳ lương</th>
                                <th>Tổng lương</th>
                                <th>Khấu trừ</th>
                                <th>Lương thực nhận</th>
                                <th>Trạng thái</th>
                                <th>Ngày tạo</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payslips as $payslip)
                                <tr>
                                    <td>{{ $payslip->id }}</td>
                                    <td>
                                        <strong>{{ $payslip->payrollCycle->period_month }}</strong>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($payslip->gross_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                    <td>
                                        <span class="text-danger">{{ number_format($payslip->deduction_amount, 0, ',', '.') }} VND</span>
                                    </td>
                                    <td>
                                        <strong class="text-primary">{{ number_format($payslip->net_amount, 0, ',', '.') }} VND</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $payslip->status == 'paid' ? 'success' : 'warning' }}">
                                            {{ $statuses[$payslip->status] }}
                                        </span>
                                    </td>
                                    <td>{{ $payslip->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('agent.payslips.show', $payslip->id) }}" class="btn btn-sm btn-info">
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
                    {{ $payslips->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có phiếu lương nào</h5>
                    <p class="text-muted">Bạn chưa có phiếu lương nào được tạo.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('select[name="status"], input[name="period"], input[name="date_from"], input[name="date_to"]').forEach(function(element) {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
