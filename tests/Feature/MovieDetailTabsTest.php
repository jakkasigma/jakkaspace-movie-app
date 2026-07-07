<?php

use App\Models\Review;
use App\Models\ReviewLike;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

const MOVIE_ID = 550;

/**
 * Fake TMDB HTTP response for a movie detail page.
 * Used in every test that hits GET /movies/{id}.
 */
function fakeTmdb(int $movieId = MOVIE_ID): void
{
    Http::fake([
        'api.themoviedb.org/*' => Http::response([
            'id' => $movieId,
            'title' => 'Fight Club',
            'overview' => 'A ticking-time-bomb insomniac and a slippery soap salesman sinopsis.',
            'tagline' => 'Mischief. Mayhem. Soap.',
            'poster_path' => '/poster.jpg',
            'backdrop_path' => '/backdrop.jpg',
            'vote_average' => 8.8,
            'release_date' => '1999-10-15',
            'runtime' => 139,
            'genres' => [['id' => 18, 'name' => 'Drama']],
            'credits' => [
                'cast' => [
                    [
                        'id' => 819,
                        'name' => 'Edward Norton',
                        'character' => 'The Narrator',
                        'profile_path' => '/profile.jpg',
                        'order' => 0,
                    ],
                ],
                'crew' => [
                    [
                        'id' => 7467,
                        'name' => 'David Fincher',
                        'job' => 'Director',
                        'department' => 'Directing',
                        'profile_path' => null,
                    ],
                ],
            ],
            'videos' => ['results' => []],
            'release_dates' => ['results' => []],
            'production_countries' => [],
            'production_companies' => [],
        ], 200),
    ]);
}

beforeEach(function (): void {
    // Clear cache so each test starts fresh — avoids stale movie_detail / community_rating leaks
    Cache::flush();
});

describe('info tab', function (): void {
    it('shows info tab by default', function (): void {
        fakeTmdb();

        $this->get(route('movies.show', MOVIE_ID))
            ->assertOk()
            ->assertSee('sinopsis');
    });

    it('shows info tab when tab=info', function (): void {
        fakeTmdb();

        $this->get(route('movies.show', MOVIE_ID).'?tab=info')
            ->assertOk()
            ->assertSee('Sinopsis')
            ->assertSee('Pemeran');
    });

    it('shows diary and review forms for authenticated users in info tab', function (): void {
        fakeTmdb();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('movies.show', MOVIE_ID).'?tab=info')
            ->assertOk()
            ->assertSee('Tulis Diary')
            ->assertSee('Tulis Review');
    });
});

describe('diskusi tab', function (): void {
    it('shows diskusi tab with paginated reviews', function (): void {
        fakeTmdb();
        Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'body' => 'Review luar biasa untuk film ini.',
            'rating' => 5,
        ]);

        $this->get(route('movies.show', MOVIE_ID).'?tab=diskusi')
            ->assertOk()
            ->assertSee('Review luar biasa untuk film ini.');
    });

    it('sorts diskusi reviews by popular', function (): void {
        fakeTmdb();

        $popular = Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'body' => 'Review paling disukai banyak orang.',
            'rating' => 4,
        ]);

        $recent = Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'body' => 'Review biasa tanpa likes.',
            'rating' => 3,
        ]);

        // Give the popular review 5 likes
        ReviewLike::factory()->count(5)->create(['review_id' => $popular->id]);

        $response = $this->get(route('movies.show', MOVIE_ID).'?tab=diskusi&sort=popular');

        $response->assertOk();

        $content = $response->getContent();
        $posPopular = strpos($content, 'Review paling disukai banyak orang.');
        $posRecent = strpos($content, 'Review biasa tanpa likes.');

        expect($posPopular)->toBeLessThan($posRecent);
    });

    it('sorts diskusi reviews by recent', function (): void {
        fakeTmdb();

        $older = Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'body' => 'Review lama sekali.',
            'rating' => 3,
            'created_at' => now()->subDays(5),
        ]);

        $newer = Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'body' => 'Review terbaru hari ini.',
            'rating' => 4,
            'created_at' => now(),
        ]);

        $response = $this->get(route('movies.show', MOVIE_ID).'?tab=diskusi&sort=recent');

        $response->assertOk();

        $content = $response->getContent();
        $posNewer = strpos($content, 'Review terbaru hari ini.');
        $posOlder = strpos($content, 'Review lama sekali.');

        expect($posNewer)->toBeLessThan($posOlder);
    });

    it('shows login prompt for guests in diskusi tab', function (): void {
        fakeTmdb();

        $this->get(route('movies.show', MOVIE_ID).'?tab=diskusi')
            ->assertOk()
            ->assertSee(route('login'));
    });
});

describe('serupa tab', function (): void {
    it('shows serupa tab', function (): void {
        Http::fake([
            'api.themoviedb.org/*' => Http::response([
                'id' => MOVIE_ID,
                'title' => 'Fight Club',
                'overview' => 'Sinopsis fight club untuk serupa tab.',
                'tagline' => '',
                'poster_path' => '/poster.jpg',
                'backdrop_path' => '/backdrop.jpg',
                'vote_average' => 8.8,
                'release_date' => '1999-10-15',
                'runtime' => 139,
                'genres' => [],
                'credits' => ['cast' => [], 'crew' => []],
                'videos' => ['results' => []],
                'release_dates' => ['results' => []],
                'production_countries' => [],
                'production_companies' => [],
                // similar endpoint also matched — returns results array for listing calls
                'results' => [
                    [
                        'id' => 807,
                        'title' => 'Se7en',
                        'overview' => 'Two detectives.',
                        'poster_path' => '/se7en.jpg',
                        'backdrop_path' => '/bd.jpg',
                        'vote_average' => 8.6,
                        'release_date' => '1995-09-22',
                    ],
                ],
            ], 200),
        ]);

        $this->get(route('movies.show', MOVIE_ID).'?tab=serupa')
            ->assertOk()
            ->assertSee('Serupa');
    });
});

describe('community rating', function (): void {
    it('shows community rating in hero when reviews with rating exist', function (): void {
        fakeTmdb();
        Review::factory()->create([
            'tmdb_id' => MOVIE_ID,
            'rating' => 4,
        ]);

        $this->get(route('movies.show', MOVIE_ID))
            ->assertOk()
            ->assertSee('detail-community-rating', false);
    });

    it('does not show community rating when no reviews exist', function (): void {
        fakeTmdb();

        $this->get(route('movies.show', MOVIE_ID))
            ->assertOk()
            ->assertDontSee('detail-community-rating', false);
    });
});
