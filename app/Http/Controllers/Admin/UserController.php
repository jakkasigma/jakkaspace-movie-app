<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::query();

        $search = $request->string('q')->value();
        if ($search) {
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $filter = $request->string('filter')->value();
        if ($filter === 'banned') {
            $query->where('is_banned', true);
        } elseif ($filter === 'admin') {
            $query->where('is_admin', true);
        } elseif ($filter === 'plus') {
            $query->where('subscription_tier', 'plus');
        }

        $users = $query->latest()->paginate(30);

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'filter' => $filter,
            'totalUsers' => User::count(),
            'totalBanned' => User::where('is_banned', true)->count(),
            'totalAdmin' => User::where('is_admin', true)->count(),
        ]);
    }

    public function ban(User $user): RedirectResponse
    {
        $user->update([
            'is_banned' => true,
            'banned_at' => now(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been banned.");
    }

    public function unban(User $user): RedirectResponse
    {
        $user->update([
            'is_banned' => false,
            'banned_at' => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} has been unbanned.");
    }
}
