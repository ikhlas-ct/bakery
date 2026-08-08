{{-- resources/views/vendor/pagination/custom.blade.php --}}

@if ($paginator->hasPages())
<ul class="pagination justify-content-center mb-0">

    {{-- Previous --}}
    @if ($paginator->onFirstPage())
        <li class="page-item disabled">
            <span class="page-link" aria-hidden="true">«</span>
        </li>
    @else
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a>
        </li>
    @endif

    {{-- Halaman pertama + ellipsis jika perlu --}}
    @if ($paginator->currentPage() > 3)
        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
        <li class="page-item disabled"><span class="page-link">...</span></li>
    @endif

    {{-- Halaman sekitar current page (window 2 kiri & kanan) --}}
    @for ($i = max(1, $paginator->currentPage() - 2); $i <= min($paginator->lastPage(), $paginator->currentPage() + 2); $i++)
        <li class="page-item {{ $i == $paginator->currentPage() ? 'active' : '' }}">
            @if ($i == $paginator->currentPage())
                <span class="page-link">{{ $i }}</span>
            @else
                <a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a>
            @endif
        </li>
    @endfor

    {{-- Ellipsis + halaman terakhir --}}
    @if ($paginator->currentPage() < $paginator->lastPage() - 2)
        <li class="page-item disabled"><span class="page-link">...</span></li>
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">
                {{ $paginator->lastPage() }}
            </a>
        </li>
    @endif

    {{-- Next --}}
    @if ($paginator->hasMorePages())
        <li class="page-item">
            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">»</a>
        </li>
    @else
        <li class="page-item disabled">
            <span class="page-link" aria-hidden="true">»</span>
        </li>
    @endif

</ul>
@endif
