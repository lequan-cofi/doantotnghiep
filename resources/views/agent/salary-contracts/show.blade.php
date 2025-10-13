@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết hợp đồng lương')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết hợp đồng lương</h1>
            <p class="mb-0">Thông tin chi tiết hợp đồng lương #{{ $salaryContract->id }}</p>
        </div>
        <a href="{{ route('agent.salary-contracts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại
        </a>
    </div>

    <div class="row">
        <!-- Basic Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin cơ bản</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">ID hợp đồng:</label>
                                <p class="form-control-plaintext">{{ $salaryContract->id }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Trạng thái:</label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-{{ $salaryContract->status == 'active' ? 'success' : ($salaryContract->status == 'inactive' ? 'warning' : 'danger') }}">
                                        @switch($salaryContract->status)
                                            @case('active')
                                                Đang hoạt động
                                                @break
                                            @case('inactive')
                                                Không hoạt động
                                                @break
                                            @case('terminated')
                                                Đã chấm dứt
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
                                <label class="form-label fw-bold">Lương cơ bản:</label>
                                <p class="form-control-plaintext">
                                    <strong class="text-success">{{ number_format($salaryContract->base_salary, 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Chu kỳ trả lương:</label>
                                <p class="form-control-plaintext">
                                    @switch($salaryContract->pay_cycle)
                                        @case('monthly')
                                            Hàng tháng (ngày {{ $salaryContract->pay_day }})
                                            @break
                                        @case('weekly')
                                            Hàng tuần
                                            @break
                                        @case('biweekly')
                                            Hai tuần một lần
                                            @break
                                        @default
                                            {{ $salaryContract->pay_cycle }}
                                    @endswitch
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày hiệu lực:</label>
                                <p class="form-control-plaintext">{{ $salaryContract->effective_from->format('d/m/Y') }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày kết thúc:</label>
                                <p class="form-control-plaintext">
                                    {{ $salaryContract->effective_to ? $salaryContract->effective_to->format('d/m/Y') : 'Không giới hạn' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Allowances -->
            @if($salaryContract->allowances_json && count($salaryContract->allowances_json) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Phụ cấp</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Loại phụ cấp</th>
                                        <th>Số tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryContract->allowances_json as $type => $amount)
                                        <tr>
                                            <td>{{ $type }}</td>
                                            <td class="text-success">
                                                <strong>{{ number_format($amount, 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-info">
                                        <th>Tổng phụ cấp:</th>
                                        <th class="text-success">
                                            <strong>{{ number_format(array_sum($salaryContract->allowances_json), 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- KPI Targets -->
            @if($salaryContract->kpi_target_json && count($salaryContract->kpi_target_json) > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Mục tiêu KPI</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Chỉ tiêu</th>
                                        <th>Mục tiêu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($salaryContract->kpi_target_json as $kpi => $target)
                                        <tr>
                                            <td>{{ $kpi }}</td>
                                            <td>
                                                <strong>{{ $target }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Summary -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tổng kết</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Lương cơ bản:</label>
                        <p class="form-control-plaintext text-success">
                            <strong>{{ number_format($salaryContract->base_salary, 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                        </p>
                    </div>

                    @if($salaryContract->allowances_json && count($salaryContract->allowances_json) > 0)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tổng phụ cấp:</label>
                            <p class="form-control-plaintext text-info">
                                <strong>{{ number_format(array_sum($salaryContract->allowances_json), 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                            </p>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Tổng thu nhập:</label>
                            <p class="form-control-plaintext text-primary">
                                <strong class="fs-5">{{ number_format($salaryContract->total_compensation, 0, ',', '.') }} {{ $salaryContract->currency }}</strong>
                            </p>
                        </div>
                    @endif

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Thông tin này được cập nhật lần cuối: {{ $salaryContract->updated_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Organization Info -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin tổ chức</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên tổ chức:</label>
                        <p class="form-control-plaintext">{{ $salaryContract->organization->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
