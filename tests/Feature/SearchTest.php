<?php

use App\Models\MovieList;
use App\Models\User;

describe('search page', function (): void {
    it('renders the search page without query', function (): void {
        $this->get(route('search'))
            ->assertOk()
            ->assertViewIs('search.index')
            ->assertSee('Cari apa hari ini?');
    });

    it('defaults to films tab', function (): void {
        $response = $this->get(route('search'));

        $response->assertOk()
            ->assertViewHas('tab', 'films');
    });

    it('accepts valid tab parameter', function (string $tab): void {
        $this->get(route('search', ['tab' => $tab]))
            ->assertOk()
            ->assertViewHas('tab', $tab);
    })->with(['films', 'users', 'lists']);

    it('falls back to films tab for invalid tab value', function (): void {
        $this->get(route('search', ['tab' => 'invalid']))
            ->assertOk()
            ->assertViewHas('tab', 'films');
    });

    it('passes query to the view', function (): void {
        $this->get(route('search', ['q' => 'inception']))
            ->assertOk()
            ->assertViewHas('query', 'inception');
    });

    it('searches users by name', function (): void {
        $user = User::factory()->create(['name' => 'Budi Santoso', 'username' => 'budisant']);

        $this->get(route('search', ['q' => 'Budi', 'tab' => 'users']))
            ->assertOk()
            ->assertSee('Budi Santoso');
    });

    it('searches users by username', function (): void {
        $user = User::factory()->create(['name' => 'Candra', 'username' => 'chandrafilm']);

        $this->get(route('search', ['q' => 'chandrafilm', 'tab' => 'users']))
            ->assertOk()
            ->assertSee('Candra');
    });

    it('shows no results message when users not found', function (): void {
        $this->get(route('search', ['q' => 'zzznobody123', 'tab' => 'users']))
            ->assertOk()
            ->assertSee('tidak ditemukan');
    });

    it('searches public lists by name', function (): void {
        $owner = User::factory()->create();
        MovieList::factory()->for($owner)->create(['name' => 'Film Noir Terbaik', 'is_public' => true]);

        $this->get(route('search', ['q' => 'Noir', 'tab' => 'lists']))
            ->assertOk()
            ->assertSee('Film Noir Terbaik');
    });

    it('does not return private lists', function (): void {
        $owner = User::factory()->create();
        MovieList::factory()->for($owner)->create(['name' => 'List Pribadi', 'is_public' => false]);

        $this->get(route('search', ['q' => 'Pribadi', 'tab' => 'lists']))
            ->assertOk()
            ->assertDontSee('List Pribadi');
    });

    it('shows no results message when lists not found', function (): void {
        $this->get(route('search', ['q' => 'zzznothingxyz', 'tab' => 'lists']))
            ->assertOk()
            ->assertSee('tidak ditemukan');
    });

    it('is publicly accessible without login', function (): void {
        $this->get(route('search'))
            ->assertOk();
    });

    it('trims whitespace from query', function (): void {
        $user = User::factory()->create(['name' => 'Dewi', 'username' => 'dewimovie']);

        $this->get(route('search', ['q' => '  Dewi  ', 'tab' => 'users']))
            ->assertOk()
            ->assertSee('Dewi');
    });
});
