<?php

namespace App\Http\Controllers;

use App\Services\User\TimelineService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TimelineController extends Controller
{
    public function __construct(
        private readonly TimelineService $timelineService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->string('tab', 'all')->value();

        $validTabs = ['all', 'trending', 'following'];
        if (! in_array($tab, $validTabs, true)) {
            $tab = 'all';
        }

        $data = match ($tab) {
            'trending' => $this->timelineService->getTrendingSections(),
            'following' => $this->resolveFollowingTab($request),
            default => $this->timelineService->getAllSections(),
        };

        return view('timeline.index', array_merge(['tab' => $tab], $data));
    }

    /** @return array<string, mixed> */
    private function resolveFollowingTab(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return ['feed' => collect(), 'trending_among_following' => []];
        }

        return $this->timelineService->getFollowingSections($user);
    }
}
