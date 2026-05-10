<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Sert les fichiers du disque public avec les en-têtes Laravel (dont CORS sur api/*).
 * Indispensable pour Flutter Web : /storage/… est souvent hors middleware et bloque fetch sans CORS.
 */
class PublicMediaController extends Controller
{
    /** @param  string  $path  peut contenir des « / » (chemins musée, etc.) */
    public function serve(string $path): Response
    {
        $path = $this->sanitizePath($path);
        if ($path === '') {
            abort(404);
        }

        if (! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return Storage::disk('public')->response($path);
    }

    private function sanitizePath(string $path): string
    {
        $path = str_replace(["\0", '\\'], '', $path);
        $path = ltrim($path, '/');
        if (Str::contains($path, '..')) {
            return '';
        }

        return $path;
    }
}
