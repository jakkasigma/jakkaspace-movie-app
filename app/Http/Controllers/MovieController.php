<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\Movie\MovieService;
use App\Services\Movie\MovieTransformer;
use App\Services\Movie\RecommendationService;
use App\Services\Tmdb\TmdbClient;
use App\Services\User\MovieListService;
use App\Services\User\PinnedMovieService;
use App\Services\User\UserActivityService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Cache;

class MovieController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly TmdbClient $tmdb,
        private readonly MovieTransformer $transformer,
        private readonly UserActivityService $activityService,
        private readonly MovieListService $listService,
        private readonly PinnedMovieService $pinnedService,
        private readonly RecommendationService $recommendationService,
    ) {}

    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->value();

        // Discover filters — aktif kalau ada salah satu parameter ini
        $filters = array_filter([
            'genre' => $request->integer('genre') ?: null,
            'year' => $request->integer('year') ?: null,
            'rating_min' => $request->float('rating_min') ?: null,
            'sort_by' => $request->string('sort_by')->value() ?: null,
            'page' => $request->integer('page', 1),
        ]);

        $isFiltered = ! empty(array_filter(array_diff_key($filters, array_flip(['page', 'sort_by']))));
        $isSearch = $search !== '';

        if ($isSearch) {
            $movieSections = [$this->movieService->searchMovieSection($search)];
            $discoverResult = null;
        } elseif ($isFiltered) {
            $discoverResult = $this->movieService->discoverMovies($filters);
            $movieSections = [];
        } else {
            $movieSections = $this->movieService->homeMovieSections();
            $discoverResult = null;
        }

        $genres = $this->movieService->genres();

        $personalizedMovies = [];
        if (auth()->check() && ! $isSearch && ! $isFiltered) {
            $personalizedMovies = $this->recommendationService->getPersonalizedMovies(auth()->user());
        }

        return view('welcome', [
            'movieSections' => $movieSections,
            'heroMovie' => $this->firstMovieFromSections($movieSections),
            'search' => $search,
            'filters' => $filters,
            'isFiltered' => $isFiltered,
            'discoverResult' => $discoverResult,
            'genres' => $genres,
            'personalizedMovies' => $personalizedMovies,
        ]);
    }

    public function show(int $movie, Request $request): View
    {
        [$movieDetail, $errorMessage] = $this->movieService->findMovie($movie);

        $similarMovies = $movieDetail !== null
            ? $this->movieService->similarMovies($movie)
            : [];

        $user = $request->user();
        $userActivity = $user !== null ? [
            'watch_status' => $this->activityService->getWatchStatus($user, $movie),
            'is_on_watchlist' => $this->activityService->isOnWatchlist($user, $movie),
            'is_favorited' => $this->activityService->isFavorited($user, $movie),
        ] : null;

        $userLists = $user !== null ? $this->listService->getUserLists($user) : collect();
        $movieInLists = [];
        if ($user !== null) {
            foreach ($userLists as $list) {
                $movieInLists[$list->id] = $this->listService->isMovieInList($list, $movie);
            }
        }

        $isPinned = $user !== null ? $this->pinnedService->isPinned($user, $movie) : false;
        $pinnedCount = $user !== null ? $this->pinnedService->getPinnedCount($user) : 0;

        $tab = $request->string('tab', 'info')->value();
        $sort = $request->string('sort', 'recent')->value();

        $communityRating = Cache::remember(
            "movie.community_rating.{$movie}",
            3600,
            fn () => Review::where('tmdb_id', $movie)
                ->selectRaw('ROUND(AVG(rating), 1) as avg_rating, COUNT(*) as review_count')
                ->whereNotNull('rating')
                ->first()
        );

        $reviewCount = Review::where('tmdb_id', $movie)->count();

        $communityReviews = null;
        if ($tab === 'diskusi') {
            $reviewQuery = Review::where('tmdb_id', $movie)
                ->with('user')
                ->withCount(['likes', 'comments']);

            if ($sort === 'popular') {
                $reviewQuery->orderByDesc('likes_count');
            } else {
                $reviewQuery->latest();
            }

            $communityReviews = $reviewQuery->paginate(10)->withQueryString();
        }

        // Genre recommendations — hanya kalau sudah ditonton
        $genreRecommendations = [];
        if ($user !== null && $movieDetail !== null && ($userActivity['watch_status'] === 'watched')) {
            $genreIds = $this->extractGenreIds($movieDetail);
            if (! empty($genreIds)) {
                $genreRecommendations = $this->recommendationService
                    ->getGenreRecommendations($user, $genreIds, $movie);
            }
        }

        return view('movies.show', [
            'movie' => $movieDetail,
            'tab' => $tab,
            'sort' => $sort,
            'reviewCount' => $reviewCount,
            'communityRating' => $communityRating,
            'communityReviews' => $communityReviews,
            'similarMovies' => $similarMovies,
            'userActivity' => $userActivity,
            'userLists' => $userLists,
            'movieInLists' => $movieInLists,
            'isPinned' => $isPinned,
            'pinnedCount' => $pinnedCount,
            'genreRecommendations' => $genreRecommendations,
            'errorMessage' => $errorMessage ?? 'Detail film tidak ditemukan.',
        ]);
    }

    public function image(string $size, string $path): HttpResponse
    {
        $cleanPath = $this->transformer->cleanImagePath($path);

        if ($cleanPath === null || ! $this->transformer->isAllowedImageSize($size)) {
            abort(404);
        }

        $image = $this->tmdb->image($size, $cleanPath);

        if ($image === null) {
            abort(404);
        }

        return response($image['body'])
            ->header('Content-Type', $image['content_type'])
            ->header('Cache-Control', 'public, max-age=604800, immutable')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * @param  array<int, array{movies: array<int, array<string, mixed>>}>  $movieSections
     * @return array<string, mixed>|null
     */
    private function firstMovieFromSections(array $movieSections): ?array
    {
        foreach ($movieSections as $section) {
            if ($section['movies'] !== []) {
                return $section['movies'][0];
            }
        }

        return null;
    }

    /**
     * Extract genre IDs from a transformed movie detail using the genre list.
     *
     * @param  array<string, mixed>  $movieDetail
     * @return array<int, int>
     */
    private function extractGenreIds(array $movieDetail): array
    {
        $genresString = $movieDetail['genres'] ?? '';
        if ($genresString === '') {
            return [];
        }

        $genreNames = array_map('trim', explode(',', $genresString));
        $allGenres = $this->movieService->genres();

        $ids = [];
        foreach ($allGenres as $genre) {
            if (in_array($genre['name'], $genreNames, true)) {
                $ids[] = (int) $genre['id'];
            }
        }

        return $ids;
    }
}
