@props([
    'movie',
    'search' => '',
])

<section id="hero">
    <div class="hero-overlay"></div>

    @if ($movie['backdrop_url'] ?? $movie['poster_url'] ?? null)
        <img
            src="{{ $movie['backdrop_url'] ?? $movie['poster_url'] }}"
            alt="Backdrop {{ $movie['title'] }}"
            class="hero-bg"
        >
    @endif

    <div class="hero-content">
        <div class="hero-badge">
            {{ $search !== '' ? 'Hasil Pencarian' : 'Film Unggulan' }}
        </div>

        <h1 class="hero-title">{{ $movie['title'] }}</h1>

        <p class="hero-meta">
            @if ($movie['release_date'] ?? null)
                <span>{{ $movie['release_date'] }}</span>
            @endif

            <span>Rating {{ $movie['rating'] }}</span>

            @if ($search !== '')
                <span>Kata kunci "{{ $search }}"</span>
            @endif
        </p>

        <p class="hero-desc">{{ $movie['overview'] }}</p>

        <div class="hero-actions">
            <a href="{{ route('movies.show', $movie['id'], false) }}" class="btn-info">Info Film</a>
        </div>
    </div>
</section>
