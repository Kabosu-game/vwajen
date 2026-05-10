<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CooperationInterest;
use App\Models\CooperationProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CooperationController extends Controller
{
    private const LISTING_TYPES = ['collaboration', 'job', 'announcement', 'exchange'];

    private const SECTORS = ['business', 'education', 'agriculture', 'sante', 'technologie', 'culture', 'autre'];

    public function index(Request $request)
    {
        $table = (new CooperationProject)->getTable();

        $query = CooperationProject::query()->published();

        if (Schema::hasColumn($table, 'user_id')) {
            $query->with('author');
        }

        if ($request->filled('sector')) {
            $sector = (string) $request->input('sector');
            if (in_array($sector, self::SECTORS, true)) {
                $query->where('sector', $sector);
            }
        }

        if (
            Schema::hasColumn($table, 'listing_type')
            && $request->filled('listing_type')
            && in_array((string) $request->input('listing_type'), self::LISTING_TYPES, true)
        ) {
            $query->where('listing_type', (string) $request->input('listing_type'));
        }

        $projects = $query->orderByDesc('created_at')->paginate(15);

        $data = collect($projects->items())->map(fn ($p) => $this->formatProject($p))->values()->all();

        return $this->success($data, 'Coopération Afrique–Haïti', 200, [
            'current_page' => $projects->currentPage(),
            'last_page' => $projects->lastPage(),
            'total' => $projects->total(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        $table = (new CooperationProject)->getTable();
        $q = CooperationProject::query()->published();
        if (Schema::hasColumn($table, 'user_id')) {
            $q->with('author');
        }
        $project = $q->findOrFail($id);

        return $this->success($this->formatProject($project));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:20000',
            'listing_type' => 'required|in:collaboration,job,announcement,exchange',
            'sector' => 'nullable|in:'.implode(',', self::SECTORS),
            'countries' => 'nullable|string|max:500',
            'organization' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'cover_url' => 'nullable|string|max:2048',
        ]);

        $data['user_id'] = $request->user()->id;
        $data['sector'] = $data['sector'] ?? 'autre';
        $data['is_published'] = false;

        $project = CooperationProject::create($data);
        $project->load('author');

        return $this->success(
            $this->formatProject($project),
            'Annonce enregistrée. Elle sera visible après validation par l’équipe.',
            201
        );
    }

    public function expressInterest(Request $request, int $id)
    {
        $request->validate([
            'message' => 'nullable|string|max:2000',
        ]);

        $project = CooperationProject::published()->findOrFail($id);
        $user = $request->user();

        $interest = CooperationInterest::firstOrCreate(
            ['user_id' => $user->id, 'cooperation_project_id' => $id],
            ['message' => $request->input('message')]
        );

        if ($interest->wasRecentlyCreated) {
            $project->increment('interests_count');
        }

        return $this->success(['message' => 'Intérêt enregistré.']);
    }

    private function formatProject(CooperationProject $p): array
    {
        $table = $p->getTable();
        $base = $p->toArray();

        if (Schema::hasColumn($table, 'user_id')) {
            $p->loadMissing('author');
            $base['author'] = $p->author ? [
                'id' => $p->author->id,
                'name' => $p->author->name,
                'username' => $p->author->username,
                'avatar' => $p->author->avatar,
            ] : null;
        } else {
            $base['author'] = null;
        }

        return $base;
    }
}
