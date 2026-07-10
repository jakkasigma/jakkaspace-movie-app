@extends('layouts.movie')

@section('title', $search !== '' ? 'Jakka Space - ' . $search : 'Jakka Space - Movie Indonesia')
@section('description', 'Jakka Space menampilkan daftar film populer dan hasil pencarian film dari TMDB.')

@php
    $bodyClass = 'movie-page' . ($heroMovie ? ' has-hero' : '') . ($search === '' && ! $isFiltered ? ' is-home' : ' is-search');
@endphp

@section('body-class', $bodyClass)

@section('body')
    <audio id="intro-sound" src="/assets/sound1.mp3" preload="none"></audio>

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
        <x-movie.navbar />

        @if ($heroMovie)
            <x-movie.hero :movie="$heroMovie" :search="$search" />
        @endif

        <main class="movies-page-content">

            {{-- Personalized section — hanya di home, bukan saat filter/search --}}
            @if (! empty($personalizedMovies) && ! $isFiltered && $search === '')
                <section class="discover-personal-section">
                    <div class="discover-personal-header">
                        <h2 class="discover-personal-title">Untukmu</h2>
                        <p class="discover-personal-desc">Berdasarkan genre yang sering kamu tonton.</p>
                    </div>
                    <div class="movie-row">
                        @foreach ($personalizedMovies as $movie)
                            <x-movie.card :movie="$movie" />
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Filter bar — selalu tampil di bawah hero/personalized --}}
            @if ($search === '')
                <div class="home-filter-wrap">
                    <form method="GET" action="{{ route('movies.index') }}" class="discover-filters">
                        <div class="filter-group">
                            <label class="filter-label" for="filter-genre">Genre</label>
                            <select id="filter-genre" name="genre" class="filter-select">
                                <option value="">Semua Genre</option>
                                @foreach ($genres as $genre)
                                    <option
                                        value="{{ $genre['id'] }}"
                                        {{ ($filters['genre'] ?? null) == $genre['id'] ? 'selected' : '' }}
                                    >{{ $genre['name'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="filter-year">Tahun</label>
                            <select id="filter-year" name="year" class="filter-select">
                                <option value="">Semua Tahun</option>
                                @foreach (range(date('Y'), 1970) as $year)
                                    <option
                                        value="{{ $year }}"
                                        {{ ($filters['year'] ?? null) == $year ? 'selected' : '' }}
                                    >{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="filter-group">
                            <label class="filter-label" for="filter-sort">Urutkan</label>
                            <select id="filter-sort" name="sort_by" class="filter-select">
                                <option value="popularity.desc" {{ ($filters['sort_by'] ?? '') === 'popularity.desc' ? 'selected' : '' }}>Paling Populer</option>
                                <option value="vote_average.desc" {{ ($filters['sort_by'] ?? '') === 'vote_average.desc' ? 'selected' : '' }}>Rating Tertinggi</option>
                                <option value="release_date.desc" {{ ($filters['sort_by'] ?? '') === 'release_date.desc' ? 'selected' : '' }}>Terbaru</option>
                                <option value="release_date.asc" {{ ($filters['sort_by'] ?? '') === 'release_date.asc' ? 'selected' : '' }}>Terlama</option>
                            </select>
                        </div>

                        <button type="submit" class="filter-submit">Terapkan</button>

                        @if ($isFiltered)
                            <a href="{{ route('movies.index') }}" class="filter-reset">Reset</a>
                        @endif
                    </form>
                </div>
            @endif

            {{-- Filter result grid --}}
            @if ($isFiltered && $discoverResult !== null)
                <div class="discover-body">
                    @if (empty($discoverResult['movies']))
                        <div class="empty-state">Tidak ada film yang ditemukan dengan filter ini.</div>
                    @else
                        <div class="movie-grid">
                            @foreach ($discoverResult['movies'] as $movie)
                                <x-movie.card :movie="$movie" :rank="$loop->iteration" />
                            @endforeach
                        </div>
                        <x-movie.pagination :currentPage="$discoverResult['current_page']" :totalPages="$discoverResult['total_pages']" />
                    @endif
                </div>

            {{-- Home sections or search result --}}
            @else
                @foreach ($movieSections as $section)
                    <x-movie.section :section="$section" />
                @endforeach
            @endif

        </main>

        <footer id="footer">
            <div>&copy; 2026 JAKKA SPACE</div>
            <div id="clock">YOGYAKARTA - 00:00</div>
            <div>STAY CURIOUS / STAY WATCHING</div>
        </footer>
    </div>
@endsection
