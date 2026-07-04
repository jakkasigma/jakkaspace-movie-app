<?php

namespace App\Services\User;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InboxService
{
    /**
     * Get all conversations for the given user, latest activity first.
     *
     * @return Collection<int, Conversation>
     */
    public function getConversations(User $user): Collection
    {
        return Conversation::whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->with([
                'members' => fn ($q) => $q->where('users.id', '!=', $user->id)->limit(1),
                'messages' => fn ($q) => $q->latest()->limit(1),
            ])
            ->withCount('messages')
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();
    }

    /**
     * Get a single conversation (only if the user is a member).
     */
    public function findConversation(User $user, int $conversationId): ?Conversation
    {
        return Conversation::whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->where('id', $conversationId)
            ->with(['members', 'messages.sender'])
            ->first();
    }

    /**
     * Find or create a direct conversation between two users.
     */
    public function findOrCreateDirect(User $sender, User $recipient): Conversation
    {
        // Look for existing DM between these two users
        $existing = Conversation::where('type', 'direct')
            ->whereHas('members', fn ($q) => $q->where('users.id', $sender->id))
            ->whereHas('members', fn ($q) => $q->where('users.id', $recipient->id))
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return DB::transaction(function () use ($sender, $recipient): Conversation {
            $conversation = Conversation::create([
                'type' => 'direct',
                'created_by' => $sender->id,
            ]);

            $conversation->members()->attach([
                $sender->id => ['joined_at' => now()],
                $recipient->id => ['joined_at' => now()],
            ]);

            return $conversation;
        });
    }

    /**
     * Send a text message.
     */
    public function sendText(User $sender, Conversation $conversation, string $body): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'type' => 'text',
            'body' => $body,
        ]);
    }

    /**
     * Share a film in a conversation.
     */
    public function sendFilmShare(User $sender, Conversation $conversation, int $tmdbId): Message
    {
        return Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'type' => 'film_share',
            'body' => null,
            'tmdb_id' => $tmdbId,
        ]);
    }

    /**
     * Get paginated messages for a conversation (oldest first for chat layout).
     *
     * @return Collection<int, Message>
     */
    public function getMessages(Conversation $conversation, int $limit = 50): Collection
    {
        return Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->oldest()
            ->limit($limit)
            ->get();
    }
}
