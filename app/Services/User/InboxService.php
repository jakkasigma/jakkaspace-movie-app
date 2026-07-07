<?php

namespace App\Services\User;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class InboxService
{
    /**
     * Get all conversations for the given user, latest activity first.
     * Each conversation includes an `unread_count` attribute.
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
            ->withCount([
                'messages as unread_count' => fn ($q) => $q
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->whereNotExists(function ($q) use ($user) {
                        $q->select(DB::raw(1))
                            ->from('conversation_members')
                            ->whereColumn('conversation_members.conversation_id', 'messages.conversation_id')
                            ->where('conversation_members.user_id', $user->id)
                            ->whereNotNull('conversation_members.last_read_at')
                            ->whereColumn('messages.created_at', '<=', 'conversation_members.last_read_at');
                    }),
            ])
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('conversation_id', 'conversations.id')
                    ->latest()
                    ->limit(1)
            )
            ->get();
    }

    /**
     * Count total unread messages across all conversations for the user.
     */
    public function getUnreadCount(User $user): int
    {
        return Message::selectRaw('COUNT(*)')
            ->join('conversation_members', 'conversation_members.conversation_id', '=', 'messages.conversation_id')
            ->where('conversation_members.user_id', $user->id)
            ->where(function ($q) {
                $q->whereNull('conversation_members.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_members.last_read_at');
            })
            ->value('COUNT(*)') ?? 0;
    }

    /**
     * Mark a conversation as read by the user.
     */
    public function markAsRead(User $user, Conversation $conversation): void
    {
        $conversation->members()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    /**
     * Get a single conversation (only if the user is a member) and mark it as read.
     */
    public function findConversation(User $user, int $conversationId): ?Conversation
    {
        $conv = Conversation::whereHas('members', fn ($q) => $q->where('users.id', $user->id))
            ->where('id', $conversationId)
            ->with(['members', 'messages.sender'])
            ->first();

        if ($conv !== null) {
            $this->markAsRead($user, $conv);
        }

        return $conv;
    }

    /**
     * Find or create a direct conversation between two users.
     */
    public function findOrCreateDirect(User $sender, User $recipient): Conversation
    {
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

            $now = now();

            $conversation->members()->attach([
                $sender->id => ['joined_at' => $now, 'last_read_at' => $now],
                $recipient->id => ['joined_at' => $now, 'last_read_at' => null],
            ]);

            return $conversation;
        });
    }

    public function sendText(User $sender, Conversation $conversation, string $body): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'type' => 'text',
            'body' => $body,
        ]);

        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return $message;
    }

    public function sendFilmShare(User $sender, Conversation $conversation, int $tmdbId): Message
    {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $sender->id,
            'type' => 'film_share',
            'body' => null,
            'tmdb_id' => $tmdbId,
        ]);

        broadcast(new MessageSent($message->load('sender')))->toOthers();

        return $message;
    }

    public function getMessages(Conversation $conversation, int $limit = 50): Collection
    {
        return Message::where('conversation_id', $conversation->id)
            ->with('sender')
            ->oldest()
            ->limit($limit)
            ->get();
    }
}
