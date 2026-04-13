<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CooperationProject;
use Illuminate\Http\Request;

class CooperationController extends Controller
{
    public function index(Request $request)
    {
        // CooperationProject model may not exist yet — return graceful empty
        if (!class_exists(CooperationProject::class)) {
            return $this->success([]);
        }

        $projects = CooperationProject::where('is_published', true)
            ->orderByDesc('created_at')
            ->paginate(10);

        return $this->success($projects->items(), 'Succès', 200, [
            'current_page' => $projects->currentPage(),
            'last_page'    => $projects->lastPage(),
        ]);
    }

    public function show(Request $request, int $id)
    {
        if (!class_exists(CooperationProject::class)) {
            return $this->error('Module non disponible', 404);
        }

        $project = CooperationProject::findOrFail($id);
        return $this->success($project);
    }

    public function expressInterest(Request $request, int $id)
    {
        // Log interest — extend when model is ready
        return $this->success(['message' => 'Enterè ou anrejistre!']);
    }
}
