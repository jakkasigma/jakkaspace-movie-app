@extends('layouts.movie')

@section('title', 'Diary — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">DIARY</h1>
                    <p class="space-page-subtitle">Catatan perjalanan menontonmu.</p>
                </div>
                <div class="space-header-stats">
                    <span class="space-header-stat">{{ $diaryStats['total_entries'] }} entri</span>
                    <span class="space-header-stat-sep">·</span>
                    <span class="space-header-stat">{{ $diaryStats['monthly_avg'] }}/bulan</span>
                </div>
            </div>
        </header>

        <x-space.nav active="diary" />
        <x-space.tab-bar active="diary" />

        <div class="space-body">
            {{-- Filters --}}
            <div class="space-filters">
                <div class="space-filter-group">
                    <label class="space-filter-label">Tahun</label>
                    <select class="space-filter-select" onchange="window.location.href = '{{ route('your-space.diary') }}?year=' + this.value + '&sort={{ $activeSort }}'">
                        <option value="">Semua Tahun</option>
                        @foreach ($yearOptions as $year)
                            <option value="{{ $year }}" {{ (string) $activeYear === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="space-filter-group">
                    <label class="space-filter-label">Urut</label>
                    <select class="space-filter-select" onchange="window.location.href = '{{ route('your-space.diary') }}?year={{ $activeYear ?? '' }}&sort=' + this.value">
                        <option value="newest" {{ $activeSort === 'newest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="oldest" {{ $activeSort === 'oldest' ? 'selected' : '' }}>Terlama</option>
                    </select>
                </div>
            </div>

            @if ($entries->isEmpty())
                <x-space.empty icon="book" message="Belum ada diary." :link="route('movies.index')" linkText="Tonton film dan catat diaries" />
            @else
                <div class="diary-list">
                    @foreach ($entries as $entry)
                        <article class="diary-card">
                            <div class="diary-card-left">
                                @if ($entry->movie_poster_url)
                                    <img src="{{ $entry->movie_poster_url }}" alt="" class="diary-card-poster" loading="lazy">
                                @else
                                    <div class="diary-card-poster diary-card-poster-empty"></div>
                                @endif
                            </div>

                            <div class="diary-card-date">
                                <span class="diary-date-day">{{ $entry->watched_at->format('d') }}</span>
                                <span class="diary-date-month">{{ $entry->watched_at->locale('id')->translatedFormat('M Y') }}</span>
                            </div>

                            <div class="diary-card-body">
                                <div class="diary-card-meta">
                                    <a href="{{ route('movies.show', $entry->tmdb_id) }}" class="diary-movie-link">
                                        {{ $entry->movie_title }}
                                        @if ($entry->movie_release_year)
                                            <span class="diary-movie-year">({{ $entry->movie_release_year }})</span>
                                        @endif
                                    </a>

                                    @if ($entry->user_rating)
                                        <span class="diary-rating-stars">{{ str_repeat('★', min($entry->user_rating, 5)) }}{{ str_repeat('☆', max(0, 5 - min($entry->user_rating, 5))) }}</span>
                                    @endif

                                    @if ($entry->mood)
                                        <span class="diary-mood">{{ $entry->mood }}</span>
                                    @endif

                                    @if ($entry->is_rewatch)
                                        <span class="diary-rewatch">Rewatch</span>
                                    @endif
                                </div>

                                @if ($entry->notes)
                                    <p class="diary-notes">{{ $entry->notes }}</p>
                                @endif

                                <div class="diary-card-actions">
                                    <a href="{{ route('your-space.diary.edit', $entry) }}" class="diary-edit-btn">Edit</a>
                                    <form method="POST" action="{{ route('diary.destroy', $entry) }}" class="diary-delete-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="diary-delete-btn" onclick="return confirm('Hapus diary ini?')">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if ($entries->hasPages())
                    <div class="space-pagination">
                        {{ $entries->links() }}
                    </div>
                @endif
            @endif
        </div>
    </main>

    @endsection
