<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Live;
use App\Models\SavedContent;
use App\Models\Video;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $saved = SavedContent::where('user_id', $request->user()->id)
            ->with('saveable')
            ->latest()
            ->paginate(20);

        return $this->paginated($saved, 'Bibliothèque personnelle');
    }

    public function save(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:video,course,live',
            'id' => 'required|integer|min:1',
        ]);

        $modelClass = $this->resolveModelClass($validated['type']);
        $model = $modelClass::findOrFail($validated['id']);

        $saved = SavedContent::firstOrCreate([
            'user_id' => $request->user()->id,
            'saveable_type' => $model::class,
            'saveable_id' => $model->id,
        ]);

        return $this->success($saved, 'Contenu sauvegardé', 201);
    }

    public function unsave(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:video,course,live',
            'id' => 'required|integer|min:1',
        ]);

        $modelClass = $this->resolveModelClass($validated['type']);

        SavedContent::where('user_id', $request->user()->id)
            ->where('saveable_type', $modelClass)
            ->where('saveable_id', $validated['id'])
            ->delete();

        return $this->success(null, 'Contenu retiré de la bibliothèque');
    }

    private function resolveModelClass(string $type): string
    {
        return match ($type) {
            'video' => Video::class,
            'course' => Course::class,
            'live' => Live::class,
        };
    }
}
