<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\EngagementPoint;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with(['creator:id,name,username,avatar,is_verified'])
            ->withCount('comments')
            ->where('is_published', true)
            ->orderByDesc('supports_count')
            ->paginate(10);

        $user = $request->user();
        $supportedIds = $user
            ? $user->supportedProjects()->pluck('projects.id')->toArray()
            : [];

        $data = $projects->getCollection()->map(fn($p) => [
            ...$p->toArray(),
            'is_supported' => in_array($p->id, $supportedIds),
        ]);

        return $this->success($data, 'Succès', 200, [
            'current_page' => $projects->currentPage(),
            'last_page'    => $projects->lastPage(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $project = Project::with('creator:id,name,username,avatar,is_verified')
            ->withCount('comments')
            ->findOrFail($id);

        $user = $request->user();
        $isSupported = $user && $user->supportedProjects()->where('projects.id', $id)->exists();

        return $this->success([...$project->toArray(), 'is_supported' => $isSupported]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:200',
            'description' => 'required|string',
            'cover_url'   => 'nullable|url',
            'images'      => 'nullable|array',
            'video_url'   => 'nullable|url',
        ]);

        $project = $request->user()->projects()->create([
            ...$data,
            'creator_id' => $request->user()->id,
        ]);

        return $this->success($project->fresh(), 201);
    }

    public function support(Request $request, int $id)
    {
        $project = Project::findOrFail($id);
        $user = $request->user();

        if ($user->supportedProjects()->where('projects.id', $id)->exists()) {
            $user->supportedProjects()->detach($id);
            $project->decrement('supports_count');
            return $this->success(['supported' => false]);
        }

        $user->supportedProjects()->attach($id);
        $project->increment('supports_count');

        EngagementPoint::create([
            'user_id' => $user->id,
            'type'    => 'project_supported',
            'points'  => 5,
            'pointable_type' => Project::class,
            'pointable_id'   => $id,
        ]);

        return $this->success(['supported' => true]);
    }
}
