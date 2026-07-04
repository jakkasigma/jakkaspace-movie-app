<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('home + discover merge', function (): void {
    beforeEach(function (): void {
        $this->withoutVite();
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'results' => [],
                'genres' => [],
                'total_pages' => 1,
                'page' => 1,
            ]),
        ]);
    });

    it('renders home page successfully', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertViewIs('welcome');
    });

    it('home view receives genres for filter bar', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertViewHas('genres');
    });

    it('home view receives isFiltered flag', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertViewHas('isFiltered', false);
    });

    it('activates filter mode when genre param provided', function (): void {
        $this->get(route('movies.index', ['genre' => 28]))
            ->assertOk()
            ->assertViewHas('isFiltered', true);
    });

    it('activates filter mode when year param provided', function (): void {
        $this->get(route('movies.index', ['year' => 2023]))
            ->assertOk()
            ->assertViewHas('isFiltered', true);
    });

    it('filter mode passes discoverResult to view', function (): void {
        $this->get(route('movies.index', ['genre' => 28]))
            ->assertOk()
            ->assertViewHas('discoverResult');
    });

    it('discover route redirects to home', function (): void {
        $this->get(route('movies.discover'))
            ->assertRedirectToRoute('movies.index');
    });

    it('discover route forwards genre filter to home', function (): void {
        $this->get(route('movies.discover', ['genre' => 28]))
            ->assertRedirect(route('movies.index', ['genre' => 28]));
    });

    it('discover route forwards year filter to home', function (): void {
        $this->get(route('movies.discover', ['year' => 2022]))
            ->assertRedirect(route('movies.index', ['year' => 2022]));
    });

    it('home shows personalized section for authenticated user with history', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('movies.index'))
            ->assertOk()
            ->assertViewHas('personalizedMovies');
    });

    it('isFiltered is false when only sort_by provided', function (): void {
        $this->get(route('movies.index', ['sort_by' => 'popularity.desc']))
            ->assertOk()
            ->assertViewHas('isFiltered', false);
    });
});
