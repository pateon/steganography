<?php

declare(strict_types=1);

namespace Steganography\Image;

/**
 * Represents an RGBA color value.
 */
class Color
{
    public function __construct(
        public int $r,
        public int $g,
        public int $b,
        public int $a = 0
    ) {
        // Clamp values to valid range
        $this->r = max(0, min(255, $r));
        $this->g = max(0, min(255, $g));
        $this->b = max(0, min(255, $b));
        $this->a = max(0, min(127, $a)); // GD alpha is 0-127
    }
}
