<?php

declare(strict_types=1);

namespace Steganography\Exception;

/**
 * Exception thrown when the image is too small to hold the message.
 */
final class ImageTooSmallException extends SteganographyException
{
    public function __construct(
        public readonly int $needed,
        public readonly int $available
    ) {
        parent::__construct(
            "Image is too small to hold the message. Needed: {$needed} pixels, Available: {$available} pixels"
        );
    }
}
