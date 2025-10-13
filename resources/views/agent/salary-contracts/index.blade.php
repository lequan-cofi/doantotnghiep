@extends('layouts.agent_dashboard')

@section('title', 'Hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Hợp đồng lương</h1>
            <p class="mb-0">Xem thông tin hợp đồng lương của bạn</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.salary-contracts.index') }}" class="row g-3">
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
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('agent.salary-contracts.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Contracts Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách hợp đồng lương</h6>
        </div>
        <div class="card-body">
            @if($salaryContracts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lương cơ bản</th>
                                <th>Chu kỳ trả lương</th>
                                <th>Ngày hiệu lực</th>
                                <th>Ngày kết thúc</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryContracts as $contract)
                                <tr>
                                    <td>{{ $contract->id }}</td>
                                    <td>
                                        <strong>{{ number_format($contract->base_salary, 0, ',', '.') }} {{ $contract->currency }}</strong>
                                    </td>
                                    <td>
                                        @switch($contract->pay_cycle)
                                            @case('monthly')
                                                Hàng tháng (ngày {{ $contract->pay_day }})
                                                @break
                                            @case('weekly')
                                                Hàng tuần
                                                @break
                                            @case('biweekly')
                                                Hai tuần một lần
                                                @break
                                            @default
                                                {{ $contract->pay_cycle }}
                                        @endswitch
                                    </td>
                                    <td>{{ $contract->effective_from->format('d/m/Y') }}</td>
                                    <td>{{ $contract->effective_to ? $contract->effective_to->format('d/m/Y') : 'Không giới hạn' }}</td>
                                    <td>
                                        <span class="badge badge-{{ $contract->status == 'active' ? 'success' : ($contract->status == 'inactive' ? 'warning' : 'danger') }}">
                                            {{ $statuses[$contract->status] }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('agent.salary-contracts.show', $contract->id) }}" class="btn btn-sm btn-info">
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
                    {{ $salaryContracts->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-contract fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có hợp đồng lương nào</h5>
                    <p class="text-muted">Bạn chưa có hợp đồng lương nào được tạo.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('select[name="status"], input[name="date_from"], input[name="date_to"]').forEach(function(element) {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
