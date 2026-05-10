<?php

namespace App\Notifications;

use App\Models\Vote;
use Illuminate\Notifications\Notification;

class VoteResultsNotification extends Notification
{
    public function __construct(private Vote $vote) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        $topOption = $this->vote->options()->orderByDesc('votes_count')->first();

        return [
            'type'  => 'vote_results',
            'title' => 'Rezilta vòt: ' . $this->vote->title,
            'body'  => $topOption ? 'Vencè: ' . $topOption->label . ' (' . $topOption->percentage . '%)' : 'Vòt fèmen.',
            'url'   => '/votes/' . $this->vote->id,
        ];
    }
}
