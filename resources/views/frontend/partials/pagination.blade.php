@if ($paginator->hasPages())
    <nav class="news-pagination" role="navigation" aria-label="Pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span class="pagination-btn" aria-disabled="true" aria-label="@lang('pagination.previous')">&lsaquo;</span>
        @else
            <a class="pagination-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev"
                aria-label="@lang('pagination.previous')">&lsaquo;</a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="pagination-btn pagination-btn--dots" aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pagination-btn active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="pagination-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a class="pagination-btn" href="{{ $paginator->nextPageUrl() }}" rel="next"
                aria-label="@lang('pagination.next')">&rsaquo;</a>
        @else
            <span class="pagination-btn" aria-disabled="true" aria-label="@lang('pagination.next')">&rsaquo;</span>
        @endif
    </nav>
@endif