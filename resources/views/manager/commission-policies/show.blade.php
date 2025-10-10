@extends('layouts.manager_dashboard')

@section('title', 'Chi tiết Chính sách Hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Chi tiết Chính sách Hoa hồng</h1>
            <p class="mb-0">{{ $commissionPolicy->title }}</p>
        </div>
        <div>
            <a href="{{ route('manager.commission-policies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            <a href="{{ route('manager.commission-policies.edit', $commissionPolicy->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Policy Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thông tin Chính sách</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Mã chính sách:</strong></td>
                                    <td><span class="badge bg-secondary">{{ $commissionPolicy->code }}</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Tên chính sách:</strong></td>
                                    <td>{{ $commissionPolicy->title }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sự kiện kích hoạt:</strong></td>
                                    <td>
                                        @php
                                            $triggerLabels = [
                                                'deposit_paid' => 'Thanh toán cọc',
                                                'lease_signed' => 'Ký hợp đồng',
                                                'invoice_paid' => 'Thanh toán hóa đơn',
                                                'viewing_done' => 'Hoàn thành xem phòng',
                                                'listing_published' => 'Đăng tin'
                                            ];
                                        @endphp
                                        <span class="badge bg-info">{{ $triggerLabels[$commissionPolicy->trigger_event] ?? $commissionPolicy->trigger_event }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Cơ sở tính toán:</strong></td>
                                    <td>
                                        @if($commissionPolicy->basis == 'cash')
                                            <span class="badge bg-success">Tiền mặt</span>
                                        @else
                                            <span class="badge bg-warning">Dồn tích</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Loại tính toán:</strong></td>
                                    <td>
                                        @php
                                            $calcLabels = [
                                                'percent' => 'Phần trăm',
                                                'flat' => 'Số tiền cố định',
                                                'tiered' => 'Bậc thang'
                                            ];
                                        @endphp
                                        {{ $calcLabels[$commissionPolicy->calc_type] ?? $commissionPolicy->calc_type }}
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Giá trị:</strong></td>
                                    <td>
                                        @if($commissionPolicy->calc_type == 'percent')
                                            <span class="text-primary"><strong>{{ $commissionPolicy->percent_value }}%</strong></span>
                                        @elseif($commissionPolicy->calc_type == 'flat')
                                            <span class="text-primary"><strong>{{ number_format($commissionPolicy->flat_amount, 0, ',', '.') }} VND</strong></span>
                                        @else
                                            <span class="text-primary"><strong>Bậc thang</strong></span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @if($commissionPolicy->active)
                                            <span class="badge bg-success">Hoạt động</span>
                                        @else
                                            <span class="badge bg-secondary">Không hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $commissionPolicy->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($commissionPolicy->apply_limit_months || $commissionPolicy->min_amount || $commissionPolicy->cap_amount)
                    <hr>
                    <h6 class="font-weight-bold text-primary">Điều kiện áp dụng</h6>
                    <div class="row">
                        @if($commissionPolicy->apply_limit_months)
                        <div class="col-md-4">
                            <strong>Giới hạn tháng:</strong> {{ $commissionPolicy->apply_limit_months }} tháng
                        </div>
                        @endif
                        @if($commissionPolicy->min_amount)
                        <div class="col-md-4">
                            <strong>Số tiền tối thiểu:</strong> {{ number_format($commissionPolicy->min_amount, 0, ',', '.') }} VND
                        </div>
                        @endif
                        @if($commissionPolicy->cap_amount)
                        <div class="col-md-4">
                            <strong>Số tiền tối đa:</strong> {{ number_format($commissionPolicy->cap_amount, 0, ',', '.') }} VND
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Commission Splits -->
            @if($commissionPolicy->splits->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Phân chia Hoa hồng</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Vai trò</th>
                                    <th>Tỷ lệ phần trăm</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($commissionPolicy->splits as $split)
                                <tr>
                                    <td>
                                        @php
                                            $roleLabels = [
                                                'manager' => 'Quản lý',
                                                'agent' => 'Nhân viên',
                                                'supervisor' => 'Giám sát',
                                                'admin' => 'Quản trị viên'
                                            ];
                                        @endphp
                                        {{ $roleLabels[$split->role_key] ?? $split->role_key }}
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">{{ $split->percent_share }}%</span>
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

        <!-- Statistics -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Thống kê</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary">{{ $commissionPolicy->events->count() }}</h4>
                                <p class="mb-0 text-muted">Sự kiện</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success">
                                {{ number_format($commissionPolicy->events->sum('amount'), 0, ',', '.') }}
                            </h4>
                            <p class="mb-0 text-muted">Tổng hoa hồng (VND)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Events -->
            @if($commissionPolicy->events->count() > 0)
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Sự kiện gần đây</h6>
                </div>
                <div class="card-body">
                    @foreach($commissionPolicy->events->take(5) as $event)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <small class="text-muted">{{ $event->created_at->format('d/m/Y') }}</small>
                            <br>
                            <small>{{ $event->agent->full_name ?? 'N/A' }}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge 
                                @if($event->status == 'pending') bg-warning
                                @elseif($event->status == 'approved') bg-success
                                @elseif($event->status == 'paid') bg-info
                                @else bg-secondary
                                @endif">
                                @if($event->status == 'pending') Chờ duyệt
                                @elseif($event->status == 'approved') Đã duyệt
                                @elseif($event->status == 'paid') Đã thanh toán
                                @else {{ $event->status }}
                                @endif
                            </span>
                            <br>
                            <small class="text-primary">{{ number_format($event->amount, 0, ',', '.') }} VND</small>
                        </div>
                    </div>
                    @if(!$loop->last)
                        <hr class="my-2">
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
