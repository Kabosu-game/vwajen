<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CivicAction;
use App\Models\EngagementPoint;
use Illuminate\Http\Request;

class CivicActionController extends Controller
{
    // Pôle Mobilisation Citoyenne
    public function index(Request $request)
    {
        $actions = CivicAction::whereIn('status', ['planned', 'active'])
            ->with(['creator:id,name,username,avatar'])
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->search, fn($q) => $q->where('title', 'like', '%'.$request->search.'%'))
            ->when($request->lat && $request->lng, fn($q) => $q->select([
                '*',
                \DB::raw('(6371 * acos(cos(radians('.$request->lat.')) * cos(radians(latitude)) * cos(radians(longitude) - radians('.$request->lng.')) + sin(radians('.$request->lat.')) * sin(radians(latitude)))) AS distance')
            ])->orderBy('distance'))
            ->orderBy('action_date')
            ->paginate(12);

        return $this->paginated($actions);
    }

    // Carte interactive - toutes les actions géolocalisées
    public function map(Request $request)
    {
        $actions = CivicAction::whereIn('status', ['planned', 'active'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get(['id', 'title', 'type', 'status', 'location', 'latitude', 'longitude', 'action_date', 'participants_count']);

        return $this->success($actions);
    }

    public function show(int $id)
    {
        $action = CivicAction::with(['creator:id,name,username,avatar'])->findOrFail($id);
        return $this->success($action);
    }

    public function join(Request $request, int $id)
    {
        $action = CivicAction::whereIn('status', ['planned', 'active'])->findOrFail($id);
        $user = $request->user();

        $validated = $request->validate([
            'role' => 'sometimes|in:participant,volunteer',
        ]);

        $participant = $action->participants()->where('users.id', $user->id)->exists();

        if ($participant) {
            return $this->error('Vous participez déjà à cette action', 409);
        }

        $action->participants()->attach($user->id, [
            'role' => $validated['role'] ?? 'participant',
            'status' => 'registered',
        ]);

        $action->increment('participants_count');

        EngagementPoint::create([
            'user_id' => $user->id,
            'points' => 15,
            'action' => 'civic_action_joined',
            'pointable_type' => CivicAction::class,
            'pointable_id' => $action->id,
            'description' => 'Participation à: '.$action->title,
        ]);

        return $this->success(null, 'Vous avez rejoint l\'action citoyenne', 201);
    }

    public function leave(Request $request, int $id)
    {
        $action = CivicAction::findOrFail($id);
        $user = $request->user();

        $action->participants()->detach($user->id);
        $action->decrement('participants_count');

        return $this->success(null, 'Vous avez quitté l\'action');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|in:nettoyage,reunion,action_legale,sensibilisation,petition,autre',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'action_date' => 'required|date|after:now',
            'participants_needed' => 'nullable|integer|min:1',
        ]);

        $action = CivicAction::create([
            ...$validated,
            'created_by' => $request->user()->id,
            'slug' => \Str::slug($validated['title']).'-'.uniqid(),
            'status' => 'planned',
        ]);

        return $this->success($action->load('creator:id,name,username,avatar'), 'Action créée', 201);
    }
}
