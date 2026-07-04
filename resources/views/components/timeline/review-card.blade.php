@props(['review'])

<article class="tl-review-card">
    <div class="tl-review-user">
        @if ($review->user?->avatar_url)
            <img src="{{ $review->user->avatar_url }}" alt="{{ $review->user->name }}" class="tl-review-avatar">
        @else
            <div class="tl-review-avatar tl-review-avatar-placeholder">
                {{ strtoupper(substr($review->user?->name ?? '?', 0, 1)) }}
            </div>
        @endif
        <div class="tl-review-user-info">
            @if ($review->user?->username)
                <a href="{{ route('profile.show', $review->user->username) }}" class="tl-review-user-name">
                    {{ $review->user->name }}
                </a>
            @else
                <span class="tl-review-user-name">{{ $review->user?->name ?? 'Pengguna' }}</span>
            @endif
            @if ($review->rating)
                <span class="tl-review-rating">★ {{ $review->rating }}/10</span>
            @endif
        </div>
        <div class="tl-review-likes">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            {{ $review->likes_count }}
        </div>
    </div>

    @if ($review->body)
        <a href="{{ route('reviews.show', $review) }}" class="tl-review-body">
            <p class="tl-review-text">
                @if ($review->has_spoiler)
                    <span class="tl-review-spoiler-badge">Spoiler</span>
                @endif
                {{ Str::limit($review->body, 180) }}
            </p>
        </a>
    @endif

    <div class="tl-review-footer">
        <a href="{{ route('movies.show', $review->tmdb_id) }}" class="tl-review-movie-link">
            Film #{{ $review->tmdb_id }}
        </a>
        <span class="tl-review-time">{{ $review->created_at->diffForHumans() }}</span>
    </div>
</article>
