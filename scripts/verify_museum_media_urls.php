<?php

/**
 * Vérifie la normalisation des URLs médias musée (sans base de données).
 * Usage : php scripts/verify_museum_media_urls.php
 */

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Support\MuseumMediaUrls;

$fail = 0;

function check(string $label, bool $ok): void
{
    global $fail;
    if (! $ok) {
        echo "ECHEC: {$label}\n";
        $fail++;
    }
}

check(
    'relatif museum → /api/v1/media/…',
    MuseumMediaUrls::assetUrl('museum/portraits/x.jpg') === '/api/v1/media/museum/portraits/x.jpg'
);
check(
    'déjà préfixé storage/',
    MuseumMediaUrls::assetUrl('storage/museum/x.jpg') === '/api/v1/media/museum/x.jpg'
);
check(
    'absolu interne /storage/',
    MuseumMediaUrls::assetUrl('http://localhost:8000/storage/museum/x.jpg') === '/api/v1/media/museum/x.jpg'
);
check(
    'galerie JSON chaîne',
    MuseumMediaUrls::galleryPublicUrls('["museum/gallery/a.jpg","museum/gallery/b.jpg"]') === [
        '/api/v1/media/museum/gallery/a.jpg',
        '/api/v1/media/museum/gallery/b.jpg',
    ]
);
check(
    'galerie tableaux objets',
    MuseumMediaUrls::galleryPublicUrls([['url' => 'museum/gallery/c.jpg']]) === ['/api/v1/media/museum/gallery/c.jpg']
);

if ($fail > 0) {
    echo "\n{$fail} erreur(s).\n";
    exit(1);
}

echo "verify_museum_media_urls: OK\n";
exit(0);
