<?php

use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config()->set('services.tmdb.key', 'testing-key');
    config()->set('services.tmdb.base_url', 'https://api.themoviedb.org/3');
});

test('the application returns categorized movies from tmdb', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, '/trending/movie/week')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 1,
                        'title' => 'Interstellar',
                        'overview' => 'Perjalanan lintas galaksi.',
                        'poster_path' => '/poster-interstellar.jpg',
                        'backdrop_path' => '/backdrop-interstellar.jpg',
                        'vote_average' => 8.7,
                        'release_date' => '2014-11-07',
                    ],
                    [
                        'id' => 2,
                        'title' => 'Tenet',
                        'overview' => 'Rahasia kartu kedua.',
                        'poster_path' => '/poster-tenet.jpg',
                        'backdrop_path' => '/backdrop-tenet.jpg',
                        'vote_average' => 7.4,
                        'release_date' => '2020-08-26',
                    ],
                ],
            ]);
        }

        if (str_contains($url, '/movie/now_playing')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 3,
                        'title' => 'Sinners',
                        'overview' => 'Rilis terbaru.',
                        'poster_path' => '/poster-sinners.jpg',
                        'backdrop_path' => '/backdrop-sinners.jpg',
                        'vote_average' => 7.2,
                        'release_date' => '2025-04-18',
                    ],
                ],
            ]);
        }

        if (str_contains($url, 'with_genres=16')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 4,
                        'title' => 'Inside Out 2',
                        'overview' => 'Animasi populer.',
                        'poster_path' => '/poster-inside-out.jpg',
                        'backdrop_path' => '/backdrop-inside-out.jpg',
                        'vote_average' => 7.6,
                        'release_date' => '2024-06-14',
                    ],
                ],
            ]);
        }

        if (str_contains($url, 'primary_release_date')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 5,
                        'title' => 'Indonesia Baru',
                        'overview' => 'Rilis lokal.',
                        'poster_path' => '/poster-indonesia-baru.jpg',
                        'backdrop_path' => '/backdrop-indonesia-baru.jpg',
                        'vote_average' => 6.8,
                        'release_date' => '2026-01-10',
                    ],
                ],
            ]);
        }

        if (str_contains($url, 'with_origin_country=ID')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 6,
                        'title' => 'Indonesia Trending',
                        'overview' => 'Populer lokal.',
                        'poster_path' => '/poster-indonesia-trending.jpg',
                        'backdrop_path' => '/backdrop-indonesia-trending.jpg',
                        'vote_average' => 7.1,
                        'release_date' => '2025-10-01',
                    ],
                ],
            ]);
        }

        return Http::response([
            'results' => [
            ],
        ]);
    });

    $response = $this->get(route('movies.index'));

    $response
        ->assertSuccessful()
        ->assertSee('Trending TMDB')
        ->assertSee('Film Baru Rilis')
        ->assertSee('Film Animasi')
        ->assertSee('Interstellar')
        ->assertSee('Inside Out 2')
        ->assertSee('/movies/1', false)
        ->assertDontSee('SEWA 5K')
        ->assertDontSee('BELI 15K')
        ->assertDontSee('Rahasia kartu kedua.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/trending/movie/week'));
    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/now_playing'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'with_genres=16'));
});

test('the application searches movies from tmdb', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake([
        'https://api.themoviedb.org/3/search/movie*' => Http::response([
            'results' => [
                [
                    'id' => 2,
                    'title' => 'Inception',
                    'overview' => 'Mimpi di dalam mimpi.',
                    'poster_path' => '/poster-inception.jpg',
                    'backdrop_path' => '/backdrop-inception.jpg',
                    'vote_average' => 8.8,
                    'release_date' => '2010-07-16',
                ],
            ],
        ]),
        'https://api.themoviedb.org/3/genre/movie/list*' => Http::response([
            'genres' => [],
        ]),
        'https://api.themoviedb.org/3/movie/popular*' => Http::response([
            'results' => [],
        ]),
    ]);

    $response = $this->get(route('movies.index', ['search' => 'inception']));

    $response
        ->assertSuccessful()
        ->assertSee('Inception')
        ->assertSee('Menampilkan hasil pencarian untuk "inception".');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/search/movie'));
});

test('the application falls back to english movie overviews when indonesian overviews are unavailable', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, '/trending/movie/week') && str_contains($url, 'language=id-ID')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 7,
                        'title' => 'Fallback Movie',
                        'overview' => '',
                        'poster_path' => '/poster-fallback.jpg',
                        'backdrop_path' => '/backdrop-fallback.jpg',
                        'vote_average' => 7.9,
                        'release_date' => '2026-05-01',
                    ],
                ],
            ]);
        }

        if (str_contains($url, '/trending/movie/week') && str_contains($url, 'language=en-US')) {
            return Http::response([
                'results' => [
                    [
                        'id' => 7,
                        'title' => 'Fallback Movie',
                        'overview' => 'An English synopsis from TMDB.',
                        'poster_path' => '/poster-fallback.jpg',
                        'backdrop_path' => '/backdrop-fallback.jpg',
                        'vote_average' => 7.9,
                        'release_date' => '2026-05-01',
                    ],
                ],
            ]);
        }

        return Http::response([
            'results' => [],
        ]);
    });

    $response = $this->get(route('movies.index'));

    $response
        ->assertSuccessful()
        ->assertSee('Fallback Movie')
        ->assertSee('An English synopsis from TMDB.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/trending/movie/week')
        && str_contains($request->url(), 'language=id-ID'));

    Http::assertSent(fn ($request) => str_contains($request->url(), '/trending/movie/week')
        && str_contains($request->url(), 'language=en-US'));
});

