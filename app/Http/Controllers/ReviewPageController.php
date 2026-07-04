<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\Movie\MovieService;
use App\Services\User\InteractionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReviewPageController extends Controller
{
    public function __construct(
        private readonly MovieService $movieService,
        private readonly InteractionService $interactionService,
    ) {}

    public function show(Request $request, Review $review): View
    {
        $review->loadCount('likes');
        $review->load(['user', 'comments.user']);

        [$movie] = $this->movieService->findMovie($review->tmdb_id);

        $user = $request->user();
        $isLiked = $user !== null ? $this->interactionService->isReviewLiked($user, $review) : false;

        return view('reviews.show', [
            'review' => $review,
            'movie' => $movie,
            'isLiked' => $isLiked,
            'isOwner' => $user?->id === $review->user_id,
        ]);
    }
}
