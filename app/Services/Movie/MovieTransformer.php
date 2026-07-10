<?php

namespace App\Services\Movie;

use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MovieTransformer
{
    /**
     * @param  array<int, array<string, mixed>>  $movies
     * @param  array<int, array<string, mixed>>  $fallbackMovies
     * @return array<int, array<string, mixed>>
     */
    public function transformList(array $movies, array $fallbackMovies = []): array
    {
        $fallbackById = $this->indexById($fallbackMovies);

        return array_map(function (array $movie) use ($fallbackById): array {
            $id = (int) Arr::get($movie, 'id', 0);

            return [
                'id' => $id,
                'title' => (string) Arr::get($movie, 'title', 'Tanpa Judul'),
                'overview' => $this->overview($movie, $fallbackById[$id] ?? []),
                'poster_url' => $this->imageUrl(Arr::get($movie, 'poster_path'), 'w342'),
                'backdrop_url' => $this->imageUrl(
                    Arr::get($movie, 'backdrop_path') ?: Arr::get($movie, 'poster_path'),
                    'w1280',
                ),
                'rating' => number_format((float) Arr::get($movie, 'vote_average', 0), 1),
                'release_date' => $this->formatReleaseDate(Arr::get($movie, 'release_date')),
                'release_year' => $this->releaseYear(Arr::get($movie, 'release_date')),
            ];
        }, $movies);
    }

    /**
     * @param  array<string, mixed>  $movie
     * @param  array<string, mixed>  $fallbackMovie
     * @return array<string, mixed>
     */
    public function transformDetail(array $movie, array $fallbackMovie = []): array
    {
        $genres = collect(Arr::get($movie, 'genres', []))
            ->filter(fn (mixed $genre): bool => is_array($genre) && is_string(Arr::get($genre, 'name')))
            ->map(fn (array $genre): string => (string) Arr::get($genre, 'name'))
            ->values()
            ->all();

        return [
            'id' => (int) Arr::get($movie, 'id', 0),
            'title' => (string) Arr::get($movie, 'title', 'Tanpa Judul'),
            'overview' => $this->overview($movie, $fallbackMovie),
            'tagline' => trim((string) Arr::get($movie, 'tagline', '')),
            'poster_url' => $this->imageUrl(Arr::get($movie, 'poster_path'), 'w342'),
            'backdrop_url' => $this->imageUrl(
                Arr::get($movie, 'backdrop_path') ?: Arr::get($movie, 'poster_path'),
                'w1280',
            ),
            'story_poster_url' => $this->imageProxyUrl(Arr::get($movie, 'poster_path'), 'w500'),
            'story_backdrop_url' => $this->imageProxyUrl(
                Arr::get($movie, 'backdrop_path') ?: Arr::get($movie, 'poster_path'),
                'w780',
            ),
            'rating' => number_format((float) Arr::get($movie, 'vote_average', 0), 1),
            'release_date' => $this->formatReleaseDate(Arr::get($movie, 'release_date')),
            'release_year' => $this->releaseYear(Arr::get($movie, 'release_date')),
            'runtime' => $this->formatRuntime(Arr::get($movie, 'runtime')),
            'genres' => implode(', ', $genres),
            'director' => $this->directorNames(Arr::get($movie, 'credits', [])),
            'writers' => $this->writerNames(Arr::get($movie, 'credits', [])),
            'facts' => $this->movieFacts($movie),
            'cast' => $this->castMembers(Arr::get($movie, 'credits', [])),
            'trailer_url' => $this->trailerUrl(Arr::get($movie, 'videos', [])),
        ];
    }

    /**
     * @param  array<string, mixed>  $movie
     */
    public function needsOverviewFallback(array $movie): bool
    {
        return trim((string) Arr::get($movie, 'overview', '')) === '';
    }

    /**
     * @param  array<int, mixed>  $movies
     */
    public function listNeedsOverviewFallback(array $movies): bool
    {
        foreach ($movies as $movie) {
            if (is_array($movie) && $this->needsOverviewFallback($movie)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $movie
     * @param  array<string, mixed>  $fallbackMovie
     */
    private function overview(array $movie, array $fallbackMovie = []): string
    {
        $overview = trim((string) Arr::get($movie, 'overview', ''));

        if ($overview !== '') {
            return $overview;
        }

        $fallbackOverview = trim((string) Arr::get($fallbackMovie, 'overview', ''));

        if ($fallbackOverview !== '') {
            return $fallbackOverview;
        }

        return 'Deskripsi film belum tersedia.';
    }

    private function imageUrl(mixed $path, string $size): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        return "https://image.tmdb.org/t/p/{$size}{$path}";
    }

    private function imageProxyUrl(mixed $path, string $size): ?string
    {
        $cleanPath = $this->cleanImagePath($path);

        if ($cleanPath === null || ! $this->isAllowedImageSize($size)) {
            return null;
        }

        return route('movies.image', [
            'size' => $size,
            'path' => $cleanPath,
        ], false);
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

    private function writerNames(mixed $credits): ?string
    {
        $crew = Arr::get(is_array($credits) ? $credits : [], 'crew', []);

        if (! is_array($crew)) {
            return null;
        }

        $writerJobs = ['Writer', 'Screenplay', 'Story', 'Novel', 'Characters'];

        $writers = collect($crew)
            ->filter(fn (mixed $person): bool => is_array($person)
                && in_array(Arr::get($person, 'job'), $writerJobs, true)
            )
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->unique()
            ->values();

        if ($writers->isEmpty()) {
            return null;
        }

        return $writers->join(', ', ' & ');
    }

    /**
     * @param  array<string, mixed>  $movie
     * @return array<int, array{label: string, value: string}>
     */
    private function movieFacts(array $movie): array
    {
        $facts = [
            [
                'label' => 'Batas Umur',
                'value' => $this->ageRating(Arr::get($movie, 'release_dates', [])) ?? 'Belum tersedia',
            ],
            [
                'label' => 'Jumlah Vote',
                'value' => $this->formatVoteCount(Arr::get($movie, 'vote_count')),
            ],
            [
                'label' => 'Bahasa Asli',
                'value' => $this->languageName(Arr::get($movie, 'original_language')),
            ],
            [
                'label' => 'Negara Produksi',
                'value' => $this->productionCountries(Arr::get($movie, 'production_countries', [])),
            ],
            [
                'label' => 'Studio',
                'value' => $this->productionCompanies(Arr::get($movie, 'production_companies', [])),
            ],
        ];

        return array_values(array_filter(
            $facts,
            fn (array $fact): bool => is_string($fact['value']) && $fact['value'] !== '',
        ));
    }

    private function ageRating(mixed $releaseDates): ?string
    {
        $results = Arr::get(is_array($releaseDates) ? $releaseDates : [], 'results', []);

        if (! is_array($results)) {
            return null;
        }

        foreach (['ID', 'US'] as $countryCode) {
            $countryRelease = collect($results)->first(fn (mixed $release): bool => is_array($release)
                && Arr::get($release, 'iso_3166_1') === $countryCode
            );

            if (is_array($countryRelease)) {
                $certification = $this->releaseCertification($countryRelease);

                if ($certification !== null) {
                    return $certification;
                }
            }
        }

        foreach ($results as $countryRelease) {
            if (is_array($countryRelease)) {
                $certification = $this->releaseCertification($countryRelease);

                if ($certification !== null) {
                    return $certification;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $countryRelease
     */
    private function releaseCertification(array $countryRelease): ?string
    {
        $releaseDates = Arr::get($countryRelease, 'release_dates', []);

        if (! is_array($releaseDates)) {
            return null;
        }

        foreach ($releaseDates as $releaseDate) {
            $certification = trim((string) Arr::get(is_array($releaseDate) ? $releaseDate : [], 'certification', ''));

            if ($certification !== '') {
                return $certification;
            }
        }

        return null;
    }

    private function formatVoteCount(mixed $voteCount): ?string
    {
        if (! is_numeric($voteCount) || (int) $voteCount <= 0) {
            return null;
        }

        return number_format((int) $voteCount, 0, ',', '.').' vote';
    }

    private function languageName(mixed $languageCode): ?string
    {
        if (! is_string($languageCode) || $languageCode === '') {
            return null;
        }

        $languages = [
            'id' => 'Bahasa Indonesia',
            'en' => 'Bahasa Inggris',
            'ja' => 'Bahasa Jepang',
            'ko' => 'Bahasa Korea',
            'zh' => 'Bahasa Mandarin',
            'fr' => 'Bahasa Prancis',
            'es' => 'Bahasa Spanyol',
            'de' => 'Bahasa Jerman',
            'hi' => 'Bahasa Hindi',
            'th' => 'Bahasa Thailand',
        ];

        return $languages[$languageCode] ?? mb_strtoupper($languageCode);
    }

    private function productionCountries(mixed $countries): ?string
    {
        if (! is_array($countries)) {
            return null;
        }

        $names = collect($countries)
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->take(3)
            ->values();

        if ($names->isEmpty()) {
            return null;
        }

        return $names->join(', ', ' & ');
    }

    private function productionCompanies(mixed $companies): ?string
    {
        if (! is_array($companies)) {
            return null;
        }

        $names = collect($companies)
            ->pluck('name')
            ->filter(fn (mixed $name): bool => is_string($name) && $name !== '')
            ->take(3)
            ->values();

        if ($names->isEmpty()) {
            return null;
        }

        return $names->join(', ', ' & ');
    }

    /**
     * @return array<int, array{name: string, character: string, profile_url: string|null}>
     */
    private function castMembers(mixed $credits): array
    {
        $cast = Arr::get(is_array($credits) ? $credits : [], 'cast', []);

        if (! is_array($cast)) {
            return [];
        }

        return collect($cast)
            ->filter(fn (mixed $person): bool => is_array($person)
                && is_string(Arr::get($person, 'name'))
                && Arr::get($person, 'name') !== ''
            )
            ->sortBy(fn (array $person): int => is_numeric(Arr::get($person, 'order'))
                ? (int) Arr::get($person, 'order')
                : PHP_INT_MAX
            )
            ->take(12)
            ->map(fn (array $person): array => [
                'name' => (string) Arr::get($person, 'name'),
                'character' => trim((string) Arr::get($person, 'character', '')),
                'profile_url' => $this->imageUrl(Arr::get($person, 'profile_path'), 'w185'),
            ])
            ->values()
            ->all();
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

    public function cleanImagePath(mixed $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        $cleanPath = ltrim($path, '/');

        if ($cleanPath === '' || str_contains($cleanPath, '..')) {
            return null;
        }

        if (! preg_match('/^[A-Za-z0-9_.-]+\.(jpg|jpeg|png|webp)$/i', $cleanPath)) {
            return null;
        }

        return $cleanPath;
    }

    public function isAllowedImageSize(string $size): bool
    {
        return in_array($size, ['w185', 'w342', 'w500', 'w780', 'original'], true);
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

    /**
     * @param  array<int, array<string, mixed>>  $movies
     * @return array<int, array<string, mixed>>
     */
    private function indexById(array $movies): array
    {
        $indexed = [];

        foreach ($movies as $movie) {
            $indexed[(int) Arr::get($movie, 'id', 0)] = $movie;
        }

        return $indexed;
    }
}
