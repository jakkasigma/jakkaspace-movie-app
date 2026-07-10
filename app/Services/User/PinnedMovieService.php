<?php

namespace App\Services\User;

use App\Models\PinnedMovie;
use App\Models\User;
use App\Services\Movie\MovieService;
use Illuminate\Support\Facades\Cache;

class PinnedMovieService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPinnedMovies(User $user): array
    {
        return Cache::remember("profile.{$user->id}.pinned", 600, function () use ($user): array {
            $tmdbIds = PinnedMovie::where('user_id', $user->id)
                ->orderBy('sort_order')
                ->pluck('tmdb_id')
                ->all();

            $movieDetails = $this->movieService->findMovies($tmdbIds);

            $movies = [];

            foreach ($tmdbIds as $tmdbId) {
                $detail = $movieDetails[$tmdbId] ?? null;
                if ($detail !== null) {
                    $movies[] = $detail;
                }
            }

            return $movies;
        });
    }

    public function addPinnedMovie(User $user, int $tmdbId): void
    {
        $count = PinnedMovie::where('user_id', $user->id)->count();

        PinnedMovie::firstOrCreate(
            ['user_id' => $user->id, 'tmdb_id' => $tmdbId],
            ['sort_order' => $count],
        );

        Cache::forget("profile.{$user->id}.pinned");
    }

    public function removePinnedMovie(User $user, int $tmdbId): void
    {
        PinnedMovie::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();

        Cache::forget("profile.{$user->id}.pinned");
    }

    public function isPinned(User $user, int $tmdbId): bool
    {
        return PinnedMovie::where('user_id', $user->id)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }

    public function getPinnedCount(User $user): int
    {
        return PinnedMovie::where('user_id', $user->id)->count();
    }
}
