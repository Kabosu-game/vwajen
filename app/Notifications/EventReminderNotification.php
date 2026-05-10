<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification
{
    public function __construct(private Event $event) {}

    public function via(object $notifiable): array { return ['database']; }

    public function toArray(object $notifiable): array
    {
        return [
            'type'  => 'event_reminder',
            'title' => 'Evènman: ' . $this->event->title,
            'body'  => 'Evènman an kòmanse nan ' . $this->event->start_date->diffForHumans(),
            'url'   => '/events/' . $this->event->id,
        ];
    }
}
