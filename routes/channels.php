<?php

use App\Models\Conversation;
use App\Models\ListMember;
use Illuminate\Support\Facades\Broadcast;

// Only register channels if broadcasting is properly configured
if (config('broadcasting.default') !== 'null' && config('broadcasting.default') !== null) {
    Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
        return (int) $user->id === (int) $id;
    });

    Broadcast::channel('chat.{conversationId}', function ($user, int $conversationId) {
        return Conversation::whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->where('id', $conversationId)
            ->exists();
    });

    Broadcast::channel('list.{listId}', function ($user, int $listId) {
        return ListMember::where('movie_list_id', $listId)
            ->where('user_id', $user->id)
            ->where('status', 'approved')
            ->exists();
    });
}

