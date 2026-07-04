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
