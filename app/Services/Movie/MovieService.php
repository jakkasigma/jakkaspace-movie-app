<?php

namespace App\Services\Movie;

use App\Models\Movie;
use App\Services\Tmdb\TmdbClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class MovieService
{
    public function __construct(
        private readonly TmdbClient $tmdb,
        private readonly MovieTransformer $transformer,
    ) {}

    /**
     * @return array<int, array{id: string, title: string, kicker: string, movies: array<int, array<string, mixed>>, emptyMessage: string, layout: string, statusMessage: string|null}>
     */
    public function homeMovieSections(): array
    {
        $today = Carbon::today();

        $categories = [
            [
                'id' => 'all-movies',
                'title' => 'Trending TMDB',
                'kicker' => 'Film yang sedang naik di TMDB minggu ini.',
                'endpoint' => '/trending/movie/week',
                'query' => [],
                'ttl' => 3600,
            ],
            [
                'id' => 'new-releases',
                'title' => 'Film Baru Rilis',
                'kicker' => 'Film yang sedang atau baru tayang berdasarkan data rilis TMDB.',
                'endpoint' => '/movie/now_playing',
                'query' => ['region' => 'ID'],
                'ttl' => 21600,
            ],
            [
                'id' => 'indonesia-trending',
                'title' => 'Film Indonesia Trending',
                'kicker' => 'Pendekatan trending memakai film asal Indonesia yang diurutkan berdasarkan popularitas TMDB.',
                'endpoint' => '/discover/movie',
                'query' => [
                    'region' => 'ID',
                    'sort_by' => 'popularity.desc',
                    'with_origin_country' => 'ID',
                    'with_original_language' => 'id',
                ],
                'ttl' => 21600,
            ],
            [
                'id' => 'indonesia-new-releases',
                'title' => 'Film Indonesia Baru Rilis',
                'kicker' => 'Film asal Indonesia dengan tanggal rilis terbaru di TMDB.',
                'endpoint' => '/discover/movie',
                'query' => [
                    'primary_release_date.gte' => $today->copy()->subMonths(8)->toDateString(),
                    'primary_release_date.lte' => $today->toDateString(),
                    'region' => 'ID',
                    'sort_by' => 'primary_release_date.desc',
                    'with_origin_country' => 'ID',
                    'with_original_language' => 'id',
                ],
                'ttl' => 21600,
            ],
            [
                'id' => 'animation',
                'title' => 'Film Animasi',
                'kicker' => 'Koleksi film animasi populer dari genre resmi TMDB.',
                'endpoint' => '/discover/movie',
                'query' => [
                    'sort_by' => 'popularity.desc',
                    'with_genres' => 16,
                ],
                'ttl' => 21600,
            ],
        ];

        return array_map(function (array $category): array {
            $cacheKey = 'movie_section_'.md5((string) $category['id'].serialize($category['query']));
            $ttl = is_int($category['ttl']) ? $category['ttl'] : 3600;

            $movies = Cache::remember($cacheKey, $ttl, function () use ($category): array {
                [$movies, $error] = $this->fetchMovieList(
                    (string) $category['endpoint'],
                    is_array($category['query']) ? $category['query'] : [],
                );

                return $error !== null ? [] : $movies;
            });

            return [
                'id' => (string) $category['id'],
                'title' => (string) $category['title'],
                'kicker' => (string) $category['kicker'],
                'movies' => $movies,
                'emptyMessage' => 'Belum ada film yang bisa ditampilkan.',
                'layout' => 'row',
                'statusMessage' => null,
            ];
        }, $categories);
    }

    /**
     * @return array{id: string, title: string, kicker: string, movies: array<int, array<string, mixed>>, emptyMessage: string, layout: string, statusMessage: string|null}
     */
    public function searchMovieSection(string $search): array
    {
        [$movies, $errorMessage] = $this->fetchMovieList('/search/movie', ['query' => $search]);

        return [
            'id' => 'all-movies',
            'title' => 'Hasil Pencarian Film',
            'kicker' => 'Menampilkan film dari TMDB berdasarkan kata kunci yang kamu cari.',
            'movies' => $movies,
            'emptyMessage' => $errorMessage ?? $this->emptyMessage($search),
            'layout' => 'grid',
            'statusMessage' => $movies !== [] ? "Menampilkan hasil pencarian untuk \"{$search}\"." : null,
        ];
    }

    /**
     * @return array{0: array<string, mixed>|null, 1: string|null}
     */
    public function findMovie(int $movieId): array
    {
        // Check local DB first
        $local = Movie::where('tmdb_id', $movieId)->first();

        if ($local !== null && $local->cached_at !== null && $local->cached_at->diffInHours(now()) < 24) {
            return [$local->toArray(), null];
        }

        if (! $this->tmdb->isConfigured()) {
            if ($local !== null) {
                return [$local->toArray(), null];
            }

            return [null, 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        [$data, $error] = $this->tmdb->get("/movie/{$movieId}", [
            'append_to_response' => 'videos,credits,release_dates',
        ]);

        if ($error !== null) {
            if ($local !== null) {
                return [$local->toArray(), $error];
            }

            return [null, $error];
        }

        $fallback = $this->transformer->needsOverviewFallback($data)
            ? $this->fetchFallbackMovie($movieId)
            : [];

        $transformed = $this->transformer->transformDetail($data, $fallback);

        Movie::updateOrCreate(
            ['tmdb_id' => $movieId],
            [
                'title' => $transformed['title'],
                'poster_path' => $data['poster_path'] ?? null,
                'backdrop_path' => $data['backdrop_path'] ?? null,
                'release_date' => $data['release_date'] ?? null,
                'overview' => $transformed['overview'] ?? null,
                'genres' => $transformed['genres'] ?? null,
                'rating' => $transformed['rating'] ?? null,
                'poster_url' => $transformed['poster_url'] ?? null,
                'release_year' => $transformed['release_year'] ?? null,
                'cached_at' => now(),
            ]
        );

        return [$transformed, null];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function similarMovies(int $movieId): array
    {
        $cacheKey = "movie_similar_{$movieId}";

        return Cache::remember($cacheKey, 86400, function () use ($movieId): array {
            [$results, $error] = $this->tmdb->listing("/movie/{$movieId}/similar");

            if ($error !== null) {
                return [];
            }

            return $this->transformer->transformList($results);
        });
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function genres(): array
    {
        return Cache::remember('movie_genres', 86400, function (): array {
            [$data, $error] = $this->tmdb->get('/genre/movie/list');

            if ($error !== null) {
                return [];
            }

            $genres = $data['genres'] ?? [];

            if (! is_array($genres)) {
                return [];
            }

            return collect($genres)
                ->filter(fn (mixed $g): bool => is_array($g) && isset($g['id'], $g['name']))
                ->map(fn (array $g): array => ['id' => (int) $g['id'], 'name' => (string) $g['name']])
                ->values()
                ->all();
        });
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    public function discoverMoviesRaw(array $query = []): array
    {
        $data = $this->fetchDiscoverRaw($query);
        $movies = $this->transformer->transformList($data['results'] ?? []);

        return [$movies, null];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{movies: array<int, array<string, mixed>>, total_pages: int, current_page: int}
     */
    public function discoverMovies(array $filters = []): array
    {
        $page = max(1, (int) ($filters['page'] ?? 1));

        $query = array_filter([
            'page' => $page,
            'sort_by' => $filters['sort_by'] ?? 'popularity.desc',
            'with_genres' => $filters['genre'] ?? null,
            'primary_release_year' => $filters['year'] ?? null,
            'vote_average.gte' => $filters['rating_min'] ?? null,
        ]);

        // Discover results vary by page, only cache page 1
        if ($page === 1) {
            $cacheKey = 'discover_'.md5(serialize($query));
            $data = Cache::remember($cacheKey, 3600, fn () => $this->fetchDiscoverRaw($query));
        } else {
            $data = $this->fetchDiscoverRaw($query);
        }

        return [
            'movies' => $this->transformer->transformList($data['results'] ?? []),
            'total_pages' => min((int) ($data['total_pages'] ?? 1), 500),
            'current_page' => $page,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return array{movies: array<int, array<string, mixed>>, total_pages: int, current_page: int, genre_name: string}
     */
    public function moviesByGenre(int $genreId, int $page = 1): array
    {
        $query = [
            'with_genres' => $genreId,
            'sort_by' => 'popularity.desc',
            'page' => max(1, $page),
        ];

        if ($page === 1) {
            $cacheKey = "genre_movies_{$genreId}";
            $data = Cache::remember($cacheKey, 21600, fn () => $this->fetchDiscoverRaw($query));
        } else {
            $data = $this->fetchDiscoverRaw($query);
        }

        $genreName = collect($this->genres())
            ->firstWhere('id', $genreId)['name'] ?? 'Genre';

        return [
            'movies' => $this->transformer->transformList($data['results'] ?? []),
            'total_pages' => min((int) ($data['total_pages'] ?? 1), 500),
            'current_page' => $page,
            'genre_name' => $genreName,
        ];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     */
    private function fetchDiscoverRaw(array $query): array
    {
        [$data, $error] = $this->tmdb->get('/discover/movie', $query);

        if ($error !== null) {
            return ['results' => [], 'total_pages' => 0];
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    private function fetchMovieList(string $endpoint, array $query = []): array
    {
        if (! $this->tmdb->isConfigured()) {
            return [[], 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        [$results, $error] = $this->tmdb->listing($endpoint, $query);

        if ($error !== null) {
            return [[], $error];
        }

        $fallbackResults = $this->transformer->listNeedsOverviewFallback($results)
            ? $this->fetchFallbackMovieList($endpoint, $query)
            : [];

        return [$this->transformer->transformList($results, $fallbackResults), null];
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    private function fetchFallbackMovieList(string $endpoint, array $query = []): array
    {
        [$results, $error] = $this->tmdb->listing($endpoint, $query, 'en-US');

        if ($error !== null) {
            return [];
        }

        return $results;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchFallbackMovie(int $movieId): array
    {
        [$data, $error] = $this->tmdb->get("/movie/{$movieId}", [
            'append_to_response' => 'videos,credits,release_dates',
        ], 'en-US');

        if ($error !== null) {
            return [];
        }

        return $data;
    }

    private function emptyMessage(string $search): string
    {
        if ($search !== '') {
            return "Film dengan kata kunci \"{$search}\" belum ditemukan.";
        }

        return 'Belum ada film yang bisa ditampilkan.';
    }
}
