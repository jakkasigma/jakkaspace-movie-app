<?php

use App\Models\DiaryEntry;
use App\Models\Review;
use App\Models\User;
use App\Notifications\DiaryLiked;
use App\Notifications\NewFollower;
use App\Notifications\ReviewCommented;
use App\Notifications\ReviewLiked;
use Illuminate\Support\Facades\Notification;

describe('notifications', function (): void {
    beforeEach(function (): void {
        Notification::fake();
    });

    it('sends NewFollower notification when a user is followed', function (): void {
        $follower = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($follower)
            ->post(route('users.follow', $target));

        Notification::assertSentTo($target, NewFollower::class);
    });

    it('does not send notification when following self', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.follow', $user));

        Notification::assertNothingSent();
    });

    it('sends ReviewLiked notification when review is liked', function (): void {
        $liker = User::factory()->create();
        $owner = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $this->actingAs($liker)
            ->post(route('reviews.like.store', $review));

        Notification::assertSentTo($owner, ReviewLiked::class);
    });

    it('does not notify self when liking own review', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('reviews.like.store', $review));

        Notification::assertNothingSent();
    });

    it('sends DiaryLiked notification when diary is liked', function (): void {
        $liker = User::factory()->create();
        $owner = User::factory()->create();
        $entry = DiaryEntry::factory()->for($owner)->create();

        $this->actingAs($liker)
            ->post(route('diary.like.store', $entry));

        Notification::assertSentTo($owner, DiaryLiked::class);
    });

    it('does not notify self when liking own diary', function (): void {
        $user = User::factory()->create();
        $entry = DiaryEntry::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('diary.like.store', $entry));

        Notification::assertNothingSent();
    });

    it('sends ReviewCommented notification when comment is added', function (): void {
        $commenter = User::factory()->create();
        $owner = User::factory()->create();
        $review = Review::factory()->for($owner)->create();

        $this->actingAs($commenter)
            ->post(route('reviews.comments.store', $review), ['body' => 'Komentar keren!']);

        Notification::assertSentTo($owner, ReviewCommented::class);
    });

    it('does not notify self when commenting on own review', function (): void {
        $user = User::factory()->create();
        $review = Review::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('reviews.comments.store', $review), ['body' => 'Komentar sendiri']);

        Notification::assertNothingSent();
    });

    it('shows notification page', function (): void {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('notifications'))
            ->assertOk()
            ->assertSee('Notifikasi');
    });

    it('requires auth to view notifications', function (): void {
        $this->get(route('notifications'))
            ->assertRedirectToRoute('login');
    });
});

describe('notification mark as read', function (): void {
    it('marks all notifications as read', function (): void {
        $user = User::factory()->create();
        $follower = User::factory()->create();

        $user->notify(new NewFollower($follower));

        expect($user->unreadNotifications()->count())->toBe(1);

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        expect($user->fresh()->unreadNotifications()->count())->toBe(0);
    });
});
