<?php

use App\Http\Controllers\DiaryController;
use App\Http\Controllers\DiaryLikeController;
use App\Http\Controllers\DiscoverController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GenreController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\ListChatController;
use App\Http\Controllers\ListMemberController;
use App\Http\Controllers\ListMovieController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieListController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PinnedMovieController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RedeemCodeController;
use App\Http\Controllers\ReviewCommentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewLikeController;
use App\Http\Controllers\ReviewPageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SpaceController;
use App\Http\Controllers\TimelineController;
use App\Http\Controllers\WatchHistoryController;
use App\Http\Controllers\WatchlistController;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [MovieController::class, 'index'])->name('movies.index');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/timeline', [TimelineController::class, 'index'])->name('timeline');
Route::get('/discover', [DiscoverController::class, 'index'])->name('movies.discover');
Route::get('/genre/{genre}', [GenreController::class, 'show'])->whereNumber('genre')->name('movies.genre');
Route::get('/movies/{movie}', [MovieController::class, 'show'])->whereNumber('movie')->name('movies.show');
Route::get('/tmdb-images/{size}/{path}', [MovieController::class, 'image'])
    ->where('path', '.*')
    ->name('movies.image');

// Public list view
Route::get('/lists/{list}', [MovieListController::class, 'show'])->name('lists.show');

// Public review page
Route::get('/reviews/{review}', [ReviewPageController::class, 'show'])->name('reviews.show');

// Midtrans webhook — public, tanpa CSRF
Route::post('/payment/notification', [MidtransController::class, 'notification'])
    ->withoutMiddleware([VerifyCsrfToken::class])
    ->name('payment.notification');

// Public profiles
Route::get('/@{username}', [ProfileController::class, 'show'])->name('profile.show');
Route::get('/@{username}/followers', [FollowController::class, 'followers'])->name('profile.followers');
Route::get('/@{username}/following', [FollowController::class, 'following'])->name('profile.following');

