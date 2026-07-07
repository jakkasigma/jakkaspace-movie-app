<?php

namespace App\Http\Controllers;

use App\Events\ListMessageSent;
use App\Models\ListMessage;
use App\Models\MovieList;
use App\Services\User\MovieListService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ListChatController extends Controller
{
    public function __construct(
        private readonly MovieListService $listService,
    ) {}

    public function show(Request $request, MovieList $list): View
    {
        $user = $request->user();

        abort_unless($this->listService->isMember($list, $user), 403);

        return view('lists.chat-full', [
            'list' => $list,
        ]);
    }

    public function store(Request $request, MovieList $list): RedirectResponse
    {
        $user = $request->user();

        abort_unless($this->listService->isMember($list, $user), 403);

        $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $msg = ListMessage::create([
            'movie_list_id' => $list->id,
            'user_id' => $user->id,
            'message' => $request->input('message'),
        ]);

        broadcast(new ListMessageSent($msg->load('user')))->toOthers();

        return redirect()->back();
    }
}
