@extends('layouts.agent_dashboard')

@section('title', 'Lịch tổng quan')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">
                        <i class="fas fa-calendar me-2"></i>Lịch tổng quan
                    </h1>
                    <p class="text-muted mb-0">Xem lịch hẹn theo dạng lịch tháng</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('agent.viewings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Tạo lịch hẹn mới
                    </a>
                    <a href="{{ route('agent.viewings.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>Danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Navigation -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-calendar-alt me-2"></i>
                            {{ now()->format('F Y') }}
                        </h5>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary" onclick="previousMonth()">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline-primary" onclick="nextMonth()">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body p-0">
                    <div class="calendar-container">
                        <div class="calendar-header">
                            <div class="calendar-day-header">T2</div>
                            <div class="calendar-day-header">T3</div>
                            <div class="calendar-day-header">T4</div>
                            <div class="calendar-day-header">T5</div>
                            <div class="calendar-day-header">T6</div>
                            <div class="calendar-day-header">T7</div>
                            <div class="calendar-day-header">CN</div>
                        </div>
                        <div class="calendar-grid" id="calendarGrid">
                            <!-- Calendar days will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Legend -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">Chú thích:</h6>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <div class="calendar-viewing lead me-2" style="width: 20px; height: 20px;"></div>
                            <span>Lead</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="calendar-viewing tenant me-2" style="width: 20px; height: 20px;"></div>
                            <span>Khách thuê</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-warning me-2">Chờ xác nhận</span>
                            <span>Chờ xác nhận</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-info me-2">Đã xác nhận</span>
                            <span>Đã xác nhận</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-success me-2">Hoàn thành</span>
                            <span>Hoàn thành</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Viewing Detail Modal -->
<div class="modal fade" id="viewingDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết lịch hẹn</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewingDetailContent">
                <!-- Content will be populated by JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="#" class="btn btn-primary" id="viewingDetailLink">Xem chi tiết</a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/notifications.js') }}"></script>
<script src="{{ asset('assets/js/agent/viewings.js') }}"></script>
<script>
    // Viewings data from server
    const viewings = @json($viewings);
    
    // Calendar functionality
    let currentDate = new Date();
    
    function renderCalendar() {
        const year = currentDate.getFullYear();
        const month = currentDate.getMonth();
        
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);
        const startDate = new Date(firstDay);
        startDate.setDate(startDate.getDate() - firstDay.getDay() + 1); // Start from Monday
        
        const calendarGrid = document.getElementById('calendarGrid');
        calendarGrid.innerHTML = '';
        
        // Generate 42 days (6 weeks)
        for (let i = 0; i < 42; i++) {
            const date = new Date(startDate);
            date.setDate(startDate.getDate() + i);
            
            const dayElement = document.createElement('div');
            dayElement.className = 'calendar-day';
            
            if (date.getMonth() !== month) {
                dayElement.classList.add('other-month');
            }
            
            if (date.toDateString() === new Date().toDateString()) {
                dayElement.classList.add('today');
            }
            
            dayElement.innerHTML = `
                <div class="calendar-day-number">${date.getDate()}</div>
                <div class="calendar-day-events" data-date="${date.toISOString().split('T')[0]}"></div>
            `;
            
            calendarGrid.appendChild(dayElement);
        }
        
        // Add viewings to calendar
        viewings.forEach(viewing => {
            const viewingDate = new Date(viewing.schedule_at);
            const dateString = viewingDate.toISOString().split('T')[0];
            const dayEvents = document.querySelector(`[data-date="${dateString}"]`);
            
            if (dayEvents) {
                const viewingElement = document.createElement('div');
                viewingElement.className = `calendar-viewing ${viewing.customer_type}`;
                viewingElement.innerHTML = `
                    <div class="viewing-time">${viewingDate.toLocaleTimeString('vi-VN', {hour: '2-digit', minute: '2-digit'})}</div>
                    <div class="viewing-customer">${viewing.customer_name}</div>
                `;
                viewingElement.onclick = () => showViewingDetail(viewing);
                dayEvents.appendChild(viewingElement);
            }
        });
    }
    
    function showViewingDetail(viewing) {
        const modal = new bootstrap.Modal(document.getElementById('viewingDetailModal'));
        const content = document.getElementById('viewingDetailContent');
        const link = document.getElementById('viewingDetailLink');
        
        content.innerHTML = `
            <div class="row">
                <div class="col-md-6">
                    <strong>Khách hàng:</strong><br>
                    ${viewing.customer_name}
                    <span class="customer-type-badge ${viewing.customer_type} ms-2">
                        <i class="fas ${viewing.customer_type === 'tenant' ? 'fa-user' : 'fa-user-plus'}"></i>
                        ${viewing.customer_type === 'tenant' ? 'Khách thuê' : 'Lead'}
                    </span>
                </div>
                <div class="col-md-6">
                    <strong>Thời gian:</strong><br>
                    ${new Date(viewing.schedule_at).toLocaleString('vi-VN')}
                </div>
                <div class="col-md-6 mt-2">
                    <strong>Bất động sản:</strong><br>
                    ${viewing.property.name}
                </div>
                <div class="col-md-6 mt-2">
                    <strong>Phòng:</strong><br>
                    ${viewing.unit ? viewing.unit.code + ' - ' + viewing.unit.name : 'Chưa chọn'}
                </div>
                <div class="col-12 mt-2">
                    <strong>Trạng thái:</strong><br>
                    <span class="badge ${getStatusBadgeClass(viewing.status)}">
                        ${getStatusText(viewing.status)}
                    </span>
                </div>
                ${viewing.note ? `
                    <div class="col-12 mt-2">
                        <strong>Ghi chú:</strong><br>
                        <div class="bg-light p-2 rounded">${viewing.note}</div>
                    </div>
                ` : ''}
            </div>
        `;
        
        link.href = `/agent/viewings/${viewing.id}`;
        modal.show();
    }
    
    function previousMonth() {
        currentDate.setMonth(currentDate.getMonth() - 1);
        renderCalendar();
    }
    
    function nextMonth() {
        currentDate.setMonth(currentDate.getMonth() + 1);
        renderCalendar();
    }
    
    // Initialize calendar
    document.addEventListener('DOMContentLoaded', function() {
        renderCalendar();
    });
