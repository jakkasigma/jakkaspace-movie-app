<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $search !== '' ? 'Jakka Space - ' . $search : 'Jakka Space - Movie Indonesia' }}</title>
    <meta name="description" content="Jakka Space menampilkan daftar film populer dan hasil pencarian film dari TMDB.">
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
<body class="movie-page{{ $heroMovie ? ' has-hero' : '' }}">
    <audio id="intro-sound" src="{{ asset('assets/sound1.mp3') }}" preload="auto"></audio>

    <div id="pre-splash">
        <p id="splash-text">Put on your headset and listen.</p>
        <button id="splash-start" type="button">START</button>
    </div>

    <div id="intro-overlay" aria-hidden="true">
        <div id="intro-logo">
            <div id="jakka-word">JAKKA</div>
            <div id="space-word">
                <span class="space-letter" id="s-letter">S</span>
                <span class="space-letter" id="p-letter">P</span>
                <span class="space-letter" id="a-letter">A</span>
                <span class="space-letter" id="c-letter">C</span>
                <span class="space-letter" id="e-letter">E</span>
            </div>
        </div>
    </div>

    <div id="homepage">
    <nav id="navbar">
        <a href="{{ route('movies.index') }}" class="nav-logo" aria-label="Jakka Space">
            <span class="nav-jakka">JAKKA</span>
            <span class="nav-space-wrap">
                <span class="nav-letter" style="color:#40E0D0;">S</span>
                <span class="nav-letter" style="color:#FF0000;">P</span>
                <span class="nav-letter" style="color:#FF69B4;">A</span>
                <span class="nav-letter" style="color:#00FF00;">C</span>
                <span class="nav-letter" style="color:#8A2BE2;">E</span>
            </span>
        </a>

        <div class="nav-center" data-menu-panel>
            <ul class="nav-links">
                <li>
                    <a href="{{ route('movies.index') }}" class="{{ $search === '' ? 'active' : '' }}" data-menu-link>HOME</a>
                </li>
                <li>
                    <a href="#all-movies" class="{{ $search !== '' ? 'active' : '' }}" data-menu-link>ALL MOVIE</a>
                </li>
            </ul>

            <form method="GET" action="{{ route('movies.index') }}" class="nav-search">
                <button type="submit" class="search-button" aria-label="Cari film">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon" aria-hidden="true">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </button>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Cari Film..."
                    class="search-input"
                    aria-label="Cari Film"
                    autocomplete="off"
                >

                @if ($search !== '')
                    <a href="{{ route('movies.index') }}" class="search-reset">Reset</a>
                @endif
            </form>
        </div>

        <button class="hamburger" type="button" aria-label="Menu" data-menu-button>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </nav>

    @if ($heroMovie)
        <section id="hero">
            <div class="hero-overlay"></div>

            @if ($heroMovie['backdrop_url'] ?? $heroMovie['poster_url'])
                <img
                    src="{{ $heroMovie['backdrop_url'] ?? $heroMovie['poster_url'] }}"
                    alt="Backdrop {{ $heroMovie['title'] }}"
                    class="hero-bg"
                >
            @endif

            <div class="hero-content">
                <div class="hero-badge">{{ $search !== '' ? 'Hasil Pencarian' : 'Film Populer' }}</div>
                <h1 class="hero-title">{{ $heroMovie['title'] }}</h1>

                <p class="hero-meta">
                    @if ($heroMovie['release_date'])
                        <span>{{ $heroMovie['release_date'] }}</span>
                    @endif

                    <span>Rating {{ $heroMovie['rating'] }}</span>

                    @if ($search !== '')
                        <span>Kata kunci "{{ $search }}"</span>
                    @endif
                </p>

                <p class="hero-desc">{{ $heroMovie['overview'] }}</p>

                <div class="hero-actions">
                    <a href="#all-movies" class="btn-rent">Lihat Film</a>

                    @if ($search !== '')
                        <a href="{{ route('movies.index') }}" class="btn-info">Film Populer</a>
                    @else
                        <a href="{{ route('movies.index', ['search' => $heroMovie['title']]) }}" class="btn-info">Cari Serupa</a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <main class="movies-page-content">
        <section class="movie-section" id="all-movies">
            <div class="section-header">
                <div class="section-copy">
                    <h1 class="row-title">{{ $sectionTitle }}</h1>
                    <p class="section-kicker">{{ $sectionKicker }}</p>
                </div>

                <div class="results-chip">{{ count($movies) }} Film</div>
            </div>

            @if ($search !== '' && $movies !== [])
                <div class="status-banner">Menampilkan hasil pencarian untuk "{{ $search }}".</div>
            @endif

            @if (empty($movies))
                <div class="empty-state">{{ $emptyMessage }}</div>
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <article class="movie-card-sm">
                            <div class="card-rank">{{ $loop->iteration }}</div>

                            <div class="card-poster-wrap">
                                @if ($movie['poster_url'])
                                    <img src="{{ $movie['poster_url'] }}" alt="Poster {{ $movie['title'] }}" loading="lazy">
                                @else
                                    <div class="no-poster">No Poster</div>
                                @endif

                                <div class="badge-sewa">SEWA 5K</div>
                                <div class="badge-beli">BELI 15K</div>
                            </div>

                            <div class="card-info-sm">
                                <span class="card-rating">Rating {{ $movie['rating'] }}</span>
                                <p class="card-title-sm">{{ $movie['title'] }}</p>

                                @if ($movie['release_year'])
                                    <span class="card-meta">{{ $movie['release_year'] }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
    </div>
</body>
</html>
