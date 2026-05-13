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
        ->assertSee('Film Indonesia Trending')
        ->assertSee('Film Indonesia Baru Rilis')
        ->assertSee('Film Animasi')
        ->assertSee('Interstellar')
        ->assertSee('Inside Out 2')
        ->assertSee(route('movies.show', 1), false)
        ->assertDontSee('Rahasia kartu kedua.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/trending/movie/week'));
    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/now_playing'));
    Http::assertSent(fn ($request) => str_contains($request->url(), 'with_origin_country=ID'));
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
            'release_date' => '2014-11-07',
            'runtime' => 169,
            'genres' => [
                ['name' => 'Adventure'],
                ['name' => 'Science Fiction'],
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
                'crew' => [
                    [
                        'job' => 'Director',
                        'name' => 'Christopher Nolan',
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
        ->assertSee('SEWA Rp5K')
        ->assertSee('BELI Rp15K')
        ->assertSee('Play Trailer')
        ->assertSee('https://www.youtube.com/watch?v=zSWdZVtXT7E', false)
        ->assertSee('Christopher Nolan');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/1'));
});
