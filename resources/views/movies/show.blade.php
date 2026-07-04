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
                            <button class="btn-share-detail" type="button" data-share-story
                                data-story-title="{{ $movie['title'] }}"
                                data-story-year="{{ $movie['release_year'] ?? '' }}"
                                data-story-rating="{{ $movie['rating'] }}"
                                data-story-genres="{{ $movie['genres'] }}"
                                data-story-director="{{ $movie['director'] ?? '' }}"
                                data-story-overview="{{ $movie['overview'] }}"
                                data-story-poster="{{ $movie['story_poster_url'] ?? $movie['poster_url'] ?? '' }}"
                                data-story-backdrop="{{ $movie['story_backdrop_url'] ?? $movie['backdrop_url'] ?? '' }}"
                            >Bagikan</button>
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
                        Diskusi@if ($reviewCount > 0) ({{ $reviewCount }}) @endif
                    </a>
                    <a href="{{ route('movies.show', $movieId) }}?tab=serupa"
                       class="detail-tab-link @if($tab === 'serupa') tab-active @endif"
                       @if($tab === 'serupa') aria-current="page" @endif>
                        Serupa
                    </a>
                </nav>

                <div class="detail-tab-content">
                    @if ($tab === 'info')
                        <div class="detail-extra detail-tab-info">
                            {{-- Tagline + Sinopsis --}}
                            @if ($movie['tagline'])
                                <p class="detail-tagline">"{{ $movie['tagline'] }}"</p>
                            @endif
                            <h3 class="detail-section-label">Sinopsis</h3>
                            <p class="detail-synopsis">{{ $movie['overview'] }}</p>

                            {{-- Pembuat --}}
                            <section class="detail-crew-section" aria-labelledby="detail-crew-title">
                                <h3 id="detail-crew-title" class="detail-section-label">Pembuat</h3>
                                <div class="detail-crew-grid">
                                    <div class="detail-crew">
                                        <p class="crew-name">{{ $movie['director'] ?? 'Belum tersedia' }}</p>
                                        <p class="crew-role">Sutradara</p>
                                    </div>
                                    @if ($movie['writers'])
                                        <div class="detail-crew">
                                            <p class="crew-name">{{ $movie['writers'] }}</p>
                                            <p class="crew-role">Penulis</p>
                                        </div>
                                    @endif
                                </div>
                            </section>

                            {{-- Info Film (facts) --}}
                            @if (! empty($movie['facts']))
                                <section class="detail-facts-section" aria-labelledby="detail-facts-title">
                                    <h3 id="detail-facts-title" class="detail-section-label">Info Film</h3>
                                    <div class="detail-facts-grid">
                                        @foreach ($movie['facts'] as $fact)
                                            <article class="detail-fact">
                                                <span class="fact-label">{{ $fact['label'] }}</span>
                                                <strong class="fact-value">{{ $fact['value'] }}</strong>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            {{-- Pemeran (cast) --}}
                            @if (! empty($movie['cast']))
                                <section class="detail-cast-section" aria-labelledby="detail-cast-title">
                                    <h3 id="detail-cast-title" class="detail-section-label">Pemeran</h3>
                                    <div class="detail-cast-row">
                                        @foreach ($movie['cast'] as $castMember)
                                            <article class="cast-card">
                                                @if ($castMember['profile_url'])
                                                    <img class="cast-photo" src="{{ $castMember['profile_url'] }}" alt="Foto {{ $castMember['name'] }}" loading="lazy">
                                                @else
                                                    <div class="cast-photo cast-photo-placeholder">No Photo</div>
                                                @endif
                                                <div class="cast-info">
                                                    <p class="cast-name">{{ $castMember['name'] }}</p>
                                                    <p class="cast-character">{{ $castMember['character'] ?: 'Peran belum tersedia' }}</p>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </section>
                            @endif

                            {{-- Forms for auth users, login prompt for guests --}}
                            @auth
                                <div class="user-forms">
                                    <details class="detail-form-section">
                                        <summary class="detail-form-toggle">
                                            <span>📖</span> Tulis Diary
                                        </summary>
                                        <form method="POST" action="{{ route('movies.diary.store', $movieId) }}" class="detail-form">
                                            @csrf
                                            <div class="form-row-2col">
                                                <div class="form-row">
                                                    <label class="form-label" for="watched_at">Tanggal Nonton</label>
                                                    <input id="watched_at" type="date" name="watched_at" class="form-input"
                                                        value="{{ date('Y-m-d') }}" max="{{ date('Y-m-d') }}" required>
                                                </div>
                                                <div class="form-row">
                                                    <label class="form-label" for="mood">Mood</label>
                                                    <select id="mood" name="mood" class="form-select">
                                                        <option value="">Pilih mood...</option>
                                                        <option value="happy">😊 Happy</option>
                                                        <option value="thrilled">🤩 Thrilled</option>
                                                        <option value="moved">🥹 Moved</option>
                                                        <option value="sad">😢 Sad</option>
                                                        <option value="scared">😨 Scared</option>
                                                        <option value="inspired">✨ Inspired</option>
                                                        <option value="nostalgic">🌅 Nostalgic</option>
                                                        <option value="bored">😑 Bored</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="form-row">
                                                <label class="form-label" for="notes">Catatan</label>
                                                <textarea id="notes" name="notes" class="form-textarea" placeholder="Cerita singkat soal pengalamanmu menonton film ini..." rows="3"></textarea>
                                            </div>
                                            <div class="form-footer">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="is_rewatch" value="1" class="form-checkbox">
                                                    Ini rewatch
                                                </label>
                                                <button type="submit" class="form-submit">Simpan</button>
                                            </div>
                                        </form>
                                    </details>

                                    <details id="review-form" class="detail-form-section">
                                        <summary class="detail-form-toggle">
                                            <span>✏️</span> Tulis Review
                                        </summary>
                                        <form method="POST" action="{{ route('movies.review.store', $movieId) }}" class="detail-form">
                                            @csrf
                                            <div class="form-row">
                                                <label class="form-label" for="rating">Rating (1–10)</label>
                                                <input id="rating" type="number" name="rating" class="form-input form-input-sm"
                                                    min="1" max="10" placeholder="8">
                                            </div>
                                            <div class="form-row">
                                                <label class="form-label" for="body">Reviewmu</label>
                                                <textarea id="body" name="body" class="form-textarea" placeholder="Tulis pendapatmu tentang film ini..." rows="4"></textarea>
                                            </div>
                                            <div class="form-footer">
                                                <label class="form-check-label">
                                                    <input type="checkbox" name="has_spoiler" value="1" class="form-checkbox">
                                                    Mengandung spoiler
                                                </label>
                                                <button type="submit" class="form-submit">Simpan</button>
                                            </div>
                                        </form>
                                    </details>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="user-action-login-prompt">
                                    Masuk untuk menyimpan & mencatat film ini →
                                </a>
                            @endauth
                        </div>

                    @elseif ($tab === 'diskusi')
                        <div class="detail-extra detail-tab-diskusi">
                            {{-- Header: filter + write button --}}
                            <div class="diskusi-header" style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:20px;">
                                <div class="diskusi-filters">
                                    <a href="{{ route('movies.show', $movieId) }}?tab=diskusi&sort=recent"
                                       class="{{ $sort === 'recent' ? 'filter-active' : '' }}">Terbaru</a>
                                    <a href="{{ route('movies.show', $movieId) }}?tab=diskusi&sort=popular"
                                       class="{{ $sort === 'popular' ? 'filter-active' : '' }}">Terpopuler</a>
                                </div>
                                @auth
                                    <a href="{{ route('movies.show', $movieId) }}?tab=info#review-form" class="user-action-btn">
                                        ✏️ Tulis Review
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="user-action-login-prompt">
                                        Masuk untuk menulis review →
                                    </a>
                                @endauth
                            </div>

                            {{-- Review list --}}
                            <div class="detail-reviews-list">
                                @forelse ($communityReviews as $review)
                                    <article class="detail-review-card">
                                        <div class="detail-review-header">
                                            <div class="detail-review-author">
                                                @if ($review->user?->avatar_url)
                                                    <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="detail-review-avatar">
                                                @else
                                                    <div class="detail-review-avatar detail-review-avatar-placeholder">
                                                        {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    @if ($review->user?->username)
                                                        <a href="{{ route('profile.show', $review->user->username) }}" class="detail-review-name">{{ $review->user->name }}</a>
                                                    @else
                                                        <span class="detail-review-name">{{ $review->user?->name ?? 'Pengguna' }}</span>
                                                    @endif
                                                    <span class="detail-review-date">{{ $review->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                            <div class="detail-review-meta">
                                                @if ($review->rating)
                                                    <span class="detail-review-rating">★ {{ $review->rating }}/10</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if ($review->body)
                                            @if ($review->has_spoiler)
                                                <p class="detail-review-spoiler">⚠ Mengandung spoiler</p>
                                            @endif
                                            <p class="detail-review-body">{{ Str::limit($review->body, 150) }}</p>
                                        @endif
                                        <div class="detail-review-footer">
                                            <span class="detail-review-likes">♡ {{ $review->likes_count }}</span>
                                            <span class="detail-review-comments">💬 {{ $review->comments_count }}</span>
                                            <a href="{{ route('reviews.show', $review) }}" class="detail-review-link">Lihat review penuh →</a>
                                        </div>
                                    </article>
                                @empty
                                    <div class="diskusi-empty">
                                        <p>Belum ada review untuk film ini.</p>
                                        @auth
                                            <p style="margin-top:8px;">
                                                <a href="{{ route('movies.show', $movieId) }}?tab=info#review-form" class="user-action-login-prompt">
                                                    Jadilah yang pertama menulis review →
                                                </a>
                                            </p>
                                        @else
                                            <p style="margin-top:8px;">
                                                <a href="{{ route('login') }}" class="user-action-login-prompt">
                                                    Masuk untuk menulis review →
                                                </a>
                                            </p>
                                        @endauth
                                    </div>
                                @endforelse
                            </div>

                            {{-- Pagination --}}
                            @if ($communityReviews && $communityReviews->hasPages())
                                <div style="margin-top:24px;">
                                    {{ $communityReviews->links() }}
                                </div>
                            @endif
                        </div>

                    @elseif ($tab === 'serupa')
                        <div class="detail-extra detail-tab-serupa">
                            @if (! empty($similarMovies))
                                <section class="detail-similar-section" aria-labelledby="detail-similar-title">
                                    <h3 id="detail-similar-title" class="detail-section-label">Film Serupa</h3>
                                    <div class="movie-row">
                                        @foreach ($similarMovies as $similarMovie)
                                            <x-movie.card :movie="$similarMovie" />
                                        @endforeach
                                    </div>
                                </section>
                            @else
                                <p class="detail-synopsis" style="padding-top:8px;">Tidak ada film serupa ditemukan.</p>
                            @endif

                            @if (! empty($genreRecommendations))
                                <section class="detail-similar-section" aria-labelledby="detail-genre-rec-title">
                                    <h3 id="detail-genre-rec-title" class="detail-section-label">Karena kamu menonton film ini</h3>
                                    <div class="movie-row">
                                        @foreach ($genreRecommendations as $recMovie)
                                            <x-movie.card :movie="$recMovie" />
                                        @endforeach
                                    </div>
                                </section>
                            @endif
                        </div>

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
@endsection
