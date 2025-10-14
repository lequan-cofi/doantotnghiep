@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="pagination-section">
        <!-- Pagination Info -->
        <div class="pagination-info">
            <i class="fas fa-info-circle"></i>
            <span>
                Hiển thị {{ $paginator->firstItem() ?? 0 }} - {{ $paginator->lastItem() ?? 0 }} 
                trong tổng số {{ $paginator->total() }} hóa đơn
            </span>
        </div>
        
        <!-- Pagination Navigation -->
        <ul class="pagination" role="menubar" aria-label="Pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-angle-left"></i>
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-angle-left"></i>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}" aria-label="{{ __('Go to page :page', ['page' => $page]) }}">
                                    {{ $page }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="page-link" aria-hidden="true">
                        <i class="fas fa-angle-right"></i>
                    </span>
                </li>
            @endif
        </ul>
        
        <!-- Pagination Controls -->
        <div class="pagination-controls">
            @if($paginator->currentPage() > 1)
                <a href="{{ $paginator->url(1) }}" class="btn btn-outline-primary" aria-label="Go to first page">
                    <i class="fas fa-angle-double-left"></i>
                </a>
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-outline-primary" aria-label="@lang('pagination.previous')">
                    <i class="fas fa-angle-left"></i>
                </a>
            @endif
            
            @if($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-outline-primary" aria-label="@lang('pagination.next')">
                    <i class="fas fa-angle-right"></i>
                </a>
                <a href="{{ $paginator->url($paginator->lastPage()) }}" class="btn btn-outline-primary" aria-label="Go to last page">
                    <i class="fas fa-angle-double-right"></i>
                </a>
            @endif
        </div>
    </nav>
@endif
