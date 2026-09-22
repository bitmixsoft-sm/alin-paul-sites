<?php

declare(strict_types=1);

namespace App\Services;

use App\ImageGet;
use Illuminate\Support\Str;

/**
 * Generates a low-detail preview of a priced photo, shown instead of the real file until
 * someone pays for it (see ImageGet::displayName()). Plain GD (already installed, confirmed via
 * `php -m` - no Intervention/Imagick dependency added for this one feature) rather than a proper
 * gaussian blur library: shrink the photo down to a few pixels wide, then scale it back up -
 * the classic "cheap thumbnail blur" trick, which destroys enough detail on its own that the
 * extra imagefilter() gaussian pass afterwards is just a smoothing touch-up, not the main effect.
 */
final class ImageBlurService
{
    private const TINY_WIDTH = 24;

    public function ensureBlurredCopy(ImageGet $image): void
    {
        if ($image->blurred_name !== null && $image->blurred_name !== '' && file_exists($this->publicPath($image->blurred_name))) {
            return;
        }

        $sourcePath = $this->publicPath($image->name);

        if (! file_exists($sourcePath)) {
            return;
        }

        $blurredName = $this->generate($sourcePath);

        if ($blurredName !== null) {
            $image->blurred_name = $blurredName;
            $image->save();
        }
    }

    private function generate(string $sourcePath): ?string
    {
        $info = @getimagesize($sourcePath);

        if ($info === false) {
            return null;
        }

        [$width, $height] = $info;

        $source = match ($info[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($sourcePath),
            IMAGETYPE_PNG => @imagecreatefrompng($sourcePath),
            IMAGETYPE_GIF => @imagecreatefromgif($sourcePath),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($sourcePath) : false,
            default => false,
        };

        if ($source === false || $width <= 0 || $height <= 0) {
            return null;
        }

        $tinyHeight = max(1, (int) round(self::TINY_WIDTH * $height / $width));
        $tiny = imagecreatetruecolor(self::TINY_WIDTH, $tinyHeight);
        imagecopyresampled($tiny, $source, 0, 0, 0, 0, self::TINY_WIDTH, $tinyHeight, $width, $height);

        $blurred = imagecreatetruecolor($width, $height);
        imagecopyresampled($blurred, $tiny, 0, 0, 0, 0, $width, $height, self::TINY_WIDTH, $tinyHeight);

        // Smooths the blocky upscale artifacts left by the resize trick above - a couple of
        // passes is enough since the heavy lifting was already done by the resize itself.
        imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);
        imagefilter($blurred, IMG_FILTER_GAUSSIAN_BLUR);

        $blurredName = 'blur_' . Str::random(24) . '.jpg';
        imagejpeg($blurred, $this->publicPath($blurredName), 65);

        imagedestroy($source);
        imagedestroy($tiny);
        imagedestroy($blurred);

        return $blurredName;
    }

    private function publicPath(string $name): string
    {
        return storage_path('app/public/images/' . $name);
    }
}
