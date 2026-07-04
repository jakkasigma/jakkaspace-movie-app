@extends('layouts.movie')

@section('title', ($movie ? $movie['title'] . ' — Review' : 'Review') . ' — Jakka Space')
@section('body-class', 'movie-page')

@section('body')
    <x-movie.navbar />

    <main class="space-page">
        <div class="space-body">
            <div class="review-page-wrap">
                {{-- Film info --}}
                @if ($movie)
                    <div class="review-page-movie">
                        <a href="{{ route('movies.show', $review->tmdb_id) }}" class="review-page-poster-link">
                            @if ($movie['poster_url'])
                                <img src="{{ $movie['poster_url'] }}" alt="{{ $movie['title'] }}" class="review-page-poster">
                            @else
                                <div class="review-page-poster review-page-poster-placeholder">No Poster</div>
                            @endif
                        </a>
                        <div class="review-page-movie-info">
                            <a href="{{ route('movies.show', $review->tmdb_id) }}" class="review-page-movie-title">
                                {{ $movie['title'] }}
                                @if ($movie['release_year'])
                                    <span class="review-page-movie-year">({{ $movie['release_year'] }})</span>
                                @endif
                            </a>
                            @if ($movie['genres'])
                                <p class="review-page-movie-genres">{{ $movie['genres'] }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Review body --}}
                <article class="review-page-article">
                    <header class="review-page-header">
                        <div class="review-page-author">
                            @if ($review->user->avatar_url)
                                <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="review-page-avatar">
                            @else
                                <div class="review-page-avatar review-page-avatar-placeholder">
                                    {{ strtoupper(substr($review->user->name ?? '?', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                @if ($review->user->username)
                                    <a href="{{ route('profile.show', $review->user->username) }}" class="review-page-author-name">
                                        {{ $review->user->name }}
                                    </a>
                                @else
                                    <span class="review-page-author-name">{{ $review->user->name }}</span>
                                @endif
                                <span class="review-page-date">{{ $review->created_at->locale('id')->diffForHumans() }}</span>
                            </div>
                        </div>

                        @if ($review->rating)
                            <div class="review-page-rating">
                                <span class="star-icon">★</span> {{ $review->rating }}/10
                            </div>
                        @endif
                    </header>

                    @if ($review->has_spoiler)
                        <p class="review-page-spoiler-warning">⚠ Review ini mengandung spoiler.</p>
                    @endif

                    @if ($review->body)
                        <div class="review-page-body">{{ $review->body }}</div>
                    @endif

                    <footer class="review-page-footer">
                        {{-- Like --}}
                        @auth
                            @if ($isLiked)
                                <form method="POST" action="{{ route('reviews.like.destroy', $review) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="review-like-btn review-like-active">
                                        ♥ {{ $review->likes_count }}
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('reviews.like.store', $review) }}">
                                    @csrf
                                    <button type="submit" class="review-like-btn">
                                        ♡ {{ $review->likes_count }}
                                    </button>
                                </form>
                            @endif
                        @else
                            <span class="review-like-count">♡ {{ $review->likes_count }}</span>
                        @endauth

                        @if ($isOwner)
                            <form method="POST" action="{{ route('review.destroy', $review) }}">
                                @csrf @method('DELETE')
                                <button
                                    type="submit"
                                    class="review-page-delete"
                                    onclick="return confirm('Hapus review ini?')"
                                >Hapus</button>
                            </form>
                        @endif
                    </footer>
                </article>

                {{-- Comments --}}
                <section class="review-comments-section">
                    <h2 class="review-comments-title">Komentar ({{ $review->comments->count() }})</h2>

                    @if ($review->comments->isNotEmpty())
                        <div class="review-comments-list">
                            @foreach ($review->comments as $comment)
                                <article class="review-comment">
                                    <div class="review-comment-header">
                                        @if ($comment->user->avatar_url)
                                            <img src="{{ $comment->user->avatar_url }}" alt="{{ $comment->user->name }}" class="review-comment-avatar">
                                        @else
                                            <div class="review-comment-avatar review-comment-avatar-placeholder">
                                                {{ strtoupper(substr($comment->user->name ?? '?', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="review-comment-meta">
                                            @if ($comment->user->username)
                                                <a href="{{ route('profile.show', $comment->user->username) }}" class="review-comment-author">
                                                    {{ $comment->user->name }}
                                                </a>
                                            @else
                                                <span class="review-comment-author">{{ $comment->user->name }}</span>
                                            @endif
                                            <span class="review-comment-date">{{ $comment->created_at->diffForHumans() }}</span>
                                        </div>

                                        @auth
                                            @if (auth()->id() === $comment->user_id)
                                                <form method="POST" action="{{ route('reviews.comments.destroy', [$review, $comment]) }}" class="review-comment-delete-form">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="review-comment-delete">✕</button>
                                                </form>
                                            @endif
                                        @endauth
                                    </div>
                                    <p class="review-comment-body">{{ $comment->body }}</p>
                                </article>
                            @endforeach
                        </div>
                    @endif

                    @auth
                        <form method="POST" action="{{ route('reviews.comments.store', $review) }}" class="review-comment-form">
                            @csrf
                            <textarea
                                name="body"
                                class="form-textarea review-comment-textarea"
                                placeholder="Tulis komentarmu..."
                                rows="3"
                                maxlength="1000"
                                required
                            ></textarea>
                            @error('body')
                                <p class="form-error">{{ $message }}</p>
                            @enderror
                            <div class="review-comment-form-footer">
                                <button type="submit" class="form-submit">Kirim</button>
                            </div>
                        </form>
                    @else
                        <p class="review-comments-login">
                            <a href="{{ route('login') }}" class="space-empty-link">Masuk</a> untuk menulis komentar.
                        </p>
                    @endauth
                </section>
            </div>
        </div>
    </main>

    <footer id="footer">
        <div>&copy; 2026 JAKKA SPACE</div>
        <div id="clock">YOGYAKARTA - 00:00</div>
        <div>STAY CURIOUS / STAY WATCHING</div>
    </footer>
@endsection
