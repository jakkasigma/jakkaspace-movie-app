<?php

use App\Models\User;
use Illuminate\Support\Facades\Http;

describe('bottom nav', function (): void {
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

    it('renders bottom-nav component on home page', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee('bottom-nav', false);
    });

    it('renders bottom-nav on search page', function (): void {
        $this->get(route('search'))
            ->assertOk()
            ->assertSee('bottom-nav', false);
    });

    it('renders bottom-nav on timeline page', function (): void {
        $this->get(route('timeline'))
            ->assertOk()
            ->assertSee('bottom-nav', false);
    });

    it('bottom-nav contains link to home', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee(route('movies.index'), false);
    });

    it('bottom-nav contains link to search', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee(route('search'), false);
    });

    it('bottom-nav contains link to timeline', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee(route('timeline'), false);
    });

    it('bottom-nav shows login link for guest', function (): void {
        $this->get(route('movies.index'))
            ->assertOk()
            ->assertSee(route('login'), false);
    });

    it('bottom-nav shows profile link for authenticated user', function (): void {
        $user = User::factory()->create(['username' => 'testuser']);

        $this->actingAs($user)
            ->get(route('movies.index'))
            ->assertOk()
            ->assertSee(route('profile.show', 'testuser'), false);
    });

    it('does not render bottom-nav on login page', function (): void {
        $this->get('/login')
            ->assertOk()
            ->assertDontSee('bottom-nav-item', false);
    });
});
