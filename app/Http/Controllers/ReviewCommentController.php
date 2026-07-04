<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewCommentRequest;
use App\Models\Review;
use App\Models\ReviewComment;
use App\Services\User\InteractionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewCommentController extends Controller
{
    public function __construct(
        private readonly InteractionService $interactionService,
    ) {}

    public function store(ReviewCommentRequest $request, Review $review): RedirectResponse
    {
        $this->interactionService->addComment(
            $request->user(),
            $review,
            $request->validated('body'),
        );

        return redirect()->back();
    }

    public function destroy(Request $request, Review $review, ReviewComment $comment): RedirectResponse
    {
        abort_unless($comment->review_id === $review->id, 404);

        $this->interactionService->deleteComment($request->user(), $comment);

        return redirect()->back();
    }
}
