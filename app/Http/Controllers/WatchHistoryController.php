<?php

namespace App\Http\Controllers;

use App\Services\User\UserActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    public function __construct(
        private readonly UserActivityService $activityService,
    ) {}

    public function store(Request $request, int $movie): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:watched,watching,dropped'],
        ]);

        $status = $request->string('status')->value();

        match ($status) {
            'watched' => $this->activityService->markAsWatched($request->user(), $movie),
            'watching' => $this->activityService->markAsWatching($request->user(), $movie),
            'dropped' => $this->activityService->markAsDropped($request->user(), $movie),
        };

        return redirect()->back();
    }

    public function destroy(Request $request, int $movie): RedirectResponse
    {
        $this->activityService->removeFromHistory($request->user(), $movie);

        return redirect()->back();
    }
}
