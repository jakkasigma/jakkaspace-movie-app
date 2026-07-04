<?php

namespace App\Http\Controllers;

use App\Services\User\AnalyticsService;
use App\Services\User\SpaceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        return view('space.index', [
            'user' => $user,
            'stats' => $this->spaceService->getStats($user),
            'recentWatched' => $this->spaceService->getRecentWatched($user),
            'watchlistMovies' => $this->spaceService->getWatchlistMovies($user, 6),
        ]);
    }

    public function analytics(Request $request): View
    {
        $user = $request->user();

        return view('space.analytics', [
            'user' => $user,
            'analytics' => $this->analyticsService->getAnalytics($user),
        ]);
    }

    public function diary(Request $request): View
    {
        $user = $request->user();

        return view('space.diary', [
            'user' => $user,
            'entries' => $this->spaceService->getDiaryEntries($user),
        ]);
    }

    public function history(Request $request): View
    {
        $user = $request->user();
        $status = $request->string('status')->value() ?: null;

        return view('space.history', [
            'user' => $user,
            'entries' => $this->spaceService->getWatchHistoryEntries($user, $status),
            'activeStatus' => $status,
        ]);
    }

    public function watchlist(Request $request): View
    {
        $user = $request->user();

        return view('space.watchlist', [
            'user' => $user,
            'movies' => $this->spaceService->getWatchlistMovies($user),
        ]);
    }

    public function favorites(Request $request): View
    {
        $user = $request->user();

        return view('space.favorites', [
            'user' => $user,
            'movies' => $this->spaceService->getFavoriteMovies($user),
        ]);
    }
}
