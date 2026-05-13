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
    /**
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();
        [$movies, $errorMessage] = $this->fetchMovies($search);

        return view('welcome', [
            'movies' => $movies,
            'heroMovie' => $movies[0] ?? null,
            'search' => $search,
            'sectionTitle' => $search !== '' ? 'Hasil Pencarian Film' : 'Jakkaspace Movie Indonesia',
            'sectionKicker' => $search !== ''
                ? 'Menampilkan film dari TMDB berdasarkan kata kunci yang kamu cari.'
                : 'Film populer dari TMDB langsung dari API.',
            'emptyMessage' => $errorMessage ?? $this->emptyMessage($search),
        ]);
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: string|null}
     */
    private function fetchMovies(string $search): array
    {
        $apiKey = (string) config('services.tmdb.key');
        $baseUrl = (string) config('services.tmdb.base_url');

        if ($apiKey === '' || $baseUrl === '') {
            return [[], 'Konfigurasi TMDB belum lengkap. Cek TMDB_API_KEY dan TMDB_BASE_URL di file .env.'];
        }

        $endpoint = $search !== '' ? '/search/movie' : '/movie/popular';
        $query = [
            'api_key' => $apiKey,
            'language' => 'id-ID',
            'page' => 1,
        ];

        if ($search !== '') {
            $query['query'] = $search;
        }

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
                'search' => $search,
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
     */
    private function movieOverview(array $movie): string
    {
        $overview = trim((string) Arr::get($movie, 'overview', ''));

        if ($overview === '') {
            return 'Deskripsi film belum tersedia.';
        }

        return $overview;
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
