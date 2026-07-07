@extends('layouts.movie')

@section('title', 'Statistik — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
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

            {{-- ===== Plus-Only Analytics ===== --}}
            @if ($premiumAnalytics)
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Plus Analytics</h2>
                    <div class="analytics-summary-grid">
                        <div class="analytics-stat-card">
                            <span class="analytics-stat-value">{{ $premiumAnalytics['streak'] }} hari</span>
                            <span class="analytics-stat-label">Streak Saat Ini</span>
                        </div>
                        <div class="analytics-stat-card">
                            <span class="analytics-stat-value">~{{ $premiumAnalytics['estimated_hours'] }} jam</span>
                            <span class="analytics-stat-label">Estimasi Nonton</span>
                        </div>
                        <div class="analytics-stat-card">
                            <span class="analytics-stat-value">{{ $premiumAnalytics['favorite_director'] ?? '—' }}</span>
                            <span class="analytics-stat-label">Sutradara Favorit</span>
                        </div>
                    </div>
                </section>

                @if (! empty($premiumAnalytics['rating_distribution']))
                    <section class="analytics-section">
                        <h2 class="analytics-section-title">Distribusi Rating</h2>
                        @php $maxRating = max($premiumAnalytics['rating_distribution']) ?: 1; @endphp
                        <div class="analytics-bar-chart">
                            @foreach ($premiumAnalytics['rating_distribution'] as $rating => $count)
                                @php $h = $maxRating > 0 ? round(($count / $maxRating) * 100) : 0; @endphp
                                <div class="analytics-bar-col">
                                    <span class="analytics-bar-count">{{ $count > 0 ? $count : '' }}</span>
                                    <div class="analytics-bar-track">
                                        <div class="analytics-bar-fill {{ $count > 0 ? 'analytics-bar-fill--active' : '' }}"
                                             style="height: {{ $h }}%"></div>
                                    </div>
                                    <span class="analytics-bar-label">{{ $rating }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (! empty($premiumAnalytics['rating_per_year']))
                    <section class="analytics-section">
                        <h2 class="analytics-section-title">Rating per Tahun</h2>
                        <div class="analytics-genre-list">
                            @foreach ($premiumAnalytics['rating_per_year'] as $year => $avg)
                                <div class="analytics-genre-row">
                                    <span class="analytics-genre-name">{{ $year }}</span>
                                    <div class="analytics-genre-bar-wrap">
                                        <div class="analytics-genre-bar" style="width: {{ ($avg / 5) * 100 }}%"></div>
                                    </div>
                                    <span class="analytics-genre-count">{{ $avg }}</span>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- Export Data --}}
                <section class="analytics-section">
                    <h2 class="analytics-section-title">Export Data</h2>
                    <p class="analytics-section-desc">Download data pribadimu dalam format CSV.</p>
                    <div class="export-buttons">
                        <a href="{{ route('export', 'diary') }}" class="export-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                            Diary
                        </a>
                        <a href="{{ route('export', 'reviews') }}" class="export-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                            Reviews
                        </a>
                        <a href="{{ route('export', 'history') }}" class="export-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            History
                        </a>
                        <a href="{{ route('export', 'all') }}" class="export-btn export-btn-primary">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                            Download All (ZIP)
                        </a>
                    </div>
                </section>
            @else
                <section class="analytics-section">
                    <div class="space-empty" style="border: 1px dashed rgba(255,255,255,0.15); border-radius: 12px; padding: 32px;">
                        <p style="margin-bottom: 12px;">📊 Ingin lihat rating distribution, streak, estimasi jam nonton, sutradara favorit?</p>
                        <p style="margin-bottom: 16px; color: rgba(255,255,255,0.4); font-size: 0.85rem;">Fitur ini hanya untuk pengguna Plus.</p>
                        <a href="{{ route('plus') }}" class="profile-action-btn" style="display: inline-block;">Upgrade ke Plus</a>
                    </div>
                </section>
            @endif

            {{-- Empty state --}}
            @if (
                $analytics['total_watched'] === 0 &&
                $analytics['total_diary'] === 0 &&
                $analytics['total_reviews'] === 0
            )
                <x-space.empty icon="clock" message="Belum ada data. Mulai catat film yang kamu tonton untuk melihat statistikmu." :link="route('movies.index')" linkText="Temukan film" />
            @endif

        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
