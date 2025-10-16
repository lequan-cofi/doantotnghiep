@extends('layouts.agent_dashboard')

@section('title', 'Quản lý Đánh giá')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Đánh giá</li>
                    </ol>
                </div>
                <h4 class="page-title">Quản lý Đánh giá</h4>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-star widget-icon bg-success-lighten text-success"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Tổng số đánh giá">Tổng đánh giá</h5>
                    <h3 class="mt-3 mb-3">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-star-half-full widget-icon bg-warning-lighten text-warning"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Đánh giá trung bình">Đánh giá TB</h5>
                    <h3 class="mt-3 mb-3">{{ $stats['avg_rating'] }}/5</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-comment-alert widget-icon bg-danger-lighten text-danger"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Chờ phản hồi">Chờ phản hồi</h5>
                    <h3 class="mt-3 mb-3">{{ $stats['pending_reply'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card widget-flat">
                <div class="card-body">
                    <div class="float-end">
                        <i class="mdi mdi-comment-check widget-icon bg-info-lighten text-info"></i>
                    </div>
                    <h5 class="text-muted fw-normal mt-0" title="Đã phản hồi">Đã phản hồi</h5>
                    <h3 class="mt-3 mb-3">{{ $stats['replied'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content with Tabs -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Tab Navigation -->
                    <ul class="nav nav-tabs nav-bordered mb-3" id="reviews-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="all-reviews-tab" data-bs-toggle="tab" data-bs-target="#all-reviews" type="button" role="tab" aria-controls="all-reviews" aria-selected="true">
                                <i class="mdi mdi-format-list-bulleted me-1"></i>
                                Tất cả đánh giá
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pending-reply-tab" data-bs-toggle="tab" data-bs-target="#pending-reply" type="button" role="tab" aria-controls="pending-reply" aria-selected="false">
                                <i class="mdi mdi-comment-alert me-1"></i>
                                Chờ phản hồi
                                @if($stats['pending_reply'] > 0)
                                    <span class="badge bg-danger ms-1">{{ $stats['pending_reply'] }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="replied-tab" data-bs-toggle="tab" data-bs-target="#replied" type="button" role="tab" aria-controls="replied" aria-selected="false">
                                <i class="mdi mdi-comment-check me-1"></i>
                                Đã phản hồi
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="recent-tab" data-bs-toggle="tab" data-bs-target="#recent" type="button" role="tab" aria-controls="recent" aria-selected="false">
                                <i class="mdi mdi-clock-outline me-1"></i>
                                Gần đây
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="reviews-tab-content">
                        <!-- All Reviews Tab -->
                        <div class="tab-pane fade show active" id="all-reviews" role="tabpanel" aria-labelledby="all-reviews-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control" id="search-all" placeholder="Tìm kiếm đánh giá...">
                                        <i class="mdi mdi-magnify search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="rating-filter-all">
                                        <option value="">Tất cả đánh giá</option>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" onclick="refreshReviews('all')">
                                            <i class="mdi mdi-refresh me-1"></i>Làm mới
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="loadAllReviews()">
                                            <i class="mdi mdi-eye me-1"></i>Xem tất cả
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="all-reviews-content">
                                @include('agent.reviews.partials.reviews-list', ['reviews' => $reviews, 'tab' => 'all'])
                            </div>
                        </div>

                        <!-- Pending Reply Tab -->
                        <div class="tab-pane fade" id="pending-reply" role="tabpanel" aria-labelledby="pending-reply-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control" id="search-pending" placeholder="Tìm kiếm đánh giá chờ phản hồi...">
                                        <i class="mdi mdi-magnify search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="rating-filter-pending">
                                        <option value="">Tất cả đánh giá</option>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" onclick="refreshReviews('pending_reply')">
                                            <i class="mdi mdi-refresh me-1"></i>Làm mới
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="loadAllReviews()">
                                            <i class="mdi mdi-eye me-1"></i>Xem tất cả
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="pending-reply-content">
                                <!-- Content will be loaded via AJAX -->
                            </div>
                        </div>

                        <!-- Replied Tab -->
                        <div class="tab-pane fade" id="replied" role="tabpanel" aria-labelledby="replied-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control" id="search-replied" placeholder="Tìm kiếm đánh giá đã phản hồi...">
                                        <i class="mdi mdi-magnify search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="rating-filter-replied">
                                        <option value="">Tất cả đánh giá</option>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" onclick="refreshReviews('replied')">
                                            <i class="mdi mdi-refresh me-1"></i>Làm mới
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="loadAllReviews()">
                                            <i class="mdi mdi-eye me-1"></i>Xem tất cả
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="replied-content">
                                <!-- Content will be loaded via AJAX -->
                            </div>
                        </div>

                        <!-- Recent Tab -->
                        <div class="tab-pane fade" id="recent" role="tabpanel" aria-labelledby="recent-tab">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="search-box">
                                        <input type="text" class="form-control" id="search-recent" placeholder="Tìm kiếm đánh giá gần đây...">
                                        <i class="mdi mdi-magnify search-icon"></i>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="rating-filter-recent">
                                        <option value="">Tất cả đánh giá</option>
                                        <option value="5">5 sao</option>
                                        <option value="4">4 sao</option>
                                        <option value="3">3 sao</option>
                                        <option value="2">2 sao</option>
                                        <option value="1">1 sao</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-primary" onclick="refreshReviews('recent')">
                                            <i class="mdi mdi-refresh me-1"></i>Làm mới
                                        </button>
                                        <button class="btn btn-outline-secondary" onclick="loadAllReviews()">
                                            <i class="mdi mdi-eye me-1"></i>Xem tất cả
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div id="recent-content">
                                <!-- Content will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="replyModalLabel">Phản hồi đánh giá</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="review-details" class="mb-3">
                    <!-- Review details will be loaded here -->
                </div>
                <form id="reply-form">
                    <div class="mb-3">
                        <label for="reply-content" class="form-label">Nội dung phản hồi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="reply-content" name="content" rows="4" placeholder="Nhập nội dung phản hồi..." required></textarea>
                        <div class="form-text">Tối thiểu 10 ký tự, tối đa 1000 ký tự</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="submit-reply">Gửi phản hồi</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Reply Modal -->
<div class="modal fade" id="editReplyModal" tabindex="-1" aria-labelledby="editReplyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editReplyModalLabel">Chỉnh sửa phản hồi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="edit-reply-form">
                    <div class="mb-3">
                        <label for="edit-reply-content" class="form-label">Nội dung phản hồi <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="edit-reply-content" name="content" rows="4" placeholder="Nhập nội dung phản hồi..." required></textarea>
                        <div class="form-text">Tối thiểu 10 ký tự, tối đa 1000 ký tự</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="submit-edit-reply">Cập nhật phản hồi</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('assets/css/agent/reviews.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script src="{{ asset('assets/js/agent/reviews.js') }}"></script>
@endpush
