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
        $user = $request->user();

        $ownLists = $this->listService->getUserLists($user);
        $joinedLists = MovieList::whereIn('id', $user->listMemberships()->where('status', 'approved')->pluck('movie_list_id'))
            ->withCount('listMovies')
            ->get();

        return view('space.lists.index', [
            'ownLists' => $ownLists,
            'joinedLists' => $joinedLists,
        ]);
    }

    public function create(Request $request): View
    {
        return view('space.lists.create', [
            'canUploadCover' => $request->user()?->canUploadCover() ?? false,
        ]);
    }

    public function store(MovieListRequest $request): RedirectResponse
    {
        $user = $request->user();

        $maxLists = $user->maxLists();
        if ($maxLists > 0 && $user->movieLists()->count() >= $maxLists) {
            return redirect()->back()->with('error', 'Kamu hanya bisa membuat '.$maxLists.' list. Upgrade ke Plus+ untuk lebih banyak.');
        }
        if ($maxLists === 0 && $user->movieLists()->count() >= 1) {
            return redirect()->back()->with('error', 'Free user hanya bisa membuat 1 list. Upgrade ke Plus untuk lebih banyak.');
        }

        if ($request->hasFile('cover_photo') && ! $user->canUploadCover()) {
            return redirect()->back()->with('error', 'Hanya pelanggan Plus+ yang bisa upload cover list.');
        }

        $list = $this->listService->createList($user, $request->validated());

        return redirect()->route('lists.show', $list)->with('success', 'List berhasil dibuat.');
    }

    public function show(Request $request, MovieList $list): View
    {
        $user = $request->user();
        $isOwner = $user !== null && $user->id === $list->user_id;
        $isMember = $user !== null && $this->listService->isMember($list, $user);
        $isPending = $user !== null && $this->listService->isPendingMember($list, $user);
        $userRole = $user !== null ? $this->listService->getMemberRole($list, $user) : null;

        abort_unless($list->is_public || $isOwner || $isMember, 403);

        $tab = $request->query('tab', 'movies');

        $canViewMovies = $isOwner || $isMember;
        $movies = $tab === 'movies' && $canViewMovies ? $this->listService->getMoviesInList($list) : [];

        $members = $this->listService->getMembers($list);
        $messages = $tab === 'chat' && $canViewMovies ? $list->messages()->with('user')->latest()->paginate(30) : collect();
        $following = $user ? $user->following()->get() : collect();

        return view('lists.show', [
            'list' => $list,
            'movies' => $movies,
            'tab' => $tab,
            'isOwner' => $isOwner,
            'isMember' => $isMember,
            'isPending' => $isPending,
            'userRole' => $userRole,
            'canViewMovies' => $canViewMovies,
            'members' => $members,
            'messages' => $messages,
            'following' => $following,
        ]);
    }

    public function edit(Request $request, MovieList $list): View
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        return view('space.lists.edit', [
            'list' => $list,
            'canUploadCover' => $request->user()->canUploadCover(),
        ]);
    }

    public function update(MovieListRequest $request, MovieList $list): RedirectResponse
    {
        abort_unless($list->user_id === $request->user()->id, 403);

        if ($request->hasFile('cover_photo') && ! $request->user()->canUploadCover()) {
            return redirect()->back()->with('error', 'Hanya pelanggan Plus+ yang bisa upload cover list.');
        }

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