Route::middleware('auth')->group(function () {
    // Movie actions
    Route::post('/movies/{movie}/watch', [WatchHistoryController::class, 'store'])
        ->whereNumber('movie')->name('movies.watch.store');
    Route::delete('/movies/{movie}/watch', [WatchHistoryController::class, 'destroy'])
        ->whereNumber('movie')->name('movies.watch.destroy');

    Route::post('/movies/{movie}/watchlist', [WatchlistController::class, 'store'])
        ->whereNumber('movie')->name('movies.watchlist.store');
    Route::delete('/movies/{movie}/watchlist', [WatchlistController::class, 'destroy'])
        ->whereNumber('movie')->name('movies.watchlist.destroy');

    Route::post('/movies/{movie}/favorite', [FavoriteController::class, 'store'])
        ->whereNumber('movie')->name('movies.favorite.store');
    Route::delete('/movies/{movie}/favorite', [FavoriteController::class, 'destroy'])
        ->whereNumber('movie')->name('movies.favorite.destroy');

    Route::post('/movies/{movie}/diary', [DiaryController::class, 'store'])
        ->whereNumber('movie')->name('movies.diary.store');
    Route::put('/diary/{diary}', [DiaryController::class, 'update'])->name('diary.update');
    Route::delete('/diary/{diary}', [DiaryController::class, 'destroy'])->name('diary.destroy');

    Route::post('/movies/{movie}/review', [ReviewController::class, 'store'])
        ->whereNumber('movie')->name('movies.review.store');
    Route::delete('/review/{review}', [ReviewController::class, 'destroy'])->name('review.destroy');

    // Follow
    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');

    // Review likes & comments
    Route::post('/reviews/{review}/like', [ReviewLikeController::class, 'store'])->name('reviews.like.store');
    Route::delete('/reviews/{review}/like', [ReviewLikeController::class, 'destroy'])->name('reviews.like.destroy');
    Route::post('/reviews/{review}/comments', [ReviewCommentController::class, 'store'])->name('reviews.comments.store');
    Route::delete('/reviews/{review}/comments/{comment}', [ReviewCommentController::class, 'destroy'])->name('reviews.comments.destroy');

    // Diary likes
    Route::post('/diary/{entry}/like', [DiaryLikeController::class, 'store'])->name('diary.like.store');
    Route::delete('/diary/{entry}/like', [DiaryLikeController::class, 'destroy'])->name('diary.like.destroy');

    // Pinned movies
    Route::post('/movies/{movie}/pin', [PinnedMovieController::class, 'store'])
        ->whereNumber('movie')->name('movies.pin.store');
    Route::delete('/movies/{movie}/pin', [PinnedMovieController::class, 'destroy'])
        ->whereNumber('movie')->name('movies.pin.destroy');

    // Activity feed — redirect ke timeline
    Route::get('/feed', function () {
        return redirect()->route('timeline', ['tab' => 'following']);
    })->name('feed');

    // Inbox
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/{conversation}', [InboxController::class, 'show'])->whereNumber('conversation')->name('inbox.show');
    Route::post('/inbox/{conversation}/messages', [InboxController::class, 'store'])->whereNumber('conversation')->name('inbox.messages.store');
    Route::match(['get', 'post'], '/inbox/direct/{user}', [InboxController::class, 'startDirect'])->name('inbox.direct');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    // Your Space
    Route::middleware('verified')->group(function () {
        Route::get('/your-space', [SpaceController::class, 'index'])->name('your-space');
        Route::get('/your-space/analytics', [SpaceController::class, 'analytics'])->name('your-space.analytics');
        Route::get('/your-space/diary', [SpaceController::class, 'diary'])->name('your-space.diary');
        Route::get('/your-space/diary/{entry}/edit', [SpaceController::class, 'editDiary'])->name('your-space.diary.edit');
        Route::put('/your-space/diary/{entry}', [SpaceController::class, 'updateDiary'])->name('your-space.diary.update');
        Route::get('/your-space/history', [SpaceController::class, 'history'])->name('your-space.history');
        Route::get('/your-space/watchlist', [SpaceController::class, 'watchlist'])->name('your-space.watchlist');
        Route::get('/your-space/favorites', [SpaceController::class, 'favorites'])->name('your-space.favorites');

        // Movie Lists
        Route::get('/your-space/lists', [MovieListController::class, 'index'])->name('your-space.lists');
        Route::get('/your-space/lists/create', [MovieListController::class, 'create'])->name('your-space.lists.create');
        Route::post('/your-space/lists', [MovieListController::class, 'store'])->name('your-space.lists.store');
        Route::get('/your-space/lists/{list}/edit', [MovieListController::class, 'edit'])->name('your-space.lists.edit');
        Route::put('/your-space/lists/{list}', [MovieListController::class, 'update'])->name('your-space.lists.update');
        Route::delete('/your-space/lists/{list}', [MovieListController::class, 'destroy'])->name('your-space.lists.destroy');

        // List movies (add/remove)
        Route::post('/lists/{list}/movies/{movie}', [ListMovieController::class, 'store'])
            ->whereNumber('movie')->name('lists.movies.store');
        Route::delete('/lists/{list}/movies/{movie}', [ListMovieController::class, 'destroy'])
            ->whereNumber('movie')->name('lists.movies.destroy');

        // List members
        Route::post('/lists/{list}/join', [ListMemberController::class, 'join'])->name('lists.members.join');
        Route::post('/lists/{list}/leave', [ListMemberController::class, 'leave'])->name('lists.members.leave');
        Route::get('/lists/{list}/members', [ListMemberController::class, 'manage'])->name('lists.members.manage');
        Route::post('/lists/{list}/members/{user}/approve', [ListMemberController::class, 'approve'])->name('lists.members.approve');
        Route::post('/lists/{list}/members/{user}/reject', [ListMemberController::class, 'reject'])->name('lists.members.reject');
        Route::post('/lists/{list}/members/{user}/kick', [ListMemberController::class, 'kick'])->name('lists.members.kick');
        Route::post('/lists/{list}/members/{user}/promote', [ListMemberController::class, 'promote'])->name('lists.members.promote');
        Route::post('/lists/{list}/members/{user}/demote', [ListMemberController::class, 'demote'])->name('lists.members.demote');

        // List invitations
        Route::post('/lists/{list}/invite', [ListMemberController::class, 'invite'])->name('lists.members.invite');
        Route::post('/lists/{list}/invitations/accept', [ListMemberController::class, 'acceptInvite'])->name('lists.invitations.accept');
        Route::post('/lists/{list}/invitations/decline', [ListMemberController::class, 'declineInvite'])->name('lists.invitations.decline');

        // List chat
        Route::get('/lists/{list}/chat', [ListChatController::class, 'show'])->name('lists.chat.show');
        Route::post('/lists/{list}/chat', [ListChatController::class, 'store'])->name('lists.chat.store');

        // Join by code
        Route::post('/lists/join', [ListMemberController::class, 'joinByCode'])->name('lists.join-by-code');
    });

    // Plus subscription
    Route::get('/plus', [PremiumController::class, 'index'])->name('plus');
    Route::post('/plus/subscribe', [PremiumController::class, 'subscribe'])->name('plus.subscribe');
    Route::any('/plus/finish', [MidtransController::class, 'finish'])->name('plus.finish');
    Route::get('/plus/simulate', [PremiumController::class, 'simulatePayment'])->name('plus.simulate');
    Route::put('/plus/theme', [PremiumController::class, 'updateTheme'])->name('plus.theme');
    Route::post('/plus/redeem', [RedeemCodeController::class, 'redeem'])->name('plus.redeem');
    Route::post('/plus/promo/validate', [PremiumController::class, 'validatePromo'])->name('plus.promo.validate');
    Route::get('/plus/history', [PremiumController::class, 'history'])->name('plus.history');
    Route::post('/plus/promo/dismiss', function () {
        session(['promo_popup_dismissed' => true]);

        return response()->json(['ok' => true]);
    })->name('plus.promo.dismiss');
    Route::get('/plus/settings', function (Request $request) {
        return redirect()->route('plus');
    })->name('plus.settings');

    // Export (Plus only)
    Route::get('/export/{type}', [ExportController::class, 'export'])->name('export');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
