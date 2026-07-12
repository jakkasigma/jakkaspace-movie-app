@extends('layouts.movie')

@section('title', ($profile->name ?? $profile->username) . ' — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="profile-page">

        {{-- Header --}}
        <header class="profile-page-header">
            <div class="profile-page-header-inner">

                {{-- Avatar --}}
                <x-user-avatar :user="$profile" class="profile-avatar-lg" placeholder-class="profile-avatar-lg profile-avatar-placeholder" />

                {{-- Identity + stats + actions --}}
                <div class="profile-identity-wrap">
                    <div class="profile-identity">
                        <h1 class="profile-display-name">
                            {{ $profile->name }}
                            @if ($profile->isPlus())
                                <span class="plus-indicator">Plus</span>
                            @endif
                        </h1>
                        @if ($profile->username)
                            <p class="profile-handle">{{ '@' . $profile->username }}</p>
                        @endif
                        @if ($profile->bio)
                            <p class="profile-bio-text">{{ $profile->bio }}</p>
                        @endif
                    </div>

                    <div class="profile-stats-row">
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $stats['total_watched'] }}</span>
                            <span class="profile-stat-label">Ditonton</span>
                        </div>
                        <div class="profile-stat">
                            <span class="profile-stat-value">{{ $stats['total_reviews'] }}</span>
                            <span class="profile-stat-label">Review</span>
                        </div>
                        @if ($profile->username)
                            <a href="{{ route('profile.followers', $profile->username) }}" class="profile-stat profile-stat-link">
                                <span class="profile-stat-value">{{ $stats['total_followers'] }}</span>
                                <span class="profile-stat-label">Followers</span>
                            </a>
                            <a href="{{ route('profile.following', $profile->username) }}" class="profile-stat profile-stat-link">
                                <span class="profile-stat-value">{{ $stats['total_following'] }}</span>
                                <span class="profile-stat-label">Following</span>
                            </a>
                        @endif
                    </div>

                    <div class="profile-actions">
                        @if ($isSelf)
                            <a href="{{ route('profile.edit') }}" class="profile-action-btn">Edit Profil</a>
                        @else
                            @auth
                                @if ($isFollowing)
                                    <form method="POST" action="{{ route('users.unfollow', $profile) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="profile-action-btn profile-action-following">Following</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('users.follow', $profile) }}">
                                        @csrf
                                        <button type="submit" class="profile-action-btn profile-action-follow">Follow</button>
                                    </form>
                                @endif
                                <form method="POST" action="{{ route('inbox.direct', $profile) }}">
                                    @csrf
                                    <button type="submit" class="profile-action-btn profile-action-msg" title="Kirim Pesan">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                                        </svg>
                                        Pesan
                                    </button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="profile-action-btn">Follow</a>
                            @endauth
                        @endif
                    </div>
                </div>

            </div>
        </header>

        {{-- Tab bar --}}
        <nav class="profile-tabs" aria-label="Tab profil">
            <a href="{{ route('profile.show', $profile->username) }}?tab=pinned"
               class="profile-tab-item {{ $activeTab === 'pinned' ? 'active' : '' }}"
            >
                <svg class="profile-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                </svg>
                <span>Film Pilihan</span>
            </a>
            <a href="{{ route('profile.show', $profile->username) }}?tab=reviews"
               class="profile-tab-item {{ $activeTab === 'reviews' ? 'active' : '' }}"
            >
                <svg class="profile-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
                <span>Reviews</span>
            </a>
            <a href="{{ route('profile.show', $profile->username) }}?tab=lists"
               class="profile-tab-item {{ $activeTab === 'lists' ? 'active' : '' }}"
            >
                <svg class="profile-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <line x1="8" y1="6" x2="21" y2="6"/>
                    <line x1="8" y1="12" x2="21" y2="12"/>
                    <line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/>
                    <line x1="3" y1="12" x2="3.01" y2="12"/>
                    <line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
                <span>Lists</span>
            </a>
            <a href="{{ route('profile.show', $profile->username) }}?tab=favorites"
               class="profile-tab-item {{ $activeTab === 'favorites' ? 'active' : '' }}"
            >
                <svg class="profile-tab-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                </svg>
                <span>Favorit</span>
            </a>
        </nav>

        {{-- Tab content --}}
        <div class="profile-tab-content">

            {{-- Film Pilihan --}}
            @if ($activeTab === 'pinned')
                @if (empty($tabData))
                    <div class="profile-empty">
                        @if ($isSelf)
                            Belum ada film pilihan. Buka halaman film dan klik <strong>📌 Sematkan</strong> untuk memajang film di profilmu.
                        @else
                            Belum ada film pilihan.
                        @endif
                    </div>
                @else
                    <div class="profile-grid">
                        @foreach ($tabData as $movie)
                            <div class="profile-grid-item">
                                <a href="{{ route('movies.show', $movie['id']) }}" class="profile-grid-link" aria-label="{{ $movie['title'] }}">
                                    @if ($movie['poster_url'] ?? null)
                                        <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="profile-grid-poster" loading="lazy">
                                    @else
                                        <div class="profile-grid-poster profile-grid-no-poster">No Poster</div>
                                    @endif
                                    <div class="profile-grid-overlay">
                                        <p class="profile-grid-title">{{ $movie['title'] }}</p>
                                        @if ($movie['rating'] ?? null)
                                            <span class="profile-grid-rating">★ {{ $movie['rating'] }}</span>
                                        @endif
                                    </div>
                                </a>
                                @if ($isSelf)
                                    <form method="POST" action="{{ route('movies.pin.destroy', $movie['id']) }}" class="profile-grid-remove-form">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="profile-grid-remove-btn" title="Hapus dari profil">✕</button>
                                    </form>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

            {{-- Reviews --}}
            @elseif ($activeTab === 'reviews')
                @if (empty($tabData))
                    <div class="profile-empty">Belum ada review.</div>
                @else
                    <div class="profile-grid">
                        @foreach ($tabData as $movie)
                            <div class="profile-grid-item">
                                <a href="{{ route('reviews.show', $movie['review_id']) }}" class="profile-grid-link" aria-label="Review {{ $movie['title'] }}">
                                    @if ($movie['poster_url'] ?? null)
                                        <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="profile-grid-poster" loading="lazy">
                                    @else
                                        <div class="profile-grid-poster profile-grid-no-poster">No Poster</div>
                                    @endif
                                    <div class="profile-grid-overlay">
                                        <p class="profile-grid-title">{{ $movie['title'] }}</p>
                                        @if ($movie['review_rating'] ?? null)
                                            <span class="profile-grid-rating">★ {{ $movie['review_rating'] }}/5</span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

            {{-- Lists --}}
            @elseif ($activeTab === 'lists')
                @if ($tabData->isEmpty())
                    <div class="profile-empty">Belum ada list publik.</div>
                @else
                    <div class="lists-grid profile-lists-grid">
                        @foreach ($tabData as $list)
                            <article class="list-card">
                                @if ($list->cover_photo)
                                    <div style="margin-bottom:8px;border-radius:8px;overflow:hidden;max-height:120px;">
                                        <img src="{{ asset('storage/'.$list->cover_photo) }}" alt="{{ $list->name }}" style="width:100%;height:120px;object-fit:cover;display:block;">
                                    </div>
                                @endif
                                <div class="list-card-header">
                                    <a href="{{ route('lists.show', $list) }}" class="list-card-name">{{ $list->name }}</a>
                                    <span class="list-card-count">{{ $list->list_movies_count }} film</span>
                                </div>
                                @if ($list->description)
                                    <p class="list-card-desc">{{ $list->description }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif

            {{-- Favorit --}}
            @elseif ($activeTab === 'favorites')
                @if (empty($tabData))
                    <div class="profile-empty">Belum ada film favorit.</div>
                @else
                    <div class="profile-grid">
                        @foreach ($tabData as $movie)
                            <div class="profile-grid-item">
                                <a href="{{ route('movies.show', $movie['id']) }}" class="profile-grid-link" aria-label="{{ $movie['title'] }}">
                                    @if ($movie['poster_url'] ?? null)
                                        <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="profile-grid-poster" loading="lazy">
                                    @else
                                        <div class="profile-grid-poster profile-grid-no-poster">No Poster</div>
                                    @endif
                                    <div class="profile-grid-overlay">
                                        <p class="profile-grid-title">{{ $movie['title'] }}</p>
                                        @if ($movie['rating'] ?? null)
                                            <span class="profile-grid-rating">★ {{ $movie['rating'] }}</span>
                                        @endif
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif

        </div>
    </main>

    @endsection
