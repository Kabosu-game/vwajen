<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;

class PostLikedNotification extends Notification
{
    public function __construct(private User $liker, private Post $post) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'     => 'post_liked',
            'title'    => $this->liker->name . ' renmen piblikasyon ou',
            'body'     => '"' . str($this->post->text)->limit(60) . '"',
            'url'      => '/posts/' . $this->post->id,
            'actor'    => [
                'id'         => $this->liker->id,
                'name'       => $this->liker->name,
                'username'   => $this->liker->username,
                'avatar_url' => $this->liker->avatar_url,
            ],
        ];
    }
}
