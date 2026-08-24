<?php

namespace App\Support;

/**
 * Minimal image processing on plain GD.
 *
 * Deliberately dependency-free: GD ships with PHP, so there is no package
 * whose API can change under us. Everything is written out as JPEG, which
 * is what we want for photos anyway.
 */
final class Images
{
    /** @return resource|\GdImage|null */
    protected static function open(string $path, ?string &$mime = null)
    {
        $info = @getimagesize($path);

        if ($info === false) {
            return null;
        }

        $mime = $info['mime'];

        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            default      => null,
        };

        if (! $image) {
            return null;
        }

        // Phone cameras record rotation in EXIF rather than rotating pixels.
        // Without this, portrait photos come out sideways.
        if ($mime === 'image/jpeg' && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $orientation = (int) ($exif['Orientation'] ?? 1);

            $rotated = match ($orientation) {
                3       => imagerotate($image, 180, 0),
                6       => imagerotate($image, -90, 0),
                8       => imagerotate($image, 90, 0),
                default => null,
            };

            if ($rotated) {
                imagedestroy($image);
                $image = $rotated;
            }
        }

        return $image;
    }

    protected static function canvas(int $w, int $h)
    {
        $canvas = imagecreatetruecolor($w, $h);
        // Flatten transparency onto white, since we output JPEG.
        imagefilledrectangle($canvas, 0, 0, $w, $h, imagecolorallocate($canvas, 255, 255, 255));

        return $canvas;
    }

    /** Scale down to fit within $maxWidth. Never scales up. */
    public static function scaleDown(string $source, string $destination, int $maxWidth = 1600, int $quality = 82): bool
    {
        $image = self::open($source);

        if (! $image) {
            return false;
        }

        $w = imagesx($image);
        $h = imagesy($image);

        $scale = min(1.0, $maxWidth / max(1, $w));
        $nw    = max(1, (int) round($w * $scale));
        $nh    = max(1, (int) round($h * $scale));

        $canvas = self::canvas($nw, $nh);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $nw, $nh, $w, $h);

        $ok = imagejpeg($canvas, $destination, $quality);

        imagedestroy($image);
        imagedestroy($canvas);

        return (bool) $ok;
    }

    /** Centre-crop to a square of $size x $size. */
    public static function square(string $source, string $destination, int $size = 600, int $quality = 80): bool
    {
        $image = self::open($source);

        if (! $image) {
            return false;
        }

        $w    = imagesx($image);
        $h    = imagesy($image);
        $side = min($w, $h);
        $sx   = (int) (($w - $side) / 2);
        $sy   = (int) (($h - $side) / 2);

        $canvas = self::canvas($size, $size);
        imagecopyresampled($canvas, $image, 0, 0, $sx, $sy, $size, $size, $side, $side);

        $ok = imagejpeg($canvas, $destination, $quality);

        imagedestroy($image);
        imagedestroy($canvas);

        return (bool) $ok;
    }
}
