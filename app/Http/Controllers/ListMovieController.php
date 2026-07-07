<?php

namespace App\Http\Controllers;

use App\Models\MovieList;
use App\Services\User\MovieListService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListMovieController extends Controller
{
    public function __construct(
        private readonly MovieListService $listService,
    ) {}

    public function store(Request $request, MovieList $list, int $movie): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $user = $request->user();

        $maxPerList = $user->maxMoviesPerList();
        if ($maxPerList === -1) {
            // Plus+ unlimited — no check
        } elseif ($maxPerList > 0 && $list->listMovies()->count() >= $maxPerList) {
            return redirect()->back()->with('error', 'List sudah mencapai batas '.$maxPerList.' film. Upgrade ke Plus+ untuk unlimited.');
        } elseif (! $user->isPlus() && $list->listMovies()->count() >= 50) {
            return redirect()->back()->with('error', 'List gratis maksimal 50 film. Upgrade ke Plus untuk 100 film.');
        }

        $this->listService->addMovie($list, $movie);

        return redirect()->back();
    }

    public function destroy(Request $request, MovieList $list, int $movie): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $this->listService->removeMovie($list, $movie);

        return redirect()->back();
    }
}
