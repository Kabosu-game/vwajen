<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\EngagementPoint;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['user:id,name,username,avatar,is_verified'])
            ->withCount(['comments', 'shares'])
            ->latest()
            ->paginate(15);

        $user = $request->user();
        $likedIds = $user
            ? $user->likedPosts()->pluck('posts.id')->toArray()
            : [];

        $data = $posts->getCollection()->map(fn($p) => [
            ...$p->toArray(),
            'likes_count' => $p->likes_count ?? 0,
            'is_liked'    => in_array($p->id, $likedIds),
        ]);

        return $this->success($data, 'Succès', 200, [
            'current_page' => $posts->currentPage(),
            'last_page'    => $posts->lastPage(),
            'total'        => $posts->total(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $post = Post::with('user:id,name,username,avatar,is_verified')->findOrFail($id);
        $user = $request->user();
        $isLiked = $user && $user->likedPosts()->where('posts.id', $id)->exists();

        return $this->success([...$post->toArray(), 'is_liked' => $isLiked]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'text'     => 'nullable|string|max:500',
            'type'     => 'required|in:text,image,video',
            'images'   => 'nullable|array',
            'video_url'=> 'nullable|url',
        ]);

        $post = $request->user()->posts()->create($data);

        EngagementPoint::create([
            'user_id'  => $request->user()->id,
            'type'     => 'post_created',
            'points'   => 5,
            'pointable_type' => Post::class,
            'pointable_id'   => $post->id,
        ]);

        return $this->success($post->fresh(), 201);
    }

    public function like(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $user = $request->user();

        if ($user->likedPosts()->where('posts.id', $id)->exists()) {
            $user->likedPosts()->detach($id);
            $post->decrement('likes_count');
            return $this->success(['liked' => false]);
        }

        $user->likedPosts()->attach($id);
        $post->increment('likes_count');
        return $this->success(['liked' => true]);
    }

    public function comment(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $data = $request->validate(['content' => 'required|string|max:500']);

        $comment = Comment::create([
            'user_id'          => $request->user()->id,
            'commentable_type' => Post::class,
            'commentable_id'   => $id,
            'content'          => $data['content'],
        ]);

        $post->increment('comments_count');

        return $this->success($comment->load('user:id,name,username,avatar'), 201);
    }
}
