<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EngagementPoint;
use App\Models\UserVote;
use App\Models\Vote;
use App\Models\VoteOption;
use App\Notifications\VoteResultsNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        $votes = Vote::with(['options', 'creator:id,name,username,avatar'])
            ->withCount('userVotes as total_votes')
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->paginate(10);

        $user = $request->user();

        $data = $votes->getCollection()->map(fn ($v) => $this->formatVote($v, $user));

        return $this->success($data, 'Succès', 200, [
            'current_page' => $votes->currentPage(),
            'last_page' => $votes->lastPage(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $vote = Vote::with(['options', 'creator:id,name,username,avatar'])
            ->withCount('userVotes as total_votes')
            ->findOrFail($id);

        return $this->success($this->formatVote($vote, $request->user()));
    }

    public function castVote(Request $request, int $id)
    {
        $data = $request->validate(['option_id' => 'required|integer']);
        $user = $request->user();

        try {
            return DB::transaction(function () use ($data, $user, $id) {
                $vote = Vote::where('is_published', true)->lockForUpdate()->find($id);
                if ($vote === null) {
                    return $this->error('Vòt introuvable oswa li pa disponib', 404);
                }

                // Date limite optionnelle (null = pas de fin)
                if ($vote->end_date !== null && $vote->end_date->isPast()) {
                    return $this->error('Vòt sa a fèmen', 422);
                }

                if (UserVote::where(['user_id' => $user->id, 'vote_id' => $id])->exists()) {
                    return $this->error('Ou deja vote', 422);
                }

                $option = VoteOption::query()
                    ->where('vote_id', $id)
                    ->whereKey($data['option_id'])
                    ->first();

                if ($option === null) {
                    return $this->error('Opsyon vòt pa valab pou vòt sa a', 422);
                }

                UserVote::create([
                    'user_id' => $user->id,
                    'vote_id' => $id,
                    'vote_option_id' => $option->id,
                ]);

                $option->increment('votes_count');

                $totalVotes = UserVote::where('vote_id', $id)->count();

                Vote::whereKey($id)->update(['total_votes_count' => $totalVotes]);

                $options = VoteOption::where('vote_id', $id)->orderBy('order')->get();
                foreach ($options as $opt) {
                    $pct = $totalVotes > 0
                        ? round(($opt->votes_count / $totalVotes) * 100, 1)
                        : 0;
                    VoteOption::whereKey($opt->id)->update([
                        'percentage' => max(0, min(100, $pct)),
                    ]);
                }

                try {
                    EngagementPoint::create([
                        'user_id' => $user->id,
                        'action' => 'vote_cast',
                        'points' => 10,
                        'pointable_type' => Vote::class,
                        'pointable_id' => $id,
                        'description' => 'Vote enregistré',
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('vote_cast: engagement_points insert failed', [
                        'vote_id' => $id,
                        'user_id' => $user->id,
                        'exception' => $e->getMessage(),
                    ]);
                }

                $vote->refresh();

                if ($vote->end_date !== null && $vote->end_date->isPast()) {
                    try {
                        dispatch(function () use ($vote) {
                            $vote->loadMissing('options');
                            $vote->userVotes()->with('user')->cursor()->each(
                                fn ($uv) => $uv->user?->notify(new VoteResultsNotification($vote))
                            );
                        });
                    } catch (\Throwable $e) {
                        Log::warning('vote_cast: VoteResults notification dispatch failed', [
                            'vote_id' => $id,
                            'exception' => $e->getMessage(),
                        ]);
                    }
                }

                return $this->success(['message' => 'Vwa ou anrejistre!']);
            });
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('vote_cast: SQL failure', ['vote_id' => $id, 'message' => $e->getMessage()]);

            $msg = strtolower($e->getMessage());
            $state = strtoupper((string) ($e->errorInfo[0] ?? ''));
            if ($state === '23000' || str_contains($msg, 'duplicate') || str_contains($msg, 'unique')) {
                return $this->error('Ou deja vote', 422);
            }

            return $this->error('Yon erè te rive pandan anrejistreman vòt ou. Eskize nou, eseye ankò.', 500);
        } catch (\Throwable $e) {
            Log::error('vote_cast: unexpected failure', [
                'vote_id' => $id,
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->error('Yon erè teknik te rive. Eskize nou, eseye ankò.', 500);
        }
    }

    private function formatVote(Vote $v, $user): array
    {
        $myVote = $user
            ? UserVote::where(['user_id' => $user->id, 'vote_id' => $v->id])->first()
            : null;

        $creatorPayload = null;
        if (!$v->is_anonymous && $v->creator) {
            $c = $v->creator;
            $creatorPayload = [
                'id' => $c->id,
                'name' => $c->name,
                'username' => $c->username,
                'avatar' => $c->avatar,
            ];
        }

        return [
            ...$v->toArray(),
            'total_votes' => $v->total_votes ?? 0,
            'my_vote_id' => $myVote?->vote_option_id,
            'creator' => $creatorPayload,
            'gallery' => array_values(is_array($v->gallery) ? $v->gallery : []),
            'options' => $v->options->map(fn ($o) => $o->toArray())->toArray(),
        ];
    }
}
