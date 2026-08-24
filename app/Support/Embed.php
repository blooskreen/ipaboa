<?php

namespace App\Support;

/** Turns a YouTube or Vimeo watch link into something an iframe accepts. */
final class Embed
{
    public static function url(?string $raw): ?string
    {
        $url = trim((string) $raw);

        if ($url === '') {
            return null;
        }

        if (preg_match('~youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{6,})~', $url, $m)
            || preg_match('~youtu\.be/([A-Za-z0-9_-]{6,})~', $url, $m)
            || preg_match('~youtube\.com/embed/([A-Za-z0-9_-]{6,})~', $url, $m)
            || preg_match('~youtube\.com/shorts/([A-Za-z0-9_-]{6,})~', $url, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }

        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }

        return null;
    }

    public static function poster(?string $raw): ?string
    {
        $embed = self::url($raw);

        if ($embed && preg_match('~youtube\.com/embed/([A-Za-z0-9_-]+)~', $embed, $m)) {
            return 'https://img.youtube.com/vi/' . $m[1] . '/hqdefault.jpg';
        }

        return null;
    }
}
