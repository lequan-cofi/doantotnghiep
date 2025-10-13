@extends('layouts.agent_dashboard')

@section('title', 'Ứng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Ứng lương</h1>
            <p class="mb-0">Quản lý đơn ứng lương của bạn</p>
        </div>
        <a href="{{ route('agent.salary-advances.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tạo đơn ứng lương
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Tổng đơn</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Chờ duyệt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['pending'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Đã duyệt</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['approved'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Còn nợ</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['remaining_amount'], 0, ',', '.') }} VND</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Bộ lọc</h6>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('agent.salary-advances.index') }}" class="row g-3">
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
                        <a href="{{ route('agent.salary-advances.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa bộ lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Salary Advances Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách đơn ứng lương</h6>
        </div>
        <div class="card-body">
            @if($salaryAdvances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Số tiền</th>
                                <th>Ngày ứng</th>
                                <th>Ngày hoàn trả dự kiến</th>
                                <th>Lý do</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryAdvances as $advance)
                                <tr>
                                    <td>{{ $advance->id }}</td>
                                    <td>
                                        <strong class="text-success">{{ number_format($advance->amount, 0, ',', '.') }} {{ $advance->currency }}</strong>
                                    </td>
                                    <td>{{ $advance->advance_date->format('d/m/Y') }}</td>
                                    <td>{{ $advance->expected_repayment_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 200px;" title="{{ $advance->reason }}">
                                            {{ $advance->reason }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-{{ $advance->status_color }}">
                                            {{ $advance->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('agent.salary-advances.show', $advance->id) }}" class="btn btn-sm btn-info">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($advance->canBeDeleted())
                                                <a href="{{ route('agent.salary-advances.edit', $advance->id) }}" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete({{ $advance->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-secondary" disabled title="Không thể chỉnh sửa đơn đã được duyệt">
                                                    <i class="fas fa-lock"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center">
                    {{ $salaryAdvances->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-hand-holding-usd fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Chưa có đơn ứng lương nào</h5>
                    <p class="text-muted">Bạn chưa tạo đơn ứng lương nào.</p>
                    <a href="{{ route('agent.salary-advances.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo đơn ứng lương đầu tiên
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Forms (Hidden) -->
@foreach($salaryAdvances as $advance)
    @if($advance->canBeDeleted())
        <form id="delete-form-{{ $advance->id }}" action="{{ route('agent.salary-advances.destroy', $advance->id) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endforeach
@endsection

@push('scripts')
<script>
    // Auto-submit form when filters change
    document.querySelectorAll('select[name="status"], input[name="date_from"], input[name="date_to"]').forEach(function(element) {
        element.addEventListener('change', function() {
            this.form.submit();
        });
    });

    // Confirm delete function
    function confirmDelete(id) {
        notify.confirmDelete('đơn ứng lương này', function() {
            document.getElementById('delete-form-' + id).submit();
        });
    }

    // Show notifications
    @if(session('success'))
        notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        notify.error('{{ session('error') }}');
    @endif
</script>
@endpush
