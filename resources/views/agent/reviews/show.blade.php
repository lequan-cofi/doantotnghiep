@extends('layouts.agent_dashboard')

@section('title', 'Chi tiết Đánh giá')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('agent.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('agent.reviews.index') }}">Đánh giá</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </div>
                <h4 class="page-title">Chi tiết Đánh giá</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Review Details -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">{{ $review->title }}</h5>
                        <div class="d-flex align-items-center gap-2">
                            @if($review->replies->count() > 0)
                                <span class="badge bg-success">Đã phản hồi</span>
                            @else
                                <span class="badge bg-warning">Chờ phản hồi</span>
                            @endif
                            <span class="badge bg-primary">{{ $review->status_label }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Review Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Bất động sản:</label>
                                <p class="mb-0">{{ $review->unit->property->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Phòng:</label>
                                <p class="mb-0">{{ $review->unit->code }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Địa chỉ:</label>
                                <p class="mb-0">{{ $review->unit->property->address }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Người đánh giá:</label>
                                <p class="mb-0">{{ $review->tenant->full_name ?? $review->tenant->name }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Ngày đánh giá:</label>
                                <p class="mb-0">{{ $review->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hợp đồng:</label>
                                <p class="mb-0">{{ $review->lease->contract_no ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Overall Rating -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Đánh giá tổng thể:</label>
                        <div class="d-flex align-items-center">
                            <div class="rating me-3">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $review->overall_rating)
                                        <i class="mdi mdi-star text-warning"></i>
                                    @elseif($i - 0.5 <= $review->overall_rating)
                                        <i class="mdi mdi-star-half-full text-warning"></i>
                                    @else
                                        <i class="mdi mdi-star-outline text-muted"></i>
                                    @endif
                                @endfor
                            </div>
                            <span class="h4 mb-0 text-warning">{{ $review->overall_rating }}/5</span>
                        </div>
                    </div>

                    <!-- Detail Ratings -->
                    @if($review->location_rating || $review->quality_rating || $review->service_rating || $review->price_rating)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Đánh giá chi tiết:</label>
                            <div class="row">
                                @if($review->location_rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Vị trí:</span>
                                            <div class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->location_rating)
                                                        <i class="mdi mdi-star text-warning"></i>
                                                    @else
                                                        <i class="mdi mdi-star-outline text-muted"></i>
                                                    @endif
                                                @endfor
                                                <span class="ms-1">{{ $review->location_rating }}/5</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($review->quality_rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Chất lượng:</span>
                                            <div class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->quality_rating)
                                                        <i class="mdi mdi-star text-warning"></i>
                                                    @else
                                                        <i class="mdi mdi-star-outline text-muted"></i>
                                                    @endif
                                                @endfor
                                                <span class="ms-1">{{ $review->quality_rating }}/5</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($review->service_rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Dịch vụ:</span>
                                            <div class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->service_rating)
                                                        <i class="mdi mdi-star text-warning"></i>
                                                    @else
                                                        <i class="mdi mdi-star-outline text-muted"></i>
                                                    @endif
                                                @endfor
                                                <span class="ms-1">{{ $review->service_rating }}/5</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if($review->price_rating)
                                    <div class="col-md-6 mb-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span>Giá cả:</span>
                                            <div class="rating">
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $review->price_rating)
                                                        <i class="mdi mdi-star text-warning"></i>
                                                    @else
                                                        <i class="mdi mdi-star-outline text-muted"></i>
                                                    @endif
                                                @endfor
                                                <span class="ms-1">{{ $review->price_rating }}/5</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Review Content -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Nội dung đánh giá:</label>
                        <div class="border rounded p-3 bg-light">
                            <p class="mb-0">{{ $review->content }}</p>
                        </div>
                    </div>

                    <!-- Highlights -->
                    @if($review->highlights && count($review->highlights) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Điểm nổi bật:</label>
                            <div class="mt-2">
                                @foreach($review->highlights as $highlight)
                                    <span class="badge bg-primary me-2 mb-2">{{ $highlight }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Recommend -->
                    @if($review->recommend)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Khuyến nghị:</label>
                            <div class="mt-2">
                                <span class="badge bg-info">{{ $review->recommend_label }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Images -->
                    @if($review->images && count($review->images) > 0)
                        <div class="mb-4">
                            <label class="form-label fw-bold">Hình ảnh:</label>
                            <div class="row mt-2">
                                @foreach($review->images as $image)
                                    <div class="col-md-4 mb-3">
                                        <img src="{{ Storage::url($image) }}" class="img-fluid rounded" alt="Review image" style="max-height: 200px; object-fit: cover;">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Review Stats -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="mdi mdi-eye text-primary mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">{{ $review->view_count ?? 0 }}</div>
                                <small class="text-muted">Lượt xem</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="mdi mdi-thumb-up text-success mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">{{ $review->helpful_count ?? 0 }}</div>
                                <small class="text-muted">Hữu ích</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 border rounded">
                                <i class="mdi mdi-comment-multiple text-info mb-2" style="font-size: 1.5rem;"></i>
                                <div class="fw-bold">{{ $review->replies->count() }}</div>
                                <small class="text-muted">Phản hồi</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reply Section -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Phản hồi</h5>
                </div>
                <div class="card-body">
                    <!-- Reply Form -->
                    <form id="reply-form" class="mb-4">
                        <div class="mb-3">
                            <label for="reply-content" class="form-label">Nội dung phản hồi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reply-content" name="content" rows="4" placeholder="Nhập nội dung phản hồi..." required></textarea>
                            <div class="form-text">Tối thiểu 10 ký tự, tối đa 1000 ký tự</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="mdi mdi-send me-1"></i>Gửi phản hồi
                        </button>
                    </form>

                    <!-- Replies List -->
                    <div id="replies-list">
                        @if($review->replies->count() > 0)
                            @foreach($review->replies as $reply)
                                @include('agent.reviews.partials.reply-item', ['reply' => $reply])
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="mdi mdi-comment-outline text-muted" style="font-size: 2rem;"></i>
                                <p class="text-muted mt-2 mb-0">Chưa có phản hồi nào</p>
                            </div>
                        @endif
                    </div>
                </div>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const reviewId = {{ $review->id }};
    
    // Handle reply form submission
    document.getElementById('reply-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const content = document.getElementById('reply-content').value.trim();
        if (content.length < 10) {
            alert('Nội dung phản hồi phải có ít nhất 10 ký tự');
            return;
        }
        
        if (content.length > 1000) {
            alert('Nội dung phản hồi không được vượt quá 1000 ký tự');
            return;
        }
        
        // Submit reply using the manager
        if (window.agentReviewsManager) {
            window.agentReviewsManager.submitReply();
        } else {
            // Fallback to global function
            submitReply(reviewId, content);
        }
    });
});
</script>
@endpush

<style>
.rating {
    font-size: 1.2rem;
}

.rating i {
    margin-right: 2px;
}

.reply-item {
    border-left: 3px solid #e3e6f0;
    padding-left: 1rem;
    margin-bottom: 1rem;
}

.reply-item.agent-reply {
    border-left-color: #007bff;
}

.reply-item.tenant-reply {
    border-left-color: #28a745;
}
</style>
