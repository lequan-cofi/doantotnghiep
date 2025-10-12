@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết sự kiện hoa hồng')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line text-primary"></i>
                Chi tiết sự kiện hoa hồng
            </h1>
            <p class="text-muted mb-0">Sự kiện #{{ $event->id }}</p>
        </div>
        <div>
            <a href="{{ route('agent.commission-events.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
            
            <!-- Soft Delete Button -->
            <form action="{{ route('agent.commission-events.destroy', $event->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-soft-delete">
                    <i class="fas fa-trash me-1"></i>Xóa sự kiện
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Event Details -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i>
                        Thông tin sự kiện
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>ID sự kiện:</strong></td>
                                    <td>#{{ $event->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày xảy ra:</strong></td>
                                    <td>{{ $event->occurred_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Sự kiện kích hoạt:</strong></td>
                                    <td>
                                        @switch($event->trigger_event)
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
                                                <span class="badge badge-secondary">{{ $event->trigger_event }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        @switch($event->status)
                                            @case('pending')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> Chờ duyệt
                                                </span>
                                                @break
                                            @case('approved')
                                                <span class="badge badge-info">
                                                    <i class="fas fa-check"></i> Đã duyệt
                                                </span>
                                                @break
                                            @case('paid')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-money-bill-wave"></i> Đã thanh toán
                                                </span>
                                                @break
                                            @case('reversed')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-undo"></i> Đã hoàn
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-times"></i> Đã hủy
                                                </span>
                                                @break
                                            @default
                                                <span class="badge badge-secondary">{{ $event->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Ngày tạo:</strong></td>
                                    <td>{{ $event->created_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Loại tham chiếu:</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $event->ref_type }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>ID tham chiếu:</strong></td>
                                    <td>#{{ $event->ref_id }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Số tiền gốc:</strong></td>
                                    <td><strong class="text-primary">{{ number_format($event->amount_base) }}đ</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Hoa hồng:</strong></td>
                                    <td><strong class="text-success">{{ number_format($event->commission_total) }}đ</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>Tỷ lệ hoa hồng:</strong></td>
                                    <td>
                                        @if($event->amount_base > 0)
                                            <strong class="text-info">{{ number_format(($event->commission_total / $event->amount_base) * 100, 2) }}%</strong>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Cập nhật cuối:</strong></td>
                                    <td>{{ $event->updated_at->format('d/m/Y H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reference Information -->
            @if($event->lease || $event->listing)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-link"></i>
                        Thông tin tham chiếu
                    </h6>
                </div>
                <div class="card-body">
                    @if($event->lease)
                        <div class="row">
                            <div class="col-md-6">
                                <h6><i class="fas fa-file-contract"></i> Hợp đồng thuê</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>ID hợp đồng:</strong></td>
                                        <td>#{{ $event->lease->id }}</td>
                                    </tr>
                                    @if($event->lease->unit && $event->lease->unit->property)
                                    <tr>
                                        <td><strong>Bất động sản:</strong></td>
                                        <td>{{ $event->lease->unit->property->name }}</td>
                                    </tr>
                                    @endif
                                    @if($event->lease->unit)
                                    <tr>
                                        <td><strong>Phòng/Đơn vị:</strong></td>
                                        <td>{{ $event->lease->unit->name ?? 'N/A' }}</td>
                                    </tr>
                                    @endif
                                    @if($event->lease->tenant)
                                    <tr>
                                        <td><strong>Khách thuê:</strong></td>
                                        <td>{{ $event->lease->tenant->name }}</td>
                                    </tr>
                                    @endif
                                    @if($event->lease->rent_amount)
                                    <tr>
                                        <td><strong>Tiền thuê:</strong></td>
                                        <td>{{ number_format($event->lease->rent_amount) }}đ/tháng</td>
                                    </tr>
                                    @endif
                                    @if($event->lease->deposit_amount)
                                    <tr>
                                        <td><strong>Tiền cọc:</strong></td>
                                        <td>{{ number_format($event->lease->deposit_amount) }}đ</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                            <div class="col-md-6">
                                @if($event->lease->signed_at)
                                <h6><i class="fas fa-calendar"></i> Thông tin hợp đồng</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>Ngày ký:</strong></td>
                                        <td>{{ $event->lease->signed_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @if($event->lease->start_date)
                                    <tr>
                                        <td><strong>Ngày bắt đầu:</strong></td>
                                        <td>{{ $event->lease->start_date->format('d/m/Y') }}</td>
                                    </tr>
                                    @endif
                                    @if($event->lease->end_date)
                                    <tr>
                                        <td><strong>Ngày kết thúc:</strong></td>
                                        <td>{{ $event->lease->end_date->format('d/m/Y') }}</td>
                                    </tr>
                                    @endif
                                </table>
                                @endif
                            </div>
                        </div>
                    @elseif($event->listing)
                        <div class="row">
                            <div class="col-md-12">
                                <h6><i class="fas fa-bullhorn"></i> Tin đăng</h6>
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td><strong>ID tin đăng:</strong></td>
                                        <td>#{{ $event->listing->id }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tiêu đề:</strong></td>
                                        <td>{{ $event->listing->title ?? 'N/A' }}</td>
                                    </tr>
                                    @if($event->listing->price)
                                    <tr>
                                        <td><strong>Giá:</strong></td>
                                        <td>{{ number_format($event->listing->price) }}đ</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Policy Information -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs"></i>
                        Chính sách hoa hồng
                    </h6>
                </div>
                <div class="card-body">
                    @if($event->policy)
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tên chính sách:</strong></td>
                                <td>{{ $event->policy->title }}</td>
                            </tr>
                            @if($event->policy->code)
                            <tr>
                                <td><strong>Mã chính sách:</strong></td>
                                <td><span class="badge badge-secondary">{{ $event->policy->code }}</span></td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Loại tính toán:</strong></td>
                                <td>
                                    @switch($event->policy->calc_type)
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
                                            <span class="badge badge-secondary">{{ $event->policy->calc_type }}</span>
                                    @endswitch
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Giá trị:</strong></td>
                                <td>
                                    @if($event->policy->calc_type === 'percent')
                                        <strong class="text-primary">{{ $event->policy->percent_value }}%</strong>
                                    @elseif($event->policy->calc_type === 'flat')
                                        <strong class="text-success">{{ number_format($event->policy->flat_amount) }}đ</strong>
                                    @else
                                        <span class="text-muted">Bậc thang</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Cơ sở tính:</strong></td>
                                <td>
                                    @if($event->policy->basis === 'cash')
                                        <span class="badge badge-success">Tiền mặt</span>
                                    @else
                                        <span class="badge badge-info">Dồn tích</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Trạng thái:</strong></td>
                                <td>
                                    @if($event->policy->active)
                                        <span class="badge badge-success">Hoạt động</span>
                                    @else
                                        <span class="badge badge-danger">Tạm dừng</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                        <div class="text-center mt-3">
                            <a href="{{ route('agent.commission-policies.show', $event->policy->id) }}" 
                               class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye"></i> Xem chính sách
                            </a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                            <p class="text-muted">Chính sách hoa hồng không tồn tại hoặc đã bị xóa</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Agent Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user"></i>
                        Thông tin agent
                    </h6>
                </div>
                <div class="card-body">
                    @if($event->agent)
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tên:</strong></td>
                                <td>{{ $event->agent->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>{{ $event->agent->email }}</td>
                            </tr>
                            @if($event->agent->phone)
                            <tr>
                                <td><strong>Điện thoại:</strong></td>
                                <td>{{ $event->agent->phone }}</td>
                            </tr>
                            @endif
                        </table>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-user-slash fa-2x text-gray-300 mb-2"></i>
                            <p class="text-muted">Thông tin agent không khả dụng</p>
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
        const eventId = '{{ $event->id }}';
        
        Notify.confirmDelete(`sự kiện hoa hồng #${eventId}`, function() {
            form.submit();
        });
    });

    // Show event type info
    const triggerEvent = '{{ $event->trigger_event }}';
    const eventMessages = {
        'deposit_paid': 'Đây là sự kiện hoa hồng từ việc thanh toán cọc',
        'lease_signed': 'Đây là sự kiện hoa hồng từ việc ký hợp đồng',
        'invoice_paid': 'Đây là sự kiện hoa hồng từ việc thanh toán hóa đơn',
        'viewing_done': 'Đây là sự kiện hoa hồng từ việc xem phòng',
        'listing_published': 'Đây là sự kiện hoa hồng từ việc đăng tin'
    };

    if (eventMessages[triggerEvent]) {
        setTimeout(() => {
            Notify.info(eventMessages[triggerEvent], 'Loại sự kiện');
        }, 1000);
    }

    // Status change notifications
    const status = '{{ $event->status }}';
    const statusMessages = {
        'pending': 'Sự kiện hoa hồng đang chờ duyệt',
        'approved': 'Sự kiện hoa hồng đã được duyệt',
        'paid': 'Sự kiện hoa hồng đã được thanh toán',
        'reversed': 'Sự kiện hoa hồng đã bị hoàn',
        'cancelled': 'Sự kiện hoa hồng đã bị hủy'
    };

    if (statusMessages[status]) {
        setTimeout(() => {
            Notify.info(statusMessages[status], 'Trạng thái sự kiện');
        }, 1500);
    }

    // Auto refresh every 30 seconds if status is pending
    @if($event->status === 'pending')
    setInterval(function() {
        Notify.info('Đang kiểm tra cập nhật trạng thái...', 'Tự động cập nhật');
        setTimeout(() => {
            location.reload();
        }, 2000);
    }, 30000);
    @endif
});
</script>
@endpush