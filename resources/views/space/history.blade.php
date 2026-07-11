@extends('layouts.movie')

@section('title', 'History — Your Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <a href="{{ route('your-space') }}" class="profile-back-link">← Your Space</a>
        <header class="space-header">
            <div class="space-header-inner">
                <div>
                    <h1 class="space-page-title">HISTORY</h1>
                    <p class="space-page-subtitle">Pusat aktivitas — semua yang kamu lakukan.</p>
                </div>
            </div>
        </header>

        <x-space.nav active="history" />
        <x-space.tab-bar active="history" />

        <div class="space-body">
            @if ($entries->isEmpty())
                <x-space.empty icon="clock" message="Belum ada aktivitas." />
            @else
                @php
                    $grouped = $entries->groupBy(fn ($e) => $e->created_at->format('Y-m'));
                @endphp

                <div class="history-month-list">
                    @foreach ($grouped as $ym => $monthEntries)
                        <div class="history-month-group">
                            <h3 class="history-month-header">
                                {{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->locale('id')->translatedFormat('F Y') }}
                            </h3>
                            <div class="history-month-entries">
                                @foreach ($monthEntries as $entry)
                                    @php
                                        $meta = $entry->metadata ?? [];
                                        $type = $entry->type;
                                        $isMovie = in_array($type, ['watch_status', 'diary', 'review', 'watchlist', 'favorite']);
                                    @endphp

                                    <div class="history-item">
                                        <div class="history-item-left">
                                            @if ($type === 'profile_update')
                                                <x-user-avatar :user="$user" class="history-item-avatar" placeholder-class="history-item-poster history-item-poster-empty" />
                                            @elseif ($isMovie && ! empty($meta['poster_url']))
                                                <img src="{{ $meta['poster_url'] }}" alt="" class="history-item-poster" loading="lazy">
                                            @else
                                                <div class="history-item-poster history-item-poster-empty">
                                                    @if ($type === 'watch_status')
                                                        <span class="history-type-icon">🎬</span>
                                                    @elseif ($type === 'diary')
                                                        <span class="history-type-icon">📝</span>
                                                    @elseif ($type === 'review')
                                                        <span class="history-type-icon">⭐</span>
                                                    @elseif ($type === 'watchlist')
                                                        <span class="history-type-icon">📋</span>
                                                    @elseif ($type === 'favorite')
                                                        <span class="history-type-icon">❤️</span>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="history-item-body">
                                            <div class="history-item-header">
                                                @if ($isMovie && ! empty($meta['tmdb_id']))
                                                    <a href="{{ route('movies.show', $meta['tmdb_id']) }}" class="history-link">
                                                        {{ $meta['movie_title'] ?? 'Film' }}
                                                    </a>
                                                @else
                                                    <span class="history-link">{{ $meta['movie_title'] ?? ($type === 'profile_update' ? 'Profil' : '') }}</span>
                                                @endif
                                                @if ($type === 'watch_status')
                                                    <span class="history-status status-{{ $meta['status'] ?? 'watched' }}">{{ $meta['status'] ?? 'watched' }}</span>
                                                @endif
                                            </div>
                                            <div class="history-item-meta">
                                                <span class="history-description">{{ $entry->description }}</span>
                                                @if ($type === 'review' && ! empty($meta['rating']))
                                                    <span class="history-rating">{{ str_repeat('★', min($meta['rating'], 5)) }}{{ str_repeat('☆', max(0, 5 - min($meta['rating'], 5))) }}</span>
                                                @endif
                                                @if ($type === 'diary' && ! empty($meta['notes']))
                                                    <p class="history-notes">&ldquo;{{ Str::limit($meta['notes'], 100) }}&rdquo;</p>
                                                @endif
                                                @if ($type === 'review' && ! empty($meta['body']))
                                                    <p class="history-notes">&ldquo;{{ Str::limit($meta['body'], 100) }}&rdquo;</p>
                                                @endif
                                                <span class="history-date">{{ $entry->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
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

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
