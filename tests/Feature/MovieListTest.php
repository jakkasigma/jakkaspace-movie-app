<?php

use App\Models\MovieList;
use App\Models\User;

describe('movie list controller', function (): void {
    it('requires auth to view lists', function (): void {
        $this->get(route('your-space.lists'))
            ->assertRedirectToRoute('login');
    });

    it('shows empty state when user has no lists', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('your-space.lists'))
            ->assertOk()
            ->assertSee('Belum ada list');
    });

    it('shows user lists', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create(['name' => 'Film Favorit 2024']);

        $this->actingAs($user)
            ->get(route('your-space.lists'))
            ->assertOk()
            ->assertSee('Film Favorit 2024');
    });

    it('requires auth to create a list', function (): void {
        $this->post(route('your-space.lists.store'), ['name' => 'Test'])
            ->assertRedirectToRoute('login');
    });

    it('creates a new list', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('your-space.lists.store'), [
                'name' => 'Koleksi Horror',
                'description' => 'Film horror terbaik.',
                'is_public' => true,
            ])
            ->assertRedirect();

        expect(MovieList::where('user_id', $user->id)->where('name', 'Koleksi Horror')->exists())
            ->toBeTrue();
    });

    it('validates required name when creating', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('your-space.lists.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    });

    it('validates name max length', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('your-space.lists.store'), ['name' => str_repeat('a', 101)])
            ->assertSessionHasErrors('name');
    });

    it('updates a list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->public()->create();

        $this->actingAs($user)
            ->put(route('your-space.lists.update', $list), [
                'name' => 'Nama Baru',
                'is_public' => false,
            ])
            ->assertRedirect();

        expect($list->fresh()->name)->toBe('Nama Baru')
            ->and($list->fresh()->is_public)->toBeFalse();
    });

    it('only allows owner to update a list', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list = MovieList::factory()->for($owner)->create();

        $this->actingAs($other)
            ->put(route('your-space.lists.update', $list), ['name' => 'Diubah'])
            ->assertForbidden();
    });

    it('deletes a list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->create();

        $this->actingAs($user)
            ->delete(route('your-space.lists.destroy', $list))
            ->assertRedirect();

        expect(MovieList::find($list->id))->toBeNull();
    });

    it('only allows owner to delete a list', function (): void {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $list = MovieList::factory()->for($owner)->create();

        $this->actingAs($other)
            ->delete(route('your-space.lists.destroy', $list))
            ->assertForbidden();

        expect(MovieList::find($list->id))->not->toBeNull();
    });

    it('allows public list to be viewed by guest', function (): void {
        $list = MovieList::factory()->public()->create();

        $this->get(route('lists.show', $list))
            ->assertOk();
    });

    it('blocks guest from viewing private list', function (): void {
        $list = MovieList::factory()->private()->create();

        $this->get(route('lists.show', $list))
            ->assertForbidden();
    });

    it('allows owner to view their own private list', function (): void {
        $user = User::factory()->create();
        $list = MovieList::factory()->for($user)->private()->create();

        $this->actingAs($user)
            ->get(route('lists.show', $list))
            ->assertOk();
    });
});
