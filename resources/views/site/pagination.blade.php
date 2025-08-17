@if ($paginator->hasPages())
    <div class="pagenavi">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            {{-- Không hiển thị nút prev nếu đang ở page 1 --}}
        @else
            <a title="Previous Page" href="{{ $paginator->previousPageUrl() }}" data-page="{{ $paginator->currentPage() - 1 }}">←</a>
        @endif

        {{-- Pagination Elements --}}
        @php
            $start = max($paginator->currentPage() - 1, 1);
            $end = min($paginator->currentPage() + 1, $paginator->lastPage());
        @endphp

        {{-- Trang đầu luôn hiện --}}
        <a title="Page 1" href="{{ $paginator->url(1) }}"
            class="{{ $paginator->currentPage() === 1 ? 'active' : '' }}" data-page="1">1</a>

        {{-- Hiển thị dấu ... nếu cần --}}
        @if ($start > 2)
            <span>..&nbsp;</span>
        @endif

        {{-- Các trang ở giữa --}}
        @for ($page = $start; $page <= $end; $page++)
            @if ($page != 1 && $page != $paginator->lastPage())
                <a title="Page {{ $page }}" href="{{ $paginator->url($page) }}"
                    class="{{ $paginator->currentPage() === $page ? 'active' : '' }}" data-page="{{ $page }}">
                    {{ $page }}
                </a>
            @endif
        @endfor

        {{-- Hiển thị dấu ... nếu chưa tới trang cuối --}}
        @if ($end < $paginator->lastPage() - 1)
            <span>..&nbsp;</span>
        @endif

        {{-- Trang cuối --}}
        @if ($paginator->lastPage() > 1)
            <a title="Page {{ $paginator->lastPage() }}" href="{{ $paginator->url($paginator->lastPage()) }}"
                class="{{ $paginator->currentPage() === $paginator->lastPage() ? 'active' : '' }}"
                data-page="{{ $paginator->lastPage() }}">
                {{ $paginator->lastPage() }}
            </a>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a title="Next Page" href="{{ $paginator->nextPageUrl() }}" data-page="{{ $paginator->currentPage() + 1 }}">→</a>
        @endif
    </div>
@endif
