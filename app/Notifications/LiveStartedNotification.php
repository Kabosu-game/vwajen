<?php

namespace App\Notifications;

use App\Models\Live;
use App\Models\User;
use Illuminate\Notifications\Notification;

class LiveStartedNotification extends Notification
{
    public function __construct(private User $streamer, private Live $live) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'live_started',
            'title' => $this->streamer->name . ' kòmanse yon live!',
            'body'  => $this->live->title,
            'url'   => '/lives/' . $this->live->id,
            'actor' => [
                'id'         => $this->streamer->id,
                'name'       => $this->streamer->name,
                'username'   => $this->streamer->username,
                'avatar_url' => $this->streamer->avatar_url,
            ],
        ];
    }
}
