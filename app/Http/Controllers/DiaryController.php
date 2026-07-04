<?php

namespace App\Http\Controllers;

use App\Http\Requests\DiaryEntryRequest;
use App\Models\DiaryEntry;
use App\Services\User\DiaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DiaryController extends Controller
{
    public function __construct(
        private readonly DiaryService $diaryService,
    ) {}

    public function store(DiaryEntryRequest $request, int $movie): RedirectResponse
    {
        $this->diaryService->createEntry(
            $request->user(),
            $movie,
            $request->validated(),
        );

        return redirect()->back();
    }

    public function update(DiaryEntryRequest $request, DiaryEntry $diary): RedirectResponse
    {
        abort_unless($diary->user_id === $request->user()->id, 403);

        $this->diaryService->updateEntry($diary, $request->validated());

        return redirect()->back();
    }

    public function destroy(Request $request, DiaryEntry $diary): RedirectResponse
    {
        abort_unless($diary->user_id === $request->user()->id, 403);

        $this->diaryService->deleteEntry($diary);

        return redirect()->back();
    }
}
