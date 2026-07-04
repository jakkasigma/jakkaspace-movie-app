@extends('layouts.movie')

@section('title', 'Discover — Jakka Space')
@section('description', 'Temukan film dari seluruh penjuru dunia. Filter berdasarkan genre, tahun, dan rating.')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="discover-page">
        <header class="discover-header">
            <div class="discover-header-copy">
                <h1 class="discover-title">DISCOVER</h1>
                <p class="discover-subtitle">Temukan film dari seluruh penjuru dunia.</p>
            </div>
        </header>

        {{-- Personalized recommendations -- hanya kalau login dan ada data --}}
        @if (! empty($personalizedMovies))
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

        {{-- Filter bar --}}
        <form method="GET" action="{{ route('movies.discover') }}" class="discover-filters">
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

            @if (! empty(array_filter(array_diff_key($filters, array_flip(['page', 'sort_by'])))))
                <a href="{{ route('movies.discover') }}" class="filter-reset">Reset</a>
            @endif
        </form>

        {{-- Results --}}
        <div class="discover-body">
            @if (empty($movies))
                <div class="empty-state">Tidak ada film yang ditemukan dengan filter ini.</div>
            @else
                <div class="movie-grid">
                    @foreach ($movies as $movie)
                        <x-movie.card :movie="$movie" :rank="$loop->iteration" />
                    @endforeach
                </div>

                <x-movie.pagination :currentPage="$currentPage" :totalPages="$totalPages" />
            @endif
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
