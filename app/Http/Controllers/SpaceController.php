<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DiaryEntry;
use App\Models\SubscriptionPromo;
use App\Services\User\AnalyticsService;
use App\Services\User\SpaceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SpaceController extends Controller
{
    public function __construct(
        private readonly SpaceService $spaceService,
        private readonly AnalyticsService $analyticsService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $promoPopup = null;
        if (! $user->isPlus() && ! session('promo_popup_dismissed', false)) {
            $promoPopup = SubscriptionPromo::with('plan')
                ->where('show_popup', true)
                ->where('is_active', true)
                ->get()
                ->first(fn ($promo) => $promo->isValid());
        }

        return view('space.index', [
            'user' => $user,
            'promoPopup' => $promoPopup,
            'stats' => $this->spaceService->getStats($user),
            'recentWatched' => $this->spaceService->getRecentWatched($user),
            'watchlistMovies' => $this->spaceService->getWatchlistMovies($user, 6),
            'watchlistInfo' => $this->spaceService->getWatchlistInfo($user),
            'recentDiary' => $this->spaceService->getRecentDiaryEntries($user),
            'recentReviews' => $this->spaceService->getRecentReviews($user),
        ]);
    }

    public function analytics(Request $request): View
    {
        $user = $request->user();

        return view('space.analytics', [
            'user' => $user,
            'analytics' => $this->analyticsService->getAnalytics($user),
            'premiumAnalytics' => $user->isPlus()
                ? $this->analyticsService->getPremiumAnalytics($user)
                : null,
        ]);
    }

    public function diary(Request $request): View
    {
        $user = $request->user();
        $year = $request->string('year')->value() ?: null;
        $sort = $request->string('sort', 'newest')->value();

        $diaryData = $this->spaceService->getDiaryEntries($user, $year, $sort);

        return view('space.diary', [
            'user' => $user,
            'entries' => $diaryData['entries'],
            'yearOptions' => $diaryData['yearOptions'],
            'activeYear' => $year,
            'activeSort' => $sort,
            'diaryStats' => $this->spaceService->getDiarySummaryStats($user),
        ]);
    }

    public function editDiary(Request $request, DiaryEntry $entry): View
    {
        abort_unless($entry->user_id === $request->user()->id, 403);

        return view('space.diary-edit', [
            'entry' => $entry,
        ]);
    }

    public function updateDiary(Request $request, DiaryEntry $entry): RedirectResponse
    {
        abort_unless($entry->user_id === $request->user()->id, 403);

        $validated = $request->validate([
            'notes' => 'nullable|string|max:5000',
            'mood' => 'nullable|string|max:50',
            'is_rewatch' => 'nullable|boolean',
            'watched_at' => 'nullable|date',
        ]);

        $entry->update([
            'notes' => $validated['notes'] ?? $entry->notes,
            'mood' => $validated['mood'] ?? $entry->mood,
            'is_rewatch' => (bool) ($validated['is_rewatch'] ?? $entry->is_rewatch),
            'watched_at' => $validated['watched_at'] ?? $entry->watched_at,
        ]);

        return redirect()->route('your-space.diary')->with('success', 'Diary berhasil diperbarui.');
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        $entries = ActivityLog::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        return view('space.history', [
            'user' => $user,
            'entries' => $entries,
        ]);
    }

    public function watchlist(Request $request): View
    {
        $user = $request->user();

        return view('space.watchlist', [
            'user' => $user,
            'movies' => $this->spaceService->getWatchlistMovies($user),
            'watchlistInfo' => $this->spaceService->getWatchlistInfo($user),
        ]);
    }

    public function favorites(Request $request): View
    {
        $user = $request->user();

        return view('space.favorites', [
            'user' => $user,
            'movies' => $this->spaceService->getFavoriteMovies($user),
            'favoritesInfo' => $this->spaceService->getFavoritesInfo($user),
        ]);
    }
}
