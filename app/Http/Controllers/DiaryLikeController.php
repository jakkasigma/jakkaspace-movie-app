<?php

namespace App\Http\Controllers;

use App\Models\DiaryEntry;
use App\Services\User\InteractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiaryLikeController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService,
    ) {}

    public function store(Request $request, DiaryEntry $entry): RedirectResponse
    {
        $this->interactionService->likeDiary($request->user(), $entry);

        return redirect()->back();
    }

    public function destroy(Request $request, DiaryEntry $entry): RedirectResponse
    {
        $this->interactionService->unlikeDiary($request->user(), $entry);

        return redirect()->back();
    }
}
