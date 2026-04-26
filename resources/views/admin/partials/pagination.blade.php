    <nav class="pagination-nav" role="navigation" aria-label="Navigasi pagination">
        <ul class="pagination">
            {{-- Previous Page Link --}}
            <li class="page-item {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                @if ($paginator->onFirstPage())
                    <span class="page-link" aria-disabled="true" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                @else
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                @endif
            </li>

            {{-- Pagination Elements --}}
            @php
                $elements = $elements
                    ?? ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator ? $paginator->elements() : []);
            @endphp
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
                        <li class="page-item {{ $page === $paginator->currentPage() ? 'active' : '' }}">
                            @if ($page === $paginator->currentPage())
                                <span class="page-link" aria-current="page">{{ $page }}</span>
                            @else
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            <li class="page-item {{ $paginator->hasMorePages() ? '' : 'disabled' }}">
                @if ($paginator->hasMorePages())
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="page-link" aria-disabled="true" aria-label="@lang('pagination.next')">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </li>
        </ul>
    </nav>
