<?php

namespace App\Http\Controllers;

use App\Services\User\PinnedMovieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PinnedMovieController extends Controller
{
    public function __construct(
        private readonly PinnedMovieService $pinnedService,
    ) {}

    public function store(Request $request, int $movie): RedirectResponse
    {
        $user = $request->user();
        $limit = $user->maxPinned();
        $pinnedCount = $user->pinnedMovies()->count();

        if ($pinnedCount >= $limit) {
            return redirect()->back()->with('error', 'Kamu hanya bisa menyematkan '.$limit.' film. Upgrade ke Plus+ untuk 12 film.');
        }

        $this->pinnedService->addPinnedMovie($user, $movie);

        return redirect()->back();
    }

    public function destroy(Request $request, int $movie): RedirectResponse
    {
        $this->pinnedService->removePinnedMovie($request->user(), $movie);

        return redirect()->back();
    }
}
