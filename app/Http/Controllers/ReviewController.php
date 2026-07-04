<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Review;
use App\Services\User\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(
        private readonly ReviewService $reviewService,
    ) {}

    public function store(ReviewRequest $request, int $movie): RedirectResponse
    {
        $this->reviewService->upsertReview(
            $request->user(),
            $movie,
            $request->validated(),
        );

        return redirect()->back();
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $this->reviewService->deleteReview($review);

        return redirect()->back();
    }
}
