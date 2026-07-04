<?php

use App\Services\Movie\MovieService;
use App\Services\Movie\MovieTransformer;
use App\Services\Tmdb\TmdbClient;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->service = new MovieService(
        new TmdbClient,
        new MovieTransformer,
    );
});

describe('homeMovieSections', function (): void {
    it('returns sections with expected structure', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'results' => [
                    [
                        'id' => 1,
                        'title' => 'Fake Movie',
                        'overview' => 'An overview.',
                        'poster_path' => '/poster.jpg',
                        'backdrop_path' => '/backdrop.jpg',
                        'vote_average' => 7.0,
                        'release_date' => '2024-01-01',
                    ],
                ],
            ], 200),
        ]);

        $sections = $this->service->homeMovieSections();

        expect($sections)->toBeArray()->not->toBeEmpty()
            ->and($sections[0])->toHaveKeys(['id', 'title', 'kicker', 'movies', 'emptyMessage', 'layout'])
            ->and($sections[0]['movies'])->toBeArray();
    });

    it('returns empty movies with error message when TMDB is unavailable', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response(null, 503),
        ]);

        $sections = $this->service->homeMovieSections();

        expect($sections)->toBeArray()
            ->and($sections[0]['movies'])->toBeArray()->toBeEmpty()
            ->and($sections[0]['emptyMessage'])->toBeString()->not->toBeEmpty();
    });
});

describe('searchMovieSection', function (): void {
    it('returns a search section with grid layout', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'results' => [
                    [
                        'id' => 10,
                        'title' => 'Found Movie',
                        'overview' => 'Overview here.',
                        'poster_path' => '/p.jpg',
                        'backdrop_path' => '/b.jpg',
                        'vote_average' => 6.5,
                        'release_date' => '2023-05-20',
                    ],
                ],
            ], 200),
        ]);

        $section = $this->service->searchMovieSection('avengers');

        expect($section['layout'])->toBe('grid')
            ->and($section['movies'])->toHaveCount(1)
            ->and($section['statusMessage'])->toContain('avengers');
    });

    it('returns empty message when no results found', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response(['results' => []], 200),
        ]);

        $section = $this->service->searchMovieSection('xyznotfound');

        expect($section['movies'])->toBeEmpty()
            ->and($section['emptyMessage'])->toContain('xyznotfound')
            ->and($section['statusMessage'])->toBeNull();
    });
});

describe('findMovie', function (): void {
    it('returns transformed movie detail on success', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'id' => 123,
                'title' => 'Movie Detail',
                'overview' => 'Detail overview.',
                'tagline' => '',
                'poster_path' => '/p.jpg',
                'backdrop_path' => '/b.jpg',
                'vote_average' => 8.0,
                'release_date' => '2022-03-10',
                'runtime' => 120,
                'genres' => [],
                'credits' => ['cast' => [], 'crew' => []],
                'videos' => ['results' => []],
                'release_dates' => ['results' => []],
                'production_countries' => [],
                'production_companies' => [],
            ], 200),
        ]);

        [$movie, $error] = $this->service->findMovie(123);

        expect($error)->toBeNull()
            ->and($movie)->toBeArray()
            ->and($movie['id'])->toBe(123)
            ->and($movie['title'])->toBe('Movie Detail');
    });

    it('returns error message when TMDB request fails', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response(null, 404),
        ]);

        [$movie, $error] = $this->service->findMovie(999);

        expect($movie)->toBeNull()
            ->and($error)->toBeString()->not->toBeEmpty();
    });
});
