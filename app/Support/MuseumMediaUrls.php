<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Str;

/**
 * URLs des médias musée pour l’API : préfère `/api/v1/media/…`
 * (CORS Laravel) plutôt que `/storage/…` direct (souvent sans CORS → Flutter Web échoue).
 */
final class MuseumMediaUrls
{
    /**
     * @param  mixed  $rawGallery  cast JSON, tableau Filament, ou chaîne JSON
     * @return list<string>
     */
    public static function galleryPaths($rawGallery): array
    {
        if ($rawGallery === null || $rawGallery === '') {
            return [];
        }

        if (is_string($rawGallery)) {
            $decoded = json_decode($rawGallery, true);
            $rawGallery = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($rawGallery)) {
            return [];
        }

        $paths = [];
        foreach ($rawGallery as $item) {
            $p = self::extractPathFromGalleryItem($item);
            if ($p !== null && $p !== '') {
                $paths[] = $p;
            }
        }

        return array_values(array_unique($paths));
    }

    /** @param  mixed  $item */
    private static function extractPathFromGalleryItem($item): ?string
    {
        if (is_string($item)) {
            return $item;
        }
        if (! is_array($item)) {
            return null;
        }

        foreach (['url', 'path', 'name'] as $key) {
            if (! empty($item[$key]) && is_string($item[$key])) {
                return $item[$key];
            }
        }

        return null;
    }

    public static function assetUrl(?string $pathOrUrl): ?string
    {
        if ($pathOrUrl === null || $pathOrUrl === '') {
            return null;
        }

        $raw = trim($pathOrUrl);

        if (Str::startsWith($raw, ['http://', 'https://'])) {
            $parts = parse_url($raw);
            if (! is_array($parts) || empty($parts['path'])) {
                return $raw;
            }
            if (Str::startsWith($parts['path'], '/api/v1/media/')) {
                return $parts['path'].(isset($parts['query']) ? '?'.$parts['query'] : '');
            }
            if (Str::startsWith($parts['path'], '/storage/')) {
                $disk = ltrim(Str::after($parts['path'], '/storage/'), '/');

                return self::publicMediaUrl($disk) ?? $raw;
            }

            return $raw;
        }

        if (Str::startsWith($raw, '//')) {
            return $raw;
        }

        $p = ltrim($raw, '/');
        if (Str::startsWith($p, 'api/v1/media/')) {
            return '/'.$p;
        }
        if (Str::startsWith($p, 'storage/')) {
            return self::publicMediaUrl(Str::after($p, 'storage/')) ?? null;
        }

        return self::publicMediaUrl($p);
    }

    private static function publicMediaUrl(string $diskRelativePath): ?string
    {
        $diskRelativePath = ltrim($diskRelativePath, '/');
        if ($diskRelativePath === '' || Str::contains($diskRelativePath, '..')) {
            return null;
        }

        return '/api/v1/media/'.$diskRelativePath;
    }

    /**
     * @param  mixed  $rawGallery
     * @return list<string>
     */
    public static function galleryPublicUrls($rawGallery): array
    {
        return array_values(array_filter(array_map(
            fn (string $p) => self::assetUrl($p),
            self::galleryPaths($rawGallery)
        )));
    }
}