test('the application displays a movie detail page from tmdb', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake([
        'https://api.themoviedb.org/3/movie/1*' => Http::response([
            'id' => 1,
            'title' => 'Interstellar',
            'overview' => 'Perjalanan lintas galaksi.',
            'tagline' => 'Mankind was born on Earth. It was never meant to die here.',
            'poster_path' => '/poster-interstellar.jpg',
            'backdrop_path' => '/backdrop-interstellar.jpg',
            'vote_average' => 8.7,
            'vote_count' => 12345,
            'release_date' => '2014-11-07',
            'original_language' => 'en',
            'runtime' => 169,
            'genres' => [
                ['name' => 'Adventure'],
                ['name' => 'Science Fiction'],
            ],
            'production_countries' => [
                ['name' => 'United States of America'],
                ['name' => 'United Kingdom'],
            ],
            'production_companies' => [
                ['name' => 'Paramount Pictures'],
                ['name' => 'Legendary Pictures'],
            ],
            'release_dates' => [
                'results' => [
                    [
                        'iso_3166_1' => 'ID',
                        'release_dates' => [
                            ['certification' => '13+'],
                        ],
                    ],
                    [
                        'iso_3166_1' => 'US',
                        'release_dates' => [
                            ['certification' => 'PG-13'],
                        ],
                    ],
                ],
            ],
            'videos' => [
                'results' => [
                    [
                        'key' => 'zSWdZVtXT7E',
                        'site' => 'YouTube',
                        'type' => 'Trailer',
                        'official' => true,
                    ],
                ],
            ],
            'credits' => [
                'cast' => [
                    [
                        'name' => 'Matthew McConaughey',
                        'character' => 'Cooper',
                        'profile_path' => '/profile-matthew.jpg',
                        'order' => 0,
                    ],
                    [
                        'name' => 'Anne Hathaway',
                        'character' => 'Brand',
                        'profile_path' => null,
                        'order' => 1,
                    ],
                ],
                'crew' => [
                    [
                        'job' => 'Director',
                        'name' => 'Christopher Nolan',
                    ],
                    [
                        'job' => 'Writer',
                        'name' => 'Jonathan Nolan',
                    ],
                ],
            ],
        ]),
    ]);

    $response = $this->get(route('movies.show', 1));

    $response
        ->assertSuccessful()
        ->assertSee('Interstellar')
        ->assertSee('Perjalanan lintas galaksi.')
        ->assertSee('Bagikan')
        ->assertSee('/tmdb-images/w500/poster-interstellar.jpg', false)
        ->assertSee('/tmdb-images/w780/backdrop-interstellar.jpg', false)
        ->assertDontSee('SEWA')
        ->assertDontSee('BELI')
        ->assertSee('Trailer')
        ->assertSee('https://www.youtube.com/watch?v=zSWdZVtXT7E', false)
        ->assertSee('Christopher Nolan')
        ->assertSee('Penulis')
        ->assertSee('Jonathan Nolan')
        ->assertSee('Info Film')
        ->assertSee('Batas Umur')
        ->assertSee('13+')
        ->assertSee('Jumlah Vote')
        ->assertSee('12.345 vote')
        ->assertSee('Bahasa Asli')
        ->assertSee('Bahasa Inggris')
        ->assertSee('Negara Produksi')
        ->assertSee('United States of America')
        ->assertSee('Studio')
        ->assertSee('Paramount Pictures')
        ->assertSee('Pemeran')
        ->assertSee('Matthew McConaughey')
        ->assertSee('Cooper')
        ->assertSee('Anne Hathaway')
        ->assertSee('Brand')
        ->assertSee('https://image.tmdb.org/t/p/w185/profile-matthew.jpg', false);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/1')
        && str_contains($request->url(), 'release_dates'));
});

test('the application proxies tmdb images for story templates', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://image.tmdb.org/t/p/w500/poster-interstellar.jpg' => Http::response('fake-image-body', 200, [
            'Content-Type' => 'image/jpeg',
        ]),
    ]);

    $response = $this->get(route('movies.image', [
        'size' => 'w500',
        'path' => 'poster-interstellar.jpg',
    ]));

    $response
        ->assertSuccessful()
        ->assertHeader('Content-Type', 'image/jpeg');

    expect($response->getContent())->toBe('fake-image-body');

    Http::assertSent(fn ($request) => $request->url() === 'https://image.tmdb.org/t/p/w500/poster-interstellar.jpg');
});

test('the application falls back to english overview on movie detail pages', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake(function ($request) {
        $url = $request->url();

        if (str_contains($url, '/movie/9') && str_contains($url, 'language=id-ID')) {
            return Http::response([
                'id' => 9,
                'title' => 'Detail Fallback',
                'overview' => '',
                'tagline' => '',
                'poster_path' => '/poster-detail.jpg',
                'backdrop_path' => '/backdrop-detail.jpg',
                'vote_average' => 7.3,
                'release_date' => '2026-02-14',
                'runtime' => 100,
                'genres' => [
                    ['name' => 'Drama'],
                ],
                'videos' => [
                    'results' => [],
                ],
                'credits' => [
                    'crew' => [],
                ],
            ]);
        }

        if (str_contains($url, '/movie/9') && str_contains($url, 'language=en-US')) {
            return Http::response([
                'id' => 9,
                'title' => 'Detail Fallback',
                'overview' => 'The English detail synopsis from TMDB.',
            ]);
        }

        return Http::response([], 404);
    });

    $response = $this->get(route('movies.show', 9));

    $response
        ->assertSuccessful()
        ->assertSee('Detail Fallback')
        ->assertSee('The English detail synopsis from TMDB.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/9')
        && str_contains($request->url(), 'language=id-ID'));

    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/9')
        && str_contains($request->url(), 'language=en-US'));
});
