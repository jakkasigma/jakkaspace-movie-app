<?php

namespace App\Http\Controllers;

use App\Services\User\UserActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WatchlistController extends Controller
{
    public function __construct(
        private readonly UserActivityService $activityService,
    ) {}

    public function store(Request $request, int $movie): RedirectResponse
    {
        $this->activityService->addToWatchlist($request->user(), $movie);

        return redirect()->back();
    }

    public function destroy(Request $request, int $movie): RedirectResponse
    {
        $this->activityService->removeFromWatchlist($request->user(), $movie);

        return redirect()->back();
    }
}
