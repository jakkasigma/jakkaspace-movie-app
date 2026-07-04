<?php

namespace App\Services\User;

use App\Models\User;
use App\Notifications\NewFollower;
use Illuminate\Database\Eloquent\Collection;

class FollowService
{
    public function follow(User $follower, User $target): void
    {
        if ($follower->id === $target->id) {
            return;
        }

        $follower->following()->syncWithoutDetaching([$target->id]);

        $target->notify(new NewFollower($follower));
    }

    public function unfollow(User $follower, User $target): void
    {
        $follower->following()->detach($target->id);
    }

    public function isFollowing(User $follower, User $target): bool
    {
        return $follower->following()->where('following_id', $target->id)->exists();
    }

    /**
     * @return Collection<int, User>
     */
    public function getFollowers(User $user): Collection
    {
        return $user->followers()->latest('follows.created_at')->get();
    }

    /**
     * @return Collection<int, User>
     */
    public function getFollowing(User $user): Collection
    {
        return $user->following()->latest('follows.created_at')->get();
    }
}
