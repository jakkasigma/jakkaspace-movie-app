<?php

namespace App\Events;

use App\Models\ListMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class ListMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public function __construct(
        public ListMessage $message,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('list.'.$this->message->movie_list_id),
        ];
    }

    public function broadcastWith(): array
    {
        $user = $this->message->user;

        return [
            'id' => $this->message->id,
            'list_id' => $this->message->movie_list_id,
            'user_id' => $this->message->user_id,
            'type' => $this->message->type,
            'message' => $this->message->message,
            'created_at' => $this->message->created_at->toISOString(),
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
                'is_plus' => $user->isPlus(),
                'theme' => $user->theme ? [
                    'avatar_border_css' => $user->theme->avatar_border_css,
                    'accent_color' => $user->theme->accent_color,
                    'badge_icon' => $user->theme->badge_icon,
                ] : null,
            ],
        ];
    }
}
