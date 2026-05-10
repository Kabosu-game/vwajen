<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

class NewFollowerNotification extends Notification
{
    public function __construct(private User $follower) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'new_follower',
            'title' => $this->follower->name . ' kòmanse suiv ou',
            'body'  => '@' . $this->follower->username . ' se yon nouvo abonman ou.',
            'url'   => '/profile/' . $this->follower->username,
            'actor' => [
                'id'         => $this->follower->id,
                'name'       => $this->follower->name,
                'username'   => $this->follower->username,
                'avatar_url' => $this->follower->avatar_url,
            ],
        ];
    }
}
