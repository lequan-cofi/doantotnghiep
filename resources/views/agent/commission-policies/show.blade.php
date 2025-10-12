@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết chính sách hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-percentage text-primary"></i>
                Chi tiết chính sách hoa hồng
            </h1>
            <p class="text-muted mb-0">{{ $policy->title }}</p>
        </div>
        <div>
            <a href="{{ route('agent.commission-policies.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            
            <!-- Soft Delete Button -->
            <form action="{{ route('agent.commission-policies.destroy', $policy->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-soft-delete">
                    <i class="fas fa-trash me-1"></i>Xóa chính sách
                </button>
            </form>
        </div>
    </div>

    <!-- Policy Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Tổng sự kiện
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($policyStats['total_events']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Tổng hoa hồng
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($policyStats['total_commission']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Đã thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($policyStats['paid_commission']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Chờ thanh toán
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($policyStats['pending_commission']) }}đ
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Policy Details -->
    <div class="row">
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i>
                        Thông tin chính sách
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Mã chính sách:</strong></td>
                            <td>
                                @if($policy->code)
                                    <span class="badge badge-secondary">{{ $policy->code }}</span>
                                @else
                                    <span class="text-muted">Chưa có</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Tên chính sách:</strong></td>
                            <td>{{ $policy->title }}</td>
                        </tr>
                        <tr>
                            <td><strong>Sự kiện kích hoạt:</strong></td>
                            <td>
                                @switch($policy->trigger_event)
                                    @case('deposit_paid')
                                        <span class="badge badge-info">
                                            <i class="fas fa-hand-holding-usd"></i> Đặt cọc
                                        </span>
                                        @break
                                    @case('lease_signed')
                                        <span class="badge badge-success">
                                            <i class="fas fa-file-contract"></i> Ký hợp đồng
                                        </span>
                                        @break
                                    @case('invoice_paid')
                                        <span class="badge badge-primary">
                                            <i class="fas fa-file-invoice-dollar"></i> Thanh toán hóa đơn
                                        </span>
                                        @break
                                    @case('viewing_done')
                                        <span class="badge badge-warning">
                                            <i class="fas fa-eye"></i> Xem phòng
                                        </span>
                                        @break
                                    @case('listing_published')
                                        <span class="badge badge-dark">
                                            <i class="fas fa-bullhorn"></i> Đăng tin
                                        </span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $policy->trigger_event }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Cơ sở tính:</strong></td>
                            <td>
                                @if($policy->basis === 'cash')
                                    <span class="badge badge-success">Tiền mặt</span>
                                @else
                                    <span class="badge badge-info">Dồn tích</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Loại tính toán:</strong></td>
                            <td>
                                @switch($policy->calc_type)
                                    @case('percent')
                                        <span class="badge badge-primary">Phần trăm</span>
                                        @break
                                    @case('flat')
                                        <span class="badge badge-info">Số tiền cố định</span>
                                        @break
                                    @case('tiered')
                                        <span class="badge badge-warning">Bậc thang</span>
                                        @break
                                    @default
                                        <span class="badge badge-secondary">{{ $policy->calc_type }}</span>
                                @endswitch
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Giá trị:</strong></td>
                            <td>
                                @if($policy->calc_type === 'percent')
                                    <strong class="text-primary">{{ $policy->percent_value }}%</strong>
                                @elseif($policy->calc_type === 'flat')
                                    <strong class="text-success">{{ number_format($policy->flat_amount) }}đ</strong>
                                @else
                                    <span class="text-muted">Bậc thang</span>
                                @endif
                            </td>
                        </tr>
                        @if($policy->min_amount)
                        <tr>
                            <td><strong>Số tiền tối thiểu:</strong></td>
                            <td><strong class="text-warning">{{ number_format($policy->min_amount) }}đ</strong></td>
                        </tr>
                        @endif
                        @if($policy->cap_amount)
                        <tr>
                            <td><strong>Số tiền tối đa:</strong></td>
                            <td><strong class="text-danger">{{ number_format($policy->cap_amount) }}đ</strong></td>
                        </tr>
                        @endif
                        @if($policy->apply_limit_months)
                        <tr>
                            <td><strong>Giới hạn tháng:</strong></td>
                            <td><strong>{{ $policy->apply_limit_months }} tháng</strong></td>
                        </tr>
                        @endif
                        @if($policy->filters_json)
                        <tr>
                            <td><strong>Bộ lọc:</strong></td>
                            <td>
                                <small class="text-muted">
                                    {{ json_encode($policy->filters_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                                </small>
                            </td>
                        </tr>
                        @endif
                        <tr>
                            <td><strong>Trạng thái:</strong></td>
                            <td>
                                @if($policy->active)
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> Tạm dừng
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Ngày tạo:</strong></td>
                            <td>{{ $policy->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Cập nhật cuối:</strong></td>
                            <td>{{ $policy->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i>
                        Lịch sử sự kiện hoa hồng
                    </h6>
                </div>
                <div class="card-body">
                    @if($events->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Sự kiện</th>
                                        <th>Tham chiếu</th>
                                        <th>Số tiền gốc</th>
                                        <th>Hoa hồng</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($events as $event)
                                    <tr>
                                        <td>
                                            <small>{{ $event->occurred_at->format('d/m/Y H:i') }}</small>
                                        </td>
                                        <td>
                                            @switch($event->trigger_event)
                                                @case('deposit_paid')
                                                    <span class="badge badge-info">Đặt cọc</span>
                                                    @break
                                                @case('lease_signed')
                                                    <span class="badge badge-success">Ký hợp đồng</span>
                                                    @break
                                                @case('invoice_paid')
                                                    <span class="badge badge-primary">Thanh toán hóa đơn</span>
                                                    @break
                                                @case('viewing_done')
                                                    <span class="badge badge-warning">Xem phòng</span>
                                                    @break
                                                @case('listing_published')
                                                    <span class="badge badge-dark">Đăng tin</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $event->trigger_event }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            @if($event->lease)
                                                <small class="text-muted">
                                                    Hợp đồng #{{ $event->lease->id }}
                                                    @if($event->lease->unit && $event->lease->unit->property)
                                                        <br>{{ $event->lease->unit->property->name }}
                                                    @endif
                                                </small>
                                            @elseif($event->listing)
                                                <small class="text-muted">
                                                    Tin đăng #{{ $event->listing->id }}
                                                </small>
                                            @else
                                                <small class="text-muted">
                                                    {{ $event->ref_type }} #{{ $event->ref_id }}
                                                </small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong>{{ number_format($event->amount_base) }}đ</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">{{ number_format($event->commission_total) }}đ</strong>
                                        </td>
                                        <td>
                                            @switch($event->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">Chờ duyệt</span>
                                                    @break
                                                @case('approved')
                                                    <span class="badge badge-info">Đã duyệt</span>
                                                    @break
                                                @case('paid')
                                                    <span class="badge badge-success">Đã thanh toán</span>
                                                    @break
                                                @case('reversed')
                                                    <span class="badge badge-danger">Đã hoàn</span>
                                                    @break
                                                @case('cancelled')
                                                    <span class="badge badge-secondary">Đã hủy</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-secondary">{{ $event->status }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center">
                            {{ $events->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-gray-300 mb-3"></i>
                            <h5 class="text-gray-600">Chưa có sự kiện hoa hồng nào</h5>
                            <p class="text-muted">Chưa có sự kiện hoa hồng nào được tạo từ chính sách này.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Show notifications from session
    @if(session('success'))
        Notify.success('{{ session('success') }}');
    @endif

    @if(session('error'))
        Notify.error('{{ session('error') }}');
    @endif

    @if(session('warning'))
        Notify.warning('{{ session('warning') }}');
    @endif

    @if(session('info'))
        Notify.info('{{ session('info') }}');
    @endif

    // Soft delete confirmation
    $(document).on('click', '.btn-soft-delete', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const policyId = '{{ $policy->id }}';
        
        Notify.confirmDelete(`chính sách hoa hồng #${policyId}`, function() {
            form.submit();
        });
    });

    // Show policy type info
    const triggerEvent = '{{ $policy->trigger_event }}';
    const eventMessages = {
        'deposit_paid': 'Đây là chính sách hoa hồng cho việc thanh toán cọc',
        'lease_signed': 'Đây là chính sách hoa hồng cho việc ký hợp đồng',
        'invoice_paid': 'Đây là chính sách hoa hồng cho việc thanh toán hóa đơn',
        'viewing_done': 'Đây là chính sách hoa hồng cho việc xem phòng',
        'listing_published': 'Đây là chính sách hoa hồng cho việc đăng tin'
    };

    if (eventMessages[triggerEvent]) {
        setTimeout(() => {
            Notify.info(eventMessages[triggerEvent], 'Loại chính sách');
        }, 1000);
    }

    // Status change notifications
    const isActive = {{ $policy->active ? 'true' : 'false' }};
    if (isActive) {
        setTimeout(() => {
            Notify.info('Chính sách hoa hồng đang hoạt động', 'Trạng thái chính sách');
        }, 1500);
    } else {
        setTimeout(() => {
            Notify.warning('Chính sách hoa hồng đã tạm dừng', 'Trạng thái chính sách');
        }, 1500);
    }

    // Auto refresh stats every 30 seconds
    setInterval(function() {
        // You can add AJAX call here to refresh stats if needed
    }, 30000);
});
</script>
@endpush
