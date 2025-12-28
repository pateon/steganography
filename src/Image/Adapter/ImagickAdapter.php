<?php

declare(strict_types=1);

namespace Steganography\Image\Adapter;

use Steganography\Image\Color;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use ImagickException;
use RuntimeException;

/**
 * Imagick adapter for image manipulation with LSB steganography support.
 * 
 * Provides high-quality image processing using the ImageMagick library.
 * Optimized for PHP 8.5+ with modern syntax and performance improvements.
 */
final class ImagickAdapter implements ImageAdapterInterface
{
    private Imagick $image;
    private int $width;
    private int $height;

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: {$path}");
        }

        try {
            $this->image = new Imagick($path);
            
            // Ensure image is in TrueColor format (RGB)
            $this->image->setImageType(Imagick::IMGTYPE_TRUECOLOR);
            
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
        } catch (ImagickException $e) {
            throw new RuntimeException("Failed to load image: {$e->getMessage()}");
        }
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
        $pixel = $this->image->getImagePixelColor($x, $y);
        
        // PHP 8.5: ImagickPixel::getColor() signature changed
        // The boolean parameter is removed, now uses ImagickColorNormalization enum
        // Use getColorAsString() and parse RGB values for cross-version compatibility
        $colorString = $pixel->getColorAsString();
        
        // Parse "srgb(r,g,b)" or "rgb(r,g,b)" format
        if (preg_match('/\((\d+),(\d+),(\d+)/', $colorString, $matches)) {
            return new Color(
                (int) $matches[1],
                (int) $matches[2],
                (int) $matches[3],
                0
            );
        }
        
        // Fallback: use getColorValue for individual channels (0.0-1.0 range)
        return new Color(
            (int) round($pixel->getColorValue(Imagick::COLOR_RED) * 255),
            (int) round($pixel->getColorValue(Imagick::COLOR_GREEN) * 255),
            (int) round($pixel->getColorValue(Imagick::COLOR_BLUE) * 255),
            0
        );
    }

    public function setColor(int $x, int $y, Color $color): void
    {
        $pixel = new ImagickPixel("rgb({$color->r},{$color->g},{$color->b})");
        
        // Use ImagickDraw to set individual pixel (more reliable across versions)
        $draw = new ImagickDraw();
        $draw->setFillColor($pixel);
        $draw->point($x, $y);
        $this->image->drawImage($draw);
    }

    public function save(string $path): void
    {
        $this->image->setImageFormat('png');
        // Set compression to none for lossless output
        $this->image->setImageCompression(Imagick::COMPRESSION_NO);
        $this->image->writeImage($path);
    }

    public function render(): string
    {
        $this->image->setImageFormat('png');
        $this->image->setImageCompression(Imagick::COMPRESSION_NO);
        return $this->image->getImageBlob();
    }

    public function destroy(): void
    {
        if (isset($this->image)) {
            $this->image->clear();
            $this->image->destroy();
        }
    }
}
