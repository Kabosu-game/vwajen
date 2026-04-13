<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Live;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    // Feed principal - vidéos courtes algorithme
    public function index(Request $request)
    {
        $user = $request->user();

        $videos = Video::published()
            ->with(['user:id,name,username,avatar,is_verified', 'category:id,name,color'])
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->type, fn($q) => $q->where('content_type', $request->type))
            ->orderByDesc('algorithm_score')
            ->orderByDesc('created_at')
            ->paginate(15);

        $items = collect($videos->items())->map(fn($video) => $this->formatVideo($video, $user));

        return response()->json([
            'success' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ],
        ]);
    }

    // Vidéos d'un utilisateur suivi
    public function following(Request $request)
    {
        $user = $request->user();
        $followingIds = $user->following()->pluck('users.id');

        $videos = Video::published()
            ->whereIn('user_id', $followingIds)
            ->with(['user:id,name,username,avatar,is_verified', 'category:id,name,color'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $items = collect($videos->items())->map(fn($video) => $this->formatVideo($video, $user));

        return $this->paginated($videos->setCollection(collect($items)));
    }

    public function show(Request $request, int $id)
    {
        $video = Video::published()
            ->with(['user:id,name,username,avatar,is_verified', 'category'])
            ->findOrFail($id);

        // Increment views
        $video->increment('views_count');

        $user = $request->user();
        return $this->success($this->formatVideo($video, $user, true));
    }

    public function like(Request $request, int $id)
    {
        $video = Video::published()->findOrFail($id);
        $user = $request->user();

        $liked = $video->likedBy()->toggle($user->id);

        if (!empty($liked['attached'])) {
            $video->increment('likes_count');
            $isLiked = true;
        } else {
            $video->decrement('likes_count');
            $isLiked = false;
        }

        return $this->success(['is_liked' => $isLiked, 'likes_count' => $video->fresh()->likes_count]);
    }

    public function comment(Request $request, int $id)
    {
        $video = Video::published()->where('comments_enabled', true)->findOrFail($id);

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $video->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $validated['content'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $video->increment('comments_count');

        return $this->success($comment->load('user:id,name,username,avatar'), 'Commentaire ajouté', 201);
    }

    public function comments(Request $request, int $id)
    {
        $video = Video::published()->findOrFail($id);

        $comments = $video->comments()
            ->whereNull('parent_id')
            ->where('status', 'visible')
            ->with(['user:id,name,username,avatar', 'replies.user:id,name,username,avatar'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return $this->paginated($comments);
    }

    public function lives(Request $request)
    {
        $lives = Live::live()
            ->with(['user:id,name,username,avatar,is_verified'])
            ->orderByDesc('viewers_count')
            ->paginate(10);

        return $this->paginated($lives);
    }

    public function scheduledLives(Request $request)
    {
        $lives = Live::scheduled()
            ->where('scheduled_at', '>', now())
            ->with(['user:id,name,username,avatar,is_verified'])
            ->orderBy('scheduled_at')
            ->paginate(10);

        return $this->paginated($lives);
    }

    private function formatVideo(Video $video, ?object $user, bool $detailed = false): array
    {
        $data = [
            'id' => $video->id,
            'title' => $video->title,
            'video_url' => $video->video_url,
            'thumbnail_url' => $video->thumbnail_url,
            'duration_seconds' => $video->duration_seconds,
            'content_type' => $video->content_type,
            'views_count' => $video->views_count,
            'likes_count' => $video->likes_count,
            'comments_count' => $video->comments_count,
            'shares_count' => $video->shares_count,
            'hashtags' => $video->hashtags,
            'user' => $video->user,
            'category' => $video->category,
            'created_at' => $video->created_at,
            'is_liked' => $user ? $video->likedBy()->where('users.id', $user->id)->exists() : false,
        ];

        if ($detailed) {
            $data['description'] = $video->description;
            $data['comments_enabled'] = $video->comments_enabled;
        }

        return $data;
    }
}
