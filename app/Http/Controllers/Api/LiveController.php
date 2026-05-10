<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Live;
use App\Models\LiveGuestInvitation;
use App\Models\LiveMessage;
use App\Models\LiveViewer;
use App\Models\User;
use App\Notifications\LiveStartedNotification;
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

        // Notifier les followers via bulk send (évite N+1 notifications synchrones)
        $user = $request->user();
        \Illuminate\Support\Facades\Notification::send(
            $user->followers,
            new LiveStartedNotification($user, $live)
        );

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
        $user = $request->user();

        $live->increment('likes_count');

        LiveMessage::create([
            'live_id'   => $id,
            'user_id'   => $user->id,
            'message'   => $user->name . ' voye ' . $data['type'],
            'type'      => 'gift',
            'gift_type' => $data['type'],
        ]);

        return $this->success([
            'gift_type'   => $data['type'],
            'likes_count' => $live->likes_count,
        ], 'Kado voye!');
    }

    public function getMessages(Request $request, int $id)
    {
        $live = Live::findOrFail($id);

        $messages = LiveMessage::where('live_id', $id)
            ->with('user:id,name,username,avatar')
            ->latest()
            ->paginate(50);

        return $this->paginated($messages, 'Messages du live');
    }

    public function sendMessage(Request $request, int $id)
    {
        $data = $request->validate(['message' => 'required|string|max:300']);
        $live = Live::live()->findOrFail($id);

        $msg = LiveMessage::create([
            'live_id' => $id,
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'type'    => 'text',
        ]);

        return $this->success($msg->load('user:id,name,username,avatar'), 201);
    }

    /** Hôte uniquement — invite un utilisateur à monter comme guest co-live. */
    public function inviteGuest(Request $request, int $id)
    {
        $data = $request->validate([
            'username' => 'required|string|max:100',
        ]);

        $live = Live::where('user_id', $request->user()->id)->where('status', 'live')->findOrFail($id);

        $username = ltrim(trim($data['username']), '@');
        $invitee = User::where('username', $username)->where('status', 'active')->first();
        if (! $invitee) {
            return $this->error('Itilizatè pa jwenn oswa inaktif', 404);
        }
        if ($invitee->id === $request->user()->id) {
            return $this->error('Ou pa ka envite tèt ou', 422);
        }

        $duplicate = LiveGuestInvitation::where('live_id', $live->id)
            ->where('invitee_id', $invitee->id)
            ->where('status', LiveGuestInvitation::STATUS_PENDING)
            ->first();
        if ($duplicate) {
            return $this->success($this->formatGuestInvitation($duplicate->load('invitee:id,name,username,avatar')), 'Envitasyon deja aktif');
        }

        $inv = LiveGuestInvitation::create([
            'live_id' => $live->id,
            'inviter_id' => $request->user()->id,
            'invitee_id' => $invitee->id,
            'status' => LiveGuestInvitation::STATUS_PENDING,
        ]);

        return $this->success($this->formatGuestInvitation($inv->load('invitee:id,name,username,avatar')), 'Envitasyon voye', 201);
    }

    /** Hôte uniquement — liste des envitasyon pou live la. */
    public function listGuestInvitations(Request $request, int $id)
    {
        $live = Live::where('user_id', $request->user()->id)->findOrFail($id);

        $rows = LiveGuestInvitation::where('live_id', $live->id)
            ->with('invitee:id,name,username,avatar')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($i) => $this->formatGuestInvitation($i));

        return $this->success($rows, 'Envitasyon yo');
    }

    /** Moun ki envite a — aksepte oubyen refize. */
    public function respondGuestInvitation(Request $request, int $id, int $inviteId)
    {
        $data = $request->validate(['accept' => 'required|boolean']);

        Live::live()->findOrFail($id);

        $inv = LiveGuestInvitation::where('live_id', $id)
            ->where('id', $inviteId)
            ->where('invitee_id', $request->user()->id)
            ->firstOrFail();

        if ($inv->status !== LiveGuestInvitation::STATUS_PENDING) {
            return $this->error('Envitasyon sa a pa aplikab ankò', 409);
        }

        $inv->update([
            'status' => $data['accept'] ? LiveGuestInvitation::STATUS_ACCEPTED : LiveGuestInvitation::STATUS_DECLINED,
        ]);

        return $this->success(
            $this->formatGuestInvitation($inv->fresh()->load('invitee:id,name,username,avatar')),
            $data['accept'] ? 'Ou monte nan live la' : 'Ou refize envitasyon an'
        );
    }

    /** Hôte révoque une invitation (retire depuis le studio). */
    public function revokeGuestInvitation(Request $request, int $id, int $inviteId)
    {
        $live = Live::where('user_id', $request->user()->id)->where('status', 'live')->findOrFail($id);

        $inv = LiveGuestInvitation::where('live_id', $live->id)
            ->where('id', $inviteId)
            ->firstOrFail();

        if ($inv->status === LiveGuestInvitation::STATUS_REVOKED) {
            return $this->success($this->formatGuestInvitation($inv->load('invitee:id,name,username,avatar')), 'Deja revoke');
        }

        $inv->update(['status' => LiveGuestInvitation::STATUS_REVOKED]);

        return $this->success($this->formatGuestInvitation($inv->fresh()->load('invitee:id,name,username,avatar')), 'Retire');
    }

    private function formatGuestInvitation(LiveGuestInvitation $inv): array
    {
        $u = $inv->relationLoaded('invitee') ? $inv->invitee : $inv->invitee()->first();

        return [
            'id' => $inv->id,
            'status' => $inv->status,
            'live_id' => $inv->live_id,
            'created_at' => $inv->created_at?->toIso8601String(),
            'invitee' => $u ? [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username,
                'avatar_url' => $u->avatar_url,
            ] : null,
        ];
    }
}
