@props([
    'currentPage',
    'totalPages',
])

@if ($totalPages > 1)
    <nav class="pagination" aria-label="Navigasi halaman">
        @if ($currentPage > 1)
            <a
                href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}"
                class="pagination-btn"
                aria-label="Halaman sebelumnya"
            >&#8592; Sebelumnya</a>
        @endif

        <span class="pagination-info">
            {{ $currentPage }} / {{ $totalPages }}
        </span>

        @if ($currentPage < $totalPages)
            <a
                href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}"
                class="pagination-btn"
                aria-label="Halaman berikutnya"
            >Berikutnya &#8594;</a>
        @endif
    </nav>
@endif
