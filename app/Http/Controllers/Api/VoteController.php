<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vote;
use App\Models\VoteOption;
use App\Models\UserVote;
use App\Models\EngagementPoint;
use App\Notifications\VoteResultsNotification;
use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function index(Request $request)
    {
        $votes = Vote::with('options')
            ->withCount('userVotes as total_votes')
            ->where('is_published', true)
            ->orderByDesc('created_at')
            ->paginate(10);

        $user = $request->user();

        $data = $votes->getCollection()->map(fn($v) => $this->formatVote($v, $user));

        return $this->success($data, 'Succès', 200, [
            'current_page' => $votes->currentPage(),
            'last_page'    => $votes->lastPage(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $vote = Vote::with('options')->withCount('userVotes as total_votes')->findOrFail($id);
        return $this->success($this->formatVote($vote, $request->user()));
    }

    public function castVote(Request $request, int $id)
    {
        $data = $request->validate(['option_id' => 'required|integer']);
        $user = $request->user();

        $vote = Vote::where('is_published', true)->findOrFail($id);

        if ($vote->end_date < now()) {
            return $this->error('Vòt sa a fèmen', 422);
        }

        if (UserVote::where(['user_id' => $user->id, 'vote_id' => $id])->exists()) {
            return $this->error('Ou deja vote', 422);
        }

        $option = VoteOption::where('vote_id', $id)->findOrFail($data['option_id']);

        UserVote::create([
            'user_id'        => $user->id,
            'vote_id'        => $id,
            'vote_option_id' => $option->id,
        ]);

        $option->increment('votes_count');

        // Recalculate percentages — reload options to get fresh votes_count after increment
        $totalVotes = UserVote::where('vote_id', $id)->count();
        $vote->load('options');
        $vote->options->each(fn($opt) => $opt->update([
            'percentage' => $totalVotes > 0 ? round(($opt->votes_count / $totalVotes) * 100, 1) : 0,
        ]));

        EngagementPoint::create([
            'user_id'        => $user->id,
            'action'         => 'vote_cast',
            'points'         => 10,
            'pointable_type' => Vote::class,
            'pointable_id'   => $id,
        ]);

        if ($vote->end_date !== null && $vote->end_date->isPast()) {
            dispatch(fn() => $vote->userVotes()->with('user')->cursor()->each(
                fn($uv) => $uv->user->notify(new VoteResultsNotification($vote))
            ));
        }

        return $this->success(['message' => 'Vwa ou anrejistre!']);
    }

    private function formatVote(Vote $v, $user): array
    {
        $myVote = $user ? UserVote::where(['user_id' => $user->id, 'vote_id' => $v->id])->first() : null;
        return [
            ...$v->toArray(),
            'total_votes' => $v->total_votes ?? 0,
            'my_vote_id'  => $myVote?->vote_option_id,
            'options'     => $v->options->map(fn($o) => $o->toArray())->toArray(),
        ];
    }
}
