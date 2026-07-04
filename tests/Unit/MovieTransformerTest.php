<?php

use App\Services\Movie\MovieTransformer;

beforeEach(function (): void {
    $this->transformer = new MovieTransformer;
});

describe('transformList', function (): void {
    it('transforms a list of movies to the expected format', function (): void {
        $movies = [
            [
                'id' => 1,
                'title' => 'Test Movie',
                'overview' => 'A test overview.',
                'poster_path' => '/poster.jpg',
                'backdrop_path' => '/backdrop.jpg',
                'vote_average' => 7.5,
                'release_date' => '2024-01-15',
            ],
        ];

        $result = $this->transformer->transformList($movies);

        expect($result)->toHaveCount(1)
            ->and($result[0]['id'])->toBe(1)
            ->and($result[0]['title'])->toBe('Test Movie')
            ->and($result[0]['overview'])->toBe('A test overview.')
            ->and($result[0]['rating'])->toBe('7.5')
            ->and($result[0]['release_year'])->toBe('2024');
    });

    it('uses fallback overview when primary overview is empty', function (): void {
        $movies = [
            ['id' => 1, 'title' => 'Film Tanpa Overview', 'overview' => '', 'release_date' => '2024-01-01'],
        ];

        $fallbackMovies = [
            ['id' => 1, 'title' => 'Film Tanpa Overview', 'overview' => 'English overview fallback.'],
        ];

        $result = $this->transformer->transformList($movies, $fallbackMovies);

        expect($result[0]['overview'])->toBe('English overview fallback.');
    });

    it('uses default message when both overviews are empty', function (): void {
        $movies = [
            ['id' => 1, 'title' => 'Film', 'overview' => '', 'release_date' => '2024-01-01'],
        ];

        $result = $this->transformer->transformList($movies);

        expect($result[0]['overview'])->toBe('Deskripsi film belum tersedia.');
    });
});

describe('transformDetail', function (): void {
    it('transforms movie detail to the expected format', function (): void {
        $movie = [
            'id' => 42,
            'title' => 'Detail Movie',
            'overview' => 'Detail overview.',
            'tagline' => 'A great tagline',
            'poster_path' => null,
            'backdrop_path' => null,
            'vote_average' => 8.0,
            'release_date' => '2023-06-01',
            'runtime' => 135,
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 18, 'name' => 'Drama'],
            ],
            'credits' => ['cast' => [], 'crew' => []],
            'videos' => ['results' => []],
            'release_dates' => ['results' => []],
            'production_countries' => [],
            'production_companies' => [],
        ];

        $result = $this->transformer->transformDetail($movie);

        expect($result['id'])->toBe(42)
            ->and($result['title'])->toBe('Detail Movie')
            ->and($result['tagline'])->toBe('A great tagline')
            ->and($result['runtime'])->toBe('2j 15m')
            ->and($result['genres'])->toBe('Action, Drama')
            ->and($result['story_poster_url'])->toBeNull()
            ->and($result['story_backdrop_url'])->toBeNull();
    });
});

describe('needsOverviewFallback', function (): void {
    it('returns true when overview is empty', function (): void {
        expect($this->transformer->needsOverviewFallback(['overview' => '']))->toBeTrue()
            ->and($this->transformer->needsOverviewFallback(['overview' => '   ']))->toBeTrue()
            ->and($this->transformer->needsOverviewFallback([]))->toBeTrue();
    });

    it('returns false when overview exists', function (): void {
        expect($this->transformer->needsOverviewFallback(['overview' => 'Some overview.']))->toBeFalse();
    });
});

describe('cleanImagePath', function (): void {
    it('cleans a valid image path', function (): void {
        expect($this->transformer->cleanImagePath('/abc123.jpg'))->toBe('abc123.jpg')
            ->and($this->transformer->cleanImagePath('abc123.jpg'))->toBe('abc123.jpg');
    });

    it('returns null for invalid paths', function (): void {
        expect($this->transformer->cleanImagePath('../etc/passwd'))->toBeNull()
            ->and($this->transformer->cleanImagePath(''))->toBeNull()
            ->and($this->transformer->cleanImagePath(null))->toBeNull()
            ->and($this->transformer->cleanImagePath('file.php'))->toBeNull();
    });
});

describe('isAllowedImageSize', function (): void {
    it('allows valid sizes', function (): void {
        foreach (['w185', 'w342', 'w500', 'w780', 'original'] as $size) {
            expect($this->transformer->isAllowedImageSize($size))->toBeTrue();
        }
    });

    it('rejects invalid sizes', function (): void {
        expect($this->transformer->isAllowedImageSize('w9999'))->toBeFalse()
            ->and($this->transformer->isAllowedImageSize('large'))->toBeFalse();
    });
});
