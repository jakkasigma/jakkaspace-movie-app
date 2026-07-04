<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Movie\MovieService;
use App\Services\User\InboxService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function __construct(
        private readonly InboxService $inboxService,
        private readonly MovieService $movieService,
    ) {}

    public function index(Request $request): View
    {
        $conversations = $this->inboxService->getConversations($request->user());

        return view('inbox.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, int $conversation): View|RedirectResponse
    {
        $conv = $this->inboxService->findConversation($request->user(), $conversation);

        if ($conv === null) {
            return redirect()->route('inbox')->with('error', 'Percakapan tidak ditemukan.');
        }

        $messages = $this->inboxService->getMessages($conv);

        // For film_share messages, enrich with TMDB data
        $movieCache = [];
        foreach ($messages as $message) {
            if ($message->type === 'film_share' && $message->tmdb_id !== null) {
                $tmdbId = $message->tmdb_id;
                if (! isset($movieCache[$tmdbId])) {
                    [$detail] = $this->movieService->findMovie($tmdbId);
                    $movieCache[$tmdbId] = $detail;
                }
            }
        }

        return view('inbox.show', [
            'conversation' => $conv,
            'messages' => $messages,
            'movieCache' => $movieCache,
        ]);
    }

    /**
     * Start or continue a DM with another user.
     */
    public function startDirect(Request $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id) {
            return redirect()->route('inbox')->with('error', 'Kamu tidak bisa mengirim pesan ke diri sendiri.');
        }

        $conversation = $this->inboxService->findOrCreateDirect($request->user(), $user);

        return redirect()->route('inbox.show', $conversation);
    }

    public function store(Request $request, int $conversation): RedirectResponse
    {
        $conv = $this->inboxService->findConversation($request->user(), $conversation);

        if ($conv === null) {
            return redirect()->route('inbox')->with('error', 'Percakapan tidak ditemukan.');
        }

        $type = $request->string('type', 'text')->value();

        if ($type === 'film_share') {
            $request->validate(['tmdb_id' => ['required', 'integer', 'min:1']]);
            $this->inboxService->sendFilmShare(
                $request->user(),
                $conv,
                (int) $request->integer('tmdb_id'),
            );
        } else {
            $request->validate(['body' => ['required', 'string', 'max:2000']]);
            $this->inboxService->sendText(
                $request->user(),
                $conv,
                $request->string('body')->trim()->value(),
            );
        }

        return redirect()->route('inbox.show', $conv);
    }
}
