<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveViewer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LiveController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:discussion,debat,campagne,information,autre',
            'scheduled_at' => 'nullable|date|after:now',
            'thumbnail' => 'nullable|string|max:255',
        ]);

        $live = Live::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status' => isset($validated['scheduled_at']) ? 'scheduled' : 'live',
            'started_at' => isset($validated['scheduled_at']) ? null : now(),
            'stream_key' => Str::uuid()->toString(),
        ]);

        return $this->success($live, 'Live créé', 201);
    }

    public function start(Request $request, int $id)
    {
        $live = Live::where('user_id', $request->user()->id)->findOrFail($id);
        $live->update([
            'status' => 'live',
            'started_at' => now(),
            'ended_at' => null,
        ]);

        return $this->success($live, 'Live démarré');
    }

    public function end(Request $request, int $id)
    {
        $live = Live::where('user_id', $request->user()->id)->findOrFail($id);
        $live->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return $this->success($live, 'Live terminé');
    }

    public function join(Request $request, int $id)
    {
        $live = Live::live()->findOrFail($id);

        LiveViewer::firstOrCreate(
            ['user_id' => $request->user()->id, 'live_id' => $id, 'left_at' => null],
            ['joined_at' => now()]
        );

        $live->increment('viewers_count');
        $live->update(['peak_viewers' => max($live->peak_viewers, $live->viewers_count)]);

        return $this->success(['viewers_count' => $live->viewers_count, 'peak_viewers' => $live->peak_viewers], 'Entrée dans le live');
    }

    public function leave(Request $request, int $id)
    {
        $live = Live::findOrFail($id);

        LiveViewer::where('user_id', $request->user()->id)
            ->where('live_id', $id)
            ->whereNull('left_at')
            ->update(['left_at' => now()]);

        if ($live->viewers_count > 0) {
            $live->decrement('viewers_count');
        }

        return $this->success(['viewers_count' => $live->fresh()->viewers_count], 'Sortie du live');
    }

    public function show(Request $request, int $id)
    {
        $live = Live::with('user:id,name,username,avatar,is_verified')->findOrFail($id);
        return $this->success($live);
    }

    public function sendGift(Request $request, int $id)
    {
        $data = $request->validate(['type' => 'required|string|max:50']);
        $live = Live::findOrFail($id);

        // Log the gift — extend with gift model when needed
        $live->increment('likes_count');

        return $this->success([
            'gift_type'    => $data['type'],
            'likes_count'  => $live->fresh()->likes_count,
        ], 'Kado voye!');
    }
}
