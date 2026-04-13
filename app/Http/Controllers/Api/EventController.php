<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventParticipation;
use App\Models\EngagementPoint;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $events = Event::upcoming()
            ->with(['creator:id,name,username,avatar', 'category:id,name,color'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->orderBy('start_date')
            ->paginate(12);

        return $this->paginated($events);
    }

    public function show(int $id)
    {
        $event = Event::where('status', 'published')
            ->with(['creator:id,name,username,avatar', 'category'])
            ->findOrFail($id);

        return $this->success([
            ...$event->toArray(),
            'participants_list' => $event->participants()->limit(5)->get(['users.id', 'name', 'username', 'avatar']),
        ]);
    }

    public function participate(Request $request, int $id)
    {
        $event = Event::where('status', 'published')->findOrFail($id);
        $user = $request->user();

        if ($event->is_full) {
            return $this->error('L\'événement est complet', 422);
        }

        $participation = \App\Models\EventParticipation::firstOrCreate(
            ['user_id' => $user->id, 'event_id' => $id],
            ['status' => 'registered']
        );

        if (!$participation->wasRecentlyCreated) {
            return $this->error('Vous participez déjà à cet événement', 409);
        }

        $event->increment('participants_count');

        EngagementPoint::create([
            'user_id' => $user->id,
            'points' => 10,
            'action' => 'event_registered',
            'pointable_type' => Event::class,
            'pointable_id' => $event->id,
            'description' => 'Inscription à l\'événement: '.$event->title,
        ]);

        return $this->success($participation, 'Inscription confirmée', 201);
    }

    public function cancelParticipation(Request $request, int $id)
    {
        $participation = \App\Models\EventParticipation::where([
            'user_id' => $request->user()->id,
            'event_id' => $id,
        ])->firstOrFail();

        $participation->update(['status' => 'cancelled']);
        Event::find($id)?->decrement('participants_count');

        return $this->success(null, 'Participation annulée');
    }

    public function myEvents(Request $request)
    {
        $user = $request->user();
        $events = $user->participatedEvents()
            ->with('category')
            ->orderByDesc('start_date')
            ->paginate(10);

        return $this->paginated($events);
    }

    public function calendar(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $events = Event::where('status', 'published')
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->with(['category:id,name,color'])
            ->orderBy('start_date')
            ->get(['id', 'title', 'start_date', 'end_date', 'type', 'location', 'category_id']);

        return $this->success($events);
    }
}
