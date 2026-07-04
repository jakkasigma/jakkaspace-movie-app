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
        $this->pinnedService->addPinnedMovie($request->user(), $movie);

        return redirect()->back();
    }

    public function destroy(Request $request, int $movie): RedirectResponse
    {
        $this->pinnedService->removePinnedMovie($request->user(), $movie);

        return redirect()->back();
    }
}