</script>
@endpush

@push('styles')
<link href="{{ asset('assets/css/notifications.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/agent/viewings.css') }}" rel="stylesheet">
<style>
    .calendar-container {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }
    
    .calendar-day-header {
        padding: 1rem;
        text-align: center;
        font-weight: 600;
        color: #495057;
        border-right: 1px solid #dee2e6;
    }
    
    .calendar-day-header:last-child {
        border-right: none;
    }
    
    .calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        min-height: 500px;
    }
    
    .calendar-day {
        border-right: 1px solid #dee2e6;
        border-bottom: 1px solid #dee2e6;
        padding: 0.5rem;
        min-height: 120px;
        position: relative;
    }
    
    .calendar-day:last-child {
        border-right: none;
    }
    
    .calendar-day.other-month {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    
    .calendar-day.today {
        background-color: #e3f2fd;
    }
    
    .calendar-day-number {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .calendar-day-events {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    
    .calendar-viewing {
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .calendar-viewing:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .viewing-time {
        font-weight: 600;
        margin-bottom: 0.125rem;
    }
    
    .viewing-customer {
        font-size: 0.7rem;
        opacity: 0.9;
    }
    
    @media (max-width: 768px) {
        .calendar-day {
            min-height: 80px;
            padding: 0.25rem;
        }
        
        .calendar-day-header {
            padding: 0.5rem 0.25rem;
            font-size: 0.875rem;
        }
        
        .calendar-viewing {
            font-size: 0.7rem;
            padding: 0.125rem 0.25rem;
        }
    }
</style>
@endpush
