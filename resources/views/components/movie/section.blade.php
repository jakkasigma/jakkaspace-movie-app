@props([
    'section',
])

<section class="movie-section" id="{{ $section['id'] }}">
    <div class="section-header">
        <div class="section-copy">
            <h2 class="row-title">{{ $section['title'] }}</h2>
            <p class="section-kicker">{{ $section['kicker'] }}</p>
        </div>

        <div class="results-chip">{{ count($section['movies']) }} Film</div>
    </div>

    @if ($section['statusMessage'] ?? null)
        <div class="status-banner">{{ $section['statusMessage'] }}</div>
    @endif

    @if (empty($section['movies']))
        <div class="empty-state">{{ $section['emptyMessage'] }}</div>
    @else
        <div class="{{ $section['layout'] === 'grid' ? 'movie-grid' : 'movie-row' }}">
            @foreach ($section['movies'] as $movie)
                <x-movie.card :movie="$movie" :rank="$loop->iteration" />
            @endforeach
        </div>
    @endif
</section>
