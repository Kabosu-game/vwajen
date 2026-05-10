<?php

namespace App\Notifications;

use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;

class PostCommentedNotification extends Notification
{
    public function __construct(private User $commenter, private Post $post, private string $comment) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'post_commented',
            'title' => $this->commenter->name . ' kòmante piblikasyon ou',
            'body'  => str($this->comment)->limit(80),
            'url'   => '/posts/' . $this->post->id,
            'actor' => [
                'id'         => $this->commenter->id,
                'name'       => $this->commenter->name,
                'username'   => $this->commenter->username,
                'avatar_url' => $this->commenter->avatar_url,
            ],
        ];
    }
}
