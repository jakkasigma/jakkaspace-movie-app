@props(['item'])

@php
    $user = $item['user'];
    $movieTitle = $item['title'] ?? 'sebuah film';
@endphp

<article class="timeline-feed-item">
    {{-- Avatar --}}
    <div class="tl-feed-avatar">
        <x-user-avatar :user="$user" class="tl-feed-avatar-img" placeholder-class="tl-feed-avatar-img tl-feed-avatar-placeholder" />
    </div>

    {{-- Activity text --}}
    <div class="tl-feed-body">
        <p class="tl-feed-text">
            @if ($user?->username)
                <x-user-name :user="$user" class="tl-feed-user-link" :href="route('profile.show', $user->username)" />
            @else
                <x-user-name :user="$user" class="tl-feed-user-link" />
            @endif

            @if ($item['type'] === 'diary')
                menonton
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                @if ($item['extra'])
                    <span class="tl-feed-mood">· {{ $item['extra'] }}</span>
                @endif
            @elseif ($item['type'] === 'review')
                menulis review untuk
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                @if (! empty($item['extra']['rating']))
                    <span class="tl-feed-rating">· ★ {{ $item['extra']['rating'] }}/5</span>
                @endif
                @if (! empty($item['extra']['body']))
                    <br>
                    <a href="{{ route('reviews.show', $item['subject_id']) }}" class="tl-feed-review-body">
                        {{ Str::limit(strip_tags($item['extra']['body']), 120) }}
                    </a>
                @endif
            @elseif ($item['type'] === 'watchlist')
                menambahkan
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                ke watchlist
            @elseif ($item['type'] === 'favorite')
                menandai
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                sebagai favorit ❤️
            @elseif ($item['type'] === 'list')
                membuat list
                <a href="{{ route('lists.show', $item['extra']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
            @elseif ($item['type'] === 'list_movie_add')
                menambahkan
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                ke list
                @if ($item['extra'])
                    <a href="{{ route('lists.show', $item['extra']) }}" class="tl-feed-movie-link">lihat list</a>
                @endif
            @elseif ($item['type'] === 'pinned')
                menyematkan
                <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-movie-link">{{ $movieTitle }}</a>
                di profil 📌
            @elseif ($item['type'] === 'follow')
                mulai mengikuti
                @if ($item['extra'])
                    <a href="{{ route('profile.show', $item['extra']) }}" class="tl-feed-movie-link">{{ $item['title'] }}</a>
                @else
                    <strong>{{ $item['title'] }}</strong>
                @endif
            @endif
        </p>
        <span class="tl-feed-time">{{ $item['created_at']->diffForHumans() }}</span>
    </div>

    {{-- Poster mini --}}
    @if (! empty($item['poster_url']) && ! in_array($item['type'], ['list', 'follow']))
        <a href="{{ route('movies.show', $item['tmdb_id']) }}" class="tl-feed-poster-link" tabindex="-1" aria-hidden="true">
            <img src="{{ $item['poster_url'] }}" alt="{{ $item['title'] }}" class="tl-feed-poster" loading="lazy">
        </a>
    @endif
</article>
