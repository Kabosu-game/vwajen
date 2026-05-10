<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MuseumCategory;
use App\Models\MuseumEntry;
use App\Support\MuseumMediaUrls;
use Illuminate\Http\Request;

class MuseumController extends Controller
{
    public function index(Request $request)
    {
        $entries = MuseumEntry::query()
            ->published()
            ->when(
                $request->filled('category_id'),
                fn ($q) => $q->where('museum_category_id', $request->integer('category_id'))
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $s = trim((string) $request->search);

                return $q->where(function ($q2) use ($s) {
                    $q2->where('name', 'like', '%'.$s.'%')
                        ->orWhere('description', 'like', '%'.$s.'%');
                });
            })
            ->with(['category:id,name,slug'])
            ->select([
                'id', 'name', 'slug', 'museum_category_id',
                'portrait_url', 'is_featured', 'views_count', 'created_at',
            ])
            ->orderByDesc('is_featured')
            ->orderBy('name')
            ->paginate(20);

        return $this->success(
            array_map(fn (MuseumEntry $e) => $this->formatEntryBrief($e), $entries->items()),
            'Musée des Révolutionnaires',
            200,
            [
                'current_page' => $entries->currentPage(),
                'last_page' => $entries->lastPage(),
                'total' => $entries->total(),
            ]
        );
    }

    public function show(Request $request, int $id)
    {
        $entry = MuseumEntry::published()
            ->with(['category:id,name,slug'])
            ->findOrFail($id);

        $entry->increment('views_count');

        return $this->success($this->formatEntryDetail($entry), 'Fiche révolutionnaire');
    }

    public function showBySlug(string $slug)
    {
        $entry = MuseumEntry::published()
            ->with(['category:id,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        $entry->increment('views_count');

        return $this->success($this->formatEntryDetail($entry), 'Fiche révolutionnaire');
    }

    public function featured()
    {
        $entries = MuseumEntry::published()
            ->featured()
            ->with(['category:id,name,slug'])
            ->select([
                'id', 'name', 'slug', 'museum_category_id',
                'portrait_url', 'is_featured', 'views_count', 'created_at',
            ])
            ->take(6)
            ->get();

        return $this->success(
            array_map(fn (MuseumEntry $e) => $this->formatEntryBrief($e), $entries->all()),
            'Révolutionnaires mis en avant'
        );
    }

    public function categories()
    {
        $cats = MuseumCategory::query()
            ->active()
            ->ordered()
            ->withCount([
                'museumEntries as entries_count' => fn ($q) => $q->where('is_published', true),
            ])
            ->get(['id', 'name', 'slug', 'sort_order'])
            ->map(fn (MuseumCategory $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'entries_count' => $c->entries_count,
            ]);

        return $this->success($cats->all(), 'Catégories');
    }

    private function assetUrl(?string $pathOrUrl): ?string
    {
        return MuseumMediaUrls::assetUrl($pathOrUrl);
    }

    /** @param  mixed  $rawGallery */
    private function galleryUrls($rawGallery): array
    {
        return MuseumMediaUrls::galleryPublicUrls($rawGallery);
    }

    private function formatCategoryPayload(?MuseumCategory $category): ?array
    {
        if ($category === null) {
            return null;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
        ];
    }

    private function formatEntryBrief(MuseumEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'name' => $entry->name,
            'slug' => $entry->slug,
            'portrait_url' => $this->assetUrl($entry->portrait_url),
            'is_featured' => $entry->is_featured,
            'museum_category_id' => $entry->museum_category_id,
            'category' => $this->formatCategoryPayload($entry->category),
            'views_count' => $entry->views_count,
            'created_at' => $entry->created_at?->toIso8601String(),
        ];
    }

    private function formatEntryDetail(MuseumEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'name' => $entry->name,
            'slug' => $entry->slug,
            'description' => $entry->description,
            'portrait_url' => $this->assetUrl($entry->portrait_url),
            'gallery' => $this->galleryUrls($entry->gallery),
            'is_featured' => $entry->is_featured,
            'museum_category_id' => $entry->museum_category_id,
            'category' => $this->formatCategoryPayload($entry->category),
            'views_count' => $entry->views_count,
            'created_at' => $entry->created_at?->toIso8601String(),
        ];
    }
}
