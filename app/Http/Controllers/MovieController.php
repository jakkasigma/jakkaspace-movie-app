<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MovieController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        $movieSections = $search !== ''
            ? [$this->searchSection($search)]
            : $this->homeMovieSections();

        return view('welcome', [
            'movieSections' => $movieSections,
            'heroMovie' => $this->firstMovieFromSections($movieSections),
            'search' => $search,
        ]);
    }

    public function show(int $movie): View
    {
        [$movieDetail, $errorMessage] = $this->fetchMovie($movie);

        return view('movies.show', [
            'movie' => $movieDetail,
            'errorMessage' => $errorMessage ?? 'Detail film tidak ditemukan.',
        ]);
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    private function fetchMovies(string $search): array
    {
        return $this->fetchMovieList('/search/movie', [
            'query' => $search,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    private function fetchMovieList(string $endpoint, array $query = []): array
    {
        $apiKey = (string) config('services.tmdb.key');
        $baseUrl = (string) config('services.tmdb.base_url');

        if ($apiKey === '' || $baseUrl === '') {
            return [[], 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        $query = array_merge([
            'api_key' => $apiKey,
            'language' => 'id-ID',
            'page' => 1,
        ], $query);

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry([200, 400], throw: false)
                ->get($endpoint, $query);
        } catch (ConnectionException $exception) {
            report($exception);

            return [[], 'Koneksi ke TMDB sedang bermasalah. Coba lagi sebentar lagi.'];
        }

        if ($response->failed()) {
            Log::warning('TMDB request failed', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
            ]);

            return [[], 'Data film tidak bisa dimuat saat ini.'];
        }

        $results = $response->json('results', []);

        if (! is_array($results)) {
            return [[], 'Format data dari TMDB tidak sesuai.'];
        }

        return [$this->transformMovies($results), null];
    }

    /**
     * @return array<int, array{id: string, title: string, kicker: string, movies: array<int, array<string, mixed>>, emptyMessage: string, layout: string, statusMessage: string|null}>
     */
    private function homeMovieSections(): array
    {
        $today = Carbon::today();

        $categories = [
            [
                'id' => 'all-movies',
                'title' => 'Trending TMDB',
                'kicker' => 'Film yang sedang naik di TMDB minggu ini.',
                'endpoint' => '/trending/movie/week',
                'query' => [],
            ],
            [
                'id' => 'new-releases',
                'title' => 'Film Baru Rilis',
                'kicker' => 'Film yang sedang atau baru tayang berdasarkan data rilis TMDB.',
                'endpoint' => '/movie/now_playing',
                'query' => [
                    'region' => 'ID',
                ],
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
            ],
        ];

        return array_map(function (array $category): array {
            [$movies, $errorMessage] = $this->fetchMovieList(
                (string) $category['endpoint'],
                is_array($category['query']) ? $category['query'] : [],
            );

            return [
                'id' => (string) $category['id'],
                'title' => (string) $category['title'],
                'kicker' => (string) $category['kicker'],
                'movies' => $movies,
                'emptyMessage' => $errorMessage ?? 'Belum ada film yang bisa ditampilkan.',
                'layout' => 'row',
                'statusMessage' => null,
            ];
        }, $categories);
    }

    /**
     * @return array{id: string, title: string, kicker: string, movies: array<int, array<string, mixed>>, emptyMessage: string, layout: string, statusMessage: string|null}
     */
    private function searchSection(string $search): array
    {
        [$movies, $errorMessage] = $this->fetchMovies($search);

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
     * @param  array<int, array{movies: array<int, array<string, mixed>>}>  $movieSections
     * @return array<string, mixed>|null
     */
    private function firstMovieFromSections(array $movieSections): ?array
    {
        foreach ($movieSections as $section) {
            if ($section['movies'] !== []) {
                return $section['movies'][0];
            }
        }

        return null;
    }

    /**
     * @return array{0: array<string, mixed>|null, 1: string|null}
     */
    private function fetchMovie(int $movie): array
    {
        $apiKey = (string) config('services.tmdb.key');
        $baseUrl = (string) config('services.tmdb.base_url');

        if ($apiKey === '' || $baseUrl === '') {
            return [null, 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        try {
            $response = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(10)
                ->retry([200, 400], throw: false)
                ->get("/movie/{$movie}", [
                    'api_key' => $apiKey,
                    'append_to_response' => 'videos,credits',
                    'language' => 'id-ID',
                ]);
        } catch (ConnectionException $exception) {
            report($exception);

            return [null, 'Koneksi ke TMDB sedang bermasalah. Coba lagi sebentar lagi.'];
        }

        if ($response->failed()) {
            Log::warning('TMDB detail request failed', [
                'movie' => $movie,
                'status' => $response->status(),
            ]);

            return [null, 'Detail film tidak bisa dimuat saat ini.'];
        }

        $result = $response->json();

        if (! is_array($result)) {
            return [null, 'Format detail film dari TMDB tidak sesuai.'];
        }

        return [$this->transformMovieDetail($result), null];
    }

    /**
     * @param  array<int, array<string, mixed>>  $movies
     * @return array<int, array<string, mixed>>
     */
    private function transformMovies(array $movies): array
    {
        return array_map(function (array $movie): array {
            return [
                'id' => (int) Arr::get($movie, 'id', 0),
                'title' => (string) Arr::get($movie, 'title', 'Tanpa Judul'),
                'overview' => $this->movieOverview($movie),
                'poster_url' => $this->tmdbImageUrl(Arr::get($movie, 'poster_path'), 'w500'),
                'backdrop_url' => $this->tmdbImageUrl(
                    Arr::get($movie, 'backdrop_path') ?: Arr::get($movie, 'poster_path'),
                    'original',
                ),
                'rating' => number_format((float) Arr::get($movie, 'vote_average', 0), 1),
                'release_date' => $this->formatReleaseDate(Arr::get($movie, 'release_date')),
                'release_year' => $this->releaseYear(Arr::get($movie, 'release_date')),
            ];
        }, $movies);
    }

    /**
     * @param  array<string, mixed>  $movie
     * @return array<string, mixed>
     */
    private function transformMovieDetail(array $movie): array
    {
        $genres = collect(Arr::get($movie, 'genres', []))
            ->filter(fn (mixed $genre): bool => is_array($genre) && is_string(Arr::get($genre, 'name')))
            ->map(fn (array $genre): string => (string) Arr::get($genre, 'name'))
            ->values()
            ->all();

        return [
            'id' => (int) Arr::get($movie, 'id', 0),
            'title' => (string) Arr::get($movie, 'title', 'Tanpa Judul'),
            'overview' => $this->movieOverview($movie),
            'tagline' => trim((string) Arr::get($movie, 'tagline', '')),
            'poster_url' => $this->tmdbImageUrl(Arr::get($movie, 'poster_path'), 'w500'),
            'backdrop_url' => $this->tmdbImageUrl(
                Arr::get($movie, 'backdrop_path') ?: Arr::get($movie, 'poster_path'),
                'original',
            ),
            'rating' => number_format((float) Arr::get($movie, 'vote_average', 0), 1),
            'release_date' => $this->formatReleaseDate(Arr::get($movie, 'release_date')),
            'release_year' => $this->releaseYear(Arr::get($movie, 'release_date')),
            'runtime' => $this->formatRuntime(Arr::get($movie, 'runtime')),
            'genres' => implode(', ', $genres),
            'director' => $this->directorNames(Arr::get($movie, 'credits', [])),
            'trailer_url' => $this->trailerUrl(Arr::get($movie, 'videos', [])),
        ];
    }

    /**
     * @param  array<string, mixed>  $movie
     */
    private function movieOverview(array $movie): string
    {
        $overview = trim((string) Arr::get($movie, 'overview', ''));

        if ($overview === '') {
            return 'Deskripsi film belum tersedia.';
        }

        return $overview;
    }

    private function formatRuntime(mixed $runtime): ?string
    {
        if (! is_numeric($runtime) || (int) $runtime <= 0) {
            return null;
        }

        $runtime = (int) $runtime;
        $hours = intdiv($runtime, 60);
        $minutes = $runtime % 60;

        if ($hours === 0) {
            return "{$minutes}m";
        }

        return "{$hours}j {$minutes}m";
    }

    private function directorNames(mixed $credits): ?string
    {
        $crew = Arr::get(is_array($credits) ? $credits : [], 'crew', []);

        if (! is_array($crew)) {
            return null;
        }

        $directors = collect($crew)
            ->filter(fn (mixed $person): bool => is_array($person) && Arr::get($person, 'job') === 'Director')
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->values();

        if ($directors->isEmpty()) {
            return null;
        }

        return $directors->join(', ', ' & ');
    }

    private function trailerUrl(mixed $videos): ?string
    {
        $results = Arr::get(is_array($videos) ? $videos : [], 'results', []);

        if (! is_array($results)) {
            return null;
        }

        $youtubeVideos = collect($results)
            ->filter(fn (mixed $video): bool => is_array($video)
                && Arr::get($video, 'site') === 'YouTube'
                && is_string(Arr::get($video, 'key'))
                && Arr::get($video, 'key') !== ''
            );

        $video = $youtubeVideos->first(fn (array $video): bool => Arr::get($video, 'type') === 'Trailer'
            && (bool) Arr::get($video, 'official', false)
        ) ?? $youtubeVideos->first(fn (array $video): bool => Arr::get($video, 'type') === 'Trailer')
            ?? $youtubeVideos->first();

        $key = is_array($video) ? Arr::get($video, 'key') : null;

        if (! is_string($key) || $key === '') {
            return null;
        }

        return "https://www.youtube.com/watch?v={$key}";
    }

    private function tmdbImageUrl(mixed $path, string $size): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return "https://image.tmdb.org/t/p/{$size}{$path}";
    }

    private function formatReleaseDate(mixed $releaseDate): ?string
    {
        if (! is_string($releaseDate) || $releaseDate === '') {
            return null;
        }

        return Carbon::parse($releaseDate)->locale('id')->translatedFormat('d M Y');
    }

    private function releaseYear(mixed $releaseDate): ?string
    {
        if (! is_string($releaseDate) || $releaseDate === '') {
            return null;
        }

        return Carbon::parse($releaseDate)->format('Y');
    }

    private function emptyMessage(string $search): string
    {
        if ($search !== '') {
            return "Film dengan kata kunci \"{$search}\" belum ditemukan.";
        }

        return 'Belum ada film yang bisa ditampilkan.';
    }
}
