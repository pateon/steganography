<?php

declare(strict_types=1);

namespace Steganography\Image\Adapter;

use Steganography\Image\Color;
use Imagick;
use ImagickDraw;
use ImagickPixel;
use RuntimeException;

class ImagickAdapter implements ImageAdapterInterface
{
    private Imagick $image;
    private int $width;
    private int $height;

    public function load(string $path): void
    {
        if (!file_exists($path)) {
            throw new RuntimeException("File not found: $path");
        }

        try {
            $this->image = new Imagick($path);
            
            // Ensure image is in TrueColor format (RGB)
            $this->image->setImageType(Imagick::IMGTYPE_TRUECOLOR);
            
            $this->width = $this->image->getImageWidth();
            $this->height = $this->image->getImageHeight();
        } catch (\ImagickException $e) {
            throw new RuntimeException("Failed to load image: " . $e->getMessage());
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
        // Pass false to get integer values (0-255) instead of normalized floats (0.0-1.0)
        $color = $pixel->getColor(false);
        
        return new Color(
            (int) $color['r'],
            (int) $color['g'],
            (int) $color['b'],
            0
        );
    }

    public function setColor(int $x, int $y, Color $color): void
    {
        $pixel = new ImagickPixel(sprintf('rgb(%d,%d,%d)', $color->r, $color->g, $color->b));
        
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
