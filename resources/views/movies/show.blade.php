<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $movie ? 'Detail Film - ' . $movie['title'] : 'Detail Film - Jakka Space' }}</title>
    <meta name="description" content="{{ $movie ? $movie['overview'] : 'Detail film Jakka Space.' }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@300;400;600;700&family=Lora:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://fonts.cdnfonts.com/css/peace-sans" rel="stylesheet">
    @php
        $hotFilePath = public_path('hot');
        $hotUrl = file_exists($hotFilePath) ? trim(file_get_contents($hotFilePath)) : null;
        $hotParts = is_string($hotUrl) ? parse_url($hotUrl) : false;
        $shouldUseHotAssets = false;

        if (is_array($hotParts) && isset($hotParts['host'], $hotParts['port'])) {
            $socket = @fsockopen($hotParts['host'], (int) $hotParts['port'], $errorNumber, $errorMessage, 0.3);

            if (is_resource($socket)) {
                fclose($socket);
                $shouldUseHotAssets = true;
            }
        }

        $buildManifestPath = public_path('build/manifest.json');
        $buildManifest = file_exists($buildManifestPath)
            ? json_decode(file_get_contents($buildManifestPath), true)
            : null;
        $buildEntries = is_array($buildManifest) ? array_values($buildManifest) : [];
        $compiledCss = collect($buildEntries)->first(
            fn (array $entry): bool => str_ends_with($entry['file'] ?? '', '.css'),
        );
        $compiledJs = collect($buildEntries)->first(
            fn (array $entry): bool => str_ends_with($entry['file'] ?? '', '.js'),
        );
    @endphp

    @if ($shouldUseHotAssets)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        @if ($compiledCss)
            <link rel="stylesheet" href="{{ asset('build/' . $compiledCss['file']) }}">
        @endif

        @if ($compiledJs)
            <script type="module" src="{{ asset('build/' . $compiledJs['file']) }}"></script>
        @endif
    @endif
</head>
<body class="detail-page anim-started intro-complete">
    <div id="movie-detail" class="detail-overlay active">
        <a href="{{ route('movies.index') }}" id="detail-back" class="detail-back" aria-label="Kembali">KEMBALI</a>

        @if (! $movie)
            <div class="detail-container">
                <div class="detail-empty-state">{{ $errorMessage }}</div>
            </div>
        @else
            <div class="detail-container">
                @if ($movie['backdrop_url'])
                    <div class="detail-backdrop" style="background-image: url('{{ $movie['backdrop_url'] }}')"></div>
                @endif

                <div class="detail-body">
                    <div class="detail-poster-wrap">
                        @if ($movie['poster_url'])
                            <img id="detail-poster" class="detail-poster" src="{{ $movie['poster_url'] }}" alt="Poster {{ $movie['title'] }}">
                        @else
                            <div class="detail-poster detail-poster-placeholder">No Poster</div>
                        @endif
                    </div>

                    <div class="detail-info">
                        <h1 class="detail-title">
                            {{ $movie['title'] }}
                            @if ($movie['release_year'])
                                ({{ $movie['release_year'] }})
                            @endif
                        </h1>

                        <div class="detail-meta">
                            @if ($movie['release_date'])
                                <span>{{ $movie['release_date'] }}</span>
                            @endif

                            @if ($movie['genres'])
                                <span>{{ $movie['genres'] }}</span>
                            @endif

                            @if ($movie['runtime'])
                                <span>{{ $movie['runtime'] }}</span>
                            @endif
                        </div>

                        <div class="detail-score-row">
                            <div class="detail-star-rating">
                                <span class="star-icon" aria-hidden="true">&#9733;</span>
                                <span class="score-text">{{ $movie['rating'] }}</span>
                            </div>
                            <span class="score-label">Rating</span>
                        </div>

                        <div class="detail-actions">
                            <button class="btn-sewa-detail" type="button">SEWA Rp5K</button>
                            <button class="btn-beli-detail" type="button">BELI Rp15K</button>

                            @if ($movie['trailer_url'])
                                <a class="btn-trailer" href="{{ $movie['trailer_url'] }}" target="_blank" rel="noopener noreferrer">Play Trailer</a>
                            @endif
                        </div>

                        @if ($movie['tagline'])
                            <p class="detail-tagline">"{{ $movie['tagline'] }}"</p>
                        @endif

                        <h3 class="detail-section-label">Kilasan Singkat</h3>
                        <p class="detail-synopsis">{{ $movie['overview'] }}</p>

                        <div class="detail-crew">
                            <p class="crew-name">{{ $movie['director'] ?? 'Belum tersedia' }}</p>
                            <p class="crew-role">Director, Screenplay</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</body>
</html>
