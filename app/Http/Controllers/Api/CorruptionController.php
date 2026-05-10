<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CorruptionReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CorruptionController extends Controller
{
    /**
     * Déposer une dénonciation anonyme.
     * Aucune donnée d'identification n'est stockée — ni IP, ni user_id.
     *
     * Corps JSON classique, ou multipart/form-data avec pièces jointes optionnelles :
     * proof_image, proof_video, proof_audio (un fichier par champ max).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:administration_publique,niveau_local_communal,projets_institutions,police_justice,education,sante,autre',
            'title' => 'required|string|min:10|max:200',
            'description' => 'required|string|min:30|max:5000',
            'documents' => 'nullable|array|max:10',
            'documents.*' => 'url',
            'location' => 'nullable|string|max:200',
            'period' => 'nullable|string|max:100',
            'proof_image' => 'nullable|file|image|max:10240',
            'proof_video' => 'nullable|file|mimes:mp4,webm,mov,m4v|max:51200',
            'proof_audio' => 'nullable|file|mimes:mp3,m4a,wav,ogg,aac|max:20480',
        ]);

        $documents = [];

        if (! empty($validated['documents'])) {
            foreach ($validated['documents'] as $url) {
                $documents[] = ['type' => 'link', 'url' => $url];
            }
        }

        if ($request->hasFile('proof_image')) {
            $storedPath = $request->file('proof_image')->store('corruption-proofs', 'public');
            $documents[] = [
                'type' => 'image',
                'url' => asset('storage/'.$storedPath),
            ];
        }

        if ($request->hasFile('proof_video')) {
            $storedPath = $request->file('proof_video')->store('corruption-proofs', 'public');
            $documents[] = [
                'type' => 'video',
                'url' => asset('storage/'.$storedPath),
            ];
        }

        if ($request->hasFile('proof_audio')) {
            $storedPath = $request->file('proof_audio')->store('corruption-proofs', 'public');
            $documents[] = [
                'type' => 'audio',
                'url' => asset('storage/'.$storedPath),
            ];
        }

        $token = Str::random(64);

        $report = CorruptionReport::create([
            'category' => $validated['category'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'location' => $validated['location'] ?? null,
            'period' => $validated['period'] ?? null,
            'documents' => count($documents) ? $documents : null,
            'anonymous_token' => $token,
            'status' => 'pending',
        ]);

        return $this->success([
            'tracking_token' => $token,
            'message' => 'Denonsiasyon ou resevwa avèk siksè. Konsève token sa a pou swiv estati denonsiasyon ou.',
            'id' => $report->id,
        ], 'Dénonciation enregistrée', 201);
    }

    /**
     * Suivi anonyme d'une dénonciation via son token.
     */
    public function track(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string|size:64',
        ]);

        $report = CorruptionReport::where('anonymous_token', $validated['token'])->first();

        if (! $report) {
            return $this->error('Token invalide ou dénonciation introuvable', 404);
        }

        return $this->success([
            'id' => $report->id,
            'category' => $report->category,
            'title' => $report->title,
            'status' => $report->status,
            'is_verified' => $report->is_verified,
            'moderator_note' => $report->status !== 'pending' ? $report->moderator_note : null,
            'created_at' => $report->created_at->toISOString(),
        ], 'Statut de la dénonciation');
    }

    /**
     * Liste des dénonciations publiques (sans données sensibles).
     * Affiche uniquement les dénonciations vérifiées pour pression sociale.
     */
    public function index(Request $request)
    {
        $reports = CorruptionReport::where('status', 'verified')
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->select(['id', 'category', 'title', 'location', 'period', 'status', 'is_verified', 'created_at'])
            ->latest()
            ->paginate(20);

        return $this->success($reports->items(), 'Dénonciations vérifiées', 200, [
            'current_page' => $reports->currentPage(),
            'last_page' => $reports->lastPage(),
            'total' => $reports->total(),
        ]);
    }

    /**
     * Statistiques publiques de corruption par catégorie.
     */
    public function stats()
    {
        $stats = CorruptionReport::where('status', 'verified')
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->get()
            ->pluck('total', 'category');

        return $this->success([
            'by_category' => $stats,
            'total_verified' => CorruptionReport::where('status', 'verified')->count(),
            'total_pending' => CorruptionReport::where('status', 'pending')->count(),
        ]);
    }
}
