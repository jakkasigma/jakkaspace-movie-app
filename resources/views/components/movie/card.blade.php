@props([
    'movie',
    'rank' => null,
    'badge' => null,
])

<a
    href="{{ route('movies.show', $movie['id'], false) }}"
    class="movie-card-sm"
    aria-label="Lihat detail {{ $movie['title'] }}"
>
    @if ($rank !== null)
        <div class="card-rank">{{ $rank }}</div>
    @endif

    <div class="card-poster-wrap">
        @if ($movie['poster_url'] ?? null)
            <img
                src="{{ $movie['poster_url'] }}"
                alt="Poster {{ $movie['title'] }}"
                loading="lazy"
            >
        @else
            <div class="no-poster">No Poster</div>
        @endif

        @if ($badge !== null)
            <span class="card-badge">{{ $badge }}</span>
        @endif
    </div>

    <div class="card-info-sm">
        <span class="card-rating">Rating {{ $movie['rating'] }}</span>
        <p class="card-title-sm">{{ $movie['title'] }}</p>

        @if ($movie['release_year'] ?? null)
            <span class="card-meta">{{ $movie['release_year'] }}</span>
        @endif
    </div>
</a>
