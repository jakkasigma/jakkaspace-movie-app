<?php

namespace App\Notifications;

use App\Models\MovieList;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ListInvitation extends Notification
{
    use Queueable;

    public function __construct(
        public MovieList $list,
        public User $inviter,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'list_invitation',
            'list_id' => $this->list->id,
            'list_name' => $this->list->name,
            'inviter_id' => $this->inviter->id,
            'inviter_name' => $this->inviter->name,
            'inviter_avatar' => $this->inviter->avatar_url,
            'message' => "{$this->inviter->name} mengundangmu ke list {$this->list->name}",
        ];
    }
}
