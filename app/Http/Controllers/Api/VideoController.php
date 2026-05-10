<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EngagementPoint;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    // Upload d'une vidéo courte
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:200',
            'description'     => 'nullable|string|max:1000',
            'video'           => 'required|file|mimes:mp4,mov,avi,webm|max:102400', // 100MB max
            'thumbnail'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'content_type'    => 'nullable|in:citoyen,solution,terrain,education,autre',
            'category_id'     => 'nullable|integer|exists:categories,id',
            'hashtags'        => 'nullable|array',
            'hashtags.*'      => 'string|max:50',
            'comments_enabled'=> 'nullable|boolean',
        ]);

        $user = $request->user();

        // Stocker la vidéo
        $videoPath = $request->file('video')->store('videos', 'public');
        $thumbnailPath = $request->hasFile('thumbnail')
            ? $request->file('thumbnail')->store('thumbnails', 'public')
            : null;

        $video = Video::create([
            'user_id'          => $user->id,
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'video_url'        => Storage::url($videoPath),
            'thumbnail_url'    => $thumbnailPath ? Storage::url($thumbnailPath) : null,
            'content_type'     => $validated['content_type'] ?? 'citoyen',
            'category_id'      => $validated['category_id'] ?? null,
            'hashtags'         => $validated['hashtags'] ?? [],
            'comments_enabled' => $validated['comments_enabled'] ?? true,
            'status'           => 'published',
            'algorithm_score'  => 50,
        ]);

        EngagementPoint::create([
            'user_id'        => $user->id,
            'points'         => 10,
            'action'         => 'video_uploaded',
            'pointable_type' => Video::class,
            'pointable_id'   => $video->id,
            'description'    => 'Vidéo publiée: ' . $video->title,
        ]);

        return $this->success($video->load('user:id,name,username,avatar'), 'Vidéo publiée', 201);
    }

    // Supprimer une vidéo
    public function destroy(Request $request, int $id)
    {
        $video = Video::where('user_id', $request->user()->id)->findOrFail($id);
        $video->delete();
        return $this->success(null, 'Vidéo supprimée');
    }

    // Mes vidéos
    public function myVideos(Request $request)
    {
        $videos = Video::where('user_id', $request->user()->id)
            ->whereIn('status', ['published', 'processing'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return $this->paginated($videos, 'Mes vidéos');
    }
}
