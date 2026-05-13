<?php

use Illuminate\Support\Facades\Http;

test('the application returns popular movies from tmdb', function () {
    $this->withoutVite();
    Http::preventStrayRequests();

    Http::fake([
        'https://api.themoviedb.org/3/movie/popular*' => Http::response([
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
            ],
        ]),
    ]);

    $response = $this->get(route('movies.index'));

    $response
        ->assertSuccessful()
        ->assertSee('Interstellar')
        ->assertSee('Film populer dari TMDB langsung dari API.');

    Http::assertSent(fn ($request) => str_contains($request->url(), '/movie/popular'));
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
        ->assertSee('Menampilkan hasil pencarian untuk "inception".', false);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/search/movie'));
});
