@extends('layouts.movie')

@section('title', 'Statistik — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <header class="space-header">
            <div class="space-header-inner">
                <h1 class="space-page-title">STATISTIK</h1>
                <p class="space-page-subtitle">Rekap perjalanan menontonmu.</p>
            </div>
        </header>

        <x-space.nav active="analytics" />
        <x-space.tab-bar active="analytics" />

        <div class="space-body">

            {{-- ===== Angka utama ===== --}}
            <section class="analytics-section">
                <div class="analytics-summary-grid">
                    <div class="analytics-stat-card">
                        <span class="analytics-stat-value">{{ $analytics['total_watched'] }}</span>
                        <span class="analytics-stat-label">Film Ditonton</span>
                    </div>
                    <div class="analytics-stat-card">
                        <span class="analytics-stat-value">{{ $analytics['total_diary'] }}</span>
                        <span class="analytics-stat-label">Entri Diary</span>
                    </div>
                    <div class="analytics-stat-card">
                        <span class="analytics-stat-value">{{ $analytics['total_reviews'] }}</span>
                        <span class="analytics-stat-label">Review</span>
                    </div>
                    <div class="analytics-stat-card">
                        <span class="analytics-stat-value">
                            {{ $analytics['avg_rating'] !== null ? $analytics['avg_rating'] . '/10' : '—' }}
                        </span>
                        <span class="analytics-stat-label">Rata-rata Rating</span>
                    </div>
                    <div class="analytics-stat-card">
                        <span class="analytics-stat-value">{{ $analytics['rewatch_count'] }}</span>
                        <span class="analytics-stat-label">Rewatch</span>
                    </div>
                </div>
            </section>

            {{-- ===== Aktivitas Bulanan ===== --}}
            @if (! empty($analytics['monthly_activity']))
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Aktivitas 12 Bulan Terakhir</h2>
                    <p class="analytics-section-desc">Jumlah entri diary per bulan.</p>
                    @php
                        $maxCount = max(array_values($analytics['monthly_activity'])) ?: 1;
                    @endphp
                    <div class="analytics-bar-chart">
                        @foreach ($analytics['monthly_activity'] as $month => $count)
                            @php
                                $heightPct = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0;
                                $label = \Carbon\Carbon::createFromFormat('Y-m', $month)->format('M');
                            @endphp
                            <div class="analytics-bar-col">
                                <span class="analytics-bar-count">{{ $count > 0 ? $count : '' }}</span>
                                <div class="analytics-bar-track">
                                    <div
                                        class="analytics-bar-fill {{ $count > 0 ? 'analytics-bar-fill--active' : '' }}"
                                        style="height: {{ $heightPct }}%"
                                        aria-label="{{ $count }} entri di {{ $label }}"
                                    ></div>
                                </div>
                                <span class="analytics-bar-label">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ===== Genre Favorit ===== --}}
            @if (! empty($analytics['top_genres']))
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Genre Favorit</h2>
                    <p class="analytics-section-desc">Berdasarkan 50 film terakhir yang ditonton.</p>
                    @php $maxGenre = $analytics['top_genres'][0]['count'] ?? 1; @endphp
                    <div class="analytics-genre-list">
                        @foreach ($analytics['top_genres'] as $i => $genre)
                            <div class="analytics-genre-row">
                                <span class="analytics-genre-rank">{{ $i + 1 }}</span>
                                <span class="analytics-genre-name">{{ $genre['name'] }}</span>
                                <div class="analytics-genre-bar-wrap">
                                    <div
                                        class="analytics-genre-bar"
                                        style="width: {{ round(($genre['count'] / $maxGenre) * 100) }}%"
                                    ></div>
                                </div>
                                <span class="analytics-genre-count">{{ $genre['count'] }}x</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ===== Film Paling Sering Ditonton ===== --}}
            @if (! empty($analytics['most_rewatched']))
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Paling Sering Ditonton Ulang</h2>
                    <div class="movie-row">
                        @foreach ($analytics['most_rewatched'] as $movie)
                            <div class="analytics-rewatch-wrap">
                                <x-movie.card :movie="$movie" :badge="$movie['watch_count'] . 'x'" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- ===== Mood Distribution ===== --}}
            @if (! empty($analytics['mood_distribution']))
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Mood Menonton</h2>
                    <p class="analytics-section-desc">Dari entri diary yang mencatat mood.</p>
                    <div class="analytics-mood-grid">
                        @foreach ($analytics['mood_distribution'] as $mood => $count)
                            <div class="analytics-mood-item">
                                <span class="analytics-mood-emoji">{{ $mood }}</span>
                                <span class="analytics-mood-count">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Empty state --}}
            @if (
                $analytics['total_watched'] === 0 &&
                $analytics['total_diary'] === 0 &&
                $analytics['total_reviews'] === 0
            )
                <div class="space-empty">
                    Belum ada data. Mulai catat film yang kamu tonton untuk melihat statistikmu.
                    <a href="{{ route('movies.index') }}" class="space-empty-link">Temukan film</a>.
                </div>
            @endif

        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
