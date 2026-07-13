<?php

namespace App\Http\Controllers;

use App\Models\MovieList;
use App\Models\User;
use App\Services\Movie\MovieService;
use App\Services\User\InboxService;
use App\Services\User\MovieListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MovieShareController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly InboxService $inboxService,
        private readonly MovieListService $listService,
    ) {}

    public function share(Request $request, int $movie): View
    {
        $user = $request->user();
        $movieData = $this->movieService->findMovie($movie);

        abort_if(! $movieData, 404);

        // Conversations user has (recent first)
        $conversations = $user->conversations()
            ->with(['members', 'lastMessage'])
            ->latest('updated_at')
            ->take(20)
            ->get()
            ->map(function ($conv) use ($user) {
                $other = $conv->members->filter(fn ($m) => $m->id !== $user->id)->first();

                return [
                    'id' => $conv->id,
                    'user_id' => $other?->id,
                    'name' => $other?->name ?? 'Pengguna',
                    'username' => $other?->username,
                    'avatar_url' => $other?->avatar_url,
                    'is_plus' => $other?->isPlus() ?? false,
                    'theme' => $other?->theme,
                ];
            });

        // Joined lists
        $joinedLists = MovieList::whereIn('id', $user->listMemberships()
            ->where('status', 'approved')
            ->pluck('movie_list_id'))
            ->orWhere('user_id', $user->id)
            ->withCount('listMovies')
            ->latest()
            ->get();

        return view('movies.partials.share-modal', [
            'movie' => $movieData,
            'conversations' => $conversations,
            'joinedLists' => $joinedLists,
        ]);
    }

    public function toUser(Request $request, int $movie): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $targetId = $request->integer('user_id');

        if (! $targetId) {
            return response()->json(['error' => 'Pilih user terlebih dahulu.'], 422);
        }

        $target = User::find($targetId);
        if (! $target) {
            return response()->json(['error' => 'User tidak ditemukan.'], 404);
        }

        $movieData = $this->movieService->findMovie($movie);
        $conversation = $this->inboxService->findOrCreateDirect($user, $target);
        $this->inboxService->sendFilmShare($user, $conversation, $movie, $movieData['title'] ?? null);

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('inbox.show', $conversation)]);
        }

        return redirect()->route('inbox.show', $conversation)->with('success', 'Film berhasil dibagikan.');
    }

    public function toList(Request $request, int $movie): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $listId = $request->integer('list_id');
        $movieData = $this->movieService->findMovie($movie);

        if (! $listId) {
            return response()->json(['error' => 'Pilih list terlebih dahulu.'], 422);
        }

        $list = MovieList::find($listId);
        if (! $list) {
            return response()->json(['error' => 'List tidak ditemukan.'], 404);
        }

        $isMember = $this->listService->isMember($list, $user) || $list->user_id === $user->id;
        if (! $isMember) {
            return response()->json(['error' => 'Kamu bukan anggota list ini.'], 403);
        }

        $list->messages()->create([
            'user_id' => $user->id,
            'message' => $movieData['title'] ?? 'Film',
            'type' => 'film_share',
            'tmdb_id' => $movie,
            'metadata' => [
                'title' => $movieData['title'] ?? null,
                'poster_url' => $movieData['poster_url'] ?? null,
                'release_year' => $movieData['release_year'] ?? null,
                'rating' => $movieData['rating'] ?? null,
            ],
        ]);

        if ($request->wantsJson()) {
            return response()->json(['redirect' => route('lists.chat.show', $list)]);
        }

        return redirect()->route('lists.chat.show', $list)->with('success', 'Film berhasil dibagikan ke list.');
    }
}
