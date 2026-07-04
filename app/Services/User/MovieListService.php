<?php

namespace App\Services\User;

use App\Models\ListMovie;
use App\Models\MovieList;
use App\Models\User;
use App\Services\Movie\MovieService;
use Illuminate\Database\Eloquent\Collection;

class MovieListService
{
    public function __construct(
        private readonly MovieService $movieService,
    ) {}

    /**
     * @param  array{name: string, description?: string|null, is_public?: bool}  $data
     */
    public function createList(User $user, array $data): MovieList
    {
        return MovieList::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_public' => $data['is_public'] ?? true,
        ]);
    }

    /**
     * @param  array{name?: string, description?: string|null, is_public?: bool}  $data
     */
    public function updateList(MovieList $list, array $data): MovieList
    {
        $list->update(array_filter([
            'name' => $data['name'] ?? null,
            'description' => array_key_exists('description', $data) ? $data['description'] : null,
            'is_public' => $data['is_public'] ?? null,
        ], fn (mixed $v): bool => $v !== null));

        return $list->fresh();
    }

    public function deleteList(MovieList $list): void
    {
        $list->delete();
    }

    /**
     * @return Collection<int, MovieList>
     */
    public function getUserLists(User $user): Collection
    {
        return MovieList::where('user_id', $user->id)
            ->withCount('listMovies')
            ->latest()
            ->get();
    }

    public function addMovie(MovieList $list, int $tmdbId): ListMovie
    {
        $maxOrder = $list->listMovies()->max('sort_order') ?? 0;

        return ListMovie::firstOrCreate(
            ['movie_list_id' => $list->id, 'tmdb_id' => $tmdbId],
            ['sort_order' => $maxOrder + 1],
        );
    }

    public function removeMovie(MovieList $list, int $tmdbId): void
    {
        ListMovie::where('movie_list_id', $list->id)
            ->where('tmdb_id', $tmdbId)
            ->delete();
    }

    public function isMovieInList(MovieList $list, int $tmdbId): bool
    {
        return ListMovie::where('movie_list_id', $list->id)
            ->where('tmdb_id', $tmdbId)
            ->exists();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMoviesInList(MovieList $list): array
    {
        $tmdbIds = $list->listMovies()
            ->orderBy('sort_order')
            ->pluck('tmdb_id')
            ->all();

        $movies = [];

        foreach ($tmdbIds as $tmdbId) {
            [$detail] = $this->movieService->findMovie((int) $tmdbId);
            if ($detail !== null) {
                $movies[] = $detail;
            }
        }

        return $movies;
    }
}
