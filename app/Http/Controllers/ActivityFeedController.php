<?php

namespace App\Http\Controllers;

use App\Services\Movie\RecommendationService;
use App\Services\User\ActivityFeedService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ActivityFeedController extends Controller
{
    public function __construct(
        private readonly ActivityFeedService $feedService,
        private readonly RecommendationService $recommendationService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $feed = $this->feedService->getFeed($user);
        $trendingFollowing = $this->recommendationService->getTrendingAmongFollowing($user);

        return view('feed.index', [
            'feed' => $feed,
            'trendingFollowing' => $trendingFollowing,
        ]);
    }
}
