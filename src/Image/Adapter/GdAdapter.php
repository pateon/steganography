<?php

declare(strict_types=1);

namespace Steganography\Image\Adapter;

use Steganography\Image\Color;
use GdImage;
use RuntimeException;

/**
 * GD adapter for image manipulation with LSB steganography support.
 * 
 * Provides image processing using the GD library as a fallback
 * when ImageMagick is not available. Optimized for PHP 8.5+.
 */
final class GdAdapter implements ImageAdapterInterface
{
    private GdImage $image;
    private int $width;
    private int $height;

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: {$path}");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new RuntimeException("Invalid image file: {$path}");
        }

        [$this->width, $this->height] = $info;
        $type = $info[2];

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => imagecreatefrompng($path),
            IMAGETYPE_GIF  => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            IMAGETYPE_BMP  => imagecreatefrombmp($path),
            default        => throw new RuntimeException(
                "Unsupported image type: {$path} (type: {$type})"
            ),
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image: {$path}");
        }
        
        $this->image = $image;

        // Ensure we can get true colors
        imagepalettetotruecolor($this->image);
        imagealphablending($this->image, false);
        imagesavealpha($this->image, true);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getColor(int $x, int $y): Color
    {
        $rgb = imagecolorat($this->image, $x, $y);
        
        return new Color(
            ($rgb >> 16) & 0xFF,
            ($rgb >> 8) & 0xFF,
            $rgb & 0xFF,
            ($rgb >> 24) & 0x7F  // GD alpha is 0-127
        );
    }

    public function setColor(int $x, int $y, Color $color): void
    {
        $col = imagecolorallocatealpha($this->image, $color->r, $color->g, $color->b, $color->a);
        imagesetpixel($this->image, $x, $y, $col);
        imagecolordeallocate($this->image, $col);
    }

    public function save(string $path): void
    {
        // Always save as PNG with no compression (0) to preserve lossless data
        imagepng($this->image, $path, 0);
    }

    public function render(): string
    {
        ob_start();
        imagepng($this->image, null, 0);
        return (string) ob_get_clean();
    }

    public function destroy(): void
    {
        if (isset($this->image)) {
            imagedestroy($this->image);
        }
    }
}
