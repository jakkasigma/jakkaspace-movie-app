<?php

use App\Models\ListMovie;
use App\Models\MovieList;
use App\Models\User;

describe('list movie controller', function (): void {
    it('requires auth to add a movie to a list', function (): void {
        $list = MovieList::factory()->create();

        $this->post(route('lists.movies.store', [$list, 12345]))
            ->assertRedirectToRoute('login');
    });

    it('adds a movie to a list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('lists.movies.store', [$list, 12345]))
            ->assertRedirect();

        expect(ListMovie::where('movie_list_id', $list->id)->where('tmdb_id', 12345)->exists())
            ->toBeTrue();
    });

    it('only allows owner to add a movie to a list', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list = MovieList::factory()->for($owner)->create();

        $this->actingAs($other)
            ->post(route('lists.movies.store', [$list, 12345]))
            ->assertForbidden();
    });

    it('does not add duplicate movies to the same list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create();

        $this->actingAs($user)->post(route('lists.movies.store', [$list, 12345]));
        $this->actingAs($user)->post(route('lists.movies.store', [$list, 12345]));

        expect(ListMovie::where('movie_list_id', $list->id)->where('tmdb_id', 12345)->count())
            ->toBe(1);
    });

    it('removes a movie from a list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create();
        ListMovie::factory()->for($list, 'movieList')->create(['tmdb_id' => 12345]);

        $this->actingAs($user)
            ->delete(route('lists.movies.destroy', [$list, 12345]))
            ->assertRedirect();

        expect(ListMovie::where('movie_list_id', $list->id)->where('tmdb_id', 12345)->exists())
            ->toBeFalse();
    });

    it('only allows owner to remove a movie from a list', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list = MovieList::factory()->for($owner)->create();
        $entry = ListMovie::factory()->for($list, 'movieList')->create(['tmdb_id' => 12345]);

        $this->actingAs($other)
            ->delete(route('lists.movies.destroy', [$list, 12345]))
            ->assertForbidden();

        expect(ListMovie::find($entry->id))->not->toBeNull();
    });

    it('assigns ascending sort order when adding multiple movies', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create();

        $this->actingAs($user)->post(route('lists.movies.store', [$list, 111]));
        $this->actingAs($user)->post(route('lists.movies.store', [$list, 222]));

        $first = ListMovie::where('movie_list_id', $list->id)->where('tmdb_id', 111)->value('sort_order');
        $second = ListMovie::where('movie_list_id', $list->id)->where('tmdb_id', 222)->value('sort_order');

        expect($second)->toBeGreaterThan($first);
    });
});
