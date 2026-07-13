@extends('layouts.movie')

@section('title', $movie ? $movie['title'] . ' — Jakka Space' : 'Detail Film — Jakka Space')
@section('description', $movie ? $movie['overview'] : 'Detail film Jakka Space.')
@section('body-class', 'detail-page anim-started intro-complete')

@section('body')
    <div id="movie-detail" class="detail-overlay active">
        <a href="{{ route('movies.index', [], false) }}" id="detail-back" class="detail-back" aria-label="Kembali">KEMBALI</a>

        @if (! $movie)
            <div class="detail-container">
                <div class="detail-empty-state">{{ $errorMessage }}</div>
            </div>
        @else
            @php $movieId = $movie['id']; @endphp

            <div class="detail-container">
                @if ($movie['backdrop_url'])
                    <div class="detail-backdrop" style="background-image: url('{{ $movie['backdrop_url'] }}')"></div>
                @endif

                <div class="detail-body">
                    {{-- Poster --}}
                    <div class="detail-poster-wrap">
                        @if ($movie['poster_url'])
                            <img id="detail-poster" class="detail-poster" src="{{ $movie['poster_url'] }}" alt="Poster {{ $movie['title'] }}">
                        @else
                            <div class="detail-poster detail-poster-placeholder">No Poster</div>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="detail-info">
                        <h1 class="detail-title">
                            {{ $movie['title'] }}
                            @if ($movie['release_year'])
                                <span class="detail-title-year">({{ $movie['release_year'] }})</span>
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

                        <div class="detail-ratings-row">
                            <div class="detail-star-rating">
                                <span class="star-icon" aria-hidden="true">&#9733;</span>
                                <span class="score-text">{{ $movie['rating'] }}</span>
                            </div>
                            <span class="score-label">Rating TMDB</span>
                            @if ($communityRating && $communityRating->avg_rating)
                                <span class="detail-ratings-divider" aria-hidden="true"></span>
                                <div class="detail-community-rating">
                                    <span class="star-icon" aria-hidden="true">&#9733;</span>
                                    <span class="score-text">{{ $communityRating->avg_rating }}</span>
                                    <span class="score-label">Komunitas ({{ $communityRating->review_count }} review)</span>
                                </div>
                            @endif
                        </div>

                        {{-- Primary actions: trailer + share --}}
                        <div class="detail-actions">
                            @if ($movie['trailer_url'])
                                <a class="btn-trailer" href="{{ $movie['trailer_url'] }}" target="_blank" rel="noopener noreferrer">
                                    ▶ Trailer
                                </a>
                            @endif
                            <button class="btn-share-detail" type="button" onclick="openShareModal()">Bagikan</button>
                            <button class="btn-share-detail" type="button"
                                data-share-story
                                data-story-backdrop="{{ $movie['backdrop_url'] ?? '' }}"
                                data-story-poster="{{ $movie['poster_url'] ?? '' }}"
                                data-story-title="{{ $movie['title'] ?? '' }}"
                                data-story-year="{{ $movie['release_year'] ?? '' }}"
                                data-story-genres="{{ $movie['genres'] ?? '' }}"
                                data-story-rating="{{ $movie['rating'] ?? '' }}"
                                data-story-director="{{ $movie['director'] ?? '' }}">Story</button>
                        </div>

                        {{-- User activity actions --}}
                        <div class="user-actions-wrap">
                            @auth
                                <div class="user-actions">
                                    {{-- Watch --}}
                                    @if ($userActivity['watch_status'] === 'watched')
                                        <form method="POST" action="{{ route('movies.watch.destroy', $movieId) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="user-action-btn user-action-active" title="Klik untuk batalkan">
                                                <span class="user-action-icon">✓</span> Ditonton
                                            </button>
                                        </form>
                                    @elseif ($userActivity['watch_status'] === 'watching')
                                        <form method="POST" action="{{ route('movies.watch.store', $movieId) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="watched">
                                            <button type="submit" class="user-action-btn user-action-watching">
                                                <span class="user-action-icon">▶</span> Sedang Nonton
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('movies.watch.store', $movieId) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="watched">
                                            <button type="submit" class="user-action-btn">
                                                <span class="user-action-icon">+</span> Ditonton
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Watching (jika belum) --}}
                                    @if (! in_array($userActivity['watch_status'], ['watching', 'watched']))
                                        <form method="POST" action="{{ route('movies.watch.store', $movieId) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="watching">
                                            <button type="submit" class="user-action-btn user-action-sm">Sedang Nonton</button>
                                        </form>
                                    @endif

                                    {{-- Watchlist --}}
                                    @if ($userActivity['is_on_watchlist'])
                                        <form method="POST" action="{{ route('movies.watchlist.destroy', $movieId) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="user-action-btn user-action-active" title="Hapus dari watchlist">
                                                <span class="user-action-icon">🔖</span> Watchlist
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('movies.watchlist.store', $movieId) }}">
                                            @csrf
                                            <button type="submit" class="user-action-btn">
                                                <span class="user-action-icon">🔖</span> Watchlist
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Favorite --}}
                                    @if ($userActivity['is_favorited'])
                                        <form method="POST" action="{{ route('movies.favorite.destroy', $movieId) }}">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="user-action-btn user-action-active" title="Hapus dari favorit">
                                                <span class="user-action-icon">♥</span> Favorit
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('movies.favorite.store', $movieId) }}">
                                            @csrf
                                            <button type="submit" class="user-action-btn">
                                                <span class="user-action-icon">♡</span> Favorit
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Pin ke profil — hanya muncul kalau sudah ditonton --}}
                                    @if ($userActivity['watch_status'] === 'watched')
                                        @if ($isPinned)
                                            <form method="POST" action="{{ route('movies.pin.destroy', $movieId) }}">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="user-action-btn user-action-active" title="Hapus dari profil">
                                                    <span class="user-action-icon">📌</span> Di Profil
                                                </button>
                                            </form>
                                        @elseif ($pinnedCount < 6)
                                            <form method="POST" action="{{ route('movies.pin.store', $movieId) }}">
                                                @csrf
                                                <button type="submit" class="user-action-btn">
                                                    <span class="user-action-icon">📌</span> Sematkan
                                                </button>
                                            </form>
                                        @else
                                            <button type="button" class="user-action-btn user-action-disabled" disabled title="Profil penuh (6/6)">
                                                <span class="user-action-icon">📌</span> Penuh (6/6)
                                            </button>
                                        @endif
                                    @endif
                                </div>

                                {{-- Tambah ke List --}}
                                @if ($userLists->isNotEmpty())
                                    <div class="user-lists-section">
                                        <details class="detail-form-section">
                                            <summary class="detail-form-toggle">
                                                <span>📋</span> Tambah ke List
                                            </summary>
                                            <div class="user-lists-items">
                                                @foreach ($userLists as $list)
                                                    <div class="user-list-item">
                                                        <span class="user-list-name">{{ $list->name }}</span>
                                                        @if ($movieInLists[$list->id] ?? false)
                                                            <form method="POST" action="{{ route('lists.movies.destroy', [$list, $movieId]) }}">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="user-list-btn user-list-btn-remove">Hapus</button>
                                                            </form>
                                                        @else
                                                            <form method="POST" action="{{ route('lists.movies.store', [$list, $movieId]) }}">
                                                                @csrf
                                                                <button type="submit" class="user-list-btn user-list-btn-add">+ Tambah</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                @endforeach
                                                <a href="{{ route('your-space.lists.create') }}" class="user-list-create-link">+ Buat list baru</a>
                                            </div>
                                        </details>
                                    </div>
                                @else
                                    <div class="user-lists-section">
                                        <a href="{{ route('your-space.lists.create') }}" class="user-action-btn">
                                            <span class="user-action-icon">📋</span> Buat List
                                        </a>
                                    </div>
                                @endif

                            @endauth
                        </div>
                    </div>
                </div>

                <nav class="detail-tab-bar" aria-label="Navigasi konten film">
                    <a href="{{ route('movies.show', $movieId) }}?tab=info"
                       class="detail-tab-link @if($tab === 'info') tab-active @endif"
                       @if($tab === 'info') aria-current="page" @endif>
                        Info
                    </a>
                    <a href="{{ route('movies.show', $movieId) }}?tab=diskusi"
                       class="detail-tab-link @if($tab === 'diskusi') tab-active @endif"
                       @if($tab === 'diskusi') aria-current="page" @endif>
                        Diskusi{{ $reviewCount > 0 ? ' (' . $reviewCount . ')' : '' }}
                    </a>
                    <a href="{{ route('movies.show', $movieId) }}?tab=serupa"
                       class="detail-tab-link @if($tab === 'serupa') tab-active @endif"
                       @if($tab === 'serupa') aria-current="page" @endif>
                        Serupa
                    </a>
                </nav>

                <div class="detail-tab-content">
                    @if ($tab === 'info')
                        @include('movies.partials.tab-info')
                    @elseif ($tab === 'diskusi')
                        @include('movies.partials.tab-diskusi')
                    @elseif ($tab === 'serupa')
                        @include('movies.partials.tab-serupa')
                    @endif
                </div>{{-- .detail-tab-content --}}

                {{-- Story modal --}}
                <div class="story-modal" data-story-modal hidden>
                    <button class="story-modal-backdrop" type="button" data-story-close aria-label="Tutup template story"></button>
                    <section class="story-modal-panel" role="dialog" aria-modal="true" aria-labelledby="story-modal-title">
                        <div class="story-preview-frame">
                            <canvas class="story-canvas" width="1080" height="1920" data-story-canvas></canvas>
                        </div>
                        <div class="story-modal-side">
                            <div>
                                <p class="story-modal-kicker">JAKKA SPACE</p>
                                <h2 id="story-modal-title" class="story-modal-title">Film Diary Story</h2>
                                <p class="story-modal-copy">Template dengan poster, rating, genre, sutradara, dan watermark Film Diary by Jakka Space.</p>
                            </div>
                            <div class="story-modal-actions">
                                <button class="story-action-button story-action-primary" type="button" data-story-native>Story Instagram</button>
                                <button class="story-action-button" type="button" data-story-download>Unduh PNG</button>
                                <button class="story-action-button story-action-ghost" type="button" data-story-close>Tutup</button>
                            </div>
                            <p class="story-status" data-story-status aria-live="polite"></p>
                        </div>
                    </section>
                </div>
            </div>
        @endif
    </div>

    @auth
        <div id="share-modal-container"></div>
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.openShareModal = function() {
                const container = document.getElementById('share-modal-container');
                if (container.innerHTML === '') {
                    fetch('{{ route('movies.share', $movie['id']) }}')
                        .then(r => r.text())
                        .then(html => {
                            container.innerHTML = html;
                            openShareModal();
                        });
                } else {
                    openShareModal();
                }
            };
        });
        </script>
    @endauth
@endsection
