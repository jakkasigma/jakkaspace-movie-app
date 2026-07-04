<?php

namespace App\Services\User;

use App\Models\DiaryEntry;
use App\Models\Favorite;
use App\Models\Follow;
use App\Models\ListMovie;
use App\Models\MovieList;
use App\Models\PinnedMovie;
use App\Models\Review;
use App\Models\User;
use App\Models\Watchlist;
use App\Services\Movie\MovieService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class ActivityFeedService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * Build activity feed from the users the given user follows.
     * Each item: ['type', 'user', 'tmdb_id', 'title', 'poster_url', 'extra', 'created_at', 'subject_id']
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getFeed(User $user, int $limit = 30): Collection
    {
        $followingIds = $user->following()->pluck('users.id');

        if ($followingIds->isEmpty()) {
            return collect();
        }

        $activities = collect();

        // Diary entries
        $diaries = DiaryEntry::whereIn('user_id', $followingIds)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($entry): array => [
                'type' => 'diary',
                'user' => $entry->user,
                'tmdb_id' => $entry->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => $entry->mood,
                'created_at' => $entry->created_at,
                'subject_id' => $entry->id,
            ]);

        // Reviews
        $reviews = Review::whereIn('user_id', $followingIds)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($review): array => [
                'type' => 'review',
                'user' => $review->user,
                'tmdb_id' => $review->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => $review->rating,
                'created_at' => $review->created_at,
                'subject_id' => $review->id,
            ]);

        // Watchlist adds
        $watchlists = Watchlist::whereIn('user_id', $followingIds)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($item): array => [
                'type' => 'watchlist',
                'user' => $item->user,
                'tmdb_id' => $item->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => null,
                'created_at' => $item->created_at,
                'subject_id' => $item->id,
            ]);

        // Favorites
        $favorites = Favorite::whereIn('user_id', $followingIds)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($item): array => [
                'type' => 'favorite',
                'user' => $item->user,
                'tmdb_id' => $item->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => null,
                'created_at' => $item->created_at,
                'subject_id' => $item->id,
            ]);

        // Movie lists created
        $lists = MovieList::whereIn('user_id', $followingIds)
            ->where('is_public', true)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($list): array => [
                'type' => 'list',
                'user' => $list->user,
                'tmdb_id' => null,
                'title' => $list->name,
                'poster_url' => null,
                'extra' => $list->id,
                'created_at' => $list->created_at,
                'subject_id' => $list->id,
            ]);

        $feed = $activities
            ->merge($diaries)
            ->merge($reviews)
            ->merge($watchlists)
            ->merge($favorites)
            ->merge($lists)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();

        // Enrich with movie titles from TMDB (cached per tmdb_id)
        return $feed->map(fn (array $item): array => $this->enrichWithMovie($item));
    }

    /**
     * Extended feed for Timeline — includes pinned and list_movie_add activity types.
     * Each item: ['type', 'user', 'tmdb_id', 'title', 'poster_url', 'extra', 'created_at', 'subject_id']
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getEnrichedFeed(User $user, int $limit = 40): Collection
    {
        $followingIds = $user->following()->pluck('users.id');

        if ($followingIds->isEmpty()) {
            return collect();
        }

        $baseFeed = $this->getFeed($user, $limit);

        // Pinned movies
        $pinned = PinnedMovie::whereIn('user_id', $followingIds)
            ->with('user')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($pin): array => [
                'type' => 'pinned',
                'user' => $pin->user,
                'tmdb_id' => $pin->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => null,
                'created_at' => $pin->created_at,
                'subject_id' => $pin->id,
            ]);

        // Films added to public lists
        $listMovies = ListMovie::whereHas(
            'movieList',
            fn ($q) => $q->whereIn('user_id', $followingIds)->where('is_public', true)
        )
            ->with(['movieList.user'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($item): array => [
                'type' => 'list_movie_add',
                'user' => $item->movieList?->user,
                'tmdb_id' => $item->tmdb_id,
                'title' => null,
                'poster_url' => null,
                'extra' => $item->movieList?->id,
                'created_at' => $item->created_at,
                'subject_id' => $item->id,
            ])
            ->filter(fn (array $item): bool => $item['user'] !== null);

        // New follows
        $follows = Follow::whereIn('follower_id', $followingIds)
            ->with(['follower', 'following'])
            ->latest()
            ->limit($limit)
            ->get()
            ->map(fn ($follow): array => [
                'type' => 'follow',
                'user' => $follow->follower,
                'tmdb_id' => null,
                'title' => $follow->following?->name,
                'poster_url' => $follow->following?->avatar_url,
                'extra' => $follow->following?->username,
                'created_at' => $follow->created_at,
                'subject_id' => $follow->id,
            ])
            ->filter(fn (array $item): bool => $item['user'] !== null);

        return $baseFeed
            ->merge($pinned)
            ->merge($listMovies)
            ->merge($follows)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values()
            ->map(fn (array $item): array => $this->enrichWithMovie($item));
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    private function enrichWithMovie(array $item): array
    {
        if ($item['tmdb_id'] === null) {
            return $item;
        }

        $movie = Cache::remember(
            "tmdb.title.{$item['tmdb_id']}",
            86400,
            function () use ($item): ?array {
                [$detail] = $this->movieService->findMovie((int) $item['tmdb_id']);

                return $detail;
            }
        );

        if ($movie !== null) {
            $item['title'] = $movie['title'];
            $item['poster_url'] = $movie['poster_url'] ?? null;
        }

        return $item;
    }
}
