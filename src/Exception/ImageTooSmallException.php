<?php

namespace Steganography\Exception;

class ImageTooSmallException extends SteganographyException
{
    public function __construct(int $needed, int $available)
    {
        parent::__construct(
            "Image is too small to hold the message. Needed: $needed pixels, Available: $available pixels"
        );
    }
}
