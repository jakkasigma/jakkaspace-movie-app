<?php

namespace App\Notifications;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ListJoinRequest extends Notification
{
    use Queueable;

    public function __construct(
        public MovieList $list,
        public User $requester,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'list_join_request',
            'list_id' => $this->list->id,
            'list_name' => $this->list->name,
            'actor_id' => $this->requester->id,
            'actor_name' => $this->requester->name,
            'actor_username' => $this->requester->username,
            'actor_avatar' => $this->requester->avatar_url,
            'message' => "{$this->requester->name} ingin bergabung ke list {$this->list->name}",
        ];
    }
}
