<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Live;
use App\Models\Report;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:video,comment,live,user',
            'id' => 'required|integer|min:1',
            'reason' => 'required|in:violence,desinformation,haine,spam,contenu_inapproprie,harcelement,autre',
            'description' => 'nullable|string|max:1000',
        ]);

        $modelClass = $this->resolveModelClass($validated['type']);
        $target = $modelClass::findOrFail($validated['id']);

        $report = Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $target::class,
            'reportable_id' => $target->id,
            'reason' => $validated['reason'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        return $this->success($report, 'Signalement envoyé', 201);
    }

    public function myReports(Request $request)
    {
        $reports = Report::where('reporter_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return $this->paginated($reports, 'Mes signalements');
    }

    private function resolveModelClass(string $type): string
    {
        return match ($type) {
            'video' => Video::class,
            'comment' => Comment::class,
            'live' => Live::class,
            'user' => User::class,
        };
    }
}
