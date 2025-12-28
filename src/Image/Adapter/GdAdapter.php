<?php

declare(strict_types=1);

namespace Steganography\Image\Adapter;

use Steganography\Image\Color;
use RuntimeException;

class GdAdapter implements ImageAdapterInterface
{
    private \GdImage $image;
    private int $width;
    private int $height;

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: $path");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new RuntimeException("Invalid image file: $path");
        }

        $this->width = $info[0];
        $this->height = $info[1];
        $type = $info[2];

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            default => false,
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image or unsupported type: $path (type: $type)");
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
        $r = ($rgb >> 16) & 0xFF;
        $g = ($rgb >> 8) & 0xFF;
        $b = $rgb & 0xFF;
        $a = ($rgb >> 24) & 0x7F; // GD alpha is 0-127

        return new Color($r, $g, $b, $a);
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
        return ob_get_clean();
    }

    public function destroy(): void
    {
        if (isset($this->image)) {
            imagedestroy($this->image);
        }
    }
}
