#!/usr/bin/env bash
# PHP système (ex. 8.3) peut être sans mbstring → Laravel plante sur mb_split().
# Ce script force le PHP Lampp, qui inclut en général mbstring.
cd "$(dirname "$0")"
exec /opt/lampp/bin/php artisan "$@"
