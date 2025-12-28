<?php

declare(strict_types=1);

namespace Steganography\Image;

/**
 * Represents an RGBA color value with clamped channels.
 * 
 * R, G, B channels are clamped to 0-255 range.
 * Alpha channel is clamped to 0-127 (GD format).
 */
final class Color
{
    public function __construct(
        public int $r,
        public int $g,
        public int $b,
        public int $a = 0
    ) {
        // Clamp values to valid range using min/max
        $this->r = max(0, min(255, $r));
        $this->g = max(0, min(255, $g));
        $this->b = max(0, min(255, $b));
        $this->a = max(0, min(127, $a)); // GD alpha is 0-127
    }

    /**
     * Create a Color from a hex string (e.g., "#FF0000" or "FF0000").
     */
    public static function fromHex(string $hex): self
    {
        $hex = ltrim($hex, '#');
        
        return new self(
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
            strlen($hex) === 8 ? (int) hexdec(substr($hex, 6, 2)) : 0
        );
    }

    /**
     * Convert the color to a hex string.
     */
    public function toHex(bool $includeAlpha = false): string
    {
        $hex = sprintf('%02X%02X%02X', $this->r, $this->g, $this->b);
        
        if ($includeAlpha) {
            $hex .= sprintf('%02X', $this->a);
        }
        
        return "#{$hex}";
    }
}
