<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use App\Models\EngagementPoint;
use App\Notifications\PostCommentedNotification;
use App\Notifications\PostLikedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['user:id,name,username,avatar,is_verified'])
            ->withCount(['comments'])
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
            'text'      => 'nullable|string|max:500',
            'type'      => 'required|in:text,image,video',
            'images'    => 'nullable|array',
            'images.*'  => 'string|max:500',
            'video_url' => 'nullable|url',
            'image'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ]);

        $images = $data['images'] ?? null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('posts', 'public');
            $images = [Storage::disk('public')->url($path)];
        }

        $type = ($images !== null && count($images) > 0) ? 'image' : $data['type'];

        $post = $request->user()->posts()->create([
            'text'      => $data['text'] ?? null,
            'type'      => $type,
            'images'    => $images,
            'video_url' => $data['video_url'] ?? null,
        ]);

        EngagementPoint::create([
            'user_id'        => $request->user()->id,
            'action'         => 'post_created',
            'points'         => 5,
            'pointable_type' => Post::class,
            'pointable_id'   => $post->id,
        ]);

        return $this->success($post, 'Piblikasyon kreye', 201);
    }

    public function like(Request $request, int $id)
    {
        $post = Post::with('user:id,name,username')->findOrFail($id);
        $user = $request->user();

        if ($user->likedPosts()->where('posts.id', $id)->exists()) {
            $user->likedPosts()->detach($id);
            $post->decrement('likes_count');
            return $this->success(['liked' => false]);
        }

        $user->likedPosts()->attach($id);
        $post->increment('likes_count');

        if ($post->user_id !== $user->id) {
            $post->user->notify(new PostLikedNotification($user, $post));
        }

        return $this->success(['liked' => true]);
    }

    public function comment(Request $request, int $id)
    {
        $post = Post::with('user:id,name,username')->findOrFail($id);
        $data = $request->validate([
            'content'   => 'required|string|max:500',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = Comment::create([
            'user_id'          => $request->user()->id,
            'commentable_type' => Post::class,
            'commentable_id'   => $id,
            'content'          => $data['content'],
            'parent_id'        => $data['parent_id'] ?? null,
        ]);

        $post->increment('comments_count');

        if ($post->user_id !== $request->user()->id) {
            $post->user->notify(new PostCommentedNotification($request->user(), $post, $data['content']));
        }

        return $this->success($comment->load('user:id,name,username,avatar'), 201);
    }

    public function getComments(Request $request, int $id)
    {
        $post = Post::findOrFail($id);

        $comments = $post->comments()
            ->whereNull('parent_id')
            ->where('status', 'visible')
            ->with(['user:id,name,username,avatar,is_verified', 'replies.user:id,name,username,avatar'])
            ->latest()
            ->paginate(20);

        return $this->paginated($comments, 'Commentaires');
    }

    public function share(Request $request, int $id)
    {
        $post = Post::findOrFail($id);
        $post->increment('shares_count');
        return $this->success(['shares_count' => $post->shares_count, 'message' => 'Pataj anrejistre!']);
    }
}
