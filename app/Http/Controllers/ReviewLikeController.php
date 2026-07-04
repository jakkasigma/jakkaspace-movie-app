<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\User\InteractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewLikeController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService,
    ) {}

    public function store(Request $request, Review $review): RedirectResponse
    {
        $this->interactionService->likeReview($request->user(), $review);

        return redirect()->back();
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        $this->interactionService->unlikeReview($request->user(), $review);

        return redirect()->back();
    }
}
