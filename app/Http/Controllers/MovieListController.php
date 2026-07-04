<?php

namespace App\Http\Controllers;

use App\Http\Requests\MovieListRequest;
use App\Models\MovieList;
use App\Services\User\MovieListService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MovieListController extends Controller
{
    public function __construct(
        private readonly MovieListService $listService,
    ) {}

    public function index(Request $request): View
    {
        $lists = $this->listService->getUserLists($request->user());

        return view('space.lists.index', [
            'lists' => $lists,
        ]);
    }

    public function create(): View
    {
        return view('space.lists.create');
    }

    public function store(MovieListRequest $request): RedirectResponse
    {
        $list = $this->listService->createList($request->user(), $request->validated());

        return redirect()->route('lists.show', $list)->with('success', 'List berhasil dibuat.');
    }

    public function show(MovieList $list): View
    {
        abort_unless($list->is_public || (auth()->check() && auth()->id() === $list->user_id), 403);

        $movies = $this->listService->getMoviesInList($list);

        return view('lists.show', [
            'list' => $list,
            'movies' => $movies,
            'isOwner' => auth()->check() && auth()->id() === $list->user_id,
        ]);
    }

    public function edit(Request $request, MovieList $list): View
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        return view('space.lists.edit', ['list' => $list]);
    }

    public function update(MovieListRequest $request, MovieList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $this->listService->updateList($list, $request->validated());

        return redirect()->route('lists.show', $list)->with('success', 'List berhasil diupdate.');
    }

    public function destroy(Request $request, MovieList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        $this->listService->deleteList($list);

        return redirect()->route('your-space.lists')->with('success', 'List berhasil dihapus.');
    }
}
