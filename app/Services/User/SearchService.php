<?php

namespace App\Services\User;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SearchService
{
    private const int PER_PAGE = 20;

    /**
     * Search users by name or username.
     *
     * @return LengthAwarePaginator<User>
     */
    public function searchUsers(string $query): LengthAwarePaginator
    {
        return User::where(function ($q) use ($query): void {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('username', 'like', "%{$query}%");
        })
            ->withCount('followers')
            ->orderByDesc('followers_count')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }

    /**
     * Search public movie lists by name.
     *
     * @return LengthAwarePaginator<MovieList>
     */
    public function searchLists(string $query): LengthAwarePaginator
    {
        return MovieList::where('is_public', true)
            ->where('name', 'like', "%{$query}%")
            ->with('user')
            ->withCount('listMovies')
            ->orderByDesc('list_movies_count')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
    }
}
