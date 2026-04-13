<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        $data = $notifications->getCollection()->map(fn($n) => [
            'id'         => $n->id,
            'type'       => $n->data['type'] ?? 'info',
            'title'      => $n->data['title'] ?? 'Notifikasyon',
            'body'       => $n->data['body'] ?? '',
            'url'        => $n->data['url'] ?? null,
            'is_read'    => $n->read_at !== null,
            'created_at' => $n->created_at->toISOString(),
        ]);

        return $this->success($data, 'Succès', 200, [
            'current_page' => $notifications->currentPage(),
            'last_page'    => $notifications->lastPage(),
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();
        }
        return $this->success(['message' => 'Notifikasyon li.']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->success(['message' => 'Tout notifikasyon li.']);
    }
}
